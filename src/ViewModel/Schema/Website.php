<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\ViewModel\Schema;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Helper\Data as SearchHelper;

class Website extends AbstractSchema
{
    private const XML_PATH_WEBSITE_ENABLED = 'structured_data/homepage/website';

    private SearchHelper $searchHelper;

    private UrlInterface $urlBuilder;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer,
        UrlInterface $urlBuilder,
        SearchHelper $searchHelper
    ) {
        parent::__construct($scopeConfig, $serializer);

        $this->urlBuilder   = $urlBuilder;
        $this->searchHelper = $searchHelper;
    }

    public function getStructuredData(): array
    {
        return [
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => self::SCHEMA_TYPE_WEBSITE,
            'url' => $this->urlBuilder->getBaseUrl(),
            'potentialAction' => $this->getPotentialActionData()
        ];
    }

    private function getPotentialActionData(): array
    {
        return [
            '@type' => self::SCHEMA_TYPE_SEARCH_ACTION,
            'target' => $this->searchHelper->getResultUrl() . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ];
    }

    public function isEnabled(): bool
    {
        return parent::isEnabled() &&
            $this->scopeConfig->isSetFlag(
                self::XML_PATH_WEBSITE_ENABLED,
                ScopeInterface::SCOPE_STORE
            );
    }
}
