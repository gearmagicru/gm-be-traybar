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
use Gm\Panel\Data\Model\FormModel;

/**
 * Модель данных профиля записи выбора роли пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class RolesGridRow extends FormModel
{
    /**
     * Идентификатор роли пользователя.
     * 
     * @var int
     */
    protected int $roleId;

    /**
     * Идентификатор уведомления.
     * 
     * @var int
     */
    protected int $traybarId; 

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
                ['available']
            ],
            'resetIncrements' => ['{{panel_traybar_roles}}'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns, $result, $message) {
                // если успешно добавлен доступ
                if ($message['success']) {
                    // если выбранная роль входит в раздел
                    $available = (int) $this->available > 0;
                    $message['message'] = $this->module->t(
                        'Traybar element for user role {0} - ' . ($available > 0 ? 'enabled' : 'disabled'),
                        [$this->name]
                    );
                    $message['title'] = $this->t('Access to the traybar');
                }
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function get(mixed $identifier = null): ?static
    {
        // т.к. записи формируются при выводе списка, то нет
        // необходимости делать запрос к бд (нет основной таблицы)
        return $this;
    }

    /**
     * Возвращает идентификатор роли пользователя.
     * 
     * @return int
     */
    public function getRoleId(): int
    {
        if (!isset($this->roleId)) {
            $this->roleId = $this->getIdentifier();
        }
        return $this->roleId;
    }

    /**
     * Возвращает идентификатор уведомления.
     * 
     * @return int
     */
    public function getTraybarId(): int
    {
        if (!isset($this->traybarId)) {
            $store = $this->module->getStorage();
            $this->traybarId = isset($store->traybar['id']) ? (int) $store->traybar['id'] : 0;
        }
        return $this->traybarId;
    }

    /**
     * {@inheritdoc}
     */
    protected function insertProcess(array $attributes = null): false|int|string
    {
        if (!$this->beforeSave(true))
            return false;

        $columns = [];
        // если выбранная роль доступна для уведомления
        if ((int) $this->available > 0) {
            $columns = [
                'traybar_id' => $this->getTraybarId(),
                'role_id'    => $this->getRoleId()
            ];
            $this->insertRecord($columns);
            // т.к. ключ составной, то при добавлении всегда будет "0"
            $this->result = 1;
        // если выбранная роль не доступна для уведомления
        } else {
            $this->result = $this->deleteRecord([
                'traybar_id' => $this->getTraybarId(),
                'role_id'    => $this->getRoleId()
            ]);
        }
        $this->afterSave(true, $columns, $this->result, $this->saveMessage(true, (int) $this->result));
        return $this->result;
    }
}
