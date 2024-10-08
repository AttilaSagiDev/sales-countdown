<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Ui\Component;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Ui\Component\Container;
use Magento\Framework\Api\Filter;

/**
 * DataProvider for cms ui.
 */
class DataProvider extends UiDataProvider
{
    /**
     * @var AddFilterInterface[]
     */
    private array $additionalFilterPool;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     * @param array $additionalFilterPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = [],
        array $additionalFilterPool = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->meta = array_replace_recursive($meta, $this->prepareMetadata());
        $this->additionalFilterPool = $additionalFilterPool;
    }

    /**
     * Prepares Meta
     *
     * @return array
     */
    public function prepareMetadata(): array
    {
        return [
            'sales_countdown_rule_columns' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'editorConfig' => [
                                'enabled' => false
                            ],
                            'componentType' => Container::NAME
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter): void
    {
        if (!empty($this->additionalFilterPool[$filter->getField()])) {
            $this->additionalFilterPool[$filter->getField()]->addFilter($this->searchCriteriaBuilder, $filter);
        } else {
            parent::addFilter($filter);
        }
    }
}
