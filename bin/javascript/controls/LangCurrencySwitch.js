/**
 * @module package/quiqqer/product-bricks/bin/controls/LangCurrencySwitch
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'qui/controls/loader/Loader',
    'package/quiqqer/currency/bin/controls/Switch'

], function (QUI, QUIControl, QUIAjax, QUILoader, CurrencySwitch) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch',

        Binds: [
            '$onImport',
            '$onChange',
            '$changeDisplayCurrency',
            'open',
            'close',
            'hide'
        ],

        options: {},

        initialize: function (options) {
            this.parent(options);

            this.Button                  = null;
            this.Currency                = null;
            this.Menu                    = null;
            this.Loader                  = null;
            this.isOpen                  = false;
            this.closeAnimationIsRunning = false;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * @event on import
         */
        $onImport: function () {
            var self = this;

            this.MenuWrapper = this.$Elm.getElement('.lcs-menu-wrapper');
            this.Button      = this.$Elm.getElement('.lcs-button');

            if (typeof window.DEFAULT_USER_CURRENCY !== 'undefined') {
                self.$changeDisplayCurrency(window.DEFAULT_USER_CURRENCY);
            }

            this.Button.addEvent('click', function () {
                if (self.isOpen) {
                    self.close();
                    return;
                }

                self.open();
            });
        },

        /**
         * Open the menu
         */
        open: function () {
            if (this.Menu) {
                return;
            }

            var self = this;

            QUI.addEvent('scroll', this.hide);

            this.Loader = new QUILoader();
            this.Loader.inject(this.Button);
            this.Loader.show();

            this.createMenu().then(function () {
                self.Menu.inject(self.MenuWrapper);
                self.Loader.hide();
                self.isOpen = true;
                document.body.addEvent('click', self.close);
            });
        },

        /**
         * Hide (with animation) and destroy the menu
         */
        close: function () {
            if (this.closeAnimationIsRunning) {
                return;
            }

            var self                     = this;
            this.closeAnimationIsRunning = true;

            QUI.removeEvent('scroll', this.hide);
            document.body.removeEvent('click', self.close);

            moofx(this.Menu).animate({
                opacity: 0
            }, {
                duration: 300,
                callback: function () {
                    self.Menu.destroy();
                    self.Menu                    = null;
                    self.isOpen                  = false;
                    self.closeAnimationIsRunning = false;
                }
            });
        },

        /**
         * Hide the menu immediately e.g. by on scroll
         */
        hide: function () {
            this.Menu.setStyle('display', 'none');
            this.close();
        },

        /**
         * Create dropdown menu with currency switch and language list
         */
        createMenu: function () {
            var self = this;

            return new Promise(function (resolve) {
                self.Menu = new Element('div', {
                    'class': 'lcs-menu',
                    events : {
                        click: function (event) {
                            var Target = event.target;

                            if (Target.nodeName !== 'A') {
                                Target = Target.getParent('a');
                            }

                            if (Target === null) {
                                event.stop();
                            }
                        }
                    }
                });

                self.CurrencySwitch = new CurrencySwitch({
                    events: {
                        onChangeCurrency: function (Switch, Data) {
                            self.$changeDisplayCurrency(Switch.$Elm, Data);
                            // close menu after each click
                            Switch.$Elm.blur();
                        }
                    }
                }).inject(self.Menu);

                if (typeof window.DEFAULT_USER_CURRENCY !== 'undefined') {
                    self.$changeDisplayCurrency(window.DEFAULT_USER_CURRENCY);
                }

                QUIAjax.get('package_quiqqer_template-cologne_ajax_getLangList', function (html) {
                    new Element('div', {
                        'class': 'huh',
                        html   : html
                    }).inject(self.Menu);

                    resolve();
                }, {
                    'package'     : 'quiqqer/template-cologne',
                    flagFolderPath: self.$Elm.getAttribute('data-qui-options-flag-folder')
                });
            });
        },

        /**
         * Change button currency data (html)
         *
         * @param CurrencySwitch
         * @param Data
         */
        $changeDisplayCurrency: function (CurrencySwitch, Data) {
            var Display = this.$Elm.getElement('.lcs-button-currency');

            if (!Data) {
                return;
            }

            if (Data.text) {
                Display.set({
                    title: Data.text
                });
            }

            if (Data.sign) {
                Display.getElement('.lcs-button-currency-sign').set({
                    html: Data.sign
                });
            }

            if (Data.code) {
                Display.getElement('.lcs-button-currency-code').set({
                    html: Data.code
                });
            }
        }
    });
});

