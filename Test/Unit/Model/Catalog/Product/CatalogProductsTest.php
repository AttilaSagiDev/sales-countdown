<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Catalog\Product;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Catalog\Product\CatalogProducts;
use Space\SalesCountdown\Model\Catalog\Product\ConditionsToCollectionApplier;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Model\ProductFactory;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Model\Product;
use Space\SalesCountdown\Model\Rule\Condition\Combine;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

interface RuleMockInterface extends RuleInterface
{
    public function getConditions();
    public function setCollectedAttributes($attributes);
    public function getCollectedAttributes();
    public function getWebsiteIds();
}

class CatalogProductsTest extends TestCase
{
    /**
     * @var CatalogProducts
     */
    private CatalogProducts $model;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $productCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManagerMock;

    /**
     * @var Iterator|MockObject
     */
    private Iterator|MockObject $resourceIteratorMock;

    /**
     * @var ProductFactory|MockObject
     */
    private ProductFactory|MockObject $productFactoryMock;

    /**
     * @var ConditionsToCollectionApplier|MockObject
     */
    private ConditionsToCollectionApplier|MockObject $conditionsToCollectionApplierMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->resourceIteratorMock = $this->createMock(Iterator::class);
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->conditionsToCollectionApplierMock = $this->createMock(ConditionsToCollectionApplier::class);

        $this->model = $objectManager->getObject(
            CatalogProducts::class,
            [
                'productCollectionFactory' => $this->productCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'resourceIterator' => $this->resourceIteratorMock,
                'productFactory' => $this->productFactoryMock,
                'conditionsToCollectionApplier' => $this->conditionsToCollectionApplierMock
            ]
        );
    }

    public function testGetMatchingProductIds(): void
    {
        /** @var RuleMockInterface|MockObject $ruleMock */
        $ruleMock = $this->createMock(RuleMockInterface::class);

        $collectionMock = $this->createMock(Collection::class);
        $storeMock = $this->createMock(StoreInterface::class);
        $conditionMock = $this->createMock(Combine::class);
        $selectMock = $this->createMock(Select::class);
        $productMock = $this->createMock(Product::class);

        $ruleMock->expects($this->once())->method('setCollectedAttributes')->with([])->willReturnSelf();
        $ruleMock->expects($this->atLeastOnce())->method('getWebsiteIds')->willReturn([1]);
        $ruleMock->expects($this->once())->method('getCollectedAttributes')->willReturn(['attr']);

        $this->productCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn(1);

        $collectionMock->expects($this->once())->method('setStoreId')->with(1);
        $collectionMock->expects($this->once())->method('addWebsiteFilter')->with([1]);
        $collectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);

        $ruleMock->expects($this->atLeastOnce())->method('getConditions')->willReturn($conditionMock);
        $conditionMock->expects($this->once())->method('collectValidatedAttributes')->with($collectionMock);
        $conditionMock->expects($this->once())->method('getConditions')->willReturn(['something']);

        $this->conditionsToCollectionApplierMock->expects($this->once())
            ->method('applyConditionsToCollection')
            ->with($conditionMock, $collectionMock)
            ->willReturn($collectionMock);

        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($productMock);

        $this->resourceIteratorMock->expects($this->once())
            ->method('walk')
            ->with(
                $selectMock,
                [[$this->model, 'callbackValidateProduct']],
                [
                    'attributes' => ['attr'],
                    'product' => $productMock,
                    'rule' => $ruleMock
                ]
            );

        $this->assertEquals([], $this->model->getMatchingProductIds($ruleMock));
    }
}
