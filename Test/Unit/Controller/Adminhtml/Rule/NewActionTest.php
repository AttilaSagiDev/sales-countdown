<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Controller\Adminhtml\Rule\NewAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class NewActionTest extends TestCase
{
    /**
     * @var NewAction
     */
    private NewAction $controller;

    /**
     * @var ForwardFactory|MockObject
     */
    private ForwardFactory|MockObject $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    private Forward|MockObject $resultForwardMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->resultForwardMock = $this->createMock(Forward::class);

        $contextMock = $this->createMock(Context::class);

        $this->controller = $objectManagerHelper->getObject(
            NewAction::class,
            [
                'context' => $contextMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock
            ]
        );
    }

    public function testExecute(): void
    {
        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForwardMock);

        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('edit')
            ->willReturnSelf();

        $this->assertSame($this->resultForwardMock, $this->controller->execute());
    }
}
