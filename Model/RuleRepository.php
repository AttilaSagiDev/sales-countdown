<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Space\SalesCountdown\Api\Data;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var ResourceModel\Rule
     */
    protected $ruleResource;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @param ResourceModel\Rule $ruleResource
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        ResourceModel\Rule $ruleResource,
        RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
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
            /** @var \Space\SalesCountdown\Model\Rule $rule */
            $rule = $this->ruleFactory->create();

            /* TODO: change to resource model after entity manager will be fixed */
            $rule->load($ruleId);
            if (!$rule->getId()) {
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
        if ($rule->getId()) {
            $rule = $this->getById($rule->getId())->addData($rule->getData());
        }

        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The "%1" rule was unable to be saved. Please try again.', $rule->getId())
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
