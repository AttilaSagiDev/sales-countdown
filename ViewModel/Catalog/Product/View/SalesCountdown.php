<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\ViewModel\Catalog\Product\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;

class SalesCountdown implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * Constructor
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Get product special to date
     *
     * @return string
     */
    public function getSpecialToDate(): string
    {
        $specialToDate = '';

        if ($this->getProduct()->hasData('special_to_date')) {
            return $this->getProduct()->getSpecialToDate();
        }

        return $specialToDate;
    }

    /**
     * Get product
     *
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->registry->registry('product');
    }
}
