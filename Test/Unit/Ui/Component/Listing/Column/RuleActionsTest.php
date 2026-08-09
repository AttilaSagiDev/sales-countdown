<?php
/**
 * Copyright (c) 2026 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

declare(strict_types=1);

namespace Space\SalesCountdown\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Space\SalesCountdown\Ui\Component\Listing\Column\RuleActions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RuleActionsTest extends TestCase
{
    /**
     * @var RuleActions
     */
    private RuleActions $model;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface|MockObject $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    private Escaper|MockObject $escaperMock;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    private UiComponentFactory|MockObject $uiComponentFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);

        $this->model = $objectManager->getObject(
            RuleActions::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper' => $this->escaperMock,
                'data' => [
                    'name' => 'actions'
                ]
            ]
        );
    }

    public function testPrepareDataSource(): void
    {
        $ruleId = 1;
        $ruleName = 'Test Rule';
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'rule_id' => $ruleId,
                        'name' => $ruleName
                    ]
                ]
            ]
        ];

        $this->escaperMock->expects($this->once())
            ->method('escapeHtmlAttr')
            ->with($ruleName)
            ->willReturn($ruleName);

        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnMap([
                [RuleActions::URL_PATH_EDIT, ['rule_id' => $ruleId], 'edit_url'],
                [RuleActions::URL_PATH_DELETE, ['rule_id' => $ruleId], 'delete_url']
            ]);

        $result = $this->model->prepareDataSource($dataSource);

        $this->assertArrayHasKey('actions', $result['data']['items'][0]);
        $actions = $result['data']['items'][0]['actions'];

        $this->assertEquals('edit_url', $actions['edit']['href']);
        $this->assertEquals('Edit', (string)$actions['edit']['label']);

        $this->assertEquals('delete_url', $actions['delete']['href']);
        $this->assertEquals('Delete', (string)$actions['delete']['label']);
        $this->assertEquals('Delete ' . $ruleName, (string)$actions['delete']['confirm']['title']);
        $this->assertEquals(
            'Are you sure you want to delete a ' . $ruleName . ' record?',
            (string)$actions['delete']['confirm']['message']
        );
        $this->assertTrue($actions['delete']['post']);
    }

    public function testPrepareDataSourceMultipleItems(): void
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'rule_id' => 1,
                        'name' => 'Rule 1'
                    ],
                    [
                        'rule_id' => 2,
                        'name' => 'Rule 2'
                    ]
                ]
            ]
        ];

        $this->escaperMock->expects($this->exactly(2))
            ->method('escapeHtmlAttr')
            ->willReturnMap([
                ['Rule 1', 'Rule 1'],
                ['Rule 2', 'Rule 2']
            ]);

        $this->urlBuilderMock->expects($this->exactly(4))
            ->method('getUrl')
            ->willReturnMap([
                [RuleActions::URL_PATH_EDIT, ['rule_id' => 1], 'edit_url_1'],
                [RuleActions::URL_PATH_DELETE, ['rule_id' => 1], 'delete_url_1'],
                [RuleActions::URL_PATH_EDIT, ['rule_id' => 2], 'edit_url_2'],
                [RuleActions::URL_PATH_DELETE, ['rule_id' => 2], 'delete_url_2']
            ]);

        $result = $this->model->prepareDataSource($dataSource);

        $this->assertCount(2, $result['data']['items']);
        $this->assertEquals('edit_url_1', $result['data']['items'][0]['actions']['edit']['href']);
        $this->assertEquals('edit_url_2', $result['data']['items'][1]['actions']['edit']['href']);
    }

    public function testPrepareDataSourceWithoutRuleId(): void
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'Test Rule'
                    ]
                ]
            ]
        ];

        $result = $this->model->prepareDataSource($dataSource);
        $this->assertArrayNotHasKey('actions', $result['data']['items'][0]);
    }

    public function testPrepareDataSourceEmpty(): void
    {
        $dataSource = [];
        $result = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($dataSource, $result);
    }
}
