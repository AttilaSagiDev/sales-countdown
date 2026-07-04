<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Catalog\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Space\SalesCountdown\Api\Data\RuleInterface;

/**
 * Catalog product for matching product Ids
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CatalogProducts
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Iterator
     */
    protected Iterator $resourceIterator;

    /**
     * @var ProductFactory
     */
    private ProductFactory $productFactory;

    /**
     * @var ConditionsToCollectionApplier
     */
    private ConditionsToCollectionApplier $conditionsToCollectionApplier;

    /**
     * @var array|null
     */
    private ?array $productIds = null;

    /**
     * @var array|null
     */
    private ?array $productsFilter = null;

    /**
     * @var array|null
     */
    private ?array $websitesMap = null;

    /**
     * Constructor
     *
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Iterator $resourceIterator
     * @param ProductFactory $productFactory
     * @param ConditionsToCollectionApplier|null $conditionsToCollectionApplier
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        Iterator $resourceIterator,
        ProductFactory $productFactory,
        ConditionsToCollectionApplier $conditionsToCollectionApplier = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->conditionsToCollectionApplier = $conditionsToCollectionApplier
            ?? ObjectManager::getInstance()->get(ConditionsToCollectionApplier::class);
    }

    /**
     * Get matching product Ids
     *
     * @param RuleInterface $rule
     * @return array
     * @throws InputException
     */
    public function getMatchingProductIds(RuleInterface $rule): array
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $rule->setCollectedAttributes([]);

            if ($rule->getWebsiteIds()) {
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->setStoreId($this->storeManager->getDefaultStoreView()->getId());
                $productCollection->addWebsiteFilter($rule->getWebsiteIds());

                if ($this->productsFilter) {
                    $productCollection->addIdFilter($this->productsFilter);
                }
                $rule->getConditions()->collectValidatedAttributes($productCollection);

                if ($this->canPreMapProducts($rule)) {
                    $productCollection = $this->conditionsToCollectionApplier
                        ->applyConditionsToCollection($rule->getConditions(), $productCollection);
                }

                $this->resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $rule->getCollectedAttributes(),
                        'product' => $this->productFactory->create(),
                        'rule' => $rule
                    ]
                );
            }
        }

        return $this->productIds;
    }

    /**
     * Check if we can use mapping for rule conditions
     *
     * @param RuleInterface $rule
     * @return bool
     */
    private function canPreMapProducts(RuleInterface $rule): bool
    {
        $conditions = $rule->getConditions();

        // No need to map products if there is no conditions in rule
        if (!$conditions || !$conditions->getConditions()) {
            return false;
        }

        return true;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct(array $args): void
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $rule = $args['rule'];

        $websites = $this->getWebsitesMap();
        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            if (!in_array($websiteId, $websiteIds)) {
                continue;
            }
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $rule->getConditions()->validate($product);
        }
        $this->productIds[$product->getId()] = $results;
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function getWebsitesMap(): array
    {
        if ($this->websitesMap === null) {
            $this->websitesMap = [];
            $websites = $this->storeManager->getWebsites();
            foreach ($websites as $website) {
                // Continue if website has no store to be able to create catalog rule for website without store
                if ($website->getDefaultStore() === null) {
                    continue;
                }
                $this->websitesMap[$website->getId()] = $website->getDefaultStore()->getId();
            }
        }

        return $this->websitesMap;
    }
}
