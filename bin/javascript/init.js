var lg = 'quiqqer/template-cologne';

window.addEvent('domready', function () {
    "use strict";

    require([
        'qui/QUI'
    ], function (QUI) {

        /**
         * toTop button
         */
        if (document.getElements('[href=#top]')) {
            var toTop         = document.getElements('[href=#top]'),
                buttonVisible = false;

            // show on load after 1s delay
            if (QUI.getScroll().y > 300) {
                toTop.addClass('toTop__show');
                buttonVisible = true;
            }

            // show button toTop after scrolling down
            QUI.addEvent('scroll', function () {
                if (QUI.getScroll().y > 300) {
                    if (!buttonVisible) {
                        toTop.addClass('toTop__show');
                        buttonVisible = true;
                    }
                    return;
                }

                if (!buttonVisible) {
                    return;
                }
                toTop.removeClass('toTop__show');
                buttonVisible = false;
            });

            // scroll to top
            toTop.addEvent('click', function (event) {
                event.stop();
                new Fx.Scroll(window).toTop();
            });
        }

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

        require([
            'Locale',
            'package/quiqqer/tooltips/bin/html5tooltips'
        ], function (QUILocale, html5tooltips) {
            QUI.setAttribute('control-loader-type', 'fa-spinner');
            QUI.setAttribute('control-loader-color', '#999999');

            var Logo             = document.getElement('header .logo'),
                UserButton       = document.getElements('.cologne-header-control-user'),
                UserButtonLoader = UserButton.getElement('.cologne-header-control-user-loader');

            window.addEvent('load', function () {
                document.getElement('.cologne-header-menu').setStyle('overflow', 'visible');
            });

            /**
             * Login
             */
            require([
                'utils/Controls'
            ], function (QUIControlUtils) {
                if (QUIQQER_USER.id) {
                    var UserIcon = document.getElement(
                        '[data-qui="package/quiqqer/frontend-users/bin/frontend/controls/UserIcon"]'
                    );

                    QUIControlUtils.getControlByElement(UserIcon).then(function (UserIconControl) {
                        UserIconControl.addEvent('load', function () {
                            userIconLoadEvent(UserIconControl, QUILocale);
                        });
                    })
                }

                UserButton.addEvents({
                    click: function (event) {
                        if (event) {
                            event.stop();
                        }

                        if (!QUIQQER_USER.id) {
                            createLoginWindow();
                        }
                    }
                });

                moofx(UserButtonLoader).animate({
                    opacity: 0
                }, {
                    callback: function () {
                        UserButtonLoader.destroy();
                    }
                });

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
                    open  : BASKET_OPEN.toInt(),
                    styles: {
                        float: 'right'
                    },
                    events: {
                        onCreate       : function (Basket) {
                            var BasketNode     = Basket.getElm(),
                                basketStyleCss = '';

                            if (BASKET_STYLE) {
                                basketStyleCss = 'basket-style-' + BASKET_STYLE;
                            }

                            // clear default content
                            BasketNode.set('html', '');
                            BasketNode.addClass('tpl-btn ' + basketStyleCss);

                            new Element('span', {
                                'class': 'quiqqer-order-basketButton-icon-custom',
                                html   : '<span class="fa fa-shopping-basket"></span>'
                            }).inject(BasketNode);

                            new Element('span', {
                                'class': 'quiqqer-order-basketButton-quantity quiqqer-order-basketButton-batch-custom',
                                html   : '0'
                            }).inject(BasketNode);

                            if (BASKET_STYLE && BASKET_STYLE === 'full') {
                                new Element('span', {
                                    'class': 'quiqqer-order-basketButton-sum',
                                    html   : INITAL_BASKET_PRICE
                                }).inject(BasketNode);
                            }

                            document.getElement('.cologne-header-control-basket').set('html', '');
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
                                right: 0 // right margin from .cologne-header-control-basket
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
            /*require(['package/quiqqer/currency/bin/controls/Switch'], function (Switch) {
                new Switch().inject(document.getElement('.cologne-header-control-currencies'));
            });*/


            /**
             * Sticky menu
             * @type {boolean}
             */
            var Menu         = document.getElement('.cologne-header'),
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

                    }).delay(100);
                }

                Menu.addClass('cologne-header-fixed');
                document.body.addClass('header-fixed');
            };

            var removeMenuFixed = function () {
                Menu.removeClass('cologne-header-fixed');
                Menu.setStyle('position', null);
                document.body.removeClass('header-fixed');
            };

            if (Menu) {
                // check on page load if menu should be sticked to the top
                if (QUI.getScroll().y >= topBarHeight) {
                    setMenuFixed(true);
                }

                QUI.addEvent('scroll', function () {
                    if (QUI.getScroll().y >= topBarHeight) {
                        setMenuFixed(false);
                        return;
                    }

                    removeMenuFixed();
                })
            }
        });
    });
});

/**
 * UserIcon load event
 *
 * @param UserIconControl
 * @param QUILocale
 */
function userIconLoadEvent (UserIconControl, QUILocale) {
    var Menu = UserIconControl.$Menu;

    require([
        'controls/users/LogoutWindow',
        'qui/controls/contextmenu/Item',
        'qui/controls/contextmenu/Separator'
    ], function (LogoutWindow, Item, Separator) {
        Menu.appendChild(new Separator());
        // own logout entry
        Menu.appendChild(
            new Item({
                icon  : 'fa fa-sign-out',
                text  : QUILocale.get('quiqqer/template-cologne', 'frontend.usericon.menuentry.logout.label'),
                events: {
                    click: function () {
                        createLogoutWindow(LogoutWindow);
                    }
                }
            })
        );
    });

    UserIconControl.addEvent('onMenuShow', function (UserIconControl, MenuNode) {
        MenuNode.setStyles({
            left : null,
            right: -25
        });
    });
}

/**
 * Create and open logout window
 *
 * @param LogoutWindow
 */
function createLogoutWindow (LogoutWindow) {
    new LogoutWindow({
        class    : 'cologne-logout-dialog',
        title    : false,
        icon     : false,
        maxHeight: 350,
        maxWidth : 400,
        events   : {
            onOpen: function (Popup) {

                var Content     = Popup.getElm(),
                    ContentElms = [
                        Content.getElement('.qui-window-popup-content'),
                        Content.getElement('.qui-window-popup-buttons')
                    ];

                ContentElms.each(function (ContentElm) {
                    ContentElm.setStyle('opacity', 0);
                });

                var CancelButton = Content.getElement('button[name="cancel"]');

                if (CancelButton) {
//                    CancelButton.removeClass('btn-light');
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
}

/**
 * Create and open login popup
 */
function createLoginWindow () {
    require([
        'package/quiqqer/frontend-users/bin/frontend/controls/login/Window'
    ], function (LoginWindow) {
        new LoginWindow({
            class    : 'cologne-login-dialog',
            title    : false,
            maxHeight: 500,
            maxWidth : 400,
            events   : {
                onSuccess: function () {
                    window.location.reload();
                }
            }
        }).open();
    })
}