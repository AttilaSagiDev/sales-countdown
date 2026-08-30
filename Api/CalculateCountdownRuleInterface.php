<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api;

use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface;

interface CalculateCountdownRuleInterface
{
    /**
     * Calculate countdown rules for product
     *
     * @param int $productId
     * @return \Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface
     */
    public function calculateByProductId(int $productId) : SalesCountdownRuleInterface;
}
