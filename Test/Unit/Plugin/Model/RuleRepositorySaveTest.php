<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Plugin\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Plugin\Model\RuleRepositorySave;
use Space\SalesCountdown\Model\Catalog\Product\CatalogProducts;
use Space\SalesCountdown\Model\Catalog\Product\HandleRuleProduct;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Model\RuleRepository;
use Space\SalesCountdown\Api\Data\RuleInterface;

class RuleRepositorySaveTest extends TestCase
{
    /**
     * @var RuleRepositorySave
     */
    private RuleRepositorySave $model;

    /**
     * @var CatalogProducts|MockObject
     */
    private CatalogProducts|MockObject $catalogProductsMock;

    /**
     * @var HandleRuleProduct|MockObject
     */
    private HandleRuleProduct|MockObject $handleRuleProductMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->catalogProductsMock = $this->createMock(CatalogProducts::class);
        $this->handleRuleProductMock = $this->createMock(HandleRuleProduct::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->model = new RuleRepositorySave(
            $this->catalogProductsMock,
            $this->handleRuleProductMock,
            $this->loggerMock
        );
    }

    public function testAfterSaveInactiveRule(): void
    {
        $subjectMock = $this->createMock(RuleRepository::class);
        $ruleMock = $this->createMock(RuleInterface::class);
        $ruleMock->expects($this->once())->method('isActive')->willReturn(false);

        $this->catalogProductsMock->expects($this->never())->method('getMatchingProductIds');

        $result = $this->model->afterSave($subjectMock, $ruleMock, $ruleMock);
        $this->assertSame($ruleMock, $result);
    }

    public function testAfterSaveActiveRuleWithProducts(): void
    {
        $subjectMock = $this->createMock(RuleRepository::class);
        $ruleMock = $this->createMock(RuleInterface::class);
        $productIds = [1, 2, 3];

        $ruleMock->expects($this->once())->method('isActive')->willReturn(true);
        $this->catalogProductsMock->expects($this->once())
            ->method('getMatchingProductIds')
            ->with($ruleMock)
            ->willReturn($productIds);

        $this->handleRuleProductMock->expects($this->once())
            ->method('execute')
            ->with($ruleMock, $productIds);

        $result = $this->model->afterSave($subjectMock, $ruleMock, $ruleMock);
        $this->assertSame($ruleMock, $result);
    }

    public function testAfterSaveActiveRuleNoProducts(): void
    {
        $subjectMock = $this->createMock(RuleRepository::class);
        $ruleMock = $this->createMock(RuleInterface::class);

        $ruleMock->expects($this->once())->method('isActive')->willReturn(true);
        $this->catalogProductsMock->expects($this->once())
            ->method('getMatchingProductIds')
            ->with($ruleMock)
            ->willReturn([]);

        $this->handleRuleProductMock->expects($this->never())->method('execute');

        $result = $this->model->afterSave($subjectMock, $ruleMock, $ruleMock);
        $this->assertSame($ruleMock, $result);
    }
}
