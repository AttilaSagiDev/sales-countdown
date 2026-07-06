<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\SalesCountdownRule;
use Space\SalesCountdown\Api\Data\SalesCountdownRuleInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SalesCountdownRuleTest extends TestCase
{
    /**
     * @var SalesCountdownRule
     */
    private SalesCountdownRule $model;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(SalesCountdownRule::class);
    }

    public function testGetSetCountdownEndDate(): void
    {
        $endDate = '2024-12-31 23:59:59';
        $this->model->setCountdownEndDate($endDate);
        $this->assertEquals($endDate, $this->model->getCountdownEndDate());
    }

    public function testGetSetCountdownMessage(): void
    {
        $message = 'Special offer ends in %c';
        $this->model->setCountdownMessage($message);
        $this->assertEquals($message, $this->model->getCountdownMessage());
    }

    public function testGetCountdownEndDateDefault(): void
    {
        $this->assertNull($this->model->getData(SalesCountdownRuleInterface::COUNTDOWN_END_DATE));
    }

    public function testGetCountdownMessageDefault(): void
    {
        $this->assertNull($this->model->getCountdownMessage());
    }
}
