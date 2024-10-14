<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
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
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Edit CMS block
     *
     * @return Page|Redirect|ResponseInterface|ResultInterface
     */
    public function execute(): Redirect|ResultInterface|ResponseInterface|Page
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $model = $this->_objectManager->create(Rule::class);

        if ($ruleId) {
            $model->load($ruleId);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->addBreadcrumb(
            $ruleId ? __('Edit Rule') : __('New Rule'), // NOSONAR
            $ruleId ? __('Edit Rule') : __('New Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Rule'));

        return $resultPage;
    }
}
