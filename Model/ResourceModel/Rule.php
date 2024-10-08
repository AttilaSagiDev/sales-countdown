<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Space\SalesCountdown\Api\Data\RuleInterface;

class Rule extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void
    {
        $this->_init(RuleInterface::TABLE_NAME, RuleInterface::RULE_ID);
    }
}
