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
     * @return Redirect|ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(): Redirect|ResponseInterface|ResultInterface // NOSONAR
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $data = $this->filterData($data);
            $rule = $this->ruleFactory->create();

            $ruleId = (int)$this->getRequest()->getParam('rule_id');
            if ($ruleId) {
                try {
                    $rule = $this->ruleRepository->getById($ruleId);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $rule->setData($data);

            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_sales_countdown_rule_prepare_save',
                    ['request' => $this->getRequest()]
                );

                $this->ruleRepository->save($rule);
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->dataPersistor->clear('sales_countdown_rule');
                return $this->processRuleReturn($rule, $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the rule.')
                );
            }

            $this->dataPersistor->set('sales_countdown_rule', $data);
            return $resultRedirect->setPath('*/*/edit', ['rule_id' => $ruleId]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Filter data
     *
     * @param array $data
     * @return array
     */
    private function filterData(array $data): array
    {
        if (isset($data['is_active']) && $data['is_active'] === 'true') {
            $data['is_active'] = Rule::STATUS_ENABLED;
        }
        if (empty($data['rule_id'])) {
            $data['rule_id'] = null;
        }

        if (!$this->getRequest()->getParam('from_date')) {
            $data['from_date'] = $this->localeDate->formatDate();
        }
        $filterValues = ['from_date' => $this->dateFilter];
        if ($this->getRequest()->getParam('to_date')) {
            $filterValues['to_date'] = $this->dateFilter;
        } else {
            $data['to_date'] = null;
        }

        $inputFilter = new FilterInput(
            $filterValues,
            [],
            $data
        );

        return $inputFilter->getUnescaped();
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
