<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\ViewModel\Schema;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Breadcrumbs extends AbstractSchema
{
    private const XML_PATH_BREADCRUMBS_ENABLED = 'structured_data/breadcrumb/enabled';

    private Data $catalogData;

    private StoreManagerInterface $storeManager;

    private UrlInterface $urlBuilder;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        Data $catalogData
    ) {
        parent::__construct($scopeConfig, $serializer);

        $this->catalogData  = $catalogData;
        $this->storeManager = $storeManager;
        $this->urlBuilder   = $urlBuilder;
    }

    public function getStructuredData(): array
    {
        return [
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => self::SCHEMA_TYPE_BREADCRUMB_LIST,
            'itemListElement' => $this->getBreadcrumbItems()
        ];
    }

    public function isEnabled(): bool
    {
        return parent::isEnabled() &&
            $this->scopeConfig->isSetFlag(
                self::XML_PATH_BREADCRUMBS_ENABLED,
                ScopeInterface::SCOPE_STORE
            );
    }

    private function getBreadcrumbItems(): array
    {
        $items = [
            $this->generateListItem(
                0,
                [
                    'link' => $this->urlBuilder->getBaseUrl(),
                    'label' => __('Home')
                ]
            )
        ];

        $position = 0;

        foreach ($this->catalogData->getBreadcrumbPath() as $item) {
            $items[] = $this->generateListItem(++$position, $item);
        }

        return $items;
    }

    private function generateListItem(int $position, array $item): array
    {
        return [
            '@type' => self::SCHEMA_TYPE_LIST_ITEM,
            'position' => $position,
            'item' => [
                '@id' => $item['link'] ?? $this->urlBuilder->getCurrentUrl(),
                'name' => $item['label']
            ]
        ];
    }
}
