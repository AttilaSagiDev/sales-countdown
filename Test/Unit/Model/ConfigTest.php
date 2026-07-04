<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Space\SalesCountdown\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private Config $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface|MockObject $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Config($this->scopeConfigMock);
    }

    public function testIsEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                ConfigInterface::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn(true);

        $this->assertTrue($this->model->isEnabled());
    }

    public function testIsShowCountdown(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                ConfigInterface::XML_PATH_SHOW_COUNTDOWN,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);

        $this->assertTrue($this->model->isShowCountdown());
    }

    public function testGetCountdownText(): void
    {
        $expectedText = 'Hurry up! Ends in %c';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                ConfigInterface::XML_PATH_COUNTDOWN_TEXT,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($expectedText);

        $this->assertEquals($expectedText, $this->model->getCountdownText());
    }

    public function testGetNotificationText(): void
    {
        $expectedText = 'Special offer ends soon!';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                ConfigInterface::XML_PATH_NOTIFICATION_TEXT,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($expectedText);

        $this->assertEquals($expectedText, $this->model->getNotificationText());
    }
}
