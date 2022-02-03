<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\StructuredData\Tests\ViewModel\Schema;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Elgentos\StructuredData\ViewModel\Schema\Breadcrumbs;

/**
 * @coversDefaultClass \Elgentos\StructuredData\ViewModel\Schema\Breadcrumbs
 */
class BreadcrumbsTest extends TestCase
{
    /**
     * @dataProvider breadcrumbsDataProvider
     */
    public function testGetStructuredData(
        array $breadcrumbs = []
    ): void {
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->expects(empty($breadcrumbs) ? self::never() : self::once())
            ->method('getCurrentUrl')
            ->willReturn('https://domain.com/');

        $urlBuilder->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn('https://domain.com/');

        $catalogData = $this->createMock(Data::class);
        $catalogData->expects(self::once())
            ->method('getBreadcrumbPath')
            ->willReturn($breadcrumbs);

        $subject = new Breadcrumbs(
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(Json::class),
            $urlBuilder,
            $catalogData
        );

        $result = $subject->getStructuredData();
        $this->assertCount(count($breadcrumbs) + 1, $result['itemListElement']);
    }

    public function breadcrumbsDataProvider(): array
    {
        return [
            [],
            [
                [
                    ['link' => 'https://domain.com', 'label' => 'Category 1'],
                    ['link' => 'https://domain.com', 'label' => 'Category 2'],
                    ['link' => 'https://domain.com', 'label' => 'Category 3'],
                    ['label' => 'Category 4'],
                ]
            ]
        ];
    }

    public function testIsEnabled()
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::any())
            ->method('isSetFlag')
            ->willReturn(true);

        $subject = new Breadcrumbs(
            $scopeConfig,
            $this->createMock(Json::class),
            $this->createMock(UrlInterface::class),
            $this->createMock(Data::class)
        );

        $this->assertTrue($subject->isEnabled());
    }
}
