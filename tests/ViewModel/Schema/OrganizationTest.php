<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\StructuredData\Tests\ViewModel\Schema;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Theme\Block\Html\Header\Logo;
use PHPUnit\Framework\TestCase;
use Elgentos\StructuredData\ViewModel\Schema\Organization;

/**
 * @coversDefaultClass \Elgentos\StructuredData\ViewModel\Schema\Organization
 */
class OrganizationTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::getStructuredData
     * @covers ::getOrganizationAddress
     * @covers ::getCompanyName
     * @covers ::getCompanyEmail
     * @covers ::getCompanyTelephone
     * @covers ::getCompanyAddressCity
     * @covers ::getCompanyAddressRegion
     * @covers ::getCompanyAddressCountry
     * @covers ::getCompanyAddressPostalCode
     * @covers ::getCompanyAddressStreetAddress
     * @covers ::getWebsiteLogo
     */
    public function testGetStructuredData(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::any())
            ->method('getValue')
            ->willReturn('random string');

        $logo = $this->createMock(Logo::class);
        $logo->expects(self::once())
            ->method('getLogoSrc')
            ->willReturn('https://domain.com/logo.svg');

        $subject = new Organization(
            $scopeConfig,
            $this->createMock(Json::class),
            $logo
        );

        $subject->getStructuredData();
    }

    /**
     * @return void
     *
     * @covers ::isEnabled
     */
    public function testIsEnabled(): void
    {
        $subject = new Organization(
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(Json::class),
            $this->createMock(Logo::class)
        );

        $this->assertTrue($subject->isEnabled());
    }
}
