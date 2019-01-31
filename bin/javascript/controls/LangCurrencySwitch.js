/**
 * @module package/quiqqer/product-bricks/bin/controls/LangCurrencySwitch
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'qui/controls/loader/Loader',
    'qui/controls/utils/Background',
    'package/quiqqer/currency/bin/controls/Switch',
    'package/quiqqer/currency/bin/Currency'

], function (QUI, QUIControl, QUIAjax, QUILoader, QUIBackground, CurrencySwitch, Currency) {
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
            'closeImmediately'
        ],

        options: {},

        initialize: function (options) {
            this.parent(options);

            this.Button = null;
            this.Currency = null;
            this.Menu = null;
            this.Loader = null;
            this.Background = null;
            this.isOpen = false;
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
            this.Button = this.$Elm.getElement('.lcs-button');

            // todo vllt später wenn animation kommt.
            // das braucht man damit der loader da bleibt.
            /*this.MenuContainer = new Element('div', {
                'class' : 'menu-container'
            }).inject(this.MenuWrapper);*/

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

            /*this.Background = new QUIBackground({
                events: {
                    onClick: self.close
                }
            });*/


            QUI.addEvent('scroll', this.closeImmediately);

            /*this.Background.create();
            this.Background.show();*/

            this.Loader = new QUILoader();
            this.Loader.inject(this.Button);
            this.Loader.show();

            this.createMenu().then(function () {
                self.Menu.inject(self.MenuWrapper);
                self.Loader.hide();
                self.isOpen = true;
                self.Menu.focus();

                console.log(self.Menu)
            });
        },

        /**
         * Close and destroy the menu
         */
        close: function () {
            if (this.closeAnimationIsRunning) {
                return;
            }
            
            this.closeAnimationIsRunning = true;
            var self = this;

            moofx(this.Menu).animate({
                opacity: 0
            }, {
                duration: 300,
                callback: function () {
                    self.Menu.destroy();
                    self.Menu = null;
                    self.isOpen = false;
                    self.closeAnimationIsRunning = false;
//                    self.Background.destroy();
                    document.body.removeEvent('click', self.close);
                }
            })
        },

        /**
         * Close the menu immediately e.g. by on scroll
         */
        closeImmediately: function () {
            QUI.removeEvent('scroll', this.closeImmediately);
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
                            event.stop();
                        }
                    }
                });

                self.CurrencySwitch = new CurrencySwitch({
                    events: {
                        onChangeCurrency: self.$changeDisplayCurrency
                    }
                }).inject(self.Menu);

                QUIAjax.get('package_quiqqer_template-cologne_ajax_getLangList', function (html) {
                    new Element('div', {
                        'class': 'huh',
                        html   : html
                    }).inject(self.Menu);

                    document.body.addEvent('click', self.close);

                    resolve();
                }, {
                    'package'     : 'quiqqer/template-cologne',
                    flagFolderPath: self.$Elm.getAttribute('data-qui-options-flag-folder')
                });
            })
        },

        /**
         * Change button currency data (html)
         *
         * @param Currency
         * @param Data
         */
        $changeDisplayCurrency: function (Currency, Data) {
            var Display = this.$Elm.getElement('.lcs-button-currency'),
                text    = '';

            if (Data.text) {
                Display.set({
                    title: Data.text
                });
            }


            if (Data.sign) {
                Display.getElement('.lcs-button-currency-sign').set({
                    html: Data.sign
                })
            }

            if (Data.code) {
                Display.getElement('.lcs-button-currency-code').set({
                    html: Data.code
                })
            }
        }
    });
});

