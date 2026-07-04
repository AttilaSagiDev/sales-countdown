<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model;

use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if sales countdown module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check to show countdown timer
     *
     * @return bool
     */
    public function isShowCountdown(): bool
    {
        return $this->scopeConfig->isSetFlag(
            ConfigInterface::XML_PATH_SHOW_COUNTDOWN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get countdown text
     *
     * @return string
     */
    public function getCountdownText(): string
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_COUNTDOWN_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get notification text
     *
     * @return string
     */
    public function getNotificationText(): string
    {
        return $this->scopeConfig->getValue(
            ConfigInterface::XML_PATH_NOTIFICATION_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
