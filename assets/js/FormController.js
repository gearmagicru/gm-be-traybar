/*!
 * Контроллер формы.
 * Модуль "Панель уведомлений".
 * Copyright 2015 Вeб-студия GearMagic. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://gearmagic.ru/license/
 */

Ext.define('Gm.be.traybar.FormController', {
    extend: 'Gm.view.form.PanelController',
    alias: 'controller.gm-be-traybar-form',

    /**
     * Выбо значка.
     * @param {Ext.form.field.Checkbox} me
     * @param {Boolean} value
     */
    onCheckIcon: function (me, value) {
        if (me.id == this.getViewId('__rfont')) {
            let form = this.getViewCmp(['ffont', 'fimage']);
            if (value) {
                form.ffont.show();
                form.fimage.hide();
            } else {
                form.ffont.hide();
                form.fimage.show();
            }
        }
    }
});
