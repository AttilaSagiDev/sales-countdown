<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Block\Adminhtml\Rule\Edit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Block\Adminhtml\Rule\Edit\SaveButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Ui\Component\Control\Container;

class SaveButtonTest extends TestCase
{
    /**
     * @var SaveButton
     */
    private SaveButton $model;

    /**
     * @var Context|MockObject
     */
    private Context|MockObject $contextMock;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->registryMock = $this->createMock(Registry::class);

        $this->model = new SaveButton(
            $this->contextMock,
            $this->registryMock
        );
    }

    public function testGetButtonData(): void
    {
        $data = $this->model->getButtonData();

        $this->assertIsArray($data);
        $this->assertEquals('Save', (string)$data['label']);
        $this->assertEquals('save primary', $data['class']);
        $this->assertEquals(Container::SPLIT_BUTTON, $data['class_name']);
        $this->assertArrayHasKey('options', $data);
        $this->assertCount(1, $data['options']);
        $this->assertEquals('Save & Close', (string)$data['options'][0]['label']);
    }
}
