<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Api\RuleRepositoryInterface;
use Space\SalesCountdown\Controller\Adminhtml\Rule\Delete;
use Space\SalesCountdown\Model\Rule;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DeleteTest extends TestCase
{
    /**
     * @var Delete
     */
    private Delete $controller;

    /**
     * @var RuleRepositoryInterface|MockObject
     */
    private RuleRepositoryInterface|MockObject $ruleRepositoryMock;

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

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->ruleRepositoryMock = $this->createMock(RuleRepositoryInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactoryMock);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->controller = $objectManagerHelper->getObject(
            Delete::class,
            [
                'context' => $contextMock,
                'ruleRepository' => $this->ruleRepositoryMock
            ]
        );
    }

    public function testExecuteSuccess(): void
    {
        $ruleId = 1;
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rule_id')
            ->willReturn($ruleId);

        $ruleMock = $this->createMock(Rule::class);
        $this->ruleRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($ruleId)
            ->willReturn($ruleMock);

        $this->ruleRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($ruleMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You deleted the rule.'));

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

    public function testExecuteNoRuleId(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('rule_id')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('We can\'t find a rule to delete.'));

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

    public function testExecuteException(): void
    {
        $ruleId = 1;
        $this->requestMock->method('getParam')->with('rule_id')->willReturn($ruleId);

        $this->ruleRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception('Error'));

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage');

        $redirectMock = $this->createMock(Redirect::class);
        $this->resultRedirectFactoryMock->method('create')->willReturn($redirectMock);
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['rule_id' => $ruleId])
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }
}
