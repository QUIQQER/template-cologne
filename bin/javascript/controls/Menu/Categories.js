/**
 * @module package/quiqqer/product-bricks/bin/controls/Menu/Categories
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
        Type   : 'package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch',

        Binds: [],

        options: {
            top: 50
        },

        initialize: function (options) {
            this.parent(options);


            this.addEvents({
                onImport: this.$onImport
            });
        }
    });
});

