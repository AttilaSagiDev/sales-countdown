<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Space\SalesCountdown\Model\RuleFactory;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Model\Rule\Source\IsActive;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filter\FilterInput;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Space_SalesCountdown::save';

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $localeDate;

    /**
     * @var Date
     */
    private Date $dateFilter;

    /**
     * @var RuleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $ruleRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param TimezoneInterface $localeDate
     * @param Date $dateFilter
     * @param RuleFactory|null $ruleFactory
     * @param RuleRepositoryInterface|null $ruleRepository
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        TimezoneInterface $localeDate,
        Date $dateFilter,
        RuleFactory $ruleFactory = null,
        RuleRepositoryInterface $ruleRepository = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->localeDate = $localeDate;
        $this->dateFilter = $dateFilter;
        $this->ruleFactory = $ruleFactory
            ?: ObjectManager::getInstance()->get(RuleFactory::class);
        $this->ruleRepository = $ruleRepository
            ?: ObjectManager::getInstance()->get(RuleRepositoryInterface::class);
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return null|Redirect|ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(): null|Redirect|ResponseInterface|ResultInterface // NOSONAR
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getPostValue()) {
            /** @var \Space\SalesCountdown\Model\Rule $model */
            $model = $this->ruleFactory->create();

            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_sales_countdown_rule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPostValue();
                if (!$this->getRequest()->getParam('from_date')) {
                    $data['from_date'] = $this->localeDate->formatDate();
                }
                $filterValues = ['from_date' => $this->dateFilter];
                if ($this->getRequest()->getParam('to_date')) {
                    $filterValues['to_date'] = $this->dateFilter;
                }
                $inputFilter = new FilterInput(
                    $filterValues,
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model = $this->ruleRepository->getById((int)$id);
                }

                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->dataPersistor->set('sales_countdown_rule', $data);
                    $this->_redirect('sales_countdown/*/edit', ['id' => $model->getId()]);
                    return null;
                }

                /*echo '<pre>';
                print_r($data);*/

                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }

                unset($data['conditions_serialized']);

                $model->loadPost($data);

                /*print_r($model->getData());
                print_r($model->getConditions());

                die('stop');*/

                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->dataPersistor->set('sales_countdown_rule', $data);

                $this->ruleRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData(false);
                $this->dataPersistor->clear('sales_countdown_rule');

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('sales_countdown/*/edit', ['rule_id' => $model->getId()]);
                    return null;
                }
                $this->_redirect('sales_countdown/*/');
                return null;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $ruleData = $data ?? $this->getRequest()->getPostValue();
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($ruleData);
                $this->dataPersistor->set('sales_countdown_rule', $ruleData);
                $this->_redirect('sales_countdown/*/edit', ['rule_id' => $this->getRequest()->getParam('rule_id')]);
                return null;
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process and set the rule return
     *
     * @param Rule $rule
     * @param array $data
     * @param ResultInterface $resultRedirect
     * @return ResultInterface
     */
    private function processRuleReturn(
        Rule $rule,
        array $data,
        ResultInterface $resultRedirect
    ): ResultInterface {
        $redirect = $data['back'] ?? 'close';

        if ($redirect === 'continue') {
            $resultRedirect->setPath('*/*/edit', ['rule_id' => $rule->getId()]);
        } elseif ($redirect === 'close') {
            $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }
}
