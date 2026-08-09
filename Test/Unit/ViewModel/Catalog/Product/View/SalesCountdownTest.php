<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\ViewModel\Catalog\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Space\SalesCountdown\ViewModel\Catalog\Product\View\SalesCountdown;

class SalesCountdownTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManagerMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private ConfigInterface|MockObject $configMock;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $productMock;

    /**
     * @var SalesCountdown
     */
    private SalesCountdown $salesCountdown;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->productMock = $this->createMock(Product::class);

        $this->salesCountdown = new SalesCountdown(
            $this->registryMock,
            $this->storeManagerMock,
            $this->configMock
        );
    }

    public function testHasSpecialPriceToDateReturnsTrueWhenAttributeExists(): void
    {
        $attributeMock = $this->createMock(AttributeInterface::class);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('special_to_date')
            ->willReturn($attributeMock);

        $this->assertTrue($this->salesCountdown->hasSpecialPriceToDate());
    }

    public function testHasSpecialPriceToDateReturnsFalseWhenAttributeIsNull(): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('special_to_date')
            ->willReturn(null);

        $this->assertFalse($this->salesCountdown->hasSpecialPriceToDate());
    }

    public function testIsShowCountdownReturnsTrue(): void
    {
        $this->configMock->expects($this->once())
            ->method('isShowCountdown')
            ->willReturn(true);

        $this->assertTrue($this->salesCountdown->isShowCountdown());
    }

    public function testIsShowCountdownReturnsFalse(): void
    {
        $this->configMock->expects($this->once())
            ->method('isShowCountdown')
            ->willReturn(false);

        $this->assertFalse($this->salesCountdown->isShowCountdown());
    }

    public function testIsShowSecondsReturnsTrue(): void
    {
        $this->configMock->expects($this->once())
            ->method('isShowSeconds')
            ->willReturn(true);

        $this->assertTrue($this->salesCountdown->isShowSeconds());
    }

    public function testIsShowSecondsReturnsFalse(): void
    {
        $this->configMock->expects($this->once())
            ->method('isShowSeconds')
            ->willReturn(false);

        $this->assertFalse($this->salesCountdown->isShowSeconds());
    }

    public function testGetProductId(): void
    {
        $productId = 42;

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->assertEquals($productId, $this->salesCountdown->getProductId());
    }

    public function testGetStoreCode(): void
    {
        $storeCode = 'default';
        $storeMock = $this->createMock(StoreInterface::class);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn($storeCode);

        $this->assertEquals($storeCode, $this->salesCountdown->getStoreCode());
    }
}
