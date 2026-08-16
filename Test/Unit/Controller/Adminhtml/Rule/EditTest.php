<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Space\SalesCountdown\Controller\Adminhtml\Rule\Edit;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Model\Rule\Condition\Combine;

class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    private Edit $controller;

    /**
     * @var PageFactory|MockObject
     */
    private PageFactory|MockObject $resultPageFactoryMock;

    /**
     * @var RuleRepositoryInterface|MockObject
     */
    private RuleRepositoryInterface|MockObject $ruleRepositoryMock;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $coreRegistryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private RequestInterface|MockObject $requestMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private RedirectFactory|MockObject $resultRedirectFactoryMock;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private MessageManagerInterface|MockObject $messageManagerMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private ObjectManagerInterface|MockObject $objectManagerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->ruleRepositoryMock = $this->createMock(RuleRepositoryInterface::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactoryMock);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $contextMock->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->controller = $objectManagerHelper->getObject(
            Edit::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'ruleRepository' => $this->ruleRepositoryMock,
                'coreRegistry' => $this->coreRegistryMock
            ]
        );
    }

    public function testExecuteExistingRule(): void
    {
        $ruleId = 1;
        $ruleName = 'Holiday Sale';
        $sessionData = ['name' => $ruleName];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rule_id')
            ->willReturn((string)$ruleId);

        $resultPageMock = $this->createMock(Page::class);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $ruleMock = $this->createMock(Rule::class);
        $this->ruleRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($ruleId)
            ->willReturn($ruleMock);

        $sessionInstance = new class extends Session {
            /**
             * @var array|null
             */
            private ?array $pageData = null;

            public function __construct()
            {
            }

            public function setTestPageData(?array $data): void
            {
                $this->pageData = $data;
            }

            public function getPageData($clear = false): ?array
            {
                return $this->pageData;
            }
        };
        $sessionInstance->setTestPageData($sessionData);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Session::class)
            ->willReturn($sessionInstance);

        $ruleMock->expects($this->once())
            ->method('addData')
            ->with($sessionData)
            ->willReturnSelf();

        $conditionsMock = $this->getMockBuilder(Combine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setJsFormObject'])
            ->getMock();

        $ruleMock->method('getConditions')
            ->willReturn($conditionsMock);

        $ruleMock->expects($this->once())
            ->method('getConditionsFieldSetId')
            ->with('sales_countdown_rule_form')
            ->willReturn('rule_conditions_fieldset');

        $conditionsMock->expects($this->once())
            ->method('setJsFormObject')
            ->with('rule_conditions_fieldset')
            ->willReturnSelf();

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('current_sales_countdown_rule', $ruleMock);

        $resultPageMock->expects($this->once())
            ->method('addBreadcrumb')
            ->with(__('Edit Rule'), __('Edit Rule'))
            ->willReturnSelf();

        $pageConfigMock = $this->createMock(PageConfig::class);
        $titleMock = $this->createMock(Title::class);

        $resultPageMock->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($pageConfigMock);

        $pageConfigMock->expects($this->exactly(2))
            ->method('getTitle')
            ->willReturn($titleMock);

        $ruleMock->method('getRuleId')
            ->willReturn($ruleId);

        $ruleMock->method('getName')
            ->willReturn($ruleName);

        $titleMock->expects($this->exactly(2))
            ->method('prepend')
            ->willReturnCallback(function ($title) use ($ruleName) {
                static $step = 0;
                $step++;
                if ($step === 1) {
                    $this->assertEquals(__('Rules'), $title);
                } else {
                    $this->assertEquals($ruleName, $title);
                }
                return $title;
            });

        $this->assertSame($resultPageMock, $this->controller->execute());
        $this->assertEquals('sales_countdown_rule_form', $conditionsMock->getFormName());
    }

    public function testExecuteNewRule(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rule_id')
            ->willReturn(null);

        $resultPageMock = $this->createMock(Page::class);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->ruleRepositoryMock->expects($this->never())
            ->method('getById');

        $ruleMock = $this->createMock(Rule::class);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Rule::class)
            ->willReturn($ruleMock);

        $sessionInstance = new class extends Session {
            public function __construct()
            {
            }

            public function getPageData($clear = false): ?array
            {
                return null;
            }
        };

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Session::class)
            ->willReturn($sessionInstance);

        $ruleMock->expects($this->never())
            ->method('addData');

        $conditionsMock = $this->getMockBuilder(Combine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setJsFormObject'])
            ->getMock();

        $ruleMock->method('getConditions')
            ->willReturn($conditionsMock);

        $ruleMock->expects($this->once())
            ->method('getConditionsFieldSetId')
            ->with('sales_countdown_rule_form')
            ->willReturn('rule_conditions_fieldset');

        $conditionsMock->expects($this->once())
            ->method('setJsFormObject')
            ->with('rule_conditions_fieldset')
            ->willReturnSelf();

        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('current_sales_countdown_rule', $ruleMock);

        $resultPageMock->expects($this->once())
            ->method('addBreadcrumb')
            ->with(__('New Rule'), __('New Rule'))
            ->willReturnSelf();

        $pageConfigMock = $this->createMock(PageConfig::class);
        $titleMock = $this->createMock(Title::class);

        $resultPageMock->expects($this->exactly(2))
            ->method('getConfig')
            ->willReturn($pageConfigMock);

        $pageConfigMock->expects($this->exactly(2))
            ->method('getTitle')
            ->willReturn($titleMock);

        $ruleMock->method('getRuleId')
            ->willReturn(null);

        $titleMock->expects($this->exactly(2))
            ->method('prepend')
            ->willReturnCallback(function ($title) {
                static $step = 0;
                $step++;
                if ($step === 1) {
                    $this->assertEquals(__('Rules'), $title);
                } else {
                    $this->assertEquals(__('New Rule'), $title);
                }
                return $title;
            });

        $this->assertSame($resultPageMock, $this->controller->execute());
        $this->assertEquals('sales_countdown_rule_form', $conditionsMock->getFormName());
    }

    public function testExecuteLocalizedException(): void
    {
        $ruleId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rule_id')
            ->willReturn((string)$ruleId);

        $resultPageMock = $this->createMock(Page::class);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->ruleRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($ruleId)
            ->willThrowException(new LocalizedException(__('This rule no longer exists.')));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This rule no longer exists.'));

        $redirectMock = $this->createMock(Redirect::class);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    public function testExecuteGenericException(): void
    {
        $ruleId = 1;
        $exception = new \Exception('Database connection failed');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rule_id')
            ->willReturn((string)$ruleId);

        $resultPageMock = $this->createMock(Page::class);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->ruleRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($ruleId)
            ->willThrowException($exception);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception);

        $redirectMock = $this->createMock(Redirect::class);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }
}
