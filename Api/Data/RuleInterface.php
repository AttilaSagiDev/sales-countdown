<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

interface RuleInterface
{
    /**
     * Constants for keys of data array
     */
    public const TABLE_NAME = 'sales_countdown_rule';
    public const RULE_ID = 'rule_id';
    public const NAME = 'name';
    public const DESCRIPTION = 'description';
    public const IS_ACTIVE = 'is_active';
    public const SORT_ORDER = 'sort_order';

    /**
     * Get rule ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

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
    public function setId(int $ruleId): RuleInterface;

    /**
     * Set name
     *
     * @param string $name
     * @return RuleInterface
     */
    public function setName(string $name): RuleInterface;

    /**
     * Set description
     *
     * @param string $description
     * @return RuleInterface
     */
    public function setDescription(string $description): RuleInterface;

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
