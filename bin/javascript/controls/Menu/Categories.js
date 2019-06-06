/**
 * @module package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories
 *
 * This modul provides the functionality for slide out menu of product categories.
 *
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/menu/bin/SlideOut'


], function (QUI, QUIControl, SlideOut) {
    "use strict";

    return new Class({

        Extends: SlideOut,
        Type   : 'package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories',

        Binds: [],

        options: {
            'menu-button' : false
        },

        initialize: function (options) {
            this.parent(options);


            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event : on import
         */
        $onImport: function () {
            this.parent();

            var self = this;

            this.Slideout.on('beforeopen', function () {
                self.getElm().getElement('nav').setStyle('display', null);

            });

            var openButtons = document.getElements('.shop-category-menu-button');

            if (openButtons) {
                openButtons.each(function (Button) {
                    Button.addEvent('click', self.toggle);
                });
            }

        }
    })
});

