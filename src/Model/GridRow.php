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
 * Модель данных профиля записи панели уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class GridRow extends FormModel
{
    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'tableName'  => '{{panel_traybar}}',
            'primaryKey' => 'id',
            'fields'     => [
                ['id'],
                ['title'],
                ['visible']
            ]
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
                /** @var \Gm\Panel\Http\Response $response */
                $response = $this->response();
                // всплывающие сообщение
                if ($message['success']) {
                    $response
                        ->meta
                            ->cmdPopupMsg(
                                $this->module->t('Notification area element {0} - ' . ($this->visible > 0 ? 'show' : 'hide'), [$this->title]),
                                $this->t($this->visible > 0 ? 'Show' : 'Hide'),
                                'accept'
                            );
                    // скрыть / показать элемент в интерфейсе
                    $response
                        ->meta
                            ->cmdComponent('g-traybar-btn-' . $this->getIdentifier(), $this->visible > 0 ? 'show' : 'hide');
                } else {
                    $response
                        ->meta
                            ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                }
            });
    }
}
