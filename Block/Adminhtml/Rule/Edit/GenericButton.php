<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;

class GenericButton
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * Return rule ID
     *
     * @return int|null
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     */
    public function getRuleId(): ?int
    {
        $rule = $this->registry->registry('current_sales_countdown_rule');
        return $rule ? $rule->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
