<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule\Condition;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\Condition\Combine as CombinedCondition;
use Space\SalesCountdown\Model\Rule\Condition\MappableConditionsProcessor;
use Space\SalesCountdown\Model\Rule\Condition\Product as SimpleCondition;

class MappableConditionsProcessorTest extends TestCase
{
    /**
     * @var MappableConditionsProcessor
     */
    private MappableConditionsProcessor $processor;

    /**
     * @var CustomConditionProviderInterface|MockObject
     */
    private CustomConditionProviderInterface|MockObject $customConditionProviderMock;

    /**
     * @var EavConfig|MockObject
     */
    private EavConfig|MockObject $eavConfigMock;

    protected function setUp(): void
    {
        $this->customConditionProviderMock = $this->createMock(CustomConditionProviderInterface::class);
        $this->eavConfigMock = $this->createMock(EavConfig::class);

        $objectManager = new ObjectManager($this);
        $this->processor = $objectManager->getObject(
            MappableConditionsProcessor::class,
            [
                'customConditionProvider' => $this->customConditionProviderMock,
                'eavConfig' => $this->eavConfigMock
            ]
        );
    }

    private function getMockForCombinedCondition(
        array $subConditions,
        ?string $aggregator = 'all'
    ): CombinedCondition|MockObject {
        $mock = $this->getMockBuilder(CombinedCondition::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $mock->setConditions($subConditions);
        if ($aggregator !== null) {
            $mock->setAggregator($aggregator);
        }
        $mock->setType(CombinedCondition::class);

        return $mock;
    }

    private function getMockForSimpleCondition(string $attribute): SimpleCondition|MockObject
    {
        $mock = $this->getMockBuilder(SimpleCondition::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $mock->setAttribute($attribute);
        $mock->setType(SimpleCondition::class);

        return $mock;
    }

    public function testRebuildConditionsTreeWithValidCustomConditionProviderField(): void
    {
        $field = 'custom_field';
        $simpleCondition = $this->getMockForSimpleCondition($field);
        $inputCondition = $this->getMockForCombinedCondition([$simpleCondition], 'all');

        $this->customConditionProviderMock->expects($this->once())
            ->method('hasProcessorForField')
            ->with($field)
            ->willReturn(true);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(1, $result->getConditions());
        $this->assertSame($simpleCondition, $result->getConditions()[0]);
    }

    public function testRebuildConditionsTreeWithValidEavAttribute(): void
    {
        $field = 'sku';
        $simpleCondition = $this->getMockForSimpleCondition($field);
        $inputCondition = $this->getMockForCombinedCondition([$simpleCondition], 'all');

        $this->customConditionProviderMock->expects($this->once())
            ->method('hasProcessorForField')
            ->with($field)
            ->willReturn(false);

        $attributeMock = $this->createMock(AbstractAttribute::class);

        $attributeMock->expects($this->once())
            ->method('getBackendType')
            ->willReturn('varchar');

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(CatalogProduct::ENTITY, $field)
            ->willReturn($attributeMock);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(1, $result->getConditions());
        $this->assertSame($simpleCondition, $result->getConditions()[0]);
    }

    public function testRebuildConditionsTreeWithInvalidEavAttributeNullBackendType(): void
    {
        $field = 'invalid_field';
        $simpleCondition = $this->getMockForSimpleCondition($field);
        $inputCondition = $this->getMockForCombinedCondition([$simpleCondition], 'all');

        $this->customConditionProviderMock->expects($this->once())
            ->method('hasProcessorForField')
            ->with($field)
            ->willReturn(false);

        $attributeMock = $this->createMock(AbstractAttribute::class);

        $attributeMock->expects($this->once())
            ->method('getBackendType')
            ->willReturn(null);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(CatalogProduct::ENTITY, $field)
            ->willReturn($attributeMock);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(0, $result->getConditions());
    }

    public function testRebuildConditionsTreeWithNullAttributeReturnedFromEavConfig(): void
    {
        $field = 'non_existent_field';
        $simpleCondition = $this->getMockForSimpleCondition($field);
        $inputCondition = $this->getMockForCombinedCondition([$simpleCondition], 'all');

        $this->customConditionProviderMock->expects($this->once())
            ->method('hasProcessorForField')
            ->with($field)
            ->willReturn(false);

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(CatalogProduct::ENTITY, $field)
            ->willReturn(null);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(0, $result->getConditions());
    }

    public function testRebuildConditionsTreeWithAggregatorAnyAndInvalidConditionRemovesAll(): void
    {
        $validField = 'category_ids';
        $invalidField = 'invalid_attr';

        $validSimpleCondition = $this->getMockForSimpleCondition($validField);
        $invalidSimpleCondition = $this->getMockForSimpleCondition($invalidField);

        $inputCondition = $this->getMockForCombinedCondition(
            [$validSimpleCondition, $invalidSimpleCondition],
            'any'
        );

        $this->customConditionProviderMock->expects($this->exactly(2))
            ->method('hasProcessorForField')
            ->willReturnCallback(function (string $field) use ($validField) {
                return $field === $validField;
            });

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(CatalogProduct::ENTITY, $invalidField)
            ->willReturn(null);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(0, $result->getConditions());
    }

    public function testRebuildConditionsTreeWithAggregatorAllAndInvalidConditionKeepsValid(): void
    {
        $validField = 'category_ids';
        $invalidField = 'invalid_attr';

        $validSimpleCondition = $this->getMockForSimpleCondition($validField);
        $invalidSimpleCondition = $this->getMockForSimpleCondition($invalidField);

        $inputCondition = $this->getMockForCombinedCondition(
            [$validSimpleCondition, $invalidSimpleCondition],
            'all'
        );

        $this->customConditionProviderMock->expects($this->exactly(2))
            ->method('hasProcessorForField')
            ->willReturnCallback(function (string $field) use ($validField) {
                return $field === $validField;
            });

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(CatalogProduct::ENTITY, $invalidField)
            ->willReturn(null);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(1, $result->getConditions());
        $this->assertSame($validSimpleCondition, $result->getConditions()[0]);
    }

    public function testRebuildConditionsTreeWithNestedCombinedConditions(): void
    {
        $validField = 'sku';
        $invalidField = 'invalid_attr';

        $validSimpleCondition = $this->getMockForSimpleCondition($validField);
        $invalidSimpleCondition = $this->getMockForSimpleCondition($invalidField);

        $subCombinedValid = $this->getMockForCombinedCondition([$validSimpleCondition], 'all');
        $subCombinedInvalid = $this->getMockForCombinedCondition([$invalidSimpleCondition], 'all');

        $rootCondition = $this->getMockForCombinedCondition([$subCombinedValid, $subCombinedInvalid], 'all');

        $this->customConditionProviderMock->expects($this->exactly(2))
            ->method('hasProcessorForField')
            ->willReturnCallback(function (string $field) use ($validField) {
                return $field === $validField;
            });

        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(CatalogProduct::ENTITY, $invalidField)
            ->willReturn(null);

        $result = $this->processor->rebuildConditionsTree($rootCondition);

        $this->assertCount(1, $result->getConditions());
        $rebuiltSubCondition = $result->getConditions()[0];
        $this->assertInstanceOf(CombinedCondition::class, $rebuiltSubCondition);
        $this->assertCount(1, $rebuiltSubCondition->getConditions());
        $this->assertSame($validSimpleCondition, $rebuiltSubCondition->getConditions()[0]);
    }

    public function testRebuildConditionsTreeThrowsInputExceptionOnUndefinedConditionType(): void
    {
        $invalidConditionMock = new class extends \Magento\Rule\Model\Condition\AbstractCondition {
            public function __construct()
            {
            }
            public function getType(): string
            {
                return 'Unknown\Condition\Type';
            }
        };

        $inputCondition = $this->getMockForCombinedCondition([$invalidConditionMock], 'all');

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined condition type "Unknown\Condition\Type" passed in.');

        $this->processor->rebuildConditionsTree($inputCondition);
    }

    public function testRebuildConditionsTreeWithNullAggregator(): void
    {
        $validField = 'category_ids';
        $validSimpleCondition = $this->getMockForSimpleCondition($validField);

        $inputCondition = $this->getMockForCombinedCondition([$validSimpleCondition], null);

        $this->customConditionProviderMock->expects($this->once())
            ->method('hasProcessorForField')
            ->with($validField)
            ->willReturn(true);

        $result = $this->processor->rebuildConditionsTree($inputCondition);

        $this->assertCount(1, $result->getConditions());
        $this->assertSame($validSimpleCondition, $result->getConditions()[0]);
    }
}
