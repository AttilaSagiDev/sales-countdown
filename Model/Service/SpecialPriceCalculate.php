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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param SpecialPriceCountdownFactory $countdownFactory
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        SpecialPriceCountdownFactory $countdownFactory,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->countdownFactory = $countdownFactory;
        $this->productRepository = $productRepository;
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
        $timeStart = microtime(true);

        $salesCountdown = $this->countdownFactory->create();
        try {
            $product = $this->productRepository->getById($productId);
            if ($product->getId() > 0
                && null !== $product->getCustomAttribute('special_to_date')
                && (float)$product->getFinalPrice() === (float)$product->getSpecialPrice()) {
                $salesCountdown->setCountdownEndDate($product->getSpecialToDate());
            } else {
                $salesCountdown->setCountdownEndDate('');
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $timeEnd = microtime(true);
        $executionTime = ($timeEnd - $timeStart);
        $this->logger->debug('Sales Countdown:');
        $this->logger->debug('Time: ' . $executionTime);

        return $salesCountdown;
    }
}
