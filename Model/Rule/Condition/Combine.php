<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine as ConditionCombine;
use Space\SalesCountdown\Model\Rule\Condition\ProductFactory;
use Magento\Rule\Model\Condition\Context;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Combine extends ConditionCombine
{
    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @param Context $context
     * @param ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $conditionFactory,
        array $data = []
    ) {
        $this->_productFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType(\Space\SalesCountdown\Model\Rule\Condition\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions(): array
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Space\SalesCountdown\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Space\SalesCountdown\Model\Rule\Condition\Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );
        return $conditions;
    }

    /**
     * Collect validate attributes
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes(Collection $productCollection): static
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
