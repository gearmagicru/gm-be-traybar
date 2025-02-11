<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации Карты SQL-запросов.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    'drop'   => ['{{panel_traybar}}', '{{panel_traybar_roles}}'],
    'create' => [
        '{{panel_traybar}}' => function () {
            return "CREATE TABLE `{{panel_traybar}}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `index` int(11) unsigned DEFAULT '1',
                `icon` varchar(255) DEFAULT NULL,
                `icon_type` varchar(15) DEFAULT NULL,
                `title` varchar(255) DEFAULT NULL,
                `handler` varchar(255) DEFAULT NULL,
                `handler_args` text,
                `visible` tinyint(1) unsigned DEFAULT '1',
                PRIMARY KEY (`id`)
                ) ENGINE={engine} 
                DEFAULT CHARSET={charset} COLLATE {collate}";
        },

        '{{panel_traybar_roles}}' => function () {
            return "CREATE TABLE `{{panel_traybar_roles}}` (
                `traybar_id` int(11) unsigned NOT NULL,
                `role_id` int(11) unsigned NOT NULL,
                PRIMARY KEY (`traybar_id`,`role_id`)
                ) ENGINE={engine} 
                DEFAULT CHARSET={charset} COLLATE {collate}";
        }
    ],

    'run' => [
        'install'   => ['drop', 'create'],
        'uninstall' => ['drop']
    ]
];