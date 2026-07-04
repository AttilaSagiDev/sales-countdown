<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Block\Adminhtml\Rule\Edit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Block\Adminhtml\Rule\Edit\GenericButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Space\SalesCountdown\Model\Rule;

class GenericButtonTest extends TestCase
{
    /**
     * @var GenericButton
     */
    private GenericButton $model;

    /**
     * @var Context|MockObject
     */
    private Context|MockObject $contextMock;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface|MockObject $urlBuilderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $this->model = new GenericButton(
            $this->contextMock,
            $this->registryMock
        );
    }

    public function testGetRuleId(): void
    {
        $ruleId = 123;
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRuleId'])
            ->getMock();
        $ruleMock->expects($this->once())
            ->method('getRuleId')
            ->willReturn($ruleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_sales_countdown_rule')
            ->willReturn($ruleMock);

        $this->assertEquals($ruleId, $this->model->getRuleId());
    }

    public function testGetRuleIdNull(): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_sales_countdown_rule')
            ->willReturn(null);

        $this->assertNull($this->model->getRuleId());
    }

    public function testGetUrl(): void
    {
        $route = 'sales/countdown/edit';
        $params = ['rule_id' => 1];
        $expectedUrl = 'http://example.com/admin/sales/countdown/edit/rule_id/1';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($route, $params)
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->model->getUrl($route, $params));
    }
}
