<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Traybar\Model;

use Gm\Panel\Data\Model\GridModel;

/**
 * Модель данных списка доступности уведомления для ролей пользователей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class RolesGrid extends GridModel
{
    /**
     * @var null|array
     */
    protected $traybar;

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'useAudit'   => false,
            'tableName'  => '{{panel_traybar_roles}}',
            'primaryKey' => 'id',
            'fields'     => [
                ['name'],
                [
                    'traybar_id', 
                    'alias' => 'traybarId'
                ],
                [
                    'role_id', 
                    'alias' => 'roleId'
                ],
                ['available']
            ],
            'order' => ['name' => 'asc'],
            'resetIncrements' => ['{{panel_traybar_roles}}'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getModelName(): string
    {
        return 'traybarRoles';
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->traybar = $store = $this->module->getStorage()->traybar;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSelect(mixed $command = null): void
    {
        $command->bindValues([
            ':traybar' => $this->traybar['id'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRows(): array
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS `role`.*,`roles`.`traybar_id` '
             . 'FROM `{{role}}` `role` '
             . 'LEFT JOIN `{{panel_traybar_roles}}` `roles` ON `roles`.`role_id`=`role`.`id` AND `roles`.`traybar_id`=:traybar';
        return $this->selectBySql($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRow(array $row): array
    {
        // доступность роли
        $row['available'] = empty($row['traybar_id']) ? 0 : 1;
        return $row;
    }
}
