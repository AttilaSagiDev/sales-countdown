<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Space\SalesCountdown\Model\Rule\Condition\Product;

class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    private Product $condition;

    /**
     * @var CatalogProduct|MockObject
     */
    private CatalogProduct|MockObject $productMock;

    /**
     * @var ProductResource|MockObject
     */
    private roductResource|MockObject $productResourceMock;

    /**
     * @var Attribute|MockObject
     */
    private Attribute|MockObject $attributeMock;

    /**
     * @var ProductCategoryList|MockObject
     */
    private ProductCategoryList|MockObject $productCategoryListMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->productMock = $this->createMock(CatalogProduct::class);
        $this->productResourceMock = $this->createMock(ProductResource::class);
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->productCategoryListMock = $this->createMock(ProductCategoryList::class);

        $this->productResourceMock->expects($this->any())
            ->method('loadAllAttributes')
            ->willReturnSelf();
        $this->productResourceMock->expects($this->any())
            ->method('getAttributesByCode')
            ->willReturn([$this->attributeMock]);

        $this->condition = $objectManagerHelper->getObject(
            Product::class,
            [
                'productResource' => $this->productResourceMock,
                'productCategoryList' => $this->productCategoryListMock
            ]
        );
    }

    private function setEntityAttributeValues(array $values): void
    {
        $reflection = new ReflectionProperty(Product::class, '_entityAttributeValues');
        $reflection->setValue($this->condition, $values);
    }

    public function testValidateCategoryIds(): void
    {
        $this->condition->setAttribute('category_ids');
        $this->condition->setOperator('()');
        $this->condition->setValue('1,2');
        $this->condition->setValueParsed(['1', '2']);

        $this->productMock->expects($this->any())
            ->method('getEntityId')
            ->willReturn(10);

        $this->productCategoryListMock->expects($this->once())
            ->method('getCategoryIds')
            ->with(10)
            ->willReturn([1, 3]);

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testValidateNullValueWithSpaceshipOperator(): void
    {
        $this->condition->setAttribute('sku');
        $this->condition->setOperator('<=>');

        $this->productMock->expects($this->once())
            ->method('getData')
            ->with('sku')
            ->willReturn(null);

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testValidateNullValueWithEqualsOperator(): void
    {
        $this->condition->setAttribute('sku');
        $this->condition->setOperator('==');

        $this->productMock->expects($this->once())
            ->method('getData')
            ->with('sku')
            ->willReturn(null);

        $this->assertFalse($this->condition->validate($this->productMock));
    }

    public function testValidateWithSetAttributeValueAndRestoreOldValue(): void
    {
        $attributeCode = 'price';
        $productId = 1;
        $storeId = 1;
        $oldAttrValue = '100.00';
        $newAttrValue = '50.00';

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue('50.00');

        $this->setEntityAttributeValues([
            $productId => [
                $storeId => $newAttrValue
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $getDataCalls = 0;
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls, $oldAttrValue, $newAttrValue) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return $oldAttrValue;
                }
                return $newAttrValue;
            });

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn(null);

        $setDataCalls = [];
        $this->productMock->expects($this->exactly(2))
            ->method('setData')
            ->willReturnCallback(function ($key, $val) use (&$setDataCalls) {
                $setDataCalls[] = [$key => $val];
                return $this->productMock;
            });

        $this->assertTrue($this->condition->validate($this->productMock));

        $this->assertEquals([
            ['price' => '50.00'],
            ['price' => '100.00']
        ], $setDataCalls);
    }

    public function testValidateRestoresNullOldAttrValueWithUnsetData(): void
    {
        $attributeCode = 'special_price';
        $productId = 1;
        $storeId = 1;
        $newAttrValue = '25.00';

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue('25.00');

        $this->setEntityAttributeValues([
            $productId => [
                $storeId => $newAttrValue
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $getDataCalls = 0;
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls, $newAttrValue) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return 'some_initial_value';
                }
                return $newAttrValue;
            });

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn(null);

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testRestoreOldAttrValueNullUnsetsData(): void
    {
        $this->condition->setAttribute('sku');

        $this->productMock->expects($this->once())
            ->method('unsetData')
            ->with('sku');

        $reflection = new \ReflectionMethod(Product::class, '_restoreOldAttrValue');
        $reflection->invoke($this->condition, $this->productMock, null);
    }

    public function testSetAttributeValueWithDefaultStoreIdFallback(): void
    {
        $attributeCode = 'price';
        $productId = 1;
        $storeId = 2;
        $defaultStoreValue = '30.00';

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue('30.00');

        $this->setEntityAttributeValues([
            $productId => [
                0 => $defaultStoreValue
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $getDataCalls = 0;
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls, $defaultStoreValue) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return '10.00';
                }
                return $defaultStoreValue;
            });

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn(null);

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testSetAttributeValueNoEntityAttributeValues(): void
    {
        $attributeCode = 'name';
        $productId = 99;

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue('Test Name');

        $this->setEntityAttributeValues([]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturn('Test Name');

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testSetAttributeValueMissingStoreIdAndDefaultStoreId(): void
    {
        $attributeCode = 'name';
        $productId = 1;
        $storeId = 5;

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue('Test Name');

        $this->setEntityAttributeValues([
            $productId => [
                2 => 'Other Store Value'
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturn('Test Name');

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testPrepareDatetimeValue(): void
    {
        $attributeCode = 'news_from_date';
        $productId = 1;
        $storeId = 1;
        $dateStr = '2026-08-01 00:00:00';
        $timestamp = strtotime($dateStr);

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue($dateStr);

        $this->setEntityAttributeValues([
            $productId => [
                $storeId => $dateStr
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->atLeastOnce())
            ->method('getBackendType')
            ->willReturn('datetime');

        $getDataCalls = 0;
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls, $timestamp) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return $timestamp;
                }
                return $timestamp;
            });

        $this->assertTrue($this->condition->validate($this->productMock));
        $this->assertEquals($timestamp, $this->condition->getValue());
    }

    public function testPrepareDatetimeValueWithEmptyValue(): void
    {
        $attributeCode = 'news_from_date';
        $productId = 1;
        $storeId = 1;

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('==');
        $this->condition->setValue('');

        $this->setEntityAttributeValues([
            $productId => [
                $storeId => ''
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->atLeastOnce())
            ->method('getBackendType')
            ->willReturn('datetime');

        $getDataCalls = 0;
        $this->productMock->expects($this->atLeastOnce())
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return '2026-01-01 00:00:00';
                }
                return null;
            });

        $this->condition->validate($this->productMock);
    }

    public function testPrepareMultiselectValue(): void
    {
        $attributeCode = 'color';
        $productId = 1;
        $storeId = 1;
        $multiselectStr = '10,20,30';

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('{}');
        $this->condition->setValue('20');

        $this->setEntityAttributeValues([
            $productId => [
                $storeId => $multiselectStr
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->any())
            ->method('getBackendType')
            ->willReturn('varchar');

        $this->attributeMock->expects($this->atLeastOnce())
            ->method('getFrontendInput')
            ->willReturn('multiselect');

        $getDataCalls = 0;
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return ['10', '20', '30'];
                }
                return ['10', '20', '30'];
            });

        $this->assertTrue($this->condition->validate($this->productMock));
    }

    public function testPrepareMultiselectValueEmptyString(): void
    {
        $attributeCode = 'color';
        $productId = 1;
        $storeId = 1;

        $this->condition->setAttribute($attributeCode);
        $this->condition->setOperator('{}');
        $this->condition->setValue('20');

        $this->setEntityAttributeValues([
            $productId => [
                $storeId => ''
            ]
        ]);

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);

        $this->productResourceMock->expects($this->any())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->any())
            ->method('getBackendType')
            ->willReturn('varchar');

        $this->attributeMock->expects($this->atLeastOnce())
            ->method('getFrontendInput')
            ->willReturn('multiselect');

        $getDataCalls = 0;
        $this->productMock->expects($this->exactly(2))
            ->method('getData')
            ->with($attributeCode)
            ->willReturnCallback(function () use (&$getDataCalls) {
                $getDataCalls++;
                if ($getDataCalls === 1) {
                    return [];
                }
                return [];
            });

        $this->assertFalse($this->condition->validate($this->productMock));
    }
}
