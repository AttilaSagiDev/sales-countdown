<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Controller\Adminhtml\Rule\Save;
use Space\SalesCountdown\Model\RuleFactory;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Space\SalesCountdown\Model\Rule;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as UnitObjectManager;
use Psr\Log\LoggerInterface;

class SaveTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private Context|MockObject $context;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private DataPersistorInterface|MockObject $dataPersistor;

    /**
     * @var TimezoneInterface|MockObject
     */
    private TimezoneInterface|MockObject $localeDate;

    /**
     * @var Date|MockObject
     */
    private Date|MockObject $dateFilter;

    /**
     * @var RuleFactory|MockObject
     */
    private RuleFactory|MockObject $ruleFactory;

    /**
     * @var RuleRepositoryInterface|MockObject
     */
    private RuleRepositoryInterface|MockObject $ruleRepository;

    /**
     * @var Request|MockObject
     */
    private Request|MockObject $request;

    /**
     * @var Response|MockObject
     */
    private Response|MockObject $response;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface|MockObject $messageManager;

    /**
     * @var EventManagerInterface|MockObject
     */
    private EventManagerInterface|MockObject $eventManager;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private ObjectManagerInterface|MockObject $objectManager;

    /**
     * @var RedirectFactory|MockObject
     */
    private RedirectFactory|MockObject $resultRedirectFactory;

    /**
     * @var Redirect|MockObject
     */
    private Redirect|MockObject $resultRedirect;

    /**
     * @var Session|MockObject
     */
    private Session|MockObject $session;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $logger;

    /**
     * @var Rule|MockObject
     */
    private Rule|MockObject $ruleModel;

    /**
     * @var ActionFlag|MockObject
     */
    private ActionFlag|MockObject $actionFlag;

    /**
     * @var RedirectInterface|MockObject
     */
    private RedirectInterface|MockObject $redirect;

    /**
     * @var Save|MockObject
     */
    private Save|MockObject $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->dataPersistor = $this->createMock(DataPersistorInterface::class);
        $this->localeDate = $this->createMock(TimezoneInterface::class);
        $this->dateFilter = $this->createMock(Date::class);
        $this->ruleFactory = $this->createPartialMock(RuleFactory::class, ['create']);
        $this->ruleRepository = $this->createMock(RuleRepositoryInterface::class);

        $this->request = $this->createMock(Request::class);
        $this->response = $this->createMock(Response::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->resultRedirect = $this->createMock(Redirect::class);
        $this->session = $this->createMock(Session::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->ruleModel = $this->createMock(Rule::class);
        $this->actionFlag = $this->createMock(ActionFlag::class);
        $this->redirect = $this->createMock(RedirectInterface::class);

        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
        $this->context->expects($this->any())->method('getSession')->willReturn($this->session);
        $this->context->expects($this->any())->method('getActionFlag')->willReturn($this->actionFlag);
        $this->context->expects($this->any())->method('getRedirect')->willReturn($this->redirect);

        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($class) {
                if ($class === LoggerInterface::class) {
                    return $this->logger;
                }
                if ($class === Session::class) {
                    return $this->session;
                }
                return $this->createMock($class);
            });

        $this->action = $this->getMockBuilder(Save::class)
            ->setConstructorArgs([
                'context' => $this->context,
                'dataPersistor' => $this->dataPersistor,
                'localeDate' => $this->localeDate,
                'dateFilter' => $this->dateFilter,
                'ruleFactory' => $this->ruleFactory,
                'ruleRepository' => $this->ruleRepository,
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();
    }

    public function testExecuteWithoutPostData(): void
    {
        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $result = $this->action->execute();
        $this->assertSame($this->resultRedirect, $result);
    }

    public function testExecuteSuccess(): void
    {
        $postData = [
            'rule_id' => 10,
            'from_date' => '2026-01-01',
            'to_date' => '2026-01-10',
            'rule' => ['conditions' => ['some_condition']]
        ];

        $this->request->expects($this->atLeastOnce())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnCallback(function ($key) use ($postData) {
                return $postData[$key] ?? null;
            });

        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->ruleModel);

        $this->ruleRepository->expects($this->once())
            ->method('getById')
            ->with(10)
            ->willReturn($this->ruleModel);

        $this->ruleModel->expects($this->once())
            ->method('validateData')
            ->willReturn(true);

        $this->ruleModel->expects($this->once())
            ->method('loadPost');

        $this->ruleRepository->expects($this->once())
            ->method('save')
            ->with($this->ruleModel);

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage');

        $this->dataPersistor->expects($this->once())
            ->method('clear')
            ->with('sales_countdown_rule');

        $this->action->expects($this->once())
            ->method('_redirect');

        $result = $this->action->execute();
        $this->assertNull($result);
    }

    public function testExecuteValidationFailure(): void
    {
        $postData = [
            'from_date' => '2026-01-01'
        ];

        $this->request->expects($this->atLeastOnce())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->ruleModel);

        $this->ruleModel->expects($this->once())
            ->method('validateData')
            ->willReturn(['Invalid rule name', 'Invalid date']);

        $this->messageManager->expects($this->exactly(2))
            ->method('addErrorMessage');

        $this->dataPersistor->expects($this->once())
            ->method('set')
            ->with('sales_countdown_rule', $this->isType('array'));

        $this->action->expects($this->once())
            ->method('_redirect');

        $result = $this->action->execute();
        $this->assertNull($result);
    }

    public function testExecuteExceptionHandling(): void
    {
        $postData = [
            'rule_id' => 5,
            'from_date' => '2026-01-01'
        ];

        $this->request->expects($this->atLeastOnce())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnCallback(function ($key) use ($postData) {
                return $postData[$key] ?? null;
            });

        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->ruleModel);

        $this->ruleRepository->expects($this->once())
            ->method('getById')
            ->with(5)
            ->willReturn($this->ruleModel);

        $this->ruleModel->expects($this->once())
            ->method('validateData')
            ->willReturn(true);

        $exception = new \Exception('Database error');
        $this->ruleRepository->expects($this->once())
            ->method('save')
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->action->expects($this->once())
            ->method('_redirect');

        $result = $this->action->execute();
        $this->assertNull($result);
    }
}
