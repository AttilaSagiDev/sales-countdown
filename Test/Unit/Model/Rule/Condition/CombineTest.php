<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule\Condition;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\Condition\Combine;
use Space\SalesCountdown\Model\Rule\Condition\Product;
use Space\SalesCountdown\Model\Rule\Condition\ProductFactory;
use Magento\Rule\Model\Condition\Context;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CombineTest extends TestCase
{
    /**
     * @var Combine
     */
    private Combine $model;

    /**
     * @var Context|MockObject
     */
    private Context|MockObject $contextMock;

    /**
     * @var ProductFactory|MockObject
     */
    private ProductFactory|MockObject $productFactoryMock;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $productConditionMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);

        $this->productConditionMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadAttributeOptions', 'collectValidatedAttributes'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Combine::class,
            [
                'context' => $this->contextMock,
                'conditionFactory' => $this->productFactoryMock,
                'data' => []
            ]
        );
    }

    public function testGetNewChildSelectOptions(): void
    {
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productConditionMock);

        $this->productConditionMock->expects($this->once())
            ->method('loadAttributeOptions')
            ->willReturnSelf();

        $this->productConditionMock->setData('attribute_option', ['sku' => 'SKU', 'name' => 'Name']);

        $result = $this->model->getNewChildSelectOptions();

        $this->assertIsArray($result);

        $foundProductAttributes = false;
        foreach ($result as $option) {
            if (isset($option['label']) && (string)$option['label'] === 'Product Attribute') {
                $foundProductAttributes = true;
                $this->assertIsArray($option['value']);
                $this->assertCount(2, $option['value']);
                $this->assertEquals(
                    'Space\SalesCountdown\Model\Rule\Condition\Product|sku',
                    $option['value'][0]['value']
                );
                break;
            }
        }
        $this->assertTrue($foundProductAttributes);
    }

    public function testCollectValidatedAttributes(): void
    {
        $collectionMock = $this->createMock(Collection::class);

        $this->model->setConditions([$this->productConditionMock]);

        $this->productConditionMock->expects($this->once())
            ->method('collectValidatedAttributes')
            ->with($collectionMock);

        $result = $this->model->collectValidatedAttributes($collectionMock);
        $this->assertSame($this->model, $result);
    }
}
