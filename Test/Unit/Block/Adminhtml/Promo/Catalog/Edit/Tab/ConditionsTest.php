<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Block\Adminhtml\Promo\Catalog\Edit\Tab;

use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Block\Adminhtml\Promo\Catalog\Edit\Tab\Conditions;
use Magento\Framework\Phrase;

class ConditionsTest extends TestCase
{
    /**
     * @var Conditions
     */
    private Conditions $model;

    protected function setUp(): void
    {
        $reflection = new \ReflectionClass(Conditions::class);
        $this->model = $reflection->newInstanceWithoutConstructor();
    }

    public function testGetTabLabel(): void
    {
        $label = $this->model->getTabLabel();
        $this->assertInstanceOf(Phrase::class, $label);
        $this->assertEquals('Conditions', (string)$label);
    }

    public function testGetTabTitle(): void
    {
        $title = $this->model->getTabTitle();
        $this->assertInstanceOf(Phrase::class, $title);
        $this->assertEquals('Conditions', (string)$title);
    }

    public function testCanShowTab(): void
    {
        $this->assertTrue($this->model->canShowTab());
    }

    public function testIsHidden(): void
    {
        $this->assertFalse($this->model->isHidden());
    }

    public function testIsAjaxLoaded(): void
    {
        $this->assertFalse($this->model->isAjaxLoaded());
    }

    public function testTabMethods(): void
    {
        $this->assertNull($this->model->getTabClass());
        $this->assertNull($this->model->getTabUrl());
    }
}
