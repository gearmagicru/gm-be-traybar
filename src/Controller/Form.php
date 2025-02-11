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
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер формы панели уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Controller
 * @since 1.0
 */
class Form extends FormController
{
    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var \Gm\FontAwesome\FontAwesome $fa */
        $fa = Gm::$app->fontAwesome;
        $fa->loadMap('v5.8 pro');

        $faItems = $fa->getRenderItems('fab ');

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->resizable = false;
        $window->form->bodyPadding = 10;
        $window->form->layout = 'anchor';
        $window->form->defaults = [
            'labelAlign' => 'right',
            'labelWidth' => 100,
        ];
        $window->form->controller = 'gm-be-traybar-form';
        $window->form->loadJSONFile('/form', 'items', [
            '@faStoreData' => $faItems
        ]);

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 460;
        $window->autoHeight = true;
        $window->layout = 'fit';
        $window
            ->setNamespaceJS('Gm.be.traybar')
            ->addRequire('Gm.be.traybar.FormController')
            ->addCss('/form.css');
        return $window;
    }
}
