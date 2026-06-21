<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsActive implements OptionSourceInterface
{
    /**
     * Enabled
     */
    public const int STATUS_ENABLED = 1;

    /**
     * Disabled
     */
    public const int STATUS_DISABLED = 0;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $availableOptions = $this->getAvailableStatuses();
        $options = [];

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }

    /**
     * Rules statuses
     *
     * @return array
     */
    public function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }
}
