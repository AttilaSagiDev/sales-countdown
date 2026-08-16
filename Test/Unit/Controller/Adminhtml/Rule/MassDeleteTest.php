<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Controller\Adminhtml\Rule\MassDelete;
use Space\SalesCountdown\Model\ResourceModel\Rule\Collection;
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory;
use Space\SalesCountdown\Model\Rule;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MassDeleteTest extends TestCase
{
    /**
     * @var MassDelete
     */
    private MassDelete $controller;

    /**
     * @var Filter|MockObject
     */
    private Filter|MockObject $filterMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $collectionFactoryMock;

    /**
     * @var ResultFactory|MockObject
     */
    private ResultFactory|MockObject $resultFactoryMock;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private MessageManagerInterface|MockObject $messageManagerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->filterMock = $this->createMock(Filter::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->controller = $objectManagerHelper->getObject(
            MassDelete::class,
            [
                'context' => $contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testExecute(): void
    {
        $collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $ruleMock1 = $this->createMock(Rule::class);
        $ruleMock2 = $this->createMock(Rule::class);

        $items = [$ruleMock1, $ruleMock2];
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(count($items));

        $ruleMock1->expects($this->once())->method('delete');
        $ruleMock2->expects($this->once())->method('delete');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) have been deleted.', 2));

        $redirectMock = $this->createMock(Redirect::class);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($redirectMock, $this->controller->execute());
    }
}
