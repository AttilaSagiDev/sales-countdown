<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Space\SalesCountdown\Model\Rule;
use Magento\Backend\Model\Session;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const string ADMIN_RESOURCE = 'Space_SalesCountdown::sales_countdown_rule';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $ruleRepository;

    /**
     * @var Registry|null
     */
    private ?Registry $coreRegistry = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RuleRepositoryInterface $ruleRepository,
        Registry $coreRegistry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Edit rule
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

        $data = $this->_objectManager->get(Session::class)->getPageData(true);
        if (!empty($data)) {
            $rule->addData($data);
        }
        $rule->getConditions()->setFormName('sales_countdown_rule_form');
        $rule->getConditions()->setJsFormObject(
            $rule->getConditionsFieldSetId($rule->getConditions()->getFormName())
        );

        $this->coreRegistry->register('current_sales_countdown_rule', $rule);

        $resultPage->addBreadcrumb(
            $ruleId ? __('Edit Rule') : __('New Rule'), // NOSONAR
            $ruleId ? __('Edit Rule') : __('New Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));
        $resultPage->getConfig()->getTitle()->prepend($rule->getRuleId() ? $rule->getName() : __('New Rule'));

        return $resultPage;
    }
}
