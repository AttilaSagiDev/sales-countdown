<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule\Condition;

use Magento\Framework\Api\CombinedFilterGroup as FilterGroup;
use Magento\Framework\Api\CombinedFilterGroupFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\Condition\Combine as CombinedCondition;
use Space\SalesCountdown\Model\Rule\Condition\ConditionsToSearchCriteriaMapper;
use Space\SalesCountdown\Model\Rule\Condition\Product as SimpleCondition;

//phpcs:disable Generic.Files.LineLength

class ConditionsToSearchCriteriaMapperTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ConditionsToSearchCriteriaMapper
     */
    private ConditionsToSearchCriteriaMapper $mapper;

    /**
     * @var SearchCriteriaBuilderFactory|MockObject
     */
    private SearchCriteriaBuilderFactory|MockObject $searchCriteriaBuilderFactoryMock;

    /**
     * @var CombinedFilterGroupFactory|MockObject
     */
    private CombinedFilterGroupFactory|MockObject $combinedFilterGroupFactoryMock;

    /**
     * @var FilterFactory|MockObject
     */
    private FilterFactory|MockObject $filterFactoryMock;

    protected function setUp(): void
    {
        $this->searchCriteriaBuilderFactoryMock = $this->createMock(SearchCriteriaBuilderFactory::class);
        $this->combinedFilterGroupFactoryMock = $this->createMock(CombinedFilterGroupFactory::class);
        $this->filterFactoryMock = $this->createMock(FilterFactory::class);

        $objectManager = new ObjectManager($this);
        $this->mapper = $objectManager->getObject(
            ConditionsToSearchCriteriaMapper::class,
            [
                'searchCriteriaBuilderFactory' => $this->searchCriteriaBuilderFactoryMock,
                'combinedFilterGroupFactory' => $this->combinedFilterGroupFactoryMock,
                'filterFactory' => $this->filterFactoryMock,
            ]
        );
    }

    private function createCombinedConditionMock(
        array $conditions = [],
        string $aggregator = 'all',
        $value = 1,
        string $type = CombinedCondition::class
    ): CombinedCondition|MockObject {
        $mock = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getType', 'getConditions', 'getAggregator', 'getValue']
        );

        $mock->method('getType')->willReturn($type);
        $mock->method('getConditions')->willReturn($conditions);
        $mock->method('getAggregator')->willReturn($aggregator);
        $mock->method('getValue')->willReturn($value);

        return $mock;
    }

    private function createSimpleConditionMock(
        string $attribute,
        string $operator,
        $value,
        string $type = SimpleCondition::class
    ): SimpleCondition|MockObject {
        $mock = $this->createPartialMockWithReflection(
            SimpleCondition::class,
            ['getType', 'getAttribute', 'getOperator', 'getValue']
        );

        $mock->method('getType')->willReturn($type);
        $mock->method('getAttribute')->willReturn($attribute);
        $mock->method('getOperator')->willReturn($operator);
        $mock->method('getValue')->willReturn($value);

        return $mock;
    }

    public function testMapConditionsToSearchCriteriaWithEmptyConditions(): void
    {
        $combinedConditionMock = $this->createCombinedConditionMock([]);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->never())->method('setFilterGroups');
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->searchCriteriaBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public function testMapConditionsToSearchCriteriaWithSingleSimpleCondition(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 1);

        $filterMock = $this->createMock(Filter::class);
        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    Filter::KEY_FIELD => 'sku',
                    Filter::KEY_VALUE => 'test-sku',
                    Filter::KEY_CONDITION_TYPE => 'eq'
                ]
            ])
            ->willReturn($filterMock);

        $filterGroupMock = $this->createMock(FilterGroup::class);
        $this->combinedFilterGroupFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    FilterGroup::FILTERS => [$filterMock],
                    FilterGroup::COMBINATION_MODE => 'AND'
                ]
            ])
            ->willReturn($filterGroupMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('setFilterGroups')
            ->with([$filterGroupMock]);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->searchCriteriaBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public function testMapConditionsToSearchCriteriaWithArrayValueCondition(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('category_ids', '==', ['10', '20']);
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 1);

        $filterMock1 = $this->createMock(Filter::class);
        $filterMock2 = $this->createMock(Filter::class);

        $this->filterFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [
                    [
                        'data' => [
                            Filter::KEY_FIELD => 'category_ids',
                            Filter::KEY_VALUE => '10',
                            Filter::KEY_CONDITION_TYPE => 'eq'
                        ]
                    ],
                    $filterMock1
                ],
                [
                    [
                        'data' => [
                            Filter::KEY_FIELD => 'category_ids',
                            Filter::KEY_VALUE => '20',
                            Filter::KEY_CONDITION_TYPE => 'eq'
                        ]
                    ],
                    $filterMock2
                ]
            ]);

        $innerFilterGroupMock = $this->createMock(FilterGroup::class);
        $outerFilterGroupMock = $this->createMock(FilterGroup::class);

        $this->combinedFilterGroupFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [
                    [
                        'data' => [
                            FilterGroup::FILTERS => [$filterMock1, $filterMock2],
                            FilterGroup::COMBINATION_MODE => 'OR'
                        ]
                    ],
                    $innerFilterGroupMock
                ],
                [
                    [
                        'data' => [
                            FilterGroup::FILTERS => [$innerFilterGroupMock],
                            FilterGroup::COMBINATION_MODE => 'AND'
                        ]
                    ],
                    $outerFilterGroupMock
                ]
            ]);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('setFilterGroups')
            ->with([$outerFilterGroupMock]);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->searchCriteriaBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    #[DataProvider('arrayGlueDataProvider')]
    public function testMapConditionsToSearchCriteriaArrayValueGlue(string $operator, string $expectedGlue): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('attribute_code', $operator, ['val1', 'val2']);
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 1);

        $filterMock1 = $this->createMock(Filter::class);
        $filterMock2 = $this->createMock(Filter::class);

        $this->filterFactoryMock->method('create')->willReturnOnConsecutiveCalls($filterMock1, $filterMock2);

        $innerFilterGroupMock = $this->createMock(FilterGroup::class);
        $outerFilterGroupMock = $this->createMock(FilterGroup::class);

        $this->combinedFilterGroupFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(function (array $args) use ($innerFilterGroupMock, $outerFilterGroupMock, $expectedGlue, $filterMock1, $filterMock2) {
                if (isset($args['data'][FilterGroup::FILTERS])
                    && $args['data'][FilterGroup::FILTERS] === [$filterMock1, $filterMock2]
                ) {
                    $this->assertEquals($expectedGlue, $args['data'][FilterGroup::COMBINATION_MODE]);
                    return $innerFilterGroupMock;
                }
                return $outerFilterGroupMock;
            });

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);
        $this->searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public static function arrayGlueDataProvider(): array
    {
        return [
            ['!=', 'AND'],
            ['!{}', 'AND'],
            ['!()', 'AND'],
            ['==', 'OR'],
            ['>=', 'OR'],
            ['<=', 'OR'],
            ['>', 'OR'],
            ['<', 'OR'],
            ['{}', 'OR'],
            ['()', 'OR'],
            ['<=>', 'OR'],
        ];
    }

    public function testMapConditionsToSearchCriteriaWithNegativeCombinedConditionValue(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 0);

        $filterMock = $this->createPartialMockWithReflection(Filter::class, ['getConditionType', 'setConditionType']);
        $filterMock->method('getConditionType')->willReturn('eq');
        $filterMock->expects($this->once())
            ->method('setConditionType')
            ->with('neq');

        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $filterGroupMock = $this->createMock(FilterGroup::class);
        $this->combinedFilterGroupFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    FilterGroup::FILTERS => [$filterMock],
                    FilterGroup::COMBINATION_MODE => 'AND'
                ]
            ])
            ->willReturn($filterGroupMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);
        $this->searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public function testMapConditionsToSearchCriteriaWithNegativeCombinedConditionAndFilterGroup(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('category_ids', '==', ['10', '20']);
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'any', 0);

        $filterMock1 = $this->createPartialMockWithReflection(Filter::class, ['getConditionType', 'setConditionType']);
        $filterMock1->method('getConditionType')->willReturn('eq');
        $filterMock1->expects($this->once())
            ->method('setConditionType')
            ->with('neq');

        $filterMock2 = $this->createPartialMockWithReflection(Filter::class, ['getConditionType', 'setConditionType']);
        $filterMock2->method('getConditionType')->willReturn('eq');
        $filterMock2->expects($this->once())
            ->method('setConditionType')
            ->with('neq');

        $this->filterFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($filterMock1, $filterMock2);

        $innerFilterGroupMock = $this->createPartialMockWithReflection(FilterGroup::class, ['getFilters']);
        $innerFilterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock1, $filterMock2]);

        $outerFilterGroupMock = $this->createMock(FilterGroup::class);

        $this->combinedFilterGroupFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($innerFilterGroupMock, $outerFilterGroupMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);
        $this->searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public function testMapConditionsToSearchCriteriaWithNestedCombinedConditions(): void
    {
        $nestedSimpleCondition = $this->createSimpleConditionMock('price', '>=', '100');
        $nestedCombinedCondition = $this->createCombinedConditionMock([$nestedSimpleCondition], 'any', 1);
        $rootCombinedCondition = $this->createCombinedConditionMock([$nestedCombinedCondition], 'all', 1);

        $filterMock = $this->createMock(Filter::class);
        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    Filter::KEY_FIELD => 'price',
                    Filter::KEY_VALUE => '100',
                    Filter::KEY_CONDITION_TYPE => 'gteq'
                ]
            ])
            ->willReturn($filterMock);

        $nestedFilterGroupMock = $this->createMock(FilterGroup::class);
        $rootFilterGroupMock = $this->createMock(FilterGroup::class);

        $this->combinedFilterGroupFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [
                    [
                        'data' => [
                            FilterGroup::FILTERS => [$filterMock],
                            FilterGroup::COMBINATION_MODE => 'OR'
                        ]
                    ],
                    $nestedFilterGroupMock
                ],
                [
                    [
                        'data' => [
                            FilterGroup::FILTERS => [$nestedFilterGroupMock],
                            FilterGroup::COMBINATION_MODE => 'AND'
                        ]
                    ],
                    $rootFilterGroupMock
                ]
            ]);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('setFilterGroups')
            ->with([$rootFilterGroupMock]);
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->searchCriteriaBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($rootCombinedCondition);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public function testMapConditionsToSearchCriteriaNestedCombinedConditionNegative(): void
    {
        $innerSimpleCondition = $this->createSimpleConditionMock('price', '>', '50');
        $innerCombined = $this->createCombinedConditionMock([$innerSimpleCondition], 'all', 1);
        $outerCombined = $this->createCombinedConditionMock([$innerCombined], 'all', 0);

        $innerFilterMock = $this->createPartialMockWithReflection(
            Filter::class,
            ['getConditionType', 'setConditionType']
        );
        $innerFilterMock->method('getConditionType')->willReturn('gt');
        $innerFilterMock->expects($this->once())
            ->method('setConditionType')
            ->with('lteq');

        $innerFilterGroupMock = $this->createPartialMockWithReflection(
            FilterGroup::class,
            ['getFilters']
        );
        $innerFilterGroupMock->method('getFilters')->willReturn([$innerFilterMock]);

        $outerFilterGroupMock = $this->createPartialMockWithReflection(
            FilterGroup::class,
            ['getFilters']
        );
        $outerFilterGroupMock->method('getFilters')->willReturn([$innerFilterGroupMock]);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);

        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($innerFilterMock);

        $this->combinedFilterGroupFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($innerFilterGroupMock, $outerFilterGroupMock);

        $this->searchCriteriaBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($outerCombined);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public function testMapCombinedConditionSkipsNullFilters(): void
    {
        $validCondition = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        $emptyCombinedCondition = $this->createCombinedConditionMock([]);
        $mainCombinedCondition = $this->createCombinedConditionMock([$validCondition, $emptyCombinedCondition], 'all', 1);

        $filterMock = $this->createMock(Filter::class);
        $filterGroupMock = $this->createMock(FilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);

        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    FilterGroup::FILTERS => [$filterMock],
                    FilterGroup::COMBINATION_MODE => 'AND'
                ]
            ])
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($mainCombinedCondition);

        $this->assertSame($searchCriteriaMock, $result);
    }

    #[DataProvider('operatorsDataProvider')]
    public function testMapRuleOperatorToSqlCondition(string $ruleOperator, string $expectedSqlCondition): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('test_attribute', $ruleOperator, 'test_val');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 1);

        $filterMock = $this->createMock(Filter::class);
        $this->filterFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    Filter::KEY_FIELD => 'test_attribute',
                    Filter::KEY_VALUE => 'test_val',
                    Filter::KEY_CONDITION_TYPE => $expectedSqlCondition
                ]
            ])
            ->willReturn($filterMock);

        $filterGroupMock = $this->createMock(FilterGroup::class);
        $this->combinedFilterGroupFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($filterGroupMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);
        $this->searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    public static function operatorsDataProvider(): array
    {
        return [
            ['==', 'eq'],
            ['!=', 'neq'],
            ['>=', 'gteq'],
            ['<=', 'lteq'],
            ['>', 'gt'],
            ['<', 'lt'],
            ['{}', 'like'],
            ['!{}', 'nlike'],
            ['()', 'in'],
            ['!()', 'nin'],
            ['<=>', 'is_null'],
        ];
    }

    #[DataProvider('reverseOperatorsDataProvider')]
    public function testReverseSqlOperatorInFilter(string $initialConditionType, string $expectedReversedConditionType): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('sku', '==', 'test');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 0);

        $filterMock = $this->createPartialMockWithReflection(Filter::class, ['getConditionType', 'setConditionType']);
        $filterMock->method('getConditionType')->willReturn($initialConditionType);
        $filterMock->expects($this->once())
            ->method('setConditionType')
            ->with($expectedReversedConditionType);

        $this->filterFactoryMock->method('create')->willReturn($filterMock);

        $filterGroupMock = $this->createMock(FilterGroup::class);
        $this->combinedFilterGroupFactoryMock->method('create')->willReturn($filterGroupMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);
        $this->searchCriteriaBuilderFactoryMock->method('create')->willReturn($searchCriteriaBuilderMock);

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    public static function reverseOperatorsDataProvider(): array
    {
        return [
            ['eq', 'neq'],
            ['neq', 'eq'],
            ['gteq', 'lt'],
            ['lteq', 'gt'],
            ['gt', 'lteq'],
            ['lt', 'gteq'],
            ['like', 'nlike'],
            ['nlike', 'like'],
            ['in', 'nin'],
            ['nin', 'in'],
        ];
    }

    public function testThrowsExceptionWhenUndefinedConditionType(): void
    {
        $unknownConditionMock = $this->createPartialMockWithReflection(
            SimpleCondition::class,
            ['getType']
        );
        $unknownConditionMock->method('getType')->willReturn('Space\SalesCountdown\Model\Rule\Condition\Unknown');

        $combinedConditionMock = $this->createCombinedConditionMock([$unknownConditionMock], 'all', 1);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined condition type "Space\SalesCountdown\Model\Rule\Condition\Unknown" passed in.');

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    public function testThrowsExceptionWhenUndefinedRuleOperator(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('sku', 'INVALID_OP', 'test');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 1);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined rule operator "INVALID_OP" passed in. Valid operators are: ==,!=,>=,<=,>,<,{},!{},(),!(),<=>');

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    public function testThrowsExceptionWhenUndefinedRuleAggregator(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('sku', '==', 'test');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'INVALID_AGGREGATOR', 1);

        $filterMock = $this->createMock(Filter::class);
        $this->filterFactoryMock->method('create')->willReturn($filterMock);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined rule aggregator "INVALID_AGGREGATOR" passed in. Valid operators are: all,any');

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    public function testThrowsExceptionWhenReverseUnknownSqlOperator(): void
    {
        $simpleConditionMock = $this->createSimpleConditionMock('sku', '==', 'test');
        $combinedConditionMock = $this->createCombinedConditionMock([$simpleConditionMock], 'all', 0);

        $filterMock = $this->createPartialMockWithReflection(Filter::class, ['getConditionType']);
        $filterMock->method('getConditionType')->willReturn('unknown_sql_op');

        $this->filterFactoryMock->method('create')->willReturn($filterMock);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined SQL operator "unknown_sql_op" passed in. Valid operators are: eq,neq,gteq,lteq,gt,lt,like,nlike,in,nin');

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }
}
