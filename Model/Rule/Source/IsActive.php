<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Space\SalesCountdown\Model\Rule;

class IsActive implements OptionSourceInterface
{
    /**
     * @var Rule
     */
    private Rule $rule;

    /**
     * Constructor
     *
     * @param Rule $rule
     */
    public function __construct(
        Rule $rule
    ) {
        $this->rule = $rule;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $availableOptions = $this->rule->getAvailableStatuses();
        $options = [];

        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
