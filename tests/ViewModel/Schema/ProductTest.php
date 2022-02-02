<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\StructuredData\Tests\ViewModel\Schema;

use ArrayIterator;
use Elgentos\StructuredData\ViewModel\Schema\Product;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Review\Model\Rating\Option\Vote;
use Magento\Review\Model\Review;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\ReviewFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Elgentos\StructuredData\ViewModel\Schema\Product
 */
class ProductTest extends TestCase
{
    public function testIsEnabled(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::any())
            ->method('isSetFlag')
            ->willReturn(true);

        $subject = new Product(
            $scopeConfig,
            $this->createMock(Json::class),
            $this->createMock(Registry::class),
            $this->createMock(Image::class),
            $this->createReviewCollectionFactoryMock(false),
            $this->createReviewFactoryMock(false),
            $this->createMock(StoreManagerInterface::class)
        );

        $this->assertTrue($subject->isEnabled());
    }

    /**
     * @dataProvider structuredDataDataProvider
     *
     * @throws NoSuchEntityException
     */
    public function testGetStructuredData(
        bool $hasValidProduct = true,
        bool $productIsSalable = true
    ): void {
        $registry = $this->createMock(Registry::class);
        $registry->expects(self::once())
            ->method('registry')
            ->willReturn(
                $this->createProductModelMock($hasValidProduct, $productIsSalable)
            );

        $imageHelper = $this->createMock(Image::class);
        $imageHelper->expects($hasValidProduct ? self::once() : self::never())
            ->method('init')
            ->willReturn($imageHelper);

        $imageHelper->expects($hasValidProduct ? self::once() : self::never())
            ->method('getUrl')
            ->willReturn('https://domain.com/image.jpg');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($hasValidProduct ? self::any() : self::never())
            ->method('getStore')
            ->willReturn($this->createMock(Store::class));

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::any())
            ->method('getValue')
            ->withConsecutive(
                [Product::XML_PATH_ATTRIBUTE_GTIN, ScopeInterface::SCOPE_STORE],
                [Product::XML_PATH_ATTRIBUTE_BRAND, ScopeInterface::SCOPE_STORE],
                [Product::XML_PATH_REVIEW_LIMIT, ScopeInterface::SCOPE_STORE]
            )
            ->willReturnOnConsecutiveCalls('gtin', 'brand', 3);

        $subject = new Product(
            $scopeConfig,
            $this->createMock(Json::class),
            $registry,
            $imageHelper,
            $this->createReviewCollectionFactoryMock($hasValidProduct),
            $this->createReviewFactoryMock($hasValidProduct),
            $storeManager
        );

        $subject->getStructuredData();
    }

    private function createReviewCollectionFactoryMock(bool $factoryIsCalled = true): CollectionFactory
    {
        $collection = $this->createReviewCollectionMock($factoryIsCalled);
        $review     = $this->createMock(Review::class);
        $review->expects(self::any())
            ->method('getData')
            ->willReturn([$this->createMock(Vote::class)]);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$review]));

        $factory = $this->getMockBuilder(CollectionFactory::class)
            ->allowMockingUnknownTypes()
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $factory->expects($factoryIsCalled ? self::once() : self::never())
            ->method('create')
            ->willReturn($collection);

        return $factory;
    }

    private function createReviewFactoryMock(bool $factoryIsCalled = true): ReviewFactory
    {
        $reviewModel = $this->createMock(Review::class);
        $reviewModel->expects($factoryIsCalled ? self::any() : self::never())
            ->method('getEntitySummary');

        $factory = $this->getMockBuilder(ReviewFactory::class)
            ->allowMockingUnknownTypes()
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $factory->expects($factoryIsCalled ? self::any() : self::never())
            ->method('create')
            ->willReturn($reviewModel);

        return $factory;
    }

    private function createProductModelMock(
        bool $hasValidProduct,
        bool $productIsSalable
    ): ?ProductModel {
        if (!$hasValidProduct) {
            return null;
        }

        $productModel = $this->createMock(ProductModel::class);
        $productModel->expects(self::any())
            ->method('getId')
            ->willReturn(1);

        $productModel->expects(self::once())
            ->method('isSalable')
            ->willReturn($productIsSalable);

        $productModel->expects(self::any())
            ->method('getData')
            ->withConsecutive(
                ['description'],
                ['gtin'],
                ['brand'],
                ['rating_summary']
            )
            ->willReturnOnConsecutiveCalls(
                'description',
                'gtin',
                'brand',
                new DataObject()
            );

        return $productModel;
    }

    public function structuredDataDataProvider(): array
    {
        return [
            [],
            [false]
        ];
    }

    private function createReviewCollectionMock(bool $factoryIsCalled): MockObject
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('addStatusFilter')
            ->willReturn($collection);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('addEntityFilter')
            ->willReturn($collection);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('setDateOrder')
            ->willReturn($collection);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('addRateVotes')
            ->willReturn($collection);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('getSize')
            ->willReturn(2);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('setPageSize')
            ->willReturn($collection);

        $collection->expects($factoryIsCalled ? self::once() : self::never())
            ->method('setCurPage')
            ->willReturn($collection);

        return $collection;
    }
}
