<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Magento\Framework\Model\AbstractModel;
use Space\SalesCountdown\Api\Data\SalesCountdownInterface;

class SalesCountdown extends AbstractModel implements SalesCountdownInterface
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
     * @return SalesCountdownInterface
     */
    public function setCountdownEndDate(string $endDate): SalesCountdownInterface
    {
        return $this->setData(self::COUNTDOWN_END_DATE, $endDate);
    }
}
