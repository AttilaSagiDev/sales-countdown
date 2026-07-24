<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\DataProvider;
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory;
use Space\SalesCountdown\Model\ResourceModel\Rule\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Space\SalesCountdown\Model\Rule;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private DataProvider $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private Collection|MockObject $collectionMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private DataPersistorInterface|MockObject $dataPersistorMock;

    /**
     * @var PoolInterface|MockObject
     */
    private PoolInterface|MockObject $poolMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->collectionMock = $this->createMock(Collection::class);
        $this->dataPersistorMock = $this->createMock(DataPersistorInterface::class);
        $this->poolMock = $this->createMock(PoolInterface::class);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->model = new DataProvider(
            'test_name',
            'rule_id',
            'rule_id',
            $this->collectionFactoryMock,
            $this->dataPersistorMock,
            [],
            [],
            $this->poolMock
        );
    }

    public function testGetData(): void
    {
        $ruleId = 1;
        $ruleData = ['rule_id' => $ruleId, 'name' => 'Test Rule'];

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRuleId', 'getData'])
            ->getMock();
        $ruleMock->expects($this->atLeastOnce())->method('getRuleId')->willReturn($ruleId);
        $ruleMock->expects($this->atLeastOnce())->method('getData')->willReturn($ruleData);

        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$ruleMock]);

        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('sales_countdown_rule')
            ->willReturn(null);

        $result = $this->model->getData();
        $this->assertEquals([$ruleId => $ruleData], $result);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }

    public function testGetDataWithPersistentData(): void
    {
        $ruleId = 1;
        $persistentRuleId = 2;
        $ruleData = ['rule_id' => $ruleId, 'name' => 'Test Rule'];
        $persistentData = ['rule_id' => $persistentRuleId, 'name' => 'New Name'];

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRuleId', 'getData'])
            ->getMock();
        $ruleMock->expects($this->any())->method('getRuleId')->willReturn($ruleId);
        $ruleMock->expects($this->any())->method('getData')->willReturn($ruleData);

        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$ruleMock]);

        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('sales_countdown_rule')
            ->willReturn($persistentData);

        $persistentRuleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRuleId', 'getData', 'setData'])
            ->getMock();
        $persistentRuleMock->expects($this->any())->method('getRuleId')->willReturn($persistentRuleId);
        $persistentRuleMock->expects($this->any())->method('getData')->willReturn($persistentData);

        $this->collectionMock->expects($this->once())
            ->method('getNewEmptyItem')
            ->willReturn($persistentRuleMock);

        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('sales_countdown_rule');

        $result = $this->model->getData();
        $this->assertEquals([
            $ruleId => $ruleData,
            $persistentRuleId => $persistentData
        ], $result);
    }
}
