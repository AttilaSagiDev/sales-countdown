<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
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
    protected $_eventObject = 'countdown_rule_collection'; // NOSONAR

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
}
