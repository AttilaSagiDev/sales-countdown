<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\ResourceModel;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\ResourceModel\ReadHandler;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;

class ReadHandlerTest extends TestCase
{
    /**
     * @var ReadHandler
     */
    private ReadHandler $model;

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

        $this->model = new ReadHandler(
            $this->ruleResourceMock,
            $this->metadataPoolMock
        );
    }

    public function testExecute(): void
    {
        $entityType = 'Space\SalesCountdown\Api\Data\RuleInterface';
        $entityData = ['row_id' => 123];
        $customerGroupIds = [0, 1];
        $websiteIds = [1];

        $metadataMock = $this->createMock(EntityMetadataInterface::class);
        $metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('row_id');

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $this->ruleResourceMock->expects($this->once())
            ->method('getCustomerGroupIds')
            ->with(123)
            ->willReturn($customerGroupIds);

        $this->ruleResourceMock->expects($this->once())
            ->method('getWebsiteIds')
            ->with(123)
            ->willReturn($websiteIds);

        $result = $this->model->execute($entityType, $entityData);

        $this->assertArrayHasKey('customer_group_ids', $result);
        $this->assertArrayHasKey('website_ids', $result);
        $this->assertEquals($customerGroupIds, $result['customer_group_ids']);
        $this->assertEquals($websiteIds, $result['website_ids']);
    }
}
