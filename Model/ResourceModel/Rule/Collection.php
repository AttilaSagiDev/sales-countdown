<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\ResourceModel\Rule;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Collection extends AbstractCollection
{
    /**
     * @var array
     */
    protected $_associatedEntitiesMap; // NOSONAR

    /**
     * Identifier field name for collection items
     *
     * @var string
     */
    protected $_idFieldName = 'rule_id'; // NOSONAR

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_countdown_rule_collection'; // NOSONAR

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'sales_countdown_rule'; // NOSONAR

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_associatedEntitiesMap = $this->getAssociatedEntitiesMap();
    }

    /**
     * Define resource model
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init(Rule::class, ResourceRule::class);
    }

    /**
     * Map data for associated entities
     *
     * @param string $entityType
     * @param string $objectField
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function mapAssociatedEntities(
        string $entityType,
        string $objectField
    ): void {
        if (!$this->_items) {
            return;
        }

        $entityInfo = $this->_getAssociatedEntityInfo($entityType);
        $ruleIdField = $entityInfo['rule_id_field'];
        $entityIds = $this->getColumnValues($ruleIdField);

        $select = $this->getConnection()->select()->from(
            $this->getTable($entityInfo['associations_table'])
        )->where(
            $ruleIdField . ' IN (?)',
            $entityIds
        );

        $associatedEntities = $this->getConnection()->fetchAll($select);

        array_map(function ($associatedEntity) use ($entityInfo, $ruleIdField, $objectField) {
            $item = $this->getItemByColumnValue($ruleIdField, $associatedEntity[$ruleIdField]);
            $itemAssociatedValue = $item->getData($objectField) === null ? [] : $item->getData($objectField);
            $itemAssociatedValue[] = $associatedEntity[$entityInfo['entity_id_field']];
            $item->setData($objectField, $itemAssociatedValue);
        }, $associatedEntities);
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _afterLoad(): static
    {
        $this->mapAssociatedEntities('website', 'website_ids');

        $this->setFlag('add_websites_to_result', false);
        return parent::_afterLoad();
    }

    /**
     * Getter for _associatedEntitiesMap property
     *
     * @return array
     * @deprecated 100.1.0
     */
    private function getAssociatedEntitiesMap()
    {
        if (!$this->_associatedEntitiesMap) {
            $this->_associatedEntitiesMap = \Magento\Framework\App\ObjectManager::getInstance()
                // phpstan:ignore
                ->get(\Space\SalesCountdown\Model\ResourceModel\Rule\AssociatedEntityMap::class)
                ->getData();
        }
        return $this->_associatedEntitiesMap;
    }
}
