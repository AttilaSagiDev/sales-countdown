<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\ViewModel\Catalog\Product\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Catalog\Model\Product;

class SalesCountdown implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * Constructor
     *
     * @param Registry $registry
     * @param ConfigInterface $config
     */
    public function __construct(
        Registry $registry,
        ConfigInterface $config
    ) {
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * Get product special to date
     *
     * @return string
     */
    public function getSpecialToDate(): string
    {
        $specialToDate = '';

        if ($this->getProduct()->hasData('special_to_date')
            && (float)$this->getProduct()->getSpecialPrice() === (float)$this->getProduct()->getFinalPrice()
        ) {
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
