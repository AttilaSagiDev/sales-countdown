<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\ViewModel\Catalog\Product\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

class SalesCountdown implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * Constructor
     *
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */
    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager,
        ConfigInterface $config
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Has special price to date
     *
     * @return bool
     */
    public function hasSpecialPriceToDate(): bool
    {
        return null !== $this->getProduct()->getCustomAttribute('special_to_date');
    }

    /**
     * Is show countdown timer
     *
     * @return bool
     */
    public function isShowCountdown(): bool
    {
        return $this->config->isShowCountdown();
    }

    /**
     * Get product Id
     *
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->getProduct()->getId();
    }

    /**
     * Get store code
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();
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
