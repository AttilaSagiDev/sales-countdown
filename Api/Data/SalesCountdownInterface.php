<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

interface SalesCountdownInterface
{
    /**
     * Constants for keys of data array
     */
    public const COUNTDOWN_END_DATE = 'countdown_end_date';

    /**
     * Get countdown end date
     *
     * @return string
     */
    public function getCountdownEndDate(): string;

    /**
     * Set countdown end date
     *
     * @param string $endDate
     * @return SalesCountdownInterface
     */
    public function setCountdownEndDate(string $endDate): SalesCountdownInterface;
}
