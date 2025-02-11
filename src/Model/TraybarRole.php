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
use Gm\Db\ActiveRecord;

/**
 * Модель данных элементов панели уведомлений для определения доступности ролям пользователей.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class TraybarRole extends ActiveRecord
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
        return '{{panel_traybar_roles}}';
    }

    /**
     * {@inheritdoc}
     */
    public function maskedAttributes(): array
    {
        return [
            'id'        => 'id',
            'traybarId' => 'traybar_id',
            'roleId'    => 'role_id'
        ];
    }

    /**
     * Возвращает запись по указанному идентификатору элемента панели уведомлений и роли 
     * пользователя.
     * 
     * @see ActiveRecord::selectOne()
     * 
     * @param int $traybarId Идентификатор элемента панели уведомлений.
     * @param int $roleId Идентификатор роли пользователя.
     * 
     * @return TraybarRole|null Активная запись при успешном запросе, иначе `null`.
     */
    public function get(int $traybarId, int $roleId): ?static
    {
        return $this->selectOne([
            'traybar_id' => $traybarId,
            'role_id'    => $roleId
        ]);
    }

    /**
     * Возвращает все доступные идентификаторы элементов панели уведомлений для текущей 
     * роли пользователя.
     * 
     * @param bool $toString Если `true`, возвратит идентификаторы через разделитель ',' 
     *     (по умолчанию `false`).
     * 
     * @return array|string
     */
    public function getAccessible(bool $toString = false): array|string
    {
        /** @var \Gm\Db\Adapter\Adapter $db */
        $db = $this->getDb();
        /** @var \Gm\Db\Sql\Select $select */
        $select = $db
            ->select($this->tableName())
            ->columns(['*'])
            ->where([
                // доступные роли пользователю
                'role_id' => Gm::userIdentity()->getRoles()->ids(false)
            ]);
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $db
            ->createCommand($select)
                ->query();
        $rows = [];
        while ($row = $command->fetch()) {
            $rows[] = $row['traybar_id'];
        }
        return $toString ? implode(',', $rows) : $rows;
    }

    /**
     * Возвращает все записи (элементы) панели уведомлений соответствующие ролям пользователей.
     * 
     * Ключом каждой записи является значение первичного ключа {@see ActiveRecord::tableName()} 
     * текущей таблицы.
     * 
     * @see ActiveRecord::fetchAll()
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
                function () { return $this->fetchAll($this->primaryKey(), $this->maskedAttributes()); },
                null,
                true
            );
        else
            return $this->fetchAll($this->primaryKey(), $this->maskedAttributes());
    }
}
