<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Service;

use Space\SalesCountdown\Api\SpecialPriceCalculateInterface;
use Space\SalesCountdown\Model\SpecialPriceCountdownFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Api\Data\SpecialPriceCountdownInterface;
use Magento\Framework\Exception\LocalizedException;

class SpecialPriceCalculate implements SpecialPriceCalculateInterface
{
    /**
     * @var SpecialPriceCountdownFactory
     */
    private SpecialPriceCountdownFactory $countdownFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param SpecialPriceCountdownFactory $countdownFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigInterface $config
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        SpecialPriceCountdownFactory $countdownFactory,
        ProductRepositoryInterface $productRepository,
        ConfigInterface $config,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        $this->countdownFactory = $countdownFactory;
        $this->productRepository = $productRepository;
        $this->config = $config;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Calculate countdown end date
     *
     * @param int $productId
     * @return SpecialPriceCountdownInterface
     */
    public function calculateEndDate(int $productId): SpecialPriceCountdownInterface
    {
        $salesCountdown = $this->countdownFactory->create();
        try {
            $product = $this->productRepository->getById($productId);
            if ($product->getId() > 0
                && null !== $product->getCustomAttribute('special_to_date')
                && (float)$product->getFinalPrice() === (float)$product->getSpecialPrice()
            ) {
                $salesCountdown->setCountdownEndDate($product->getSpecialToDate());
                $salesCountdown->setCountdownMessage($this->getSalesMessage());
            } else {
                $salesCountdown->setCountdownEndDate('');
                $salesCountdown->setCountdownMessage('');
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $salesCountdown;
    }

    /**
     * Get sales message
     *
     * @return string
     */
    private function getSalesMessage(): string
    {
        return $this->config->isShowCountdown()
            ? $this->escaper->escapeHtml($this->config->getCountdownText(), ['strong'])
            : $this->escaper->escapeHtml($this->config->getNotificationText(), ['strong']);
    }
}
