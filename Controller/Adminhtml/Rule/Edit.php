<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Space\SalesCountdown\Model\Rule;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Space_SalesCountdown::sales_countdown_rule';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $ruleRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
    }

    /**
     * Edit CMS block
     *
     * @return Page|Redirect|ResponseInterface|ResultInterface
     */
    public function execute(): Redirect|ResultInterface|ResponseInterface|Page
    {
        $ruleId = (int)$this->getRequest()->getParam('rule_id');
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($ruleId) {
            $error = false;
            try {
                $rule = $this->ruleRepository->getById($ruleId);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $error = true;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e);
                $error = true;
            }

            if ($error) {
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $rule = $this->_objectManager->create(Rule::class);
        }

        $resultPage->addBreadcrumb(
            $ruleId ? __('Edit Rule') : __('New Rule'), // NOSONAR
            $ruleId ? __('Edit Rule') : __('New Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));
        $resultPage->getConfig()->getTitle()->prepend($rule->getId() ? $rule->getName() : __('New Rule'));

        return $resultPage;
    }
}
