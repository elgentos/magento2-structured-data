<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\Model\Config\Source;

use Magento\Eav\Model\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class ProductAttributes implements ArrayInterface
{
    private CollectionFactory $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $items      = [
            [
                'value' => '',
                'label' => __('-- Select an Attribute --')
            ]
        ];

        /** @var Attribute $attribute */
        foreach ($collection as $attribute) {
            $items[] = [
                'value' => $attribute->getId(),
                'label' => $attribute->getDefaultFrontendLabel()
            ];
        }

        return $items;
    }
}
