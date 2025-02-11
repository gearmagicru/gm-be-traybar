<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации установки модуля.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c] 2015 Этот файл является частью модуля веб-приложения GearMagic. Web-студия
 * @license https://gearmagic.ru/license/
 */

return [
    'use'         => BACKEND,
    'id'          => 'gm.be.traybar',
    'name'        => 'Tray bar',
    'description' => 'Display of system notifications and quick access to the Control Panel module',
    'namespace'   => 'Gm\Backend\Traybar',
    'path'        => '/gm/gm.be.traybar',
    'route'       => 'traybar',
    'routes'      => [
        [
            'type'    => 'crudSegments',
            'options' => [
                'module'      => 'gm.be.traybar',
                'route'       => 'traybar',
                'prefix'      => BACKEND,
                'constraints' => ['id'],
                'defaults'    => [
                    'controller' => [
                        'default' => 'grid'
                    ]
                ]
            ]
        ]
    ],
    'locales'     => ['ru_RU', 'en_GB'],
    'permissions' => ['any', 'view', 'read', 'add', 'edit', 'delete', 'clear', 'interface', 'settings', 'info'],
    'events'      => [],
    'required'    => [
        ['php', 'version' => '8.2'],
        ['app', 'code' => 'GM MS'],
        ['app', 'code' => 'GM CMS'],
        ['app', 'code' => 'GM CRM'],
    ]
];
