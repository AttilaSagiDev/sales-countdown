<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api;

use Space\SalesCountdown\Api\Data\SalesCountdownInterface;

interface CalculateCountdownInterface
{
    /**
     * Calculate countdown end date
     *
     * @param int $productId
     * @return \Space\SalesCountdown\Api\Data\SalesCountdownInterface
     */
    public function calculateEndDate(int $productId): SalesCountdownInterface;
}
