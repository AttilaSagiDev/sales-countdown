<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Catalog\Product;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Catalog\Product\ConditionsToCollectionApplier;
use Space\SalesCountdown\Model\Rule\Condition\Combine;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Space\SalesCountdown\Model\Rule\Condition\ConditionsToSearchCriteriaMapper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\AdvancedFilterProcessor;
use Space\SalesCountdown\Model\Rule\Condition\MappableConditionsProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConditionsToCollectionApplierTest extends TestCase
{
    /**
     * @var ConditionsToCollectionApplier
     */
    private ConditionsToCollectionApplier $model;

    /**
     * @var ConditionsToSearchCriteriaMapper|MockObject
     */
    private ConditionsToSearchCriteriaMapper|MockObject $conditionsToSearchCriteriaMapperMock;

    /**
     * @var AdvancedFilterProcessor|MockObject
     */
    private AdvancedFilterProcessor|MockObject $searchCriteriaProcessorMock;

    /**
     * @var MappableConditionsProcessor|MockObject
     */
    private MappableConditionsProcessor|MockObject $mappableConditionsProcessorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->conditionsToSearchCriteriaMapperMock = $this->createMock(ConditionsToSearchCriteriaMapper::class);
        $this->searchCriteriaProcessorMock = $this->createMock(AdvancedFilterProcessor::class);
        $this->mappableConditionsProcessorMock = $this->createMock(MappableConditionsProcessor::class);

        $this->model = $objectManager->getObject(
            ConditionsToCollectionApplier::class,
            [
                'conditionsToSearchCriteriaMapper' => $this->conditionsToSearchCriteriaMapperMock,
                'searchCriteriaProcessor' => $this->searchCriteriaProcessorMock,
                'mappableConditionsProcessor' => $this->mappableConditionsProcessorMock
            ]
        );
    }

    public function testApplyConditionsToCollection(): void
    {
        $conditionsMock = $this->createMock(Combine::class);
        $productCollectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mappableConditionsMock = $this->createMock(Combine::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);

        $this->mappableConditionsProcessorMock->expects($this->once())
            ->method('rebuildConditionsTree')
            ->with($conditionsMock)
            ->willReturn($mappableConditionsMock);

        $this->conditionsToSearchCriteriaMapperMock->expects($this->once())
            ->method('mapConditionsToSearchCriteria')
            ->with($mappableConditionsMock)
            ->willReturn($searchCriteriaMock);

        $this->searchCriteriaProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $this->isInstanceOf(ProductCollection::class));

        $result = $this->model->applyConditionsToCollection($conditionsMock, $productCollectionMock);

        $this->assertInstanceOf(ProductCollection::class, $result);
        $this->assertNotSame($productCollectionMock, $result);
    }
}
