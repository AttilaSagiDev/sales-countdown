<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

interface ConfigInterface
{
    /**
     * Enabled config path
     */
    public const string XML_PATH_ENABLED = 'sales_countdown/sales_countdown_config/enabled';

    /**
     * Show countdown config path
     */
    public const string XML_PATH_SHOW_COUNTDOWN = 'sales_countdown/sales_countdown_display/show_countdown';

    /**
     * Countdown text config path
     */
    public const string XML_PATH_COUNTDOWN_TEXT = 'sales_countdown/sales_countdown_display/countdown_text';

    /**
     * Notification text config path
     */
    public const string XML_PATH_NOTIFICATION_TEXT = 'sales_countdown/sales_countdown_display/notification_text';

    /**
     * Check if sales countdown module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Check to show countdown timer
     *
     * @return bool
     */
    public function isShowCountdown(): bool;

    /**
     * Get countdown text
     *
     * @return string
     */
    public function getCountdownText(): string;

    /**
     * Get notification text
     *
     * @return string
     */
    public function getNotificationText(): string;
}
