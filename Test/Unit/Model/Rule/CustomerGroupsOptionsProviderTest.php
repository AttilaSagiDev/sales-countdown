<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\CustomerGroupsOptionsProvider;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Framework\Convert\DataObject;

class CustomerGroupsOptionsProviderTest extends TestCase
{
    /**
     * @var CustomerGroupsOptionsProvider
     */
    private CustomerGroupsOptionsProvider $provider;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private GroupRepositoryInterface|MockObject $groupRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private SearchCriteriaBuilder|MockObject $searchCriteriaBuilderMock;

    /**
     * @var DataObject|MockObject
     */
    private DataObject|MockObject $objectConverterMock;

    protected function setUp(): void
    {
        $this->groupRepositoryMock = $this->createMock(GroupRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->objectConverterMock = $this->createMock(DataObject::class);

        $this->provider = new CustomerGroupsOptionsProvider(
            $this->groupRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->objectConverterMock
        );
    }

    public function testToOptionArrayQueriesRepositoryAndConvertsData(): void
    {
        $mockItems = [new \stdClass(), new \stdClass()];
        $expectedOptions = [
            ['value' => 1, 'label' => 'General'],
            ['value' => 2, 'label' => 'Wholesale']
        ];

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->createMock(GroupSearchResultsInterface::class);
        $this->groupRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);

        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($mockItems);

        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($mockItems, 'id', 'code')
            ->willReturn($expectedOptions);

        $result = $this->provider->toOptionArray();
        $this->assertSame($expectedOptions, $result);
    }
}
