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
use Gm\Panel\Helper\ExtGrid;
use Gm\Panel\Widget\GridDialog;
use Gm\Panel\Data\Model\FormModel;
use Gm\Panel\Controller\DialogGridController;

/**
 * Контроллер списка доступных ролей пользователей для панели уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Controller
 * @since 1.0
 */
class RolesGrid extends DialogGridController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'RolesGrid';

    /**
     * Идентификатора роли.
     * 
     * @var int
     */
    protected int $identifier;

    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
                $store = $this->module->getStorage();
                return $this->module->t('opening a window for viewing user roles available to the traybar {0}', [$store->traybar['title'] ?? '' ]);

            // вывод записей в список
            case 'data':
                $store = $this->module->getStorage();
                return $this->module->t('viewing user roles available to the traybar {0}', [$store->traybar['title'] ?? '']);

            // изменение записи по указанному идентификатору
            case 'update':
                /** @var FormModel $model */
                $model = $this->lastDataModel;
                if ($model instanceof FormModel) {
                    if ($model->available !== null) {
                        // если выбранной роли пользователя доступна паель уведомлений
                        $available = (int) $model->available;
                        return $this->module->t(
                            'traybar element for user role {0} is ' . ($available > 0 ? 'enabled' : 'disabled'), [$model->name]
                        );
                    }
                }

                default:
                    return parent::translateAction($params, $default);
        }
    }


    /**
     * Возвращает идентификатор выбранного элемента панели уведомлений.
     * 
     * @return int
     */
    public function getTraybarIdentifier(): int
    {
        if (!isset($this->identifier)) {
            $this->identifier = (int) Gm::$app->router->get('id');
        }
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): GridDialog
    {
        /** @var \Gm\Backend\Traybar\Model\Traybar $traybar  */
        $traybar = $this->module->getModel('Traybar');
        // информация о выбранном уведомлении
        $traybar = $traybar->get($this->getTraybarIdentifier());
        if ($traybar === null) {
            $this->getResponse()->error($this->t('Incorrect traybar panel'));
            return false;
        }

        // информацию в хранилище модуля
        $store = $this->module->getStorage();
        $store->traybar = $traybar->getAttributes();

        /** @var GridDialog $window Окно с Сеткей данных (Gm.view.grid.Grid GmJS) */
        $window = parent::createWidget();

        $title = $traybar->title ?? '';
        if (strncmp($title, '#', 1) === 0) {
            $title = $this->module->t(ltrim($title, '#')) . ' (' . $traybar->title . ')';
        }

        // видже окна (Ext.window.Window Sencha ExtJS)
        $window->width = 550;
        $window->height = '90%';
        $window->ui = 'light';
        $window->layout = 'fit';
        $window->resizable = true;
        $window->iconCls = 'g-icon-svg g-icon_user-roles_small';
        $window->title = $this->module->t('{roles.title}', [$title]);

        // столбцы (Gm.view.grid.Grid.columns GmJS)
        $window->grid->columns = [
            ExtGrid::columnNumberer(),
            [
                'text'      => '#Name',
                'dataIndex' => 'name',
                'cellTip'   => '{name}',
                'width'     => 400,
                'filter'    => ['type' => 'string'],
                'hideable'  => false
            ],
            [
                'text'        => ExtGrid::columnIcon('g-icon-m_unlock', 'svg'),
                'tooltip'     => '#User role availability',
                'xtype'       => 'g-gridcolumn-switch',
                'sortable'    => false,
                'collectData' => ['name'],
                'dataIndex'   => 'available',
                'filter'      => ['type' => 'boolean'],
                'hideable'    => false
            ]
        ];

        // сортировка строк в сетке
        $window->grid->sorters = [
            ['property' => 'name', 'direction' => 'ASC']
        ];
        // количество строк в сетке
        $window->grid->store->pageSize = 1000;
        $window->grid->router->route = Gm::alias('@match', '/roles');
        // локальная фильтрация и сортировка
        $window->grid->store->remoteFilter = false;
        $window->grid->store->remoteSort = false;
        // плагины сетки
        $window->grid->plugins = 'gridfilters';
        // убираем пагинацию страниц
        unset($window->grid->pagingtoolbar);
        return $window;
    }
}
