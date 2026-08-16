<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Controller\Adminhtml\Rule\NewConditionHtml;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Model\Rule\Condition\Product;

class NewConditionHtmlTest extends TestCase
{
    /**
     * @var NewConditionHtml
     */
    private NewConditionHtml $controller;

    /**
     * @var RequestInterface|MockObject
     */
    private RequestInterface|MockObject $requestMock;

    /**
     * @var HttpResponse|MockObject
     */
    private HttpResponse|MockObject $responseMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private ObjectManagerInterface|MockObject $objectManagerMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(HttpResponse::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getRequest')->willReturn($this->requestMock);
        $contextMock->method('getResponse')->willReturn($this->responseMock);
        $contextMock->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->controller = $objectManagerHelper->getObject(
            NewConditionHtml::class,
            [
                'context' => $contextMock
            ]
        );
    }

    public function testExecute(): void
    {
        $objectId = 'conditions__1__1';
        $formNamespace = 'sales_countdown_rule_form';
        $jsFormObject = 'rule_conditions_fieldset';
        $typeParam = Product::class . '|category_ids';
        $expectedType = Product::class;
        $expectedHtml = '<div>Condition HTML</div>';

        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['id', null, $objectId],
                ['form_namespace', null, $formNamespace],
                ['type', '', $typeParam],
                ['form', null, $jsFormObject],
            ]);

        $ruleMock = $this->createMock(Rule::class);
        $conditionMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['asHtmlRecursive'])
            ->getMock();

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [$expectedType, [], $conditionMock],
                [Rule::class, [], $ruleMock],
            ]);

        $conditionMock->expects($this->once())
            ->method('asHtmlRecursive')
            ->willReturn($expectedHtml);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($expectedHtml);

        $this->controller->execute();

        $this->assertEquals($objectId, $conditionMock->getId());
        $this->assertEquals($expectedType, $conditionMock->getType());
        $this->assertSame($ruleMock, $conditionMock->getRule());
        $this->assertEquals('conditions', $conditionMock->getPrefix());
        $this->assertEquals('category_ids', $conditionMock->getAttribute());
        $this->assertEquals($jsFormObject, $conditionMock->getJsFormObject());
        $this->assertEquals($formNamespace, $conditionMock->getFormName());
    }

    public function testExecuteWithoutAttribute(): void
    {
        $objectId = 'conditions__1';
        $formNamespace = 'sales_countdown_rule_form';
        $jsFormObject = 'rule_conditions_fieldset';
        $typeParam = Product::class;
        $expectedType = Product::class;
        $expectedHtml = '<div>Product HTML</div>';

        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['id', null, $objectId],
                ['form_namespace', null, $formNamespace],
                ['type', '', $typeParam],
                ['form', null, $jsFormObject],
            ]);

        $ruleMock = $this->createMock(Rule::class);
        $conditionMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['asHtmlRecursive'])
            ->getMock();

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [$expectedType, [], $conditionMock],
                [Rule::class, [], $ruleMock],
            ]);

        $conditionMock->expects($this->once())
            ->method('asHtmlRecursive')
            ->willReturn($expectedHtml);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($expectedHtml);

        $this->controller->execute();

        $this->assertEquals($objectId, $conditionMock->getId());
        $this->assertEquals($expectedType, $conditionMock->getType());
        $this->assertSame($ruleMock, $conditionMock->getRule());
        $this->assertEquals('conditions', $conditionMock->getPrefix());
        $this->assertNull($conditionMock->getAttribute());
        $this->assertEquals($jsFormObject, $conditionMock->getJsFormObject());
        $this->assertEquals($formNamespace, $conditionMock->getFormName());
    }

    public function testExecuteInvalidType(): void
    {
        $this->requestMock->method('getParam')
            ->willReturnMap([
                ['id', null, 'conditions__1'],
                ['form_namespace', null, 'sales_countdown_rule_form'],
                ['type', '', \stdClass::class],
                ['form', null, 'rule_conditions_fieldset'],
            ]);

        $this->objectManagerMock->expects($this->never())
            ->method('create');

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with('');

        $this->controller->execute();
    }
}
