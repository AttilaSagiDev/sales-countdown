<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\ResourceModel\SaveHandler;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;

class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    private SaveHandler $model;

    /**
     * @var ResourceRule|MockObject
     */
    private ResourceRule|MockObject $ruleResourceMock;

    /**
     * @var MetadataPool|MockObject
     */
    private MetadataPool|MockObject $metadataPoolMock;

    protected function setUp(): void
    {
        $this->ruleResourceMock = $this->createMock(ResourceRule::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        $this->model = new SaveHandler(
            $this->ruleResourceMock,
            $this->metadataPoolMock
        );
    }

    public function testExecuteWithArrayData(): void
    {
        $entityType = 'Space\SalesCountdown\Api\Data\RuleInterface';
        $entityData = [
            'row_id' => 123,
            'website_ids' => [1, 2],
            'customer_group_ids' => [0, 1]
        ];

        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('row_id');

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $callCount = 0;
        $this->ruleResourceMock->expects($this->exactly(2))
            ->method('bindRuleToEntity')
            ->willReturnCallback(function ($entityId, $ids, $type) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    $this->assertEquals(123, $entityId);
                    $this->assertEquals([1, 2], $ids);
                    $this->assertEquals('website', $type);
                } else {
                    $this->assertEquals(123, $entityId);
                    $this->assertEquals([0, 1], $ids);
                    $this->assertEquals('customer_group', $type);
                }
            });

        $result = $this->model->execute($entityType, $entityData);
        $this->assertEquals($entityData, $result);
    }

    public function testExecuteWithStringData(): void
    {
        $entityType = 'Space\SalesCountdown\Api\Data\RuleInterface';
        $entityData = [
            'row_id' => 123,
            'website_ids' => '1,2',
            'customer_group_ids' => '0,1'
        ];

        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('row_id');

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $callCount = 0;
        $this->ruleResourceMock->expects($this->exactly(2))
            ->method('bindRuleToEntity')
            ->willReturnCallback(function ($entityId, $ids, $type) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    $this->assertEquals(123, $entityId);
                    $this->assertEquals(['1', '2'], $ids);
                    $this->assertEquals('website', $type);
                } else {
                    $this->assertEquals(123, $entityId);
                    $this->assertEquals(['0', '1'], $ids);
                    $this->assertEquals('customer_group', $type);
                }
            });

        $result = $this->model->execute($entityType, $entityData);
        $this->assertEquals($entityData, $result);
    }

    public function testExecuteWithMissingData(): void
    {
        $entityType = 'Space\SalesCountdown\Api\Data\RuleInterface';
        $entityData = ['row_id' => 123];

        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('row_id');

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $this->ruleResourceMock->expects($this->never())
            ->method('bindRuleToEntity');

        $result = $this->model->execute($entityType, $entityData);
        $this->assertEquals($entityData, $result);
    }
}
