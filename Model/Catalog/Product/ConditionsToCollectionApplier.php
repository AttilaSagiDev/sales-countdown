<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Catalog\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Space\SalesCountdown\Model\Rule\Condition\Combine;
use Magento\Framework\Exception\InputException;
use Space\SalesCountdown\Model\Rule\Condition\ConditionsToSearchCriteriaMapper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\AdvancedFilterProcessor;
use Space\SalesCountdown\Model\Rule\Condition\MappableConditionsProcessor;

class ConditionsToCollectionApplier
{
    /**
     * @var ConditionsToSearchCriteriaMapper
     */
    private ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper;

    /**
     * @var AdvancedFilterProcessor
     */
    private AdvancedFilterProcessor $searchCriteriaProcessor;

    /**
     * @var MappableConditionsProcessor
     */
    private MappableConditionsProcessor $mappableConditionsProcessor;

    /**
     * @param ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper
     * @param AdvancedFilterProcessor $searchCriteriaProcessor
     * @param MappableConditionsProcessor $mappableConditionsProcessor
     */
    public function __construct(
        ConditionsToSearchCriteriaMapper $conditionsToSearchCriteriaMapper,
        AdvancedFilterProcessor $searchCriteriaProcessor,
        MappableConditionsProcessor $mappableConditionsProcessor
    ) {
        $this->conditionsToSearchCriteriaMapper = $conditionsToSearchCriteriaMapper;
        $this->searchCriteriaProcessor = $searchCriteriaProcessor;
        $this->mappableConditionsProcessor = $mappableConditionsProcessor;
    }

    /**
     * Transforms catalog rule conditions to search criteria and applies them on product collection
     *
     * @param Combine $conditions
     * @param ProductCollection $productCollection
     * @return ProductCollection
     * @throws InputException
     */
    public function applyConditionsToCollection(
        Combine $conditions,
        ProductCollection $productCollection
    ): ProductCollection {
        // rebuild conditions to have only those that we know how to map them to product collection
        $mappableConditions = $this->mappableConditionsProcessor->rebuildConditionsTree($conditions);

        // transform conditions to search criteria
        $searchCriteria = $this->conditionsToSearchCriteriaMapper->mapConditionsToSearchCriteria($mappableConditions);

        $mappedProductCollection = clone $productCollection;

        // apply search criteria to new version of product collection
        $this->searchCriteriaProcessor->process($searchCriteria, $mappedProductCollection);

        return $mappedProductCollection;
    }
}
