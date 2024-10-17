<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Model\AbstractModel;
use Exception;

class Rule extends AbstractDb
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param EntityManager $entityManager
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        string $connectionName = null
    ) {
        $this->entityManager = $entityManager;
        parent::__construct($context, $connectionName);
    }

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

    /**
     * Save an object
     *
     * @param AbstractModel $object
     * @return $this
     * @throws Exception
     */
    public function save(AbstractModel $object): static
    {
        $this->entityManager->save($object);

        return $this;
    }
}
