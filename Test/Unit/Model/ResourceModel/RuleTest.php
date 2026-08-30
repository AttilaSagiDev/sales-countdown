<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\ResourceModel;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\ResourceModel\Rule;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Space\SalesCountdown\Api\Data\RuleInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Select;

class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    private Rule $model;

    /**
     * @var Context|MockObject
     */
    private Context|MockObject $contextMock;

    /**
     * @var EntityManager|MockObject
     */
    private EntityManager|MockObject $entityManagerMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private AdapterInterface|MockObject $connectionMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection|MockObject $resourceConnectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(Context::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->contextMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceConnectionMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $associatedEntityMapMock = $this->createMock(DataObject::class);

        $this->model = $objectManager->getObject(
            Rule::class,
            [
                'context' => $this->contextMock,
                'entityManager' => $this->entityManagerMock,
                'associatedEntityMap' => $associatedEntityMapMock
            ]
        );
    }

    public function testGetRulesFromProduct(): void
    {
        $date = '2024-01-01';
        $timestamp = strtotime($date);
        $websiteId = 1;
        $customerGroupId = 0;
        $productId = 100;
        $tableName = 'sales_countdown_rule_product';

        $selectMock = $this->createMock(Select::class);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        $this->resourceConnectionMock->expects($this->any())
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);

        $selectMock->expects($this->once())
            ->method('from')
            ->with($tableName)
            ->willReturnSelf();

        $selectMock->expects($this->exactly(5))
            ->method('where')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($selectMock)
            ->willReturn([['rule_id' => 1]]);

        $result = $this->model->getRulesFromProduct($date, $websiteId, $customerGroupId, $productId);
        $this->assertEquals([['rule_id' => 1]], $result);
    }

    public function testLoad(): void
    {
        $objectMock = $this->createMock(AbstractModel::class);
        $value = 1;

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($objectMock, $value);

        $this->assertSame($this->model, $this->model->load($objectMock, $value));
    }

    public function testSave(): void
    {
        $objectMock = $this->createMock(AbstractModel::class);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($objectMock);

        $this->assertSame($this->model, $this->model->save($objectMock));
    }

    public function testDelete(): void
    {
        $objectMock = $this->createMock(AbstractModel::class);

        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($objectMock);

        $this->assertSame($this->model, $this->model->delete($objectMock));
    }
}
