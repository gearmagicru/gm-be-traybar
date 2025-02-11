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
 * Модель данных профиля панели уведомлений.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\Traybar\Model
 * @since 1.0
 */
class Form extends FormModel
{
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
                ['id'],
                [
                    'index', 
                    'label' => 'Index'
                ],
                ['faIcon'],
                ['rolesUrl'],
                ['icon'],
                ['icon_type', 'alias' => 'iconType'],
                [
                    'title', 
                    'label' => 'Title'
                ],
                ['visible'],
                [
                    'handler', 
                    'label' => 'Handler'
                ],
                [ 
                    'handler_args', 
                    'alias' => 'handlerArgs', 
                    'label' => 'Arguments'
                ]
            ],
            // зависимости
            'dependencies' => [
                'delete'    => [
                    '{{panel_traybar_roles}}'  => ['traybar_id' => 'id']
                ]
            ],
            // правила форматирования полей
            'formatterRules' => [
                [['title', 'handler', 'handlerArgs'], 'safe'],
                [['visible'], 'logic']
            ],
            // правила валидации полей
            'validationRules' => [
                [['index', 'title', 'iconType', 'handler'], 'notEmpty']
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
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                /** @var \Gm\Panel\Controller\FormController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            })
            ->on(self::EVENT_AFTER_DELETE, function ($result, $message) {
                // всплывающие сообщение
                $this->response()
                    ->meta
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type']);
                /** @var \Gm\Panel\Controller\FormController $controller */
                $controller = $this->controller();
                // обновить список
                $controller->cmdReloadGrid();
            });
    }

    /**
     * {@inheritDoc}
     */
    public function afterValidate(bool $isValid): bool
    {
        if ($isValid) {
            /** @var \Gm\Http\Request $request */
            $request = Gm::$app->request;

            $iconType = $request->post('iconType');
            switch ($iconType) {
                case 'font':
                    $icon = $request->post('iconFont');
                    if (empty($icon))
                        $this->addError($this->errorFormatMsg(\Gm::t('app', "Value is required and can't be empty"), 'Icon'));
                    else
                        $this->icon = $icon;
                    break;

                case 'image':
                    $icon = $request->post('iconImage');
                    if (empty($icon))
                        $this->addError($this->errorFormatMsg(\Gm::t('app', "Value is required and can't be empty"), 'Image'));
                    else
                        $this->icon = $icon;
                    break;

                default:
                    $this->addError(Gm::t('app', 'Invalid query parameter'));
            }
            return !$this->hasErrors();
        }
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function processing(): void
    {
        parent::processing();

        /** @var string $viewId Идент. добавляемый к идент. элементов формы */
        $viewId = $this->module->viewId('form') . '__';
        switch ($this->iconType) {
            case 'font':
                $this->response()
                    ->meta
                        ->cmdComponent($viewId . 'rfont', 'setValue', [true])
                        ->cmdComponent($viewId . 'fimage', 'hide')
                        ->cmdComponent($viewId . 'ffont', 'show')
                        ->cmdComponent($viewId . 'ffont', 'setValue', [$this->icon]);
                break;

            case 'image':
                $this->response()
                    ->meta
                        ->cmdComponent($viewId . 'rimage', 'setValue', [true])
                        ->cmdComponent($viewId . 'ffont', 'hide')
                        ->cmdComponent($viewId . 'fimage', 'show')
                        ->cmdComponent($viewId . 'fimage', 'setValue', [$this->icon]);
                break;
        }
    }
}
