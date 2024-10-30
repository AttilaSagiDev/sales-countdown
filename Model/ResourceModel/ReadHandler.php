<?php
/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Model\ResourceModel;

use Magento\Framework\EntityManager\Operation\AttributeInterface;
use Space\SalesCountdown\Model\ResourceModel\Rule as ResourceRule;
use Magento\Framework\EntityManager\MetadataPool;

class ReadHandler implements AttributeInterface
{
    /**
     * @var ResourceRule
     */
    protected ResourceRule $ruleResource;

    /**
     * @var MetadataPool
     */
    protected MetadataPool $metadataPool;

    /**
     * Constructor
     *
     * @param ResourceRule $ruleResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceRule $ruleResource,
        MetadataPool $metadataPool
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Execute
     *
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entityData, $arguments = []): array
    {
        $linkField = $this->metadataPool->getMetadata($entityType)->getLinkField();
        $entityId = $entityData[$linkField];

        $entityData['customer_group_ids'] = $this->ruleResource->getCustomerGroupIds($entityId);
        $entityData['website_ids'] = $this->ruleResource->getWebsiteIds($entityId);

        return $entityData;
    }
}
