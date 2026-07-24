<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Model\Rule;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Model\Rule\WebsitesOptionsProvider;
use Magento\Store\Model\System\Store;

class WebsitesOptionsProviderTest extends TestCase
{
    /**
     * @var WebsitesOptionsProvider
     */
    private WebsitesOptionsProvider $provider;

    /**
     * @var Store|MockObject
     */
    private Store|MockObject $storeMock;

    protected function setUp(): void
    {
        $this->storeMock = $this->createMock(Store::class);

        $this->provider = new WebsitesOptionsProvider(
            $this->storeMock
        );
    }

    public function testToOptionArray(): void
    {
        $expectedOptions = [
            ['value' => '1', 'label' => 'Main Website'],
            ['value' => '2', 'label' => 'Second Website']
        ];

        $this->storeMock->expects($this->once())
            ->method('getWebsiteValuesForForm')
            ->willReturn($expectedOptions);

        $this->assertSame($expectedOptions, $this->provider->toOptionArray());
    }
}
