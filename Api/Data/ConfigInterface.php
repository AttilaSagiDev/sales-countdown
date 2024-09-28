<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

interface ConfigInterface
{
    /**
     * Enabled config path
     */
    public const XML_PATH_ENABLED = 'sales_countdown/sales_countdown_config/enabled';

    /**
     * Check if sales countdown module is enabled
     *
     * @return bool
     */
    public function isEnabled() : bool;
}
