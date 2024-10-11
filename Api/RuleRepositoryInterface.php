<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api;

use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Exception\LocalizedException;

interface RuleRepositoryInterface
{
    /**
     * Retrieve rule
     *
     * @param int $ruleId
     * @return \Space\SalesCountdown\Api\Data\RuleInterface
     * @throws LocalizedException
     */
    public function getById(int $ruleId): RuleInterface;

    /**
     * Save rule
     *
     * @param \Space\SalesCountdown\Api\Data\RuleInterface $rule
     * @return RuleInterface
     * @throws LocalizedException
     */
    public function save(RuleInterface $rule): RuleInterface;
}
