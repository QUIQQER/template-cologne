/**
 * @module package/quiqqer/product-bricks/bin/controls/LangCurrencySwitch
 * @author www.pcsg.de (Michael Danielczok)
 */
define('package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'qui/controls/loader/Loader',
    'package/quiqqer/currency/bin/controls/Switch',
    'package/quiqqer/currency/bin/Currency'

], function (QUI, QUIControl, QUIAjax, QUILoader, CurrencySwitch, Currencies) {
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

        options: {
            langSwitch         : true,
            currencySwitch     : true,
            userrelatedcurrency: true
        },

        initialize: function (options) {
            this.parent(options);

            this.Button                  = null;
            this.Currency                = null;
            this.Menu                    = null;
            this.Loader                  = null;
            this.isOpen                  = false;
            this.closeAnimationIsRunning = false;
            this.langSwitch              = true;
            this.currencySwitch          = true;

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

            Promise.all([
                self.$checkLang(),
                self.$checkCurrencies()
            ]).then(function () {

                if (!self.langSwitch && !self.currencySwitch) {
                    self.Button.addClass('lcs-button__no-hover');
                    return;
                }

                self.Button.addEvent('click', function () {
                    if (self.isOpen) {
                        self.close();
                        return;
                    }

                    self.open();
                });
            });
        },

        $checkLang: function () {
            var self = this;

            return new Promise(function (resolve) {

                // save ajax request if the variable is set
                if (COUNT_LANGUAGES) {
                    if (COUNT_LANGUAGES <= 1) {
                        self.langSwitch = false;
                    }

                    resolve();
                    return;
                }

                if (!self.getAttribute('langSwitch')) {
                    self.langSwitch = false;
                    resolve();
                    return;
                }

                QUIAjax.get('package_quiqqer_template-cologne_ajax_countLang', function (langTotal) {
                    if (langTotal <= 1) {
                        self.langSwitch = false;
                    }
                    resolve();
                }, {
                    'package': 'quiqqer/template-cologne'
                });
            });
        },

        $checkCurrencies: function () {
            var self = this;

            return new Promise(function (resolve) {
                if (!self.getAttribute('currencySwitch') ||
                    !self.getAttribute('userrelatedcurrency')) {
                    self.currencySwitch = false;
                    resolve();
                    return;
                }

                // todo vllt direkt über ajax
                Currencies.getCurrencies().then(function (currencies) {
                    if (currencies.length <= 1) {
                        self.currencySwitch = false;
                    }
                    resolve();
                });
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
                if (!self.currencySwitch || !self.langSwitch) {
                    self.Menu.addClass('single-control');
                }

                self.Menu.inject(self.MenuWrapper);
                self.Loader.hide();
                self.isOpen = true;

                (function () {
                    document.body.addEvent('click', self.close);
                }).delay(1000)
            });
        },

        /**
         * Hide (with animation) and destroy the menu
         */
        close: function () {
            if (this.closeAnimationIsRunning) {
                return;
            }

            var self = this;

            this.closeAnimationIsRunning = true;

            QUI.removeEvent('scroll', this.hide);
            document.body.removeEvent('click', self.close);

            moofx(this.Menu).animate({
                opacity: 0
            }, {
                duration: 300,
                callback: function () {
                    if (self.CurrencySwitch) {
                        self.CurrencySwitch.destroy();
                    }

                    self.Menu.destroy();

                    self.Menu   = null;
                    self.isOpen = false;

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
            if (this.Menu) {
                return Promise.resolve();
            }

            var self = this;

            this.Menu = new Element('div', {
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

            return new Promise(function (resolve) {
                Promise.all([
                    self.$createCurrencySwitch(),
                    self.$createLangSwitch()
                ]).then(resolve)
            });
        },

        $createCurrencySwitch: function () {
            var self = this;

            return new Promise(function (resolve) {
                if (!self.currencySwitch) {
                    resolve();
                    return;
                }

                self.CurrencySwitch = new CurrencySwitch({
                    events: {
                        onInject        : resolve,
                        onChangeCurrency: function (Switch, Data) {
                            self.$changeDisplayCurrency(Switch.$Elm, Data);
                            // close menu after each click
                            Switch.$Elm.blur();
                        }
                    }
                });

                if (typeof window.DEFAULT_USER_CURRENCY !== 'undefined') {
                    self.$changeDisplayCurrency(window.DEFAULT_USER_CURRENCY);
                }

                self.CurrencySwitch.inject(self.Menu);
            })
        },

        $createLangSwitch: function () {
            var self = this;

            return new Promise(function (resolve) {

                if (!self.langSwitch) {
                    resolve();
                    return;
                }

                QUIAjax.get('package_quiqqer_template-cologne_ajax_getLangList', function (html) {
                    new Element('div', {
                        'class': 'lcs-language-list',
                        html   : html
                    }).inject(self.Menu);

                    resolve();
                }, {
                    'package'     : 'quiqqer/template-cologne',
                    flagFolderPath: self.$Elm.getAttribute('data-qui-options-flag-folder'),
                    siteId        : QUIQQER_SITE.id
                });
            })
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

