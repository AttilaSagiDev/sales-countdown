<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Block\Adminhtml\Rule\Edit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Block\Adminhtml\Rule\Edit\DeleteButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Space\SalesCountdown\Model\Rule;

class DeleteButtonTest extends TestCase
{
    /**
     * @var DeleteButton
     */
    private DeleteButton $model;

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

        $this->model = new DeleteButton(
            $this->contextMock,
            $this->registryMock
        );
    }

    public function testGetButtonData(): void
    {
        $ruleId = 123;
        $deleteUrl = "http://example.com/admin/sales/countdown/delete/rule_id/$ruleId";

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRuleId'])
            ->getMock();
        $ruleMock->expects($this->any())
            ->method('getRuleId')
            ->willReturn($ruleId);

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with('current_sales_countdown_rule')
            ->willReturn($ruleMock);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/delete', ['rule_id' => $ruleId])
            ->willReturn($deleteUrl);

        $data = $this->model->getButtonData();

        $this->assertIsArray($data);
        $this->assertEquals('Delete Rule', (string)$data['label']);
        $this->assertStringContainsString($deleteUrl, $data['on_click']);
        $this->assertEquals('delete', $data['class']);
    }

    public function testGetButtonDataEmpty(): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_sales_countdown_rule')
            ->willReturn(null);

        $this->assertEquals([], $this->model->getButtonData());
    }
}
