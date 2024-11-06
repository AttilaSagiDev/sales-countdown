<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Service;

use Space\SalesCountdown\Api\CalculateCountdownRuleInterface;
use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterfaceFactory;
use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface;

class CalculateCountdownRule implements CalculateCountdownRuleInterface
{
    /**
     * @var SalesCountdownRuleInterfaceFactory
     */
    private SalesCountdownRuleInterfaceFactory $salesCountdownRuleFactory;

    /**
     * Constructor
     *
     * @param SalesCountdownRuleInterfaceFactory $salesCountdownRuleFactory
     */
    public function __construct(
        SalesCountdownRuleInterfaceFactory $salesCountdownRuleFactory
    ) {
        $this->salesCountdownRuleFactory = $salesCountdownRuleFactory;
    }

    /**
     * Calculate countdown rules for product
     *
     * @param int $productId
     * @return SalesCountdownRuleInterface
     */
    public function calculateByProductId(int $productId): SalesCountdownRuleInterface
    {
        $salesCountdownRule = $this->salesCountdownRuleFactory->create();
        $salesCountdownRule->setCountdownEndDate('end date');
        $salesCountdownRule->setCountdownMessage('end message ' . $productId);

        return $salesCountdownRule;
    }
}
