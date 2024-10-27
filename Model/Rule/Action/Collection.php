<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\Rule\Action;

use Magento\Rule\Model\Action\Collection as ActionCollection;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\ActionFactory;
use Magento\CatalogRule\Model\Rule\Action\Product;

/**
 * Action collection class
 * Needed, but not used for now
 */
class Collection extends ActionCollection
{
    /**
     * Constructor
     *
     * @param Repository $assetRepo
     * @param LayoutInterface $layout
     * @param ActionFactory $actionFactory
     * @param array $data
     */
    public function __construct(
        Repository $assetRepo,
        LayoutInterface $layout,
        ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
        $this->setType(ActionCollection::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions(): array
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive(
            $actions,
            [
                ['value' => Product::class, 'label' => __('Update the Product')]
            ]
        );
        return $actions;
    }
}
