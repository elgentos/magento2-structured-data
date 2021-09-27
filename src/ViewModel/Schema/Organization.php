<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\ViewModel\Schema;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Header\Logo;

class Organization extends AbstractSchema
{
    private Logo $logo;

    private UrlInterface $urlBuilder;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer,
        UrlInterface $urlBuilder,
        Logo $logo
    ) {
        parent::__construct($scopeConfig, $serializer);

        $this->urlBuilder = $urlBuilder;
        $this->logo       = $logo;
    }

    public function getStructuredData(): array
    {
        return [
            '@context' => self::SCHEMA_CONTEXT,
            '@type' => self::SCHEMA_TYPE_ORGANIZATION,
            'name' => $this->getCompanyName(),
            'email' => $this->getCompanyEmail(),
            'telephone' => $this->getCompanyTelephone(),
            'logo' => $this->getWebsiteLogo(),
            'url' => $this->urlBuilder->getBaseUrl(),
            'address' => $this->getOrganizationAddress()
        ];
    }

    public function isEnabled(): bool
    {
        return true;
    }

    private function getOrganizationAddress(): array
    {
        return [
            '@type' => self::SCHEMA_TYPE_POSTAL_ADDRESS,
            'addressLocality' => $this->getCompanyAddressCity(),
            'addressRegion' => $this->getCompanyAddressRegion(),
            'addressCountry' => $this->getCompanyAddressCountry(),
            'postalCode' => $this->getCompanyAddressPostalCode(),
            'streetAddress' => $this->getCompanyAddressStreetAddress()
        ];
    }

    private function getCompanyName(): string
    {
        return (string) $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getCompanyEmail(): string
    {
        return (string) $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getCompanyTelephone(): string
    {
        return (string) $this->scopeConfig->getValue(
            'general/store_information/phone',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getWebsiteLogo(): string
    {
        return $this->logo->getLogoSrc();
    }

    private function getCompanyAddressCity(): string
    {
        return (string) $this->scopeConfig->getValue(
            'general/store_information/city',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getCompanyAddressRegion(): ?string
    {
        return $this->scopeConfig->getValue(
            'general/store_information/region_id',
            ScopeInterface::SCOPE_STORE
        ) ?: null;
    }

    private function getCompanyAddressCountry(): string
    {
        return (string) $this->scopeConfig->getValue(
            'general/store_information/country_id',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getCompanyAddressPostalCode(): string
    {
        return (string) $this->scopeConfig->getValue(
            'general/store_information/postcode',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getCompanyAddressStreetAddress(): string
    {
        return trim(
            $this->scopeConfig->getValue(
                'general/store_information/street_line1',
                ScopeInterface::SCOPE_STORE
            ) . ' ' .
            $this->scopeConfig->getValue(
                'general/store_information/street_line2',
                ScopeInterface::SCOPE_STORE
            )
        );
    }
}
