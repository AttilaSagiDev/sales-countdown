<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Context;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var RuleRepositoryInterface
     */
    protected RuleRepositoryInterface $ruleRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        Context $context,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->context = $context;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Return rule ID
     *
     * @return int|null
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function getRuleId(): ?int
    {
        try {
            return $this->ruleRepository->getById(
                (int)$this->context->getRequest()->getParam('rule_id')
            )->getId();
        // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (NoSuchEntityException $e) { // NOSONAR
        }

        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
