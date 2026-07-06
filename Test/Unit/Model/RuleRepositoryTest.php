<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\RuleRepository;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Space\SalesCountdown\Model\RuleFactory;
use Space\SalesCountdown\Model\Rule;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Space\SalesCountdown\Api\Data\RuleInterfaceFactory;
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Space\SalesCountdown\Api\Data\RuleSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RuleRepositoryTest extends TestCase
{
    /**
     * @var RuleRepository
     */
    private RuleRepository $model;

    /**
     * @var ResourceRule|MockObject
     */
    private ResourceRule|MockObject $ruleResourceMock;

    /**
     * @var RuleFactory|MockObject
     */
    private RuleFactory|MockObject $ruleFactoryMock;

    /**
     * @var RuleInterfaceFactory|MockObject
     */
    private RuleInterfaceFactory|MockObject $dataRuleFactoryMock;

    /**
     * @var RuleCollectionFactory|MockObject
     */
    private RuleCollectionFactory|MockObject $ruleCollectionFactoryMock;

    /**
     * @var RuleSearchResultsInterfaceFactory|MockObject
     */
    private RuleSearchResultsInterfaceFactory|MockObject $searchResultsFactoryMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private DataObjectHelper|MockObject $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private DataObjectProcessor|MockObject $dataObjectProcessorMock;

    /**
     * @var HydratorInterface|MockObject
     */
    private HydratorInterface|MockObject $hydratorMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->ruleResourceMock = $this->createMock(ResourceRule::class);
        $this->ruleFactoryMock = $this->getMockBuilder(RuleFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->dataRuleFactoryMock = $this->createMock(RuleInterfaceFactory::class);
        $this->ruleCollectionFactoryMock = $this->createMock(RuleCollectionFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(RuleSearchResultsInterfaceFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->hydratorMock = $this->createMock(HydratorInterface::class);

        $this->model = $objectManagerHelper->getObject(
            RuleRepository::class,
            [
                'ruleResource' => $this->ruleResourceMock,
                'ruleFactory' => $this->ruleFactoryMock,
                'dataRuleFactory' => $this->dataRuleFactoryMock,
                'ruleCollectionFactory' => $this->ruleCollectionFactoryMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'hydrator' => $this->hydratorMock
            ]
        );
    }

    public function testGetById(): void
    {
        $ruleId = 1;
        $ruleMock = $this->createMock(Rule::class);

        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->ruleResourceMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId);

        $ruleMock->expects($this->once())
            ->method('getRuleId')
            ->willReturn($ruleId);

        $this->assertSame($ruleMock, $this->model->getById($ruleId));
        $this->assertSame($ruleMock, $this->model->getById($ruleId));
    }

    public function testGetByIdException(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $ruleId = 1;
        $ruleMock = $this->createMock(Rule::class);

        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->ruleResourceMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId);

        $ruleMock->expects($this->once())
            ->method('getRuleId')
            ->willReturn(null);

        $this->model->getById($ruleId);
    }

    public function testSave(): void
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->any())
            ->method('getRuleId')
            ->willReturn(1);

        $ruleMock->expects($this->any())
            ->method('getOrigData')
            ->willReturn(['some_data']);

        $this->ruleResourceMock->expects($this->once())
            ->method('save')
            ->with($ruleMock);

        $this->assertSame($ruleMock, $this->model->save($ruleMock));
    }

    public function testSaveException(): void
    {
        $this->expectException(CouldNotSaveException::class);
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->any())
            ->method('getRuleId')
            ->willReturn(1);

        $ruleMock->expects($this->any())
            ->method('getOrigData')
            ->willReturn(['some_data']);

        $this->ruleResourceMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willThrowException(new \Exception('Error'));

        $this->model->save($ruleMock);
    }

    public function testDelete(): void
    {
        $ruleMock = $this->createMock(Rule::class);
        $this->ruleResourceMock->expects($this->once())
            ->method('delete')
            ->with($ruleMock);

        $this->assertTrue($this->model->delete($ruleMock));
    }

    public function testDeleteException(): void
    {
        $this->expectException(CouldNotDeleteException::class);
        $ruleMock = $this->createMock(Rule::class);
        $this->ruleResourceMock->expects($this->once())
            ->method('delete')
            ->with($ruleMock)
            ->willThrowException(new \Exception('Error'));

        $this->model->delete($ruleMock);
    }

    public function testDeleteById(): void
    {
        $ruleId = 1;
        $ruleMock = $this->createMock(Rule::class);

        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);
        $this->ruleResourceMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId);
        $ruleMock->expects($this->any())
            ->method('getRuleId')
            ->willReturn($ruleId);

        $this->ruleResourceMock->expects($this->once())
            ->method('delete')
            ->with($ruleMock);

        $this->assertTrue($this->model->deleteById($ruleId));
    }
}
