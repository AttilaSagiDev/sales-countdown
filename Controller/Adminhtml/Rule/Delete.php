<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const string ADMIN_RESOURCE = 'Space_SalesCountdown::delete';

    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $ruleRepository;

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
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute(): Redirect|ResponseInterface|ResultInterface // NOSONAR
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $ruleId = (int)$this->getRequest()->getParam('rule_id');
        if ($ruleId) {
            try {
                $rule = $this->ruleRepository->getById($ruleId);
                $this->ruleRepository->delete($rule);
                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $ruleId]);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while trying to delete the rule.')
                );
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $ruleId]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
