<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Model\Rule\Condition\CombineFactory;
use Space\SalesCountdown\Model\Rule\Action\CollectionFactory;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Action\Collection;

class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    private Rule $model;

    /**
     * @var CombineFactory|MockObject
     */
    private CombineFactory|MockObject $combineFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $actionCollectionFactoryMock;

    /**
     * @var ResourceRule|MockObject
     */
    private ResourceRule|MockObject $ruleResourceModelMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->combineFactoryMock = $this->getMockBuilder(CombineFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->actionCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->ruleResourceModelMock = $this->getMockBuilder(ResourceRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            Rule::class,
            [
                'combineFactory' => $this->combineFactoryMock,
                'actionCollectionFactory' => $this->actionCollectionFactoryMock,
                'ruleResourceModel' => $this->ruleResourceModelMock
            ]
        );
    }

    public function testGetSetRuleId(): void
    {
        $ruleId = 123;
        $this->model->setData(RuleInterface::RULE_ID, $ruleId);
        $this->assertEquals($ruleId, $this->model->getRuleId());

        $this->model->setRuleId(456);
        $this->assertEquals(456, $this->model->getRuleId());
    }

    public function testGetSetName(): void
    {
        $name = 'Test Rule';
        $this->model->setName($name);
        $this->assertEquals($name, $this->model->getName());
    }

    public function testGetSetMessage(): void
    {
        $message = 'Test Message';
        $this->model->setMessage($message);
        $this->assertEquals($message, $this->model->getMessage());
    }

    public function testGetSetDescription(): void
    {
        $description = 'Test Description';
        $this->model->setDescription($description);
        $this->assertEquals($description, $this->model->getDescription());
    }

    public function testGetSetFromDate(): void
    {
        $date = '2024-01-01';
        $this->model->setFromDate($date);
        $this->assertEquals($date, $this->model->getFromDate());
    }

    public function testGetSetToDate(): void
    {
        $date = '2024-12-31';
        $this->model->setToDate($date);
        $this->assertEquals($date, $this->model->getToDate());
    }

    public function testIsSetActive(): void
    {
        $this->model->setIsActive(true);
        $this->assertTrue($this->model->isActive());
    }

    public function testGetSetSortOrder(): void
    {
        $sortOrder = 10;
        $this->model->setSortOrder($sortOrder);
        $this->assertEquals($sortOrder, $this->model->getSortOrder());
    }

    public function testGetIdentities(): void
    {
        $ruleId = 123;
        $this->model->setRuleId($ruleId);
        $expected = [Rule::CACHE_TAG . '_' . $ruleId, Rule::CACHE_TAG . '_' . $ruleId];
        $this->assertEquals($expected, $this->model->getIdentities());
    }

    public function testGetConditionsInstance(): void
    {
        $combineMock = $this->getMockBuilder(Combine::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->combineFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($combineMock);

        $this->assertSame($combineMock, $this->model->getConditionsInstance());
    }

    public function testGetActionsInstance(): void
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->assertSame($collectionMock, $this->model->getActionsInstance());
    }

    public function testGetConditionsFieldSetId(): void
    {
        $ruleId = 5;
        $this->model->setRuleId($ruleId);
        $formName = 'test_form';
        $expected = $formName . 'rule_conditions_fieldset_' . $ruleId;
        $this->assertEquals($expected, $this->model->getConditionsFieldSetId($formName));
    }

    public function testGetCustomerGroupIds(): void
    {
        $ruleId = 1;
        $customerGroupIds = [0, 1];
        $this->model->setId($ruleId);

        $this->ruleResourceModelMock->expects($this->once())
            ->method('getCustomerGroupIds')
            ->with($ruleId)
            ->willReturn($customerGroupIds);

        $this->assertEquals($customerGroupIds, $this->model->getCustomerGroupIds());
        $this->assertEquals($customerGroupIds, $this->model->getCustomerGroupIds());
    }
}
