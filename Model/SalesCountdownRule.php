<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Magento\Framework\Model\AbstractModel;
use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface;

class SalesCountdownRule extends AbstractModel implements SalesCountdownRuleInterface
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
     * Get countdown message
     *
     * @return string
     */
    public function getCountdownMessage(): string
    {
        return $this->getData(self::COUNTDOWN_MESSAGE);
    }

    /**
     * Set countdown end date
     *
     * @param string $endDate
     * @return SalesCountdownRuleInterface
     */
    public function setCountdownEndDate(string $endDate): SalesCountdownRuleInterface
    {
        return $this->setData(self::COUNTDOWN_END_DATE, $endDate);
    }

    /**
     * Set countdown message
     *
     * @param string $message
     * @return SalesCountdownRuleInterface
     */
    public function setCountdownMessage(string $message): SalesCountdownRuleInterface
    {
        return $this->setData(self::COUNTDOWN_MESSAGE, $message);
    }
}
