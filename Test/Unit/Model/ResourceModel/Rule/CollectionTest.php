<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\ResourceModel\Rule;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\ResourceModel\Rule\Collection;
use Space\SalesCountdown\Model\Rule;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;

class CollectionTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    private Collection|MockObject $model;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface|MockObject $connectionMock;

    /**
     * @var AbstractDb|MockObject
     */
    private AbstractDb|MockObject $resourceMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface|MockObject $eventManagerMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceMock = $this->createMock(AbstractDb::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);

        $this->model = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getColumnValues',
                    'getItemByColumnValue',
                    'setFlag',
                    'getConnection',
                    'getTable',
                    'getFlag'
                ]
            )
            ->getMock();
    }

    public function testAfterLoad(): void
    {
        $ruleId = 1;
        $websiteId = 1;
        $customerGroupId = 0;

        $itemMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRuleId', 'getId', 'setData', 'getData'])
            ->getMock();

        $itemMock->expects($this->any())->method('getRuleId')->willReturn($ruleId);
        $itemMock->expects($this->any())->method('getId')->willReturn($ruleId);
        $itemMock->expects($this->any())->method('getData')->willReturn(null);

        $itemMock->expects($this->exactly(2))
            ->method('setData')
            ->willReturnCallback(function ($key, $value) use ($itemMock, $websiteId, $customerGroupId) {
                if ($key === 'website_ids') {
                    $this->assertEquals([$websiteId], $value);
                } elseif ($key === 'customer_group_ids') {
                    $this->assertEquals([$customerGroupId], $value);
                }
                return $itemMock;
            });

        $reflection = new \ReflectionClass(Collection::class);

        $itemsProperty = $reflection->getProperty('_items');
        $itemsProperty->setValue($this->model, [$itemMock]);

        $mapProperty = $reflection->getProperty('_associatedEntitiesMap');
        $mapProperty->setValue($this->model, [
            'website' => [
                'associations_table' => 'sales_countdown_rule_website',
                'rule_id_field' => 'rule_id',
                'entity_id_field' => 'website_id'
            ],
            'customer_group' => [
                'associations_table' => 'sales_countdown_rule_customer_group',
                'rule_id_field' => 'rule_id',
                'entity_id_field' => 'customer_group_id'
            ]
        ]);

        $eventManagerProperty = $reflection->getProperty('_eventManager');
        $eventManagerProperty->setValue($this->model, $this->eventManagerMock);

        $this->model->expects($this->exactly(2))
            ->method('getColumnValues')
            ->willReturnCallback(function ($field) use ($ruleId) {
                $this->assertEquals('rule_id', $field);
                return [$ruleId];
            });

        $this->model->expects($this->exactly(2))
            ->method('getItemByColumnValue')
            ->willReturnCallback(function ($field, $value) use ($ruleId, $itemMock) {
                $this->assertEquals('rule_id', $field);
                $this->assertEquals($ruleId, $value);
                return $itemMock;
            });

        $this->model->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->model->expects($this->any())
            ->method('getTable')
            ->willReturnCallback(function ($table) {
                return $table;
            });

        $this->model->expects($this->any())
            ->method('getFlag')
            ->willReturn(false);

        $selectMock = $this->createMock(Select::class);
        $this->connectionMock->expects($this->exactly(2))
            ->method('select')
            ->willReturn($selectMock);

        $selectMock->expects($this->exactly(2))
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();

        $this->connectionMock->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['rule_id' => $ruleId, 'website_id' => $websiteId]],
                [['rule_id' => $ruleId, 'customer_group_id' => $customerGroupId]]
            );

        $method = $reflection->getMethod('_afterLoad');
        $result = $method->invoke($this->model);

        $this->assertSame($this->model, $result);
    }
}
