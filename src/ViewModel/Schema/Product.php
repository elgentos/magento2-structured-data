<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\ViewModel\Schema;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Review\Model\Rating\Option\Vote;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Product extends AbstractSchema
{
    public const XML_PATH_GTIN_ATTRIBUTE = 'structured_data/product/gtin',
        XML_PATH_REVIEW_LIMIT            = 'structured_data/product/review_limit',
        RATINGS_BEST_RATING              = 5,
        SCHEMA_AVAILABILITY_IN_STOCK     = 'https://schema.org/InStock',
        SCHEMA_AVAILABILITY_OUT_OF_STOCK = 'https://schema.org/OutOfStock',
        SCHEMA_ITEM_CONDITION_NEW        = 'https://schema.org/NewCondition';

    private Registry $registry;

    private Image $imageHelper;

    private CollectionFactory $reviewCollectionFactory;

    private ReviewFactory $reviewFactory;

    private StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer,
        Registry $registry,
        Image $imageHelper,
        CollectionFactory $reviewCollectionFactory,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($scopeConfig, $serializer);

        $this->registry                = $registry;
        $this->imageHelper             = $imageHelper;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->reviewFactory           = $reviewFactory;
        $this->storeManager            = $storeManager;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getStructuredData(): array
    {
        $product = $this->getProduct();

        if (!$product instanceof ProductModel || !$product->getId()) {
            return [];
        }

        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $data  = [
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => self::SCHEMA_TYPE_PRODUCT,
            'url' => $product->getProductUrl(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'description' => $product->getData('description'),
            'image' => $this->getProductImage($product),
            'offers' => [
                '@type' => self::SCHEMA_TYPE_OFFER,
                'url' => $product->getProductUrl(),
                'availability' => $product->isSalable()
                    ? self::SCHEMA_AVAILABILITY_IN_STOCK
                    : self::SCHEMA_AVAILABILITY_OUT_OF_STOCK,
                'price' => $product->getFinalPrice(),
                'sku' => $product->getSku(),
                'priceCurrency' => $store->getCurrentCurrencyCode(),
                'itemCondition' => self::SCHEMA_ITEM_CONDITION_NEW
            ]
        ];

        $attributeCode = $this->getGtinAttribute();
        $reviews       = $this->getProductReviews($product);

        if ($attributeCode) {
            $data['gtin'] = $product->getData($attributeCode);
        }

        if ($reviews->getSize()) {
            $data['review'] = [];

            foreach ($reviews as $review) {
                $data['review'][] = $this->addReviewEntity($review);
            }

            $data['aggregateRating'] = $this->getAggregateRatingValue($product);
        }

        return $data;
    }

    public function isEnabled(): bool
    {
        return true;
    }

    private function getProduct(): ?ProductModel
    {
        return $this->registry->registry('current_product');
    }

    private function getProductImage(ProductModel $product): string
    {
        return $this->imageHelper
            ->init($product, 'product_base_image')
            ->getUrl();
    }

    private function getGtinAttribute(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GTIN_ATTRIBUTE,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getProductReviews(ProductModel $product): Collection
    {
        $reviewLimit = $this->getReviewLimit();

        /** @var Collection $collection */
        $collection = $this->reviewCollectionFactory->create();
        $collection->addStatusFilter(Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product->getId())
            ->setDateOrder()
            ->addRateVotes();

        if ($reviewLimit > 0) {
            $collection->setPageSize($reviewLimit)
                ->setCurPage(1);
        }

        return $collection;
    }

    private function addReviewEntity(Review $review): array
    {
        $item = [
            '@type' => self::SCHEMA_TYPE_REVIEW,
            'author' => $review->getData('nickname'),
            'datePublished' => $review->getCreatedAt(),
            'reviewBody' => $review->getData('detail'),
            'name' => $review->getData('title')
        ];

        if ($review->getData('rating_votes')) {
            $item['reviewRating'] = [
                '@type' => self::SCHEMA_TYPE_RATING,
                'bestRating' => self::RATINGS_BEST_RATING,
                'ratingValue' => round($this->calculateRatingValue($review), 2),
                'worstRating' => 1
            ];
        }

        return $item;
    }

    private function calculateRatingValue(Review $review): float
    {
        $value = 0;

        /** @var Vote $vote */
        foreach ($review->getData('rating_votes') as $vote) {
            $value += $vote->getData('percent');
        }

        return self::RATINGS_BEST_RATING / 100 * ($value / count($review->getData('rating_votes')));
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getAggregateRatingValue(ProductModel $product): array
    {
        /** @var Review $review */
        $review = $this->reviewFactory->create();

        /** @var Store $store */
        $store = $this->storeManager->getStore();

        $review->getEntitySummary($product, $store->getId());

        /** @var DataObject $ratingSummary */
        $ratingSummary = $product->getData('rating_summary');

        return [
            '@type' => self::SCHEMA_TYPE_AGGREGATE_RATING,
            'ratingValue' => self::RATINGS_BEST_RATING / 100 * $ratingSummary->getData('rating_summary'),
            'reviewCount' => $ratingSummary->getData('reviews_count')
        ];
    }

    private function getReviewLimit(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
