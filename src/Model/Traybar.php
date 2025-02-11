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
use Closure;
use Gm\Db\Sql\Where;
use Gm\Db\Sql\Select;
use Gm\Db\ActiveRecord;

/**
 * Модель данных панели (элементов панели) уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class Traybar extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function primaryKey(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function tableName(): string
    {
        return '{{panel_traybar}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'          => 'id',
            'index'       => 'index',
            'icon'        => 'icon',
            'iconType'    => 'icon_type',
            'title'       => 'title',
            'handler'     => 'handler',
            'handlerArgs' => 'handler_args',
            'visible'     => 'visible'
        ];
    }

    /**
     * {@inheritdoc}
     * 
     * @param bool $accessible Если `true`, возвратит все доступные элементы панели 
     *     уведомлений для текущей роли пользователя (по умолчанию `true`).
     */
    public function fetchAll(
        string $fetchKey = null, 
        array $columns = ['*'], 
        Where|Closure|string|array|null $where = null, 
        string|array|null $order = null,
        bool $accessible = true
    ): array
    {
        /** @var Select $select */
        $select = $this->select($columns, $where);
        if ($order === null)
            $order = ['index' => 'ASC'];
        $select->order($order);
        // проверка доступа
        if ($accessible) {
            $role = new TraybarRole();
            $traybarId = $role->getAccessible();
            // если нет доступных элементов
            if (empty($traybarId)) {
                return [];
            }
            $select->where(['id' => $traybarId]);
        }
        return $this
            ->getDb()
                ->createCommand($select)
                    ->queryAll($fetchKey);
    }

    /**
     * Возвращает запись по указанному значению первичного ключа.
     * 
     * @see ActiveRecrod::selectByPk()
     * 
     * @param mixed $id Идентификатор записи.
     * 
     * @return null|Traybar Активная запись при успешном запросе, иначе `null`.
     */
    public function get(mixed $identifier): ?static
    {
        return $this->selectByPk($identifier);
    }

    /**
     * Возвращает все записи (элементы) панели уведомлений с указанным ключом.
     * 
     * Ключом каждой записи является значение первичного ключа {@see ActiveRecord::tableName()} 
     * текущей таблицы.
     * 
     * @see Partitionbar::fetchAll()
     * 
     * @param bool $caching Указывает на принудительное кэширование. Если служба кэширования 
     *     отключена, кэширование не будет выполнено (по умолчанию `true`).
     * 
     * @return array
     */
    public function getAll(bool $caching = true): ?array
    {
        if ($caching)
            return $this->cache(
                function () { return $this->fetchAll($this->primaryKey(), $this->maskedAttributes(), ['visible' => 1]); },
                null,
                true
            );
        else
            return $this->fetchAll($this->primaryKey(), $this->maskedAttributes(), ['visible' => 1]);
    }
}
