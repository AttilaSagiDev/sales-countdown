<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Service;

use Space\SalesCountdown\Api\CalculateCountdownRuleInterface;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Space\SalesCountdown\Model\SalesCountdownRuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface;
use Magento\Framework\Exception\LocalizedException;

class CalculateCountdownRule implements CalculateCountdownRuleInterface
{
    /**
     * @var SalesCountdownRuleFactory
     */
    private SalesCountdownRuleFactory $salesCountdownRuleFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var ResourceRule
     */
    private ResourceRule $resourceRule;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param SalesCountdownRuleFactory $salesCountdownRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param ResourceRule $resourceRule
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        SalesCountdownRuleFactory $salesCountdownRuleFactory,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        ResourceRule $resourceRule,
        CollectionFactory $collectionFactory,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->salesCountdownRuleFactory = $salesCountdownRuleFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resourceRule = $resourceRule;
        $this->collectionFactory = $collectionFactory;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Calculate countdown rules for product
     *
     * @param int $productId
     * @return SalesCountdownRuleInterface
     */
    public function calculateByProductId(int $productId): SalesCountdownRuleInterface
    {
        $startTime = microtime(true);

        $salesCountdownRule = $this->salesCountdownRuleFactory->create();
        $salesCountdownRule->setCountdownEndDate('end date');
        $salesCountdownRule->setCountdownMessage('end message ' . $productId);

        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
            $storeId = $storeId === 0 ? $this->storeManager->getDefaultStoreView()->getId() : $storeId;
            $dateTimeStamp = $this->timezone->scopeTimeStamp($storeId);
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $customerGroupId = $this->customerSession->getCustomerGroupId();

            $result = $this->resourceRule->getRulesFromProduct(
                $dateTimeStamp,
                $websiteId,
                $customerGroupId,
                $productId
            );

            $this->logger->debug('--- calculateByProductId ---');
            $this->logger->debug('Default Store ID: ' . $this->storeManager->getDefaultStoreView()->getId());
            $this->logger->debug('Store Id: ' . $storeId);
            $this->logger->debug('Website Id: ' . $websiteId);
            $this->logger->debug('Customer Group ID: ' . $customerGroupId);
            $this->logger->debug('Date Time: ' . $dateTimeStamp);
            $this->logger->debug('Result: ' . print_r($result, true));

            if (!empty($result)) {
                $ruleIds = array_column($result, RuleInterface::RULE_ID);
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter(RuleInterface::RULE_ID, ['in' => $ruleIds])
                    ->addFieldToFilter(RuleInterface::IS_ACTIVE, ['eq' => 1])
                    ->setOrder(RuleInterface::SORT_ORDER, 'DESC');
                $this->logger->debug('Collection: ' . print_r($collection->getData(), true));
            }
            $this->logger->debug('Time: ' . (microtime(true) - $startTime));

        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $salesCountdownRule;
    }
}
