<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Service\CalculateCountdownRule;
use Space\SalesCountdown\Model\SalesCountdownRuleFactory;
use Space\SalesCountdown\Model\SalesCountdownRule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Space\SalesCountdown\Model\ResourceModel\Rule\CollectionFactory;
use Space\SalesCountdown\Model\ResourceModel\Rule\Collection;
use Space\SalesCountdown\Model\Rule as SalesCountdownRuleModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Space\SalesCountdown\Api\Data\ConfigInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CalculateCountdownRuleTest extends TestCase
{
    /**
     * @var CalculateCountdownRule
     */
    private CalculateCountdownRule $model;

    /**
     * @var SalesCountdownRuleFactory|MockObject
     */
    private SalesCountdownRuleFactory|MockObject $salesCountdownRuleFactoryMock;

    /**
     * @var SalesCountdownRule|MockObject
     */
    private SalesCountdownRule|MockObject $salesCountdownRuleMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private StoreInterface|MockObject $storeMock;

    /**
     * @var CustomerSession|MockObject
     */
    private CustomerSession|MockObject $customerSessionMock;

    /**
     * @var ResourceRule|MockObject
     */
    private ResourceRule|MockObject $resourceRuleMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private Collection|MockObject $collectionMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private TimezoneInterface|MockObject $timezoneMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private ConfigInterface|MockObject $configMock;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaperMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->salesCountdownRuleFactoryMock = $this->getMockBuilder(SalesCountdownRuleFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->salesCountdownRuleMock = $this->createMock(SalesCountdownRule::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->resourceRuleMock = $this->createMock(ResourceRule::class);
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->collectionMock = $this->createMock(Collection::class);
        $this->timezoneMock = $this->createMock(TimezoneInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->salesCountdownRuleFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->salesCountdownRuleMock);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $defaultStoreMock = $this->createMock(StoreInterface::class);
        $defaultStoreMock->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->willReturn($defaultStoreMock);

        $this->customerSessionMock->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn(0);

        $this->timezoneMock->expects($this->any())
            ->method('getConfigTimezone')
            ->willReturn('UTC');

        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->model = $objectManager->getObject(
            CalculateCountdownRule::class,
            [
                'salesCountdownRuleFactory' => $this->salesCountdownRuleFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'customerSession' => $this->customerSessionMock,
                'resourceRule' => $this->resourceRuleMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'timezone' => $this->timezoneMock,
                'config' => $this->configMock,
                'escaper' => $this->escaperMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testCalculateByProductIdNoRules(): void
    {
        $productId = 1;
        $this->resourceRuleMock->expects($this->once())
            ->method('getRulesFromProduct')
            ->willReturn([]);

        $this->salesCountdownRuleMock->expects($this->once())
            ->method('setCountdownEndDate')
            ->with('')
            ->willReturnSelf();
        $this->salesCountdownRuleMock->expects($this->once())
            ->method('setCountdownMessage')
            ->with('')
            ->willReturnSelf();

        $result = $this->model->calculateByProductId($productId);
        $this->assertSame($this->salesCountdownRuleMock, $result);
    }

    public function testCalculateByProductIdSingleRule(): void
    {
        $productId = 1;
        $ruleId = 10;
        $endDate = '2024-12-31 23:59:59';
        $message = 'Single rule message';

        $this->resourceRuleMock->expects($this->once())
            ->method('getRulesFromProduct')
            ->willReturn([['rule_id' => $ruleId]]);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock->expects($this->once())
            ->method('addFieldToSelect')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('addOrder')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);

        $ruleModelMock = $this->getMockBuilder(SalesCountdownRuleModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getToDate', 'getMessage'])
            ->getMock();
        $ruleModelMock->expects($this->atLeastOnce())->method('getToDate')->willReturn($endDate);
        $ruleModelMock->expects($this->atLeastOnce())->method('getMessage')->willReturn($message);

        $this->collectionMock->expects($this->atLeastOnce())
            ->method('getFirstItem')
            ->willReturn($ruleModelMock);

        $this->configMock->expects($this->any())->method('isShowCountdown')->willReturn(true);
        $this->configMock->expects($this->any())->method('getCountdownText')->willReturn('Default text');

        $this->salesCountdownRuleMock->expects($this->once())
            ->method('setCountdownEndDate')
            ->with($endDate)
            ->willReturnSelf();
        $this->salesCountdownRuleMock->expects($this->once())
            ->method('setCountdownMessage')
            ->with($message)
            ->willReturnSelf();

        $result = $this->model->calculateByProductId($productId);
        $this->assertSame($this->salesCountdownRuleMock, $result);
    }

    public function testCalculateByProductIdMultipleRules(): void
    {
        $productId = 1;
        $ruleId1 = 10;
        $ruleId2 = 20;
        $endDate1 = '2024-12-31 23:59:59';
        $endDate2 = '2025-01-15 12:00:00';
        $message1 = 'Rule 1 message';
        $message2 = 'Rule 2 message';

        $this->resourceRuleMock->expects($this->once())
            ->method('getRulesFromProduct')
            ->willReturn([['rule_id' => $ruleId1], ['rule_id' => $ruleId2]]);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock->expects($this->once())
            ->method('addFieldToSelect')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('addOrder')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(2);

        $data1 = [
            RuleInterface::TO_DATE => $endDate1,
            RuleInterface::MESSAGE => $message1,
            RuleInterface::SORT_ORDER => 10
        ];
        $ruleModelMock1 = $this->getMockBuilder(SalesCountdownRuleModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getSortOrder'])
            ->getMock();
        $ruleModelMock1->expects($this->any())->method('getSortOrder')->willReturn(10);
        $ruleModelMock1->expects($this->any())->method('getData')->willReturn($data1);

        $data2 = [
            RuleInterface::TO_DATE => $endDate2,
            RuleInterface::MESSAGE => $message2,
            RuleInterface::SORT_ORDER => 5
        ];
        $ruleModelMock2 = $this->getMockBuilder(SalesCountdownRuleModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getSortOrder'])
            ->getMock();
        $ruleModelMock2->expects($this->any())->method('getSortOrder')->willReturn(5);
        $ruleModelMock2->expects($this->any())->method('getData')->willReturn($data2);

        $this->collectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($ruleModelMock1);
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$ruleModelMock1, $ruleModelMock2]));

        $this->configMock->expects($this->any())->method('isShowCountdown')->willReturn(true);
        $this->configMock->expects($this->any())->method('getCountdownText')->willReturn('Default text');

        $this->salesCountdownRuleMock->expects($this->once())
            ->method('setCountdownEndDate')
            ->with($endDate1)
            ->willReturnSelf();
        $this->salesCountdownRuleMock->expects($this->once())
            ->method('setCountdownMessage')
            ->with($message1)
            ->willReturnSelf();

        $result = $this->model->calculateByProductId($productId);
        $this->assertSame($this->salesCountdownRuleMock, $result);
    }

    public function testCalculateByProductIdLocalizedException(): void
    {
        $productId = 1;
        $exceptionMessage = 'Test LocalizedException';

        $this->resourceRuleMock->expects($this->once())
            ->method('getRulesFromProduct')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exceptionMessage);

        $result = $this->model->calculateByProductId($productId);
        $this->assertSame($this->salesCountdownRuleMock, $result);
    }

    public function testCalculateByProductIdGenericException(): void
    {
        $productId = 1;
        $exceptionMessage = 'Test Generic Exception';

        $this->resourceRuleMock->expects($this->once())
            ->method('getRulesFromProduct')
            ->willThrowException(new \Exception($exceptionMessage));

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exceptionMessage);

        $result = $this->model->calculateByProductId($productId);
        $this->assertSame($this->salesCountdownRuleMock, $result);
    }
}
