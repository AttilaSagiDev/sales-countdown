<?php
/**
 * Copyright (c) 2026 Attila Sagi
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
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface;
use Magento\Framework\Exception\LocalizedException;
use Space\SalesCountdown\Model\ResourceModel\Rule\Collection;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
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
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

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
     * @param ConfigInterface $config
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(// NOSONAR
        SalesCountdownRuleFactory $salesCountdownRuleFactory,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        ResourceRule $resourceRule,
        CollectionFactory $collectionFactory,
        TimezoneInterface $timezone,
        ConfigInterface $config,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        $this->salesCountdownRuleFactory = $salesCountdownRuleFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resourceRule = $resourceRule;
        $this->collectionFactory = $collectionFactory;
        $this->timezone = $timezone;
        $this->config = $config;
        $this->escaper = $escaper;
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
        $salesCountdownRule = $this->salesCountdownRuleFactory->create();
        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
            $storeId = $storeId === 0 ? $this->storeManager->getDefaultStoreView()->getId() : $storeId;
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $dateTimeStamp = $this->getDateByWebsiteTimeZone($websiteId);
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $rulesResult = $this->resourceRule->getRulesFromProduct(
                $dateTimeStamp,
                $websiteId,
                $customerGroupId,
                $productId
            );

            if (!empty($rulesResult)) {
                $ruleIds = array_column($rulesResult, RuleInterface::RULE_ID);
                /** @var Collection $collection */
                $collection = $this->collectionFactory->create();
                $collection->addFieldToSelect(
                    [
                        RuleInterface::RULE_ID,
                        RuleInterface::NAME,
                        RuleInterface::MESSAGE,
                        RuleInterface::FROM_DATE,
                        RuleInterface::TO_DATE,
                        RuleInterface::SORT_ORDER
                    ]
                )->addFieldToFilter(RuleInterface::RULE_ID, ['in' => $ruleIds])
                    ->addFieldToFilter(RuleInterface::IS_ACTIVE, ['eq' => 1])
                    ->addOrder(RuleInterface::TO_DATE, 'ASC')
                    ->addOrder(RuleInterface::SORT_ORDER, 'ASC');

                if ($collection->getSize() > 1) {
                    $rule = $this->calculateRulePriority($collection);
                    $salesCountdownRule->setCountdownEndDate($rule[RuleInterface::TO_DATE]);
                    $salesCountdownRule->setCountdownMessage($this->getCountdownMessage($rule[RuleInterface::MESSAGE]));
                } else {
                    $salesCountdownRule->setCountdownEndDate($collection->getFirstItem()->getToDate());
                    $salesCountdownRule->setCountdownMessage(
                        $this->getCountdownMessage($collection->getFirstItem()->getMessage())
                    );
                }
            } else {
                $salesCountdownRule->setCountdownEndDate('');
                $salesCountdownRule->setCountdownMessage('');
            }
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
     * @param Collection $ruleItems
     * @return array
     */
    private function calculateRulePriority(Collection $ruleItems): array
    {
        $rule = [];
        $currentSortOrder = $ruleItems->getFirstItem()->getSortOrder();
        foreach ($ruleItems as $ruleItem) {
            if ($ruleItem->getSortOrder() <= $currentSortOrder) {
                $rule[] = $ruleItem->getData();

                $currentSortOrder = $ruleItem->getSortOrder();
            }
        }

        return empty($rule) ? $ruleItems->getFirstItem()->getData() : $rule[0];
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

    /**
     * Get countdown message
     *
     * @param string $message
     * @return string
     */
    private function getCountdownMessage(string $message): string
    {
        $text = $this->config->isShowCountdown()
            ? $this->config->getCountdownText() : $this->config->getNotificationText();

        return $message === '' ? $this->escaper->escapeHtml($text, ['strong'])
            : $this->escaper->escapeHtml($message, ['strong']);
    }
}
