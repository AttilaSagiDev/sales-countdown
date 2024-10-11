<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Space\SalesCountdown\Api\Data;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Space\SalesCountdown\Model\RuleFactory;
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Space\SalesCountdown\Api\Data\RuleInterfaceFactory;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var ResourceRule
     */
    private ResourceRule $resourceRule;

    /**
     * @var RuleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * @var RuleCollectionFactory
     */
    private RuleCollectionFactory $ruleCollectionFactory;

    /**
     * @var Data\RuleSearchResultsInterfaceFactory
     */
    private Data\RuleSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private DataObjectProcessor $dataObjectProcessor;

    /**
     * @var RuleInterfaceFactory
     */
    private RuleInterfaceFactory $dataRuleFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * Constructor
     *
     * @param ResourceRule $resource
     * @param RuleFactory $ruleFactory
     * @param RuleInterfaceFactory $dataRuleFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param HydratorInterface|null $hydrator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceRule $resource,
        RuleFactory $ruleFactory,
        RuleInterfaceFactory $dataRuleFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        Data\RuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        ?HydratorInterface $hydrator = null
    ) {
        $this->resource = $resource;
        $this->ruleFactory = $ruleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRuleFactory = $dataRuleFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor ?:
            ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
        $this->hydrator = $hydrator ?? ObjectManager::getInstance()->get(HydratorInterface::class);
    }

    /**
     * Load rule data by ID
     *
     * @param int $ruleId
     * @return Rule
     * @throws NoSuchEntityException
     */
    public function getById(int $ruleId): Rule
    {
        $rule = $this->ruleFactory->create();
        $this->resource->load($rule, $ruleId);
        if (!$rule->getId()) {
            throw new NoSuchEntityException(__('The rule with the "%1" ID doesn\'t exist.', $ruleId));
        }
        return $rule;
    }

    /**
     * Save rule data
     *
     * @param Data\RuleInterface $rule
     * @return Rule
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(Data\RuleInterface $rule): Rule
    {
        if ($rule->getId() && $rule instanceof Rule && !$rule->getOrigData()) {
            $rule = $this->hydrator->hydrate($this->getById($rule->getId()), $this->hydrator->extract($rule));
        }

        try {
            $this->resource->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rule;
    }
}
