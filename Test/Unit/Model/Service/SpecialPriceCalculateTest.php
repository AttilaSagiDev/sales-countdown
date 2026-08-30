<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Service\SpecialPriceCalculate;
use Space\SalesCountdown\Model\SpecialPriceCountdownFactory;
use Space\SalesCountdown\Model\SpecialPriceCountdown;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SpecialPriceCalculateTest extends TestCase
{
    /**
     * @var SpecialPriceCalculate
     */
    private SpecialPriceCalculate $model;

    /**
     * @var SpecialPriceCountdownFactory|MockObject
     */
    private SpecialPriceCountdownFactory|MockObject $countdownFactoryMock;

    /**
     * @var SpecialPriceCountdown|MockObject
     */
    private SpecialPriceCountdown|MockObject $countdownMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private ProductRepositoryInterface|MockObject $productRepositoryMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private ConfigInterface|MockObject $configMock;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaperMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->countdownFactoryMock = $this->getMockBuilder(SpecialPriceCountdownFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->countdownMock = $this->createMock(SpecialPriceCountdown::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->countdownFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->countdownMock);

        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->model = $objectManager->getObject(
            SpecialPriceCalculate::class,
            [
                'countdownFactory' => $this->countdownFactoryMock,
                'productRepository' => $this->productRepositoryMock,
                'config' => $this->configMock,
                'escaper' => $this->escaperMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testCalculateEndDateOnSale(): void
    {
        $productId = 1;
        $endDate = '2024-12-31';
        $message = 'Sale ends soon!';

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getCustomAttribute', 'getFinalPrice', 'getSpecialPrice', 'getSpecialToDate'])
            ->getMock();

        $productMock->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $productMock->expects($this->once())->method('getFinalPrice')->willReturn(10.0);
        $productMock->expects($this->once())->method('getSpecialPrice')->willReturn(10.0);
        $productMock->expects($this->once())->method('getSpecialToDate')->willReturn($endDate);

        $attributeMock = $this->createMock(AttributeInterface::class);
        $productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('special_to_date')
            ->willReturn($attributeMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->configMock->expects($this->once())->method('isShowCountdown')->willReturn(true);
        $this->configMock->expects($this->once())->method('getCountdownText')->willReturn($message);

        $this->countdownMock->expects($this->once())
            ->method('setCountdownEndDate')
            ->with($endDate)
            ->willReturnSelf();
        $this->countdownMock->expects($this->once())
            ->method('setCountdownMessage')
            ->with($message)
            ->willReturnSelf();

        $result = $this->model->calculateEndDate($productId);
        $this->assertSame($this->countdownMock, $result);
    }

    public function testCalculateEndDateNotOnSale(): void
    {
        $productId = 1;

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getCustomAttribute', 'getFinalPrice', 'getSpecialPrice'])
            ->getMock();

        $productMock->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $productMock->expects($this->once())->method('getFinalPrice')->willReturn(20.0);
        $productMock->expects($this->once())->method('getSpecialPrice')->willReturn(10.0);

        $attributeMock = $this->createMock(AttributeInterface::class);
        $productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('special_to_date')
            ->willReturn($attributeMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->countdownMock->expects($this->once())
            ->method('setCountdownEndDate')
            ->with('')
            ->willReturnSelf();
        $this->countdownMock->expects($this->once())
            ->method('setCountdownMessage')
            ->with('')
            ->willReturnSelf();

        $result = $this->model->calculateEndDate($productId);
        $this->assertSame($this->countdownMock, $result);
    }

    public function testCalculateEndDateLocalizedException(): void
    {
        $productId = 1;
        $exceptionMessage = 'Test Exception';

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $result = $this->model->calculateEndDate($productId);
        $this->assertSame($this->countdownMock, $result);
    }

    public function testCalculateEndDateGenericException(): void
    {
        $productId = 1;
        $exceptionMessage = 'Generic Error';

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception($exceptionMessage));

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exceptionMessage);

        $result = $this->model->calculateEndDate($productId);
        $this->assertSame($this->countdownMock, $result);
    }
}
