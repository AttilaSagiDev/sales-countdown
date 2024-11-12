<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Magento\Rule\Model\AbstractModel;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Space\SalesCountdown\Model\Rule\Condition\CombineFactory;
use Space\SalesCountdown\Model\Rule\Action\CollectionFactory;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Action\Collection;

/**
 * @method array getWebsiteIds()
 * @method Rule setWebsiteIds(string $value)
 * @method Rule setCustomerGroupIds(string $value)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Rule extends AbstractModel implements RuleInterface, IdentityInterface // NOSONAR
{
    /**
     * Rule cache tag
     */
    public const CACHE_TAG = 'sales_countdown_rule';

    /**
     * Model cache tag for clear cache in after save and after delete
     *
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
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule'; // NOSONAR

    /**
     * @var CombineFactory
     */
    private CombineFactory $combineFactory;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $actionCollectionFactory;

    /**
     * @var ResourceRule
     */
    private ResourceRule $ruleResourceModel;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CombineFactory $combineFactory
     * @param CollectionFactory $actionCollectionFactory
     * @param TimezoneInterface $localeDate
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     * @param ResourceRule|null $ruleResourceModel
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CombineFactory $combineFactory,
        CollectionFactory $actionCollectionFactory,
        TimezoneInterface $localeDate,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null,
        ResourceRule $ruleResourceModel = null
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->ruleResourceModel = $ruleResourceModel ?: ObjectManager::getInstance()->get(ResourceRule::class);
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(ResourceRule::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get rule ID
     *
     * @return int|null
     */
    public function getRuleId(): ?int
    {
        return (int)$this->getData(self::RULE_ID);
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->getData(self::MESSAGE);
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
     * Get form date
     *
     * @return string|null
     */
    public function getFromDate(): ?string
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate(): ?string
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
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->getData(self::SORT_ORDER);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getRuleId(), self::CACHE_TAG . '_' . $this->getRuleId()];
    }

    /**
     * Set rule ID
     *
     * @param int $ruleId
     * @return RuleInterface
     */
    public function setRuleId(int $ruleId): RuleInterface
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
     * Set message
     *
     * @param string $message
     * @return RuleInterface
     */
    public function setMessage(string $message): RuleInterface
    {
        return $this->setData(self::MESSAGE, $message);
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
     * @param string $formDate
     * @return RuleInterface
     */
    public function setFromDate(string $formDate): RuleInterface
    {
        return $this->setData(self::FROM_DATE, $formDate);
    }

    /**
     * Set from date
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
     * Set sort order
     *
     * @param int $sortOrder
     * @return RuleInterface
     */
    public function setSortOrder(int $sortOrder): RuleInterface
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Getter for rule conditions collection
     *
     * @return Combine
     */
    public function getConditionsInstance(): Combine
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return Collection|null
     */
    public function getActionsInstance(): ?Collection
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Getter for conditions field set ID
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId(string $formName = ''): string
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getRuleId();
    }

    /**
     * Get sales countdown rule customer group Ids
     *
     * @return array|null
     */
    public function getCustomerGroupIds(): ?array
    {
        if (!$this->hasData('customer_group_ids')) {
            $customerGroupIds = $this->ruleResourceModel->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array)$customerGroupIds);
        }
        return $this->_getData('customer_group_ids');
    }
}
