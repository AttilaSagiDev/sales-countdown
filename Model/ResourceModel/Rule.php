<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DataObject;
use Magento\Framework\App\ObjectManager;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Model\AbstractModel;
use Exception;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Rule extends AbstractResource
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = []; // NOSONAR

    /**
     * Constructor
     *
     * @param Context $context
     * @param EntityManager $entityManager
     * @param string|null $connectionName
     * @param DataObject|null $associatedEntityMap
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        string $connectionName = null,
        DataObject $associatedEntityMap = null
    ) {
        $this->entityManager = $entityManager;
        $this->_associatedEntitiesMap = $associatedEntityMap ?? ObjectManager::getInstance()
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
            ->get(\Space\SalesCountdown\Model\ResourceModel\Rule\AssociatedEntityMap::class)
            ->getData();
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
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(
        AbstractModel $object,
        mixed $value,
        $field = null
    ): static {
        $this->entityManager->load($object, $value);
        return $this;
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

    /**
     * Delete the object
     *
     * @param AbstractModel $object
     * @return $this
     * @throws Exception
     */
    public function delete(AbstractModel $object): static
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
