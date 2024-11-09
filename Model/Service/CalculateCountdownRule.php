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
use Space\SalesCountdown\Model\ResourceModel\Rule\Collection;
use Magento\Store\Model\ScopeInterface;

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
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $dateTimeStamp = $this->getDateByWebsiteTimeZone($websiteId);
            $customerGroupId = $this->customerSession->getCustomerGroupId();

            $this->logger->debug('--- calculateByProductId ---');
            $this->logger->debug('Store Id: ' . $storeId);
            $this->logger->debug('Website Id: ' . $websiteId);
            $this->logger->debug('Customer Group ID: ' . $customerGroupId);
            $this->logger->debug('Date Time: ' . $dateTimeStamp);
            $this->logger->debug('Product ID: ' . $productId);

            $rulesResult = $this->resourceRule->getRulesFromProduct(
                $dateTimeStamp,
                $websiteId,
                $customerGroupId,
                $productId
            );

            if (!empty($rulesResult)) {
                $ruleIds = array_column($rulesResult, RuleInterface::RULE_ID);
                $collection = $this->collectionFactory->create();
                $collection->addFieldToSelect(
                    [
                        RuleInterface::RULE_ID,
                        RuleInterface::NAME,
                        RuleInterface::FROM_DATE,
                        RuleInterface::TO_DATE,
                        RuleInterface::SORT_ORDER
                    ]
                )->addFieldToFilter(RuleInterface::RULE_ID, ['in' => $ruleIds])
                    ->addFieldToFilter(RuleInterface::IS_ACTIVE, ['eq' => 1])
                    ->addOrder(RuleInterface::TO_DATE, 'ASC')
                    ->addOrder(RuleInterface::FROM_DATE, 'DESC')
                    ->addOrder(RuleInterface::SORT_ORDER, 'DESC');
                $this->logger->debug('Collection: ' . print_r($collection->getData(), true));
                if ($collection->getSize() > 1) {
                    $this->calculateRulePriority(
                        $collection->getData(),
                        'sort_order',
                        []
                    );
                }
            }
            $this->logger->debug('Time: ' . (microtime(true) - $startTime));

        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $salesCountdownRule;
    }

    /**
     * Calculate rule priority
     *
     * @param array $ruleItems
     * @param string $sortBy
     * @param array $rules
     * @return array
     * @deprecated
     */
    private function calculateRulePriority(
        array $ruleItems,
        string $sortBy,
        array $rules
    ): array {
        $this->logger->debug('Rule Items: ' . print_r($ruleItems, true));
        $this->logger->debug('Sort by: ' . $sortBy);

        $lastValue = -1;
        foreach ($ruleItems as $key => $item) {
            if ($lastValue <= (int)$item[$sortBy]) {
                $rules[$key] = $item;
            }

            $lastValue = (int)$item[$sortBy];

            if (isset($rules[$key - 1][$sortBy])
                && (int)$item[$sortBy] < $lastValue) {
                unset($rules[$key - 1]);
                $this->logger->debug('Unset: ' . $key);
            }
            $this->logger->debug('Last value: ' . $lastValue);
            $this->logger->debug('Item value: ' . (int)$item[$sortBy]);
        }

        if (count($rules) > 1) {
            $this->logger->debug('Rules bigger than one');
        }

        $this->logger->debug('Rules: ' . print_r($rules, true));

        return $rules;
    }

    /**
     * Get date value by the timezone of the website
     *
     * @param int $websiteId
     * @return int
     * @throws \DateInvalidTimeZoneException
     * @throws \DateMalformedStringException
     */
    private function getDateByWebsiteTimeZone(int $websiteId): int
    {
        $websiteTimeZone = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId);
        $dateTime = new \DateTime('now', new \DateTimeZone($websiteTimeZone));

        return $dateTime->getTimestamp();
    }
}
