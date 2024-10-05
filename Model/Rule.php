<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Magento\Framework\Model\AbstractModel;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Rule extends AbstractModel implements RuleInterface, IdentityInterface
{
    /**
     * Rule cache tag
     */
    public const string CACHE_TAG = 'sales_countdown_rule';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG; // NOSONAR

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_countdown_rule'; // NOSONAR

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init(ResourceRule::class);
    }

    /**
     * Get rule ID
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get from date
     *
     * @return string
     */
    public function getFromDate(): string
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * Get to date
     *
     * @return string
     */
    public function getToDate(): string
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * Get is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Get conditions serialized
     *
     * @return string|null
     */
    public function getConditionsSerialized(): ?string
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set rule ID
     *
     * @param int $ruleId
     * @return RuleInterface
     */
    public function setId($ruleId): RuleInterface
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RuleInterface
     */
    public function setName(string $name): RuleInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RuleInterface
     */
    public function setDescription(string $description): RuleInterface
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return RuleInterface
     */
    public function setFromDate(string $fromDate): RuleInterface
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * Set to date
     *
     * @param string $toDate
     * @return RuleInterface
     */
    public function setToDate(string $toDate): RuleInterface
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return RuleInterface
     */
    public function setIsActive(bool $isActive): RuleInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return RuleInterface
     */
    public function setConditionsSerialized(string $conditionsSerialized): RuleInterface
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return RuleInterface
     */
    public function setSortOrder(int $sortOrder): RuleInterface
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }
}
