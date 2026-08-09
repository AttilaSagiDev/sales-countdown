<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Ui\Component\Listing\Column;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Ui\Component\Listing\Column\Websites;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class WebsitesTest extends TestCase
{
    /**
     * @var Websites
     */
    private Websites $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManagerMock;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    private UiComponentFactory|MockObject $uiComponentFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);

        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getProcessor')
            ->willReturn($processorMock);

        $this->model = $objectManager->getObject(
            Websites::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'data' => []
            ]
        );
    }

    public function testPrepareSingleStoreMode(): void
    {
        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(true);

        $this->model->prepare();

        $config = $this->model->getData('config');
        $this->assertTrue($config['componentDisabled']);
    }

    public function testPrepareMultiStoreMode(): void
    {
        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(false);

        $this->model->prepare();

        $config = $this->model->getData('config');
        $this->assertArrayNotHasKey('componentDisabled', $config ?: []);
    }
}
