<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\Action\Collection;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutInterface;
use Magento\Rule\Model\ActionFactory;
use Magento\CatalogRule\Model\Rule\Action\Product;
use Magento\Framework\Phrase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private Collection $collection;

    /**
     * @var Repository|MockObject
     */
    private Repository|MockObject $assetRepoMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private LayoutInterface|MockObject $layoutMock;

    /**
     * @var ActionFactory|MockObject
     */
    private ActionFactory|MockObject $actionFactoryMock;

    protected function setUp(): void
    {
        $this->assetRepoMock = $this->createMock(Repository::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->actionFactoryMock = $this->createMock(ActionFactory::class);

        $this->collection = new Collection(
            $this->assetRepoMock,
            $this->layoutMock,
            $this->actionFactoryMock,
            []
        );
    }

    public function testConstructorSetsCorrectType(): void
    {
        $this->assertEquals(
            \Magento\Rule\Model\Action\Collection::class,
            $this->collection->getType()
        );
    }

    public function testGetNewChildSelectOptions(): void
    {
        $options = $this->collection->getNewChildSelectOptions();
        $this->assertIsArray($options);

        $productActionOption = null;
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value'] === Product::class) {
                $productActionOption = $option;
                break;
            }
        }

        $this->assertNotNull(
            $productActionOption,
            'Custom Product Action element was not found in options structure.'
        );
        $this->assertInstanceOf(Phrase::class, $productActionOption['label']);
        $this->assertEquals(__('Update the Product'), $productActionOption['label']);
    }
}
