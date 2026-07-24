<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule\Source;

use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\Source\IsActive;
use Magento\Framework\Phrase;

class IsActiveTest extends TestCase
{
    /**
     * @var IsActive
     */
    private IsActive $sourceModel;

    protected function setUp(): void
    {
        $this->sourceModel = new IsActive();
    }

    public function testGetAvailableStatuses(): void
    {
        $statuses = $this->sourceModel->getAvailableStatuses();

        $this->assertArrayHasKey(IsActive::STATUS_ENABLED, $statuses);
        $this->assertArrayHasKey(IsActive::STATUS_DISABLED, $statuses);

        $this->assertInstanceOf(Phrase::class, $statuses[IsActive::STATUS_ENABLED]);
        $this->assertInstanceOf(Phrase::class, $statuses[IsActive::STATUS_DISABLED]);
    }

    public function testToOptionArray(): void
    {
        $options = $this->sourceModel->toOptionArray();

        $expectedStructure = [
            [
                'label' => __('Enabled'),
                'value' => IsActive::STATUS_ENABLED,
            ],
            [
                'label' => __('Disabled'),
                'value' => IsActive::STATUS_DISABLED,
            ],
        ];

        $this->assertCount(2, $options);
        $this->assertEquals($expectedStructure, $options);
    }
}
