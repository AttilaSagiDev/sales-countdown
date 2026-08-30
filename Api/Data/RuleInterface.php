<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

interface RuleInterface
{
    /**
     * Constants for keys of data array
     */
    public const string TABLE_NAME = 'sales_countdown_rule';
    public const string RULE_ID = 'rule_id';
    public const string NAME = 'name';
    public const string MESSAGE = 'message';
    public const string DESCRIPTION = 'description';
    public const string FROM_DATE = 'from_date';
    public const string TO_DATE = 'to_date';
    public const string IS_ACTIVE = 'is_active';
    public const string SORT_ORDER = 'sort_order';

    /**
     * Get rule ID
     *
     * @return int|null
     */
    public function getRuleId(): ?int;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate(): ?string;

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate(): ?string;

    /**
     * Get is active
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * Set rule ID
     *
     * @param int $ruleId
     * @return RuleInterface
     */
    public function setRuleId(int $ruleId): RuleInterface;

    /**
     * Set name
     *
     * @param string $name
     * @return RuleInterface
     */
    public function setName(string $name): RuleInterface;

    /**
     * Set message
     *
     * @param string $message
     * @return RuleInterface
     */
    public function setMessage(string $message): RuleInterface;

    /**
     * Set description
     *
     * @param string $description
     * @return RuleInterface
     */
    public function setDescription(string $description): RuleInterface;

    /**
     * Set form date
     *
     * @param string $formDate
     * @return RuleInterface
     */
    public function setFromDate(string $formDate): RuleInterface;

    /**
     * Set to date
     *
     * @param string $toDate
     * @return RuleInterface
     */
    public function setToDate(string $toDate): RuleInterface;

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return RuleInterface
     */
    public function setIsActive(bool $isActive): RuleInterface;

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return RuleInterface
     */
    public function setSortOrder(int $sortOrder): RuleInterface;
}
