<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Ui\Component;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Ui\Component\DataProvider;
use Space\SalesCountdown\Ui\Component\AddFilterInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Filter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private DataProvider $model;

    /**
     * @var Reporting|MockObject
     */
    private Reporting|MockObject $reportingMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private SearchCriteriaBuilder|MockObject $searchCriteriaBuilderMock;

    /**
     * @var RequestInterface|MockObject
     */
    private RequestInterface|MockObject $requestMock;

    /**
     * @var FilterBuilder|MockObject
     */
    private FilterBuilder|MockObject $filterBuilderMock;

    /**
     * @var AddFilterInterface|MockObject
     */
    private AddFilterInterface|MockObject $additionalFilterMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->reportingMock = $this->createMock(Reporting::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->additionalFilterMock = $this->createMock(AddFilterInterface::class);

        $this->model = $objectManager->getObject(
            DataProvider::class,
            [
                'name' => 'test_data_provider',
                'primaryFieldName' => 'id',
                'requestFieldName' => 'id',
                'reporting' => $this->reportingMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'request' => $this->requestMock,
                'filterBuilder' => $this->filterBuilderMock,
                'additionalFilterPool' => [
                    'custom_field' => $this->additionalFilterMock
                ]
            ]
        );
    }

    public function testPrepareMetadata(): void
    {
        $expected = [
            'sales_countdown_rule_columns' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'editorConfig' => [
                                'enabled' => false
                            ],
                            'componentType' => \Magento\Ui\Component\Container::NAME
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $this->model->prepareMetadata());
    }

    public function testAddFilterFromPool(): void
    {
        /** @var Filter|MockObject $filterMock */
        $filterMock = $this->createMock(Filter::class);
        $filterMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn('custom_field');

        $this->additionalFilterMock->expects($this->once())
            ->method('addFilter')
            ->with($this->searchCriteriaBuilderMock, $filterMock);

        $this->model->addFilter($filterMock);
    }

    public function testAddFilterDefault(): void
    {
        /** @var Filter|MockObject $filterMock */
        $filterMock = $this->createMock(Filter::class);
        $filterMock->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn('standard_field');

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with($filterMock);

        $this->model->addFilter($filterMock);
    }
}
