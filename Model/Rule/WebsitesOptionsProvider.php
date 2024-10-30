<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Rule;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

class WebsitesOptionsProvider implements OptionSourceInterface
{
    /**
     * @var Store
     */
    private Store $store;

    /**
     * Constructor
     *
     * @param Store $store
     */
    public function __construct(
        Store $store
    ) {
        $this->store = $store;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->store->getWebsiteValuesForForm();
    }
}
