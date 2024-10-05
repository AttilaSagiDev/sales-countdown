<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for rule search results.
 * @api
 */
interface RuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get rule list
     *
     * @return \Space\SalesCountdown\Api\Data\RuleInterface[]
     */
    public function getItems();

    /**
     * Set rule list
     *
     * @param \Space\SalesCountdown\Api\Data\RuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
