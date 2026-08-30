<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Block\Adminhtml\Rule\Edit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Block\Adminhtml\Rule\Edit\BackButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class BackButtonTest extends TestCase
{
    /**
     * @var BackButton
     */
    private BackButton $model;

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

        $this->model = new BackButton(
            $this->contextMock,
            $this->registryMock
        );
    }

    public function testGetButtonData(): void
    {
        $backUrl = 'http://example.com/admin/sales/countdown/';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/', [])
            ->willReturn($backUrl);

        $data = $this->model->getButtonData();

        $this->assertIsArray($data);
        $this->assertEquals('Back', (string)$data['label']);
        $this->assertStringContainsString($backUrl, $data['on_click']);
        $this->assertEquals('back', $data['class']);
        $this->assertEquals(10, $data['sort_order']);
    }
}
