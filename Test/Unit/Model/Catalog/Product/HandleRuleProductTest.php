<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Catalog\Product;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Catalog\Product\HandleRuleProduct;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Interface for mocking magic methods of Rule
 * //phpcs:disable
 */
interface ExtendedRuleInterface extends RuleInterface
{
    public function getWebsiteIds();
    public function getCustomerGroupIds();
}

class HandleRuleProductTest extends TestCase
{
    /**
     * @var HandleRuleProduct
     */
    private HandleRuleProduct $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection|MockObject $resourceConnectionMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private TimezoneInterface|MockObject $timezoneMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface|MockObject $connectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->model = $objectManager->getObject(
            HandleRuleProduct::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'timezone' => $this->timezoneMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testExecuteInactiveRule(): void
    {
        $ruleMock = $this->createMock(ExtendedRuleInterface::class);
        $ruleMock->expects($this->once())->method('isActive')->willReturn(false);

        $this->assertFalse($this->model->execute($ruleMock, []));
    }

    public function testExecute(): void
    {
        $ruleMock = $this->createMock(ExtendedRuleInterface::class);
        $ruleId = 1;
        $websiteId = 10;
        $productId = 100;
        $customerGroupId = 0;

        $ruleMock->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $ruleMock->expects($this->atLeastOnce())->method('getWebsiteIds')->willReturn([$websiteId]);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('getCustomerGroupIds')->willReturn([$customerGroupId]);
        $ruleMock->expects($this->once())->method('getFromDate')->willReturn('2024-01-01');
        $ruleMock->expects($this->once())->method('getToDate')->willReturn('2024-01-02');

        $this->resourceConnectionMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('sales_countdown_rule_product');

        $this->connectionMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('sales_countdown_rule_product');

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with('sales_countdown_rule_product', "rule_id='1'");

        $this->connectionMock->expects($this->once())
            ->method('quoteInto')
            ->willReturn("rule_id='1'");

        $this->timezoneMock->expects($this->exactly(2))
            ->method('getConfigTimezone')
            ->willReturn('UTC');

        $productIds = [
            $productId => [$websiteId => true]
        ];

        $this->connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->with(
                'sales_countdown_rule_product',
                $this->callback(function ($rows) use ($ruleId, $productId, $customerGroupId, $websiteId) {
                    return count($rows) === 1 &&
                        $rows[0][RuleInterface::RULE_ID] === $ruleId &&
                        $rows[0]['product_id'] === $productId &&
                        $rows[0]['customer_group_id'] === $customerGroupId &&
                        $rows[0]['website_id'] === $websiteId;
                })
            );

        $this->assertTrue($this->model->execute($ruleMock, $productIds));
    }

    public function testExecuteException(): void
    {
        $ruleMock = $this->createMock(ExtendedRuleInterface::class);
        $ruleId = 1;
        $ruleMock->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $ruleMock->expects($this->atLeastOnce())->method('getWebsiteIds')->willReturn([$ruleId]);
        $ruleMock->expects($this->once())->method('getRuleId')->willReturn($ruleId);

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('DB Error'));

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with('DB Error');

        $this->assertFalse($this->model->execute($ruleMock, []));
    }
}
