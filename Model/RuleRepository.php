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
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Space\SalesCountdown\Api\Data\RuleInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var ResourceRule
     */
    private ResourceRule $ruleResource;

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
     * @var array
     */
    private array $rules = [];

    /**
     * Constructor
     *
     * @param ResourceRule $ruleResource
     * @param RuleFactory $ruleFactory
     * @param RuleInterfaceFactory $dataRuleFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param HydratorInterface|null $hydrator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceRule $ruleResource,
        RuleFactory $ruleFactory,
        RuleInterfaceFactory $dataRuleFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        Data\RuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        ?HydratorInterface $hydrator = null
    ) {
        $this->ruleResource = $ruleResource;
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
        if (!isset($this->rules[$ruleId])) {
            $rule = $this->ruleFactory->create();

            $this->ruleResource->load($rule, $ruleId);
            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(
                    __('The rule with the "%1" ID wasn\'t found. Verify the ID and try again.', $ruleId)
                );
            }
            $this->rules[$ruleId] = $rule;
        }

        return $this->rules[$ruleId];
    }

    /**
     * Save rule data
     *
     * @param RuleInterface $rule
     * @return Rule
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(RuleInterface $rule): Rule
    {
        if ($rule->getRuleId() && $rule instanceof Rule && !$rule->getOrigData()) {
            $rule = $this->hydrator->hydrate($this->getById($rule->getId()), $this->hydrator->extract($rule));
        }

        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getRuleId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The "%1" rule was unable to be saved. Please try again.', $rule->getRuleId())
            );
        }
        return $rule;
    }

    /**
     * Delete rule
     *
     * @param RuleInterface $rule
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RuleInterface $rule): bool
    {
        try {
            $this->ruleResource->delete($rule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete rule by ID
     *
     * @param int $ruleId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $ruleId): bool
    {
        return $this->delete($this->getById($ruleId));
    }
}
