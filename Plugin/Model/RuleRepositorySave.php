<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Plugin\Model;

use Space\SalesCountdown\Model\Catalog\Product\CatalogProducts;
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param CatalogProducts $catalogProducts
     * @param LoggerInterface $logger
     */
    public function __construct(
        CatalogProducts $catalogProducts,
        LoggerInterface $logger
    ) {
        $this->catalogProducts = $catalogProducts;
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
     */
    public function afterSave(
        RuleRepository $subject,
        RuleInterface $result,
        RuleInterface $rule
    ): RuleInterface {
        $ruleId = $rule->getId();

        $this->logger->debug('--- RuleRepositorySave ---');
        $this->logger->debug('Rule Id: ' . $ruleId);
        $productIds = $this->catalogProducts->getMatchingProductIds($rule);
        $this->logger->debug('Size: ' . count($productIds));
        //$this->logger->debug('Content: ' . print_r($productIds, true));

        return $result;
    }
}
