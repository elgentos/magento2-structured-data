<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\StructuredData\Tests\Model\Config\Source;

use ArrayIterator;
use Elgentos\StructuredData\Model\Config\Source\ProductAttributes;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Attribute;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Elgentos\StructuredData\Model\Config\Source\ProductAttributes
 */
class ProductAttributesTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::toOptionArray
     */
    public function testToOptionArray(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->expects(self::any())
            ->method('getIterator')
            ->willReturn(
                new ArrayIterator(
                    [$this->createMock(Attribute::class)]
                )
            );

        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->allowMockingUnknownTypes()
            ->setMethods(['create'])
            ->getMock();

        $collectionFactory->expects(self::once())
            ->method('create')
            ->willReturn($collection);

        $subject = new ProductAttributes($collectionFactory);

        $subject->toOptionArray();
    }
}
