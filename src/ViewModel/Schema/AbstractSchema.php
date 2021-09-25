<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\ViewModel\Schema;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractSchema implements SchemaInterface, ArgumentInterface
{
    protected const SCHEMA_CONTEXT   = 'https://schema.org/',
        SCHEMA_TYPE_ORGANIZATION     = 'Organization',
        SCHEMA_TYPE_POSTAL_ADDRESS   = 'PostalAddress',
        SCHEMA_TYPE_WEBSITE          = 'WebSite',
        SCHEMA_TYPE_SEARCH_ACTION    = 'SearchAction',
        SCHEMA_TYPE_PRODUCT          = 'Product',
        SCHEMA_TYPE_OFFER            = "Offer",
        SCHEMA_TYPE_REVIEW           = 'Review',
        SCHEMA_TYPE_RATING           = 'Rating',
        SCHEMA_TYPE_AGGREGATE_RATING = 'AggregateRating',
        SCHEMA_TYPE_BREADCRUMB_LIST  = 'BreadcrumbList',
        SCHEMA_TYPE_LIST_ITEM        = 'ListItem';

    private const XML_PATH_ENABLED = 'structured_data/general/enabled';

    private Json $serializer;

    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer  = $serializer;
    }

    public function getSerializedData(): string
    {
        return $this->serializer->serialize($this->getStructuredData());
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
