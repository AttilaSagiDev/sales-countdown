<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

interface SpecialPriceCountdownInterface
{
    /**
     * Constants for keys of data array
     */
    public const COUNTDOWN_END_DATE = 'countdown_end_date';
    public const COUNTDOWN_MESSAGE = 'countdown_message';

    /**
     * Get countdown end date
     *
     * @return string
     */
    public function getCountdownEndDate(): string;

    /**
     * Get countdown message
     *
     * @return string
     */
    public function getCountdownMessage(): string;

    /**
     * Set countdown end date
     *
     * @param string $endDate
     * @return SpecialPriceCountdownInterface
     */
    public function setCountdownEndDate(string $endDate): SpecialPriceCountdownInterface;

    /**
     * Set countdown message
     *
     * @param string $message
     * @return SpecialPriceCountdownInterface
     */
    public function setCountdownMessage(string $message): SpecialPriceCountdownInterface;
}
