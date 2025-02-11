<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\Traybar\Model\Workspace;

use Gm;
use Gm\Exception;
use Gm\Helper\Json;
use Gm\Panel\Helper\Ext;
use Gm\Mvc\Module\BaseModule;
use Gm\Data\Model\BaseModel as DataModel;

/**
 * Модель данных элементов панели уведомлений рабочего пространства пользователя.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model\Workspace
 * @since 1.0
 */
class Panel extends DataModel
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\Traybar\Module
     */
    public BaseModule $module;

    /**
     * Проверяет, должна ли отображаться панель уведомлений.
     * 
     * @return bool
     */
    public function isVisible(): bool
    {
        static $visible = null;

        if ($visible === null) {
            $workspace = Gm::$app->unifiedConfig->get('workspace');
            if ($workspace)
                $visible = $workspace['traybarVisible'] ?? false;
            else
                $visible = true;
            $visible = $this->module->getPermission()->isAllow('any', 'interface') && $visible;
        }
        return $visible;
    }

    /**
     * Возвращает настройки панели уведомлений.
     * 
     * @param bool $json Если `true`, результат будет представлен в JSON формате (по умолчанию `true`).
     * 
     * @return string|array|null
     */
    public function getSettings(bool $json = true): string|array|null
    {
        if (!$this->isVisible()) {
            return $json ? 'null' : null;
        }
        // только доступные элементы панели уведомлений текущей роли пользователя
        $settings = [
            'items' => $this->getItems()
        ];
        if ($json) {
            $settings = Json::encode($settings, true, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            if ($error = Json::error()) {
                throw new Exception\JsonFormatException($error);
            }
        }
        return $settings;
    }

    /**
     * Возвращает элементы панели уведомлений, доступные для текущей роли пользователя.
     * 
     * @see \Backend\Traybar\Model\Traybar::getAll()
     * 
     * @return array
     */
    public function getItems(): array
    {
        /** @var \Gm\Backend\Traybar\Model\Traybar $traybar */
        $traybar = $this->module->getModel('Traybar');

        $items = [];
        foreach ($traybar->getAll(false) as $row) {
            $item = [
                'xtype'   => 'button',
                'cls'     => 'g-traybar-btn',
                'id'      => 'g-traybar-btn-' . $row['id'],
                'tooltip' =>  $this->module->tH($row['title']),
                'type'    => 'widget',
            ];
            Ext::buttonIcon($item, $row['iconType'], $row['icon']);
            if ($row['handler'])
                $item['handler'] = $row['handler'];
            if ($row['handlerArgs']) {
                // замена переменных в url строке 
                parse_str($row['handlerArgs'], $item['handlerArgs']);
            }
            $items[] = $item;
        }
        return $items;
    }
}
