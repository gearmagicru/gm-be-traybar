<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Traybar\Controller;

use Gm;
use Gm\Panel\Widget\TabGrid;
use Gm\Panel\Helper\ExtGrid;
use Gm\Panel\Helper\HtmlGrid;
use Gm\Panel\Helper\HtmlNavigator as HtmlNav;
use Gm\Panel\Data\Model\FormModel;
use Gm\Panel\Controller\GridController;

/**
 * Контроллер списка панели уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Controller
 * @since 1.0
 */
class Grid extends GridController
{
    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // изменение записи по указанному идентификатору
            case 'update':
                /** @var FormModel $model */
                $model = $this->lastDataModel;
                if ($model instanceof FormModel) {
                    // если изменение элемента панели уведомлений
                    if ($model->visible !== null) {
                        return $this->module->t(
                            'notification area element {0} is ' . ($model->visible > 0 ? 'shown' : 'hidden'), [$model->title]
                        );
                    }
                }

            default:
                return parent::translateAction($params, $default);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): TabGrid
    {
        /** @var TabGrid $tab Сетка данных (Gm.view.grid.Grid GmJS) */
        $tab = parent::createWidget();

        // столбцы (Gm.view.grid.Grid.columns GmJS)
        $tab->grid->columns = [
            ExtGrid::columnNumberer(),
            ExtGrid::columnAction(),
            [
                'xtype' => 'g-gridcolumn-control',
                'width' => 30,
                'items' => [
                    [
                        'iconCls'   => 'g-icon-svg g-icon_user-roles_small',
                        'dataIndex' => 'rolesUrl',
                        'tooltip'   => '#Traybar roles',
                        'handler'   => 'loadWidgetFromCell'
                    ]
                ]
            ],
            [
                'text'      => '№',
                'tooltip'   => '#Index number',
                'dataIndex' => 'index',
                'filter'    => ['type' => 'numeric'],
                'width'     => 70
            ],
            [
                'text'      => ExtGrid::columnInfoIcon($this->t('Title')),
                'cellTip'   => HtmlGrid::tags([
                     HtmlGrid::header('{title}'),
                     HtmlNav::fieldLabel($this->t('Title'), '{titleLo}'),
                     HtmlNav::fieldLabel($this->t('Index'), '{index}'),
                     HtmlNav::fieldLabel($this->t('Icon'), '{icon}'),
                     HtmlNav::fieldLabel($this->module->t('Title') . ' (' . Gm::$app->language->name . ')', '{titleLo}'),
                     HtmlNav::fieldLabel($this->t('Handler'), '{handler}'),
                     HtmlGrid::fieldLabel(
                        $this->t('Visible'),
                        HtmlGrid::tplChecked('visible==1')
                     )
                ]),
                'dataIndex' => 'title',
                'filter'    => ['type' => 'string'],
                'width'     => 170
            ],
            [
                'text'      => $this->module->t('Title') . ' (' . Gm::$app->language->name . ')',
                'dataIndex' => 'titleLo',
                'cellTip'   => '{titleLo}',
                'sortable'  => false,
                'width'     => 200
            ],
            [
                'text'      => '#Icon / Image',
                'dataIndex' => 'icon',
                'filter'    => ['type' => 'string'],
                'width'     => 200
            ],
            [
                'text'      => '#Handler',
                'dataIndex' => 'handler',
                'tooltip'   => '#Handler name',
                'cellTip'   => '{handler}',
                'filter'    => ['type' => 'string'],
                'width'     => 140
            ],
            [
                'xtype'      => 'templatecolumn',
                'text'       => '#Roles',
                'dataIndex'  => 'roles',
                'hidden'     => true,
                'tpl'        => HtmlGrid::tpl(
                    '<div>' . ExtGrid::renderIcon('g-icon_size_16 g-icon_gridcolumn-user-roles', 'svg') . ' {.}</div>',
                    ['for' => 'roles']
                ),
                'supplement' => true,
                'sortable'   => false,
                'width'      => 200
            ],
            [
                'text'      => ExtGrid::columnIcon('g-icon-m_visible', 'svg'),
                'tooltip'   => '#Show / hide element',
                'xtype'     => 'g-gridcolumn-switch',
                'selector'  => 'gridpanel',
                'dataIndex' => 'visible'
            ]
        ];

        // панель инструментов (Gm.view.grid.Grid.tbar GmJS)
        $tab->grid->tbar = [
            'padding' => 1,
            'items'   => ExtGrid::buttonGroups(['edit', 'columns', 'search'])
        ];

        // контекстное меню записи (Gm.view.grid.Grid.popupMenu GmJS)
        $tab->grid->popupMenu = [
            'items' => [
                [
                    'text'        => '#Edit record',
                    'iconCls'     => 'g-icon-svg g-icon-m_edit g-icon-m_color_default',
                    'handlerArgs' => [
                          'route'   => Gm::alias('@match', '/form/view/{id}'),
                          'pattern' => 'grid.popupMenu.activeRecord'
                      ],
                      'handler' => 'loadWidget'
                ],
                '-',
                [
                    'text'        => '#Traybar roles',
                    'iconCls'     => 'g-icon-svg g-icon_user-roles_small',
                    'handlerArgs' => [
                        'route'   => Gm::alias('@match', '/roles/view/{id}'),
                        'pattern' => 'grid.popupMenu.activeRecord'
                    ],
                    'handler' => 'loadWidget'
                ]
            ]
        ];

        // 2-й клик по строке сетки
        $tab->grid->rowDblClickConfig = [
            'allow' => true,
            'route' => Gm::alias('@match', '/form/view/{id}')
        ];
        // количество строк в сетке
        $tab->grid->store->pageSize = 50;
        // поле аудита записи
        $tab->grid->logField = 'index';
        // плагины сетки
        $tab->grid->plugins = 'gridfilters';
        // класс CSS применяемый к элементу body сетки
        $tab->grid->bodyCls = 'g-grid_background';
        // сортировка сетки по умолчанию
        $tab->grid->sorters = [
            ['property' => 'index', 'direction' => 'ASC']
         ];

        // панель навигации (Gm.view.navigator.Info GmJS)
        $tab->navigator->info['tpl'] = HtmlNav::tags([
            HtmlNav::header('{title}'),
            HtmlNav::tag('div', '{icon}', ['style' => 'text-align:center;font-size:20px;']),
            ['fieldset',
                [
                    HtmlNav::fieldLabel($this->t('Index number'), '{index}'),
                    HtmlNav::fieldLabel($this->t('Title'), '{title}'),
                    HtmlNav::fieldLabel($this->t('Title') . ' (' . Gm::$app->language->name . ')', '{titleLo}'),
                    HtmlNav::fieldLabel($this->t('Handler'), '{handler}'),
                    HtmlNav::fieldLabel(
                        ExtGrid::columnIcon('g-icon-m_visible', 'svg') . ' ' . $this->t('Visible'),
                        HtmlNav::tplChecked('visible==1')
                    ),
                    HtmlNav::widgetButton(
                        $this->t('Edit record'),
                        ['route' => Gm::alias('@match', '/form/view/{id}'), 'long' => true],
                        ['title' => $this->t('Edit record')]
                    )
                ]
            ],
            ['fieldset',
                [
                    HtmlNav::legend($this->t('Roles')),
                    HtmlNav::tpl(
                        '<div>' . ExtGrid::renderIcon('g-icon_size_16 g-icon_gridcolumn-user-roles', 'svg') . ' {.}</div>',
                        ['for' => 'roles']
                    ),
                    HtmlNav::widgetButton(
                        $this->t('Update'),
                        ['route' => Gm::alias('@match', '/roles/view/{id}'), 'long' => true]
                    )
                ]
            ]
        ]);

        // если открыто окно настройки служб (конфигурация), закрываем его
        $this->getResponse()->meta->cmdComponent('g-setting-window', 'close');

        $tab
            ->addCss('/grid.css')
            ->addRequire('Gm.view.grid.column.Switch');
        return $tab;
    }
}
