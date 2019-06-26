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

        Binds: [
            '$onImport',
            '$onResize'
        ],

        options: {
            'menu-button': false,
            'menu-width' : 400
        },

        initialize: function (options) {
            this.parent(options);

            this.menuWidht = null;


            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event : on import
         */
        $onImport: function () {
            this.menuWidht = this.getAttribute('menu-width');

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
        },

        /**
         * event : on resize
         */
        $onResize: function () {
            if (QUI.getWindowSize().x > this.menuWidht) {
                this.setAttribute('menu-width', this.menuWidht);

                return;
            }

            this.setAttribute('menu-width', QUI.getWindowSize().x);
        }
    })
});

