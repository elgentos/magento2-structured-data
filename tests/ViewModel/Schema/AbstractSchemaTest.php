<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\StructuredData\Tests\ViewModel\Schema;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use Elgentos\StructuredData\ViewModel\Schema\AbstractSchema;

/**
 * @coversDefaultClass \Elgentos\StructuredData\ViewModel\Schema\AbstractSchema
 */
class AbstractSchemaTest extends TestCase
{
    /**
     * @dataProvider setStructuredData
     */
    public function testGetSerializedData(
        array $structuredData = []
    ): void {
        $subject = $this->createAbstractSchemaMock($structuredData);

        $this->assertEquals(
            json_encode($structuredData),
            $subject->getSerializedData()
        );
    }

    private function createAbstractSchemaMock(array $structuredData): AbstractSchema
    {
        $serializer = $this->createMock(Json::class);
        $serializer->expects(self::once())
            ->method('serialize')
            ->willReturn(json_encode($structuredData));

        return new class (
            $this->createMock(ScopeConfigInterface::class),
            $serializer
        ) extends AbstractSchema {
            public function __construct(ScopeConfigInterface $scopeConfig, Json $serializer)
            {
                parent::__construct($scopeConfig, $serializer);
            }

            public function getStructuredData(): array
            {
                return [];
            }
        };
    }

    public function setStructuredData(): array
    {
        return [
            [],
            [
                ['@type' => 'WebSite', '@context' => 'https://schema.org/WebSite']
            ]
        ];
    }
}
