<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Magento\Framework\Model\AbstractModel;
use Space\SalesCountdown\Api\Data\SpecialPriceCountdownInterface;

class SpecialPriceCountdown extends AbstractModel implements SpecialPriceCountdownInterface
{
    /**
     * Get countdown end date
     *
     * @return string
     */
    public function getCountdownEndDate(): string
    {
        return $this->getData(self::COUNTDOWN_END_DATE);
    }

    /**
     * Set countdown end date
     *
     * @param string $endDate
     * @return SpecialPriceCountdownInterface
     */
    public function setCountdownEndDate(string $endDate): SpecialPriceCountdownInterface
    {
        return $this->setData(self::COUNTDOWN_END_DATE, $endDate);
    }
}
