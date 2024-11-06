<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Catalog\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Store\Model\ScopeInterface;

class HandleRuleProduct
{
    /**
     * Table name
     */
    private const TABLE_NAME = 'sales_countdown_rule_product';

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

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
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Execute products save
     *
     * @param RuleInterface $rule
     * @param array $productIds
     * @return bool
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(RuleInterface $rule, array $productIds): bool
    {
        if (!$rule->isActive() || empty($rule->getWebsiteIds())) {
            return false;
        }

        try {
            $connection = $this->resourceConnection->getConnection();
            $websiteIds = $rule->getWebsiteIds();
            $table = $this->resourceConnection->getTableName(self::TABLE_NAME);
            $ruleId = $rule->getRuleId();
            $customerGroupIds = $rule->getCustomerGroupIds();

            $this->removeCurrentRuleProducts($ruleId);

            $rows = [];
            foreach ($websiteIds as $websiteId) {
                $fromTimeInAdminTimeZone = $this->parseDateByWebsiteTimeZone(
                    (string)$rule->getFromDate(),
                    (int)$websiteId
                );
                $toTimeInAdminTimeZone = $this->parseDateByWebsiteTimeZone(
                    (string)$rule->getToDate(),
                    (int)$websiteId
                );

                foreach ($productIds as $productId => $validationByWebsite) {
                    if (empty($validationByWebsite[$websiteId])) {
                        continue;
                    }

                    $rows = $this->getDataByCustomerIds(
                        $rows,
                        $customerGroupIds,
                        $ruleId,
                        $fromTimeInAdminTimeZone,
                        $toTimeInAdminTimeZone,
                        $productId,
                        (int)$websiteId
                    );
                }
            }

            if (!empty($rows)) {
                $connection->insertMultiple($table, $rows);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Get data by customer Ids
     *
     * @param array $rows
     * @param array $customerGroupIds
     * @param int $ruleId
     * @param int $fromTimeInAdminTimeZone
     * @param int $toTimeInAdminTimeZone
     * @param int $productId
     * @param int $websiteId
     * @return array
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function getDataByCustomerIds(
        array $rows,
        array $customerGroupIds,
        int $ruleId,
        int $fromTimeInAdminTimeZone,
        int $toTimeInAdminTimeZone,
        int $productId,
        int $websiteId
    ): array {
        foreach ($customerGroupIds as $customerGroupId) {
            $rows[] = [
                RuleInterface::RULE_ID => $ruleId,
                'from_time' => $fromTimeInAdminTimeZone,
                'to_time' => $toTimeInAdminTimeZone,
                'customer_group_id' => $customerGroupId,
                'product_id' => $productId,
                'website_id' => $websiteId
            ];
        }

        return $rows;
    }

    /**
     * Parse date value by the timezone of the website
     *
     * @param string $date
     * @param int $websiteId
     * @return int
     * @throws \DateMalformedStringException
     * @throws \DateInvalidTimeZoneException
     */
    private function parseDateByWebsiteTimeZone(string $date, int $websiteId): int
    {
        if (empty($date)) {
            return 0;
        }

        $websiteTimeZone = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId);
        $dateTime = new \DateTime($date, new \DateTimeZone($websiteTimeZone));

        return $dateTime->getTimestamp();
    }

    /**
     * Remove all products of the rule
     *
     * @param int $ruleId
     * @return void
     */
    private function removeCurrentRuleProducts(int $ruleId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $connection->getTableName(self::TABLE_NAME),
            $connection->quoteInto('rule_id=?', $ruleId)
        );
    }
}
