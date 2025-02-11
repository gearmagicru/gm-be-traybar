<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Traybar\Model;

use Gm;
use Gm\Panel\Helper\Ext;
use Gm\Panel\Data\Model\GridModel;

/**
 * Модель данных панели уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class Grid extends GridModel
{
    /**
     * {@inheritdoc}
     */
    public bool $collectRowsId = true;

    /**
     * URL-путь в последнем запросе.
     * 
     * @var string
     */
    protected string $urlMatch;

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'useAudit'   => false,
            'tableName'  => '{{panel_traybar}}',
            'primaryKey' => 'id',
            // поля
            'fields' => [
                ['index'],
                ['faIcon'],
                ['rolesUrl'],
                ['titleLo'],
                ['icon'],
                [
                    'icon_type', 
                    'alias' => 'iconType'
                ],
                ['title'],
                ['visible'],
                ['handler']
            ],
            // порядок сортировки
            'order' => ['index' => 'ASC'],
            // сброс автоинкриментов таблиц
            'resetIncrements' => ['{{panel_traybar}}'],
            // зависимости
            'dependencies' => [
                'delete'    => [
                    '{{panel_traybar_roles}}'  => ['traybar_id' => 'id']
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->urlMatch = Gm::alias('@match');
        $this
            ->on(self::EVENT_AFTER_DELETE, function ($someRecords, $result, $message) {
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                /** @var \Gm\Panel\Controller\GridController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            });
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRow(array $row): array
    {
        $row['icon'] = Ext::renderIcon($row['icon'], $row['icon_type']) . ' ' . $row['icon'];
        $row['rolesUrl'] = $this->urlMatch .'/roles/view/' . $row['id'];

        // локализация названия
        if (strncmp($row['title'], '#', 1) === 0) {
            $row['titleLo'] = $this->module->t(ltrim($row['title'], '#'));
        }
        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRow(array &$row): void
    {
        // заголовок контекстного меню записи
        $row['popupMenuTitle'] = $row['title'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSupplementRows(): array
    {
        if (empty($this->rowsId)) return [];
        $rows = [];

        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db->createCommand(
            'SELECT `troles`.`traybar_id`, `role`.`name` '
          . 'FROM {{panel_traybar_roles}} `troles` '
          . 'JOIN {{role}} `role` ON `role`.`id`=`troles`.`role_id` '
          . 'WHERE `troles`.`traybar_id` IN (:traybar)'
        );
        $command->bindValues([
            ':traybar' => $this->rowsId
        ]);
        $command->execute();
        while ($row = $command->fetch()) {
            $id = $row['traybar_id'];
            if (!isset($rows[$id])) {
                $rows[$id] = [
                    'id' => $id, 'roles' => []
                ];
            }
            $rows[$id]['roles'][] = $row['name'];
        }
        return array_values($rows);
    }
}
