<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Controller\Adminhtml\Rule\Index;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private Index $controller;

    /**
     * @var PageFactory|MockObject
     */
    private PageFactory|MockObject $resultPageFactoryMock;

    /**
     * @var Page|MockObject
     */
    private Page|MockObject $resultPageMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private ObjectManagerInterface|MockObject $objectManagerMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private DataPersistorInterface|MockObject $dataPersistorMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->resultPageMock = $this->createMock(Page::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->dataPersistorMock = $this->createMock(DataPersistorInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->controller = $objectManagerHelper->getObject(
            Index::class,
            [
                'context' => $contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    public function testExecute(): void
    {
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Space_SalesCountdown::sales_countdown')
            ->willReturnSelf();

        $this->resultPageMock->expects($this->exactly(2))
            ->method('addBreadcrumb')
            ->willReturnSelf();

        $configMock = $this->createMock(Config::class);
        $titleMock = $this->createMock(Title::class);

        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $configMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);

        $titleMock->expects($this->once())
            ->method('prepend')
            ->with(__('Sales Countdown Rules'));

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(DataPersistorInterface::class)
            ->willReturn($this->dataPersistorMock);

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('sales_countdown_rule');

        $this->assertSame($this->resultPageMock, $this->controller->execute());
    }
}
