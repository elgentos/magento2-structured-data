<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\StructuredData\Tests\ViewModel\Schema;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Search\Helper\Data as SearchHelper;
use PHPUnit\Framework\TestCase;
use Elgentos\StructuredData\ViewModel\Schema\Website;

/**
 * @coversDefaultClass \Elgentos\StructuredData\ViewModel\Schema\Website
 */
class WebsiteTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getStructuredData
     * @covers ::getPotentialActionData
     */
    public function testGetStructuredData(): void
    {
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn('https://domain.com');

        $searchHelper = $this->createMock(SearchHelper::class);
        $searchHelper->expects(self::once())
            ->method('getResultUrl')
            ->willReturn('https://domain.com/catalogsearch/result');

        $subject = new Website(
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(Json::class),
            $urlBuilder,
            $searchHelper,
        );

        $subject->getStructuredData();
    }

    public function testIsEnabled()
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::any())
            ->method('isSetFlag')
            ->willReturn(true);

        $subject = new Website(
            $scopeConfig,
            $this->createMock(Json::class),
            $this->createMock(UrlInterface::class),
            $this->createMock(SearchHelper::class),
        );

        $this->assertTrue($subject->isEnabled());
    }
}
