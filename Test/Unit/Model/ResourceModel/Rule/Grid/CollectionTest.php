<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\ResourceModel\Rule\Grid;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\ResourceModel\Rule\Grid\Collection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Data\Collection\AbstractDb as DataCollectionAbstractDb;

class CollectionTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    private Collection|MockObject $model;

    /**
     * @var TimezoneInterface|MockObject
     */
    private TimezoneInterface|MockObject $timezoneMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface|MockObject $connectionMock;

    /**
     * @var Select|MockObject
     */
    private Select|MockObject $selectMock;

    protected function setUp(): void
    {
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->model = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSize', 'getConnection'])
            ->getMock();

        $this->model->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $reflection = new \ReflectionClass(Collection::class);
        $timezoneProperty = $reflection->getProperty('timeZone');
        $timezoneProperty->setValue($this->model, $this->timezoneMock);

        $abstractDbReflection = new \ReflectionClass(DataCollectionAbstractDb::class);
        $selectProperty = $abstractDbReflection->getProperty('_select');
        $selectProperty->setValue($this->model, $this->selectMock);
    }

    public function testAddFieldToFilterWithDates(): void
    {
        $field = 'from_date';
        $condition = ['eq' => '2024-01-01'];
        $convertedDate = '2024-01-01 00:00:00';

        $this->timezoneMock->expects($this->once())
            ->method('convertConfigTimeToUtc')
            ->with('2024-01-01')
            ->willReturn($convertedDate);

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->selectMock->expects($this->atLeastOnce())
            ->method('where')
            ->willReturnSelf();

        $result = $this->model->addFieldToFilter($field, $condition);
        $this->assertSame($this->model, $result);
    }

    public function testGetSetAggregations(): void
    {
        $aggregationsMock = $this->createMock(AggregationInterface::class);
        $this->model->setAggregations($aggregationsMock);
        $this->assertSame($aggregationsMock, $this->model->getAggregations());
    }

    public function testGetTotalCount(): void
    {
        $this->model->expects($this->once())
            ->method('getSize')
            ->willReturn(10);
        $this->assertEquals(10, $this->model->getTotalCount());
    }

    public function testSearchCriteriaMethods(): void
    {
        $this->assertNull($this->model->getSearchCriteria());
        $this->assertSame($this->model, $this->model->setSearchCriteria());
    }
}
