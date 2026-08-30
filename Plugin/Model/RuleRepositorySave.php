<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Plugin\Model;

use Space\SalesCountdown\Model\Catalog\Product\CatalogProducts;
use Space\SalesCountdown\Model\Catalog\Product\HandleRuleProduct;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Model\RuleRepository;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Exception\LocalizedException;

class RuleRepositorySave
{
    /**
     * @var CatalogProducts
     */
    private CatalogProducts $catalogProducts;

    /**
     * @var HandleRuleProduct
     */
    private HandleRuleProduct $handleRuleProduct;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param CatalogProducts $catalogProducts
     * @param HandleRuleProduct $handleRuleProduct
     * @param LoggerInterface $logger
     */
    public function __construct(
        CatalogProducts $catalogProducts,
        HandleRuleProduct $handleRuleProduct,
        LoggerInterface $logger
    ) {
        $this->catalogProducts = $catalogProducts;
        $this->handleRuleProduct = $handleRuleProduct;
        $this->logger = $logger;
    }

    /**
     * Save catalog product Ids for rule
     *
     * @param RuleRepository $subject
     * @param RuleInterface $result
     * @param RuleInterface $rule
     * @return RuleInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterSave(
        RuleRepository $subject, // NOSONAR
        RuleInterface $result,
        RuleInterface $rule
    ): RuleInterface {
        if ($rule->isActive()) {
            $productIds = $this->catalogProducts->getMatchingProductIds($rule);
            if (!empty($productIds)) {
                $this->handleRuleProduct->execute($rule, $productIds);
            }
        }

        return $result;
    }
}
