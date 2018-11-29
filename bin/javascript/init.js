window.addEvent('domready', function () {
    "use strict";

    var lg = 'quiqqer/template-cologne';
    var Header = document.getElement('.cologne-header');

    require.config({
        paths: {
            Hammer   : URL_OPT_DIR + 'bin/hammerjs/hammer.min',
            FastClick: URL_OPT_DIR + 'bin/fastclick/lib/fastclick',
            Mustache : URL_OPT_DIR + 'bin/mustache/mustache'
        }
    });

    require(['FastClick'], function (FastClick) {
        FastClick.attach(document.body);
    });

    // QUI
    require(['qui/QUI', 'Locale'], function (QUI, QUILocale) {
        QUI.setAttribute('control-loader-type', 'fa-spinner');
        QUI.setAttribute('control-loader-color', '#999999');

        var Logo = document.getElement('header .logo'),
            Link = document.getElements('.cologne-header-control-user'),
            Icon = Link.getElement('.fa');

        window.addEvent('load', function () {
            document.getElement('.cologne-header-menu').setStyle('overflow', 'visible');
        });

        /**
         * Login
         */
        require([
            'controls/users/LoginWindow',
            'controls/users/LogoutWindow'
        ], function (LoginWindow, LogoutWindow) {
            Link.addEvents({
                click: function (event) {
                    if (event) {
                        event.stop();
                    }

                    if (QUIQQER_USER.id) {
                        new LogoutWindow({
                            class    : 'cologne-logout-dialog',
                            title    : false,
                            icon     : false,
                            maxHeight: 320,
                            maxWidth : 400,
                            events   : {
                                onOpen: function (Popup) {
                                    var Content = Popup.getElm();

                                    var ContentElms = [
                                        Content.getElement('.qui-window-popup-content'),
                                        Content.getElement('.qui-window-popup-buttons')
                                    ];

                                    ContentElms.each(function (ContentElm) {
                                        ContentElm.setStyle('opacity', 0);
                                    });

                                    var CancelButton = Content.getElement('button[name="cancel"]');
                                    if (CancelButton) {
                                        CancelButton.addClass('btn-secondary btn-outline');
                                    }

                                    // workaround due to the CancelButton.addClass
                                    // to avoid the "flash" effect
                                    (function () {
                                        ContentElms.each(function (ContentElm) {
                                            moofx(ContentElm).animate({
                                                opacity: 1
                                            });
                                        })
                                    }).delay(50)
                                }
                            }
                        }).open();
                    } else {
                        new LoginWindow({
                            title       : false,
                            maxHeight   : 500,
                            maxWidth    : 400,
                            icon        : false,
                            social      : false,
                            registration: false,
                            logo        : Logo.src,
                            events      : {
                                onSuccess: function () {
                                    window.location.reload();
                                },
                                onOpen   : function (Popup) {
                                    var Content = Popup.getElm().getElement('.quiqqer-loginWindow-content');

                                    Content.setStyle('opacity', 0);
                                    new Element('h2', {
                                        'class': 'quiqqer-loginWindow-content-title',
                                        html   : QUILocale.get(lg, 'frontend.popup.login.title')
                                    }).inject(Content.getElement('img'), 'after');

                                    moofx(Content).animate({
                                        opacity: 1
                                    });
                                }
                            }
                        }).open();
                    }
                }
            });

            Icon.removeClass('fa-spinner');
            Icon.removeClass('fa-spin');
            Icon.addClass('fa-user');


            if ("QUIQQER_LOGIN_FAILED" in window && window.QUIQQER_LOGIN_FAILED) {
                new LoginWindow({
                    maxHeight   : 380,
                    social      : false,
                    registration: false,
                    logo        : Logo.src,
                    events      : {
                        onSuccess: function () {
                            window.location.reload();
                        }
                    }
                }).open();
            }
        });

        /**
         * Basket
         */
        require([
            'package/quiqqer/order/bin/frontend/controls/basket/Button'
        ], function (Basket) {
            new Basket({
                styles: {
                    float: 'right'
                },
                events: {
                    onCreate       : function (Basket) {
                        var BasketNode = Basket.getElm();

                        // clear default content
                        BasketNode.set('html', '');
                        BasketNode.addClass('tpl-btn');

                        new Element('span', {
                            'class': 'quiqqer-order-basketButton-icon-custom',
                            html   : '<span class="fa fa-shopping-basket"></span>'
                        }).inject(BasketNode);

                        new Element('span', {
                            'class': 'quiqqer-order-basketButton-quantity quiqqer-order-basketButton-batch-custom',
                            html   : '0'
                        }).inject(BasketNode);

                        new Element('span', {
                            'class': 'quiqqer-order-basketButton-sum',
                            html   : '---',
                            styles : {}
                        }).inject(BasketNode);
                    },
                    /**
                     * onShowBasketBegin event
                     *
                     * @param Basket
                     * @param pos - position of popup basket
                     * @param height - height of basket button
                     */
                    showBasketBegin: function (Basket, pos, height) {

                        // move basket popup from bottom of the page to header
                        // it's better to manage for sticky header
                        Header.getElement('.cologne-header-control').adopt(Basket.$BasketContainer);

                        var headerHeight = Header.getSize().y;

                        // -1px because of header bottom border
                        pos.y = headerHeight - 1;

                        // reset button height
                        // see package/quiqqer/order/bin/frontend/controls/basket/Button.showSmallBasket()
                        height.y = 0;

                        Basket.$BasketContainer.setStyles({
                            right: 20 // right margin from .cologne-header-control-basket
                        });

                        // Do not scroll the page
                        Basket.$BasketContainer.addEvent('focus', function (event) {
                            event.preventDefault();
                        });

                        Basket.$BasketContainer.setStyles({
                            border: '1px solid #ddd'
                        });
                    }
                }
            }).inject(document.getElement('.cologne-header-control-basket'));
        });

        /**
         * Currencies
         */
        require(['package/quiqqer/currency/bin/controls/Switch'], function (Switch) {
            new Switch().inject(document.getElement('.cologne-header-control-currencies'));
        });


        /**
         * Sticky menu
         * @type {boolean}
         */
        var Menu         = document.getElement('.cologne-header'),
            menuHeight   = Menu.getSize().y,
            topBarHeight = document.getElement('.topbar').getSize().y;

        /**
         *
         * @param smooth {bool} - helpful on page reload when the page is already scrolled
         */
        var setMenuFixed = function (smooth) {

            if (smooth === true) {
                Menu.setStyles({
                    position : 'fixed',
                    transform: 'translateY(-100px)'
                });

                // Delay 500ms for performance reasons (on page load)
                (function () {
                    moofx(Menu).animate({
                        transform: 'translateY(0)'
                    })

                }).delay(500);
            }

            Menu.addClass('cologne-header-fixed');

            document.body.addClass('header-fixed');
        };

        var removeMenuFixed = function () {
            Menu.removeClass('cologne-header-fixed');
            Menu.setStyle('position', null);
//            document.body.setStyle('padding-top', null);
            document.body.removeClass('header-fixed');
        };

        if (Menu) {
            // check on page load if menu should be sticked to the top
            if (QUI.getScroll().y >= topBarHeight) {

                setMenuFixed(true);
            }

            window.addEvent('scroll', function () {
                if (QUI.getScroll().y >= topBarHeight) {
                    setMenuFixed(false);
                    return;
                }

                removeMenuFixed();
            })
        }
    });
});
