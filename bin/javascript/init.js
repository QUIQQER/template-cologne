var lg                  = 'quiqqer/template-cologne',
    USER_BUTTON_CLICKED = false;

window.addEvent('domready', function () {
    "use strict";

    require([
        'qui/QUI'
    ], function (QUI) {

        initMobileMenu();

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
                Hammer   : URL_OPT_DIR + 'bin/quiqqer-asset/hammerjs/hammerjs/hammer.min',
                FastClick: URL_OPT_DIR + 'bin/quiqqer-asset/fastclick/fastclick/lib/fastclick'
            }
        });

        require(['FastClick'], function (FastClick) {
            FastClick.attach(document.body);
        });

        /**
         * Sticky menu
         * @type {boolean}
         */
        var Menu   = document.getElement('.cologne-header'),
            TopBar = document.getElement('.topbar');

        if (Menu) {
            initMenu(Menu, TopBar);
        }

        /**
         * Init basket
         */
        if (document.getElement('.cologne-header-control-basket')) {
            initBasket();
        }

        /**
         * User icon
         */
        if (Menu && TopBar) {
            require([
                'Locale',
                'package/quiqqer/tooltips/bin/html5tooltips'
            ], function (QUILocale, html5tooltips) {
                QUI.setAttribute('control-loader-type', 'fa-spinner');
                QUI.setAttribute('control-loader-color', '#999999');

                var Logo       = Menu.getElement('.logo'),
                    UserButton = document.getElement('.cologne-header-control-user');

                if (!UserButton) {
                    return;
                }

                var UserButtonLoader = UserButton.getElement('.cologne-header-control-user-loader');

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
                        });
                    }

                    UserButton.addEvents({
                        click: function (event) {
                            if (event) {
                                event.stop();
                            }

                            if (!QUIQQER_USER.id) {
                                if (USER_BUTTON_CLICKED) {
                                    return;
                                }

                                USER_BUTTON_CLICKED = true;
                                createLoginWindow();
                            }
                        }
                    });

                    if (UserButtonLoader) {
                        moofx(UserButtonLoader).animate({
                            opacity: 0
                        }, {
                            callback: function () {
                                UserButtonLoader.destroy();
                            }
                        });
                    }

                    if ("QUIQQER_LOGIN_FAILED" in window && window.QUIQQER_LOGIN_FAILED) {
                        new LoginWindow({
                            maxHeight   : 380,
                            social      : false,
                            registration: false,
                            logo        : Logo ? Logo.src : '',
                            events      : {
                                onSuccess: function () {
                                    window.location.reload();
                                }
                            }
                        }).open();
                    }
                });
            });
        }

        // region functions

        /**
         * Init menu
         */
        function initMenu (Menu, TopBar) {
            var showMenuFrom = TopBar ? TopBar.getSize().y : 0,
                isMenuSticky = false,
                SearchBtn    = Menu.getElement('.search-button'),
                SearchInput  = TopBar ? TopBar.getElement('.template-search input[type="search"]') : null;

            if (SHOW_MENU_START_POS && SHOW_MENU_START_POS.toInt() > 0) {
                showMenuFrom = SHOW_MENU_START_POS.toInt();
            }

            if (SearchBtn && SearchInput) {
                var clickEvent = function () {
                    new Fx.Scroll(window, {
                        onComplete: function () {
                            SearchInput.focus();
                        }
                    }).toTop();
                };

                if (QUI.getWindowSize().x < 767) {
                    clickEvent = function () {
                        document.getElement('.quiqqer-products-search-suggest-form-button').click();
                    };
                }

                SearchBtn.addEvent('click', clickEvent);
            }

            /**
             * Stick menu to the top
             *
             * @param smooth {bool} - helpful on page reload when the page is already scrolled
             */
            function setMenuFixed (smooth) {
                if (smooth === true) {
                    Menu.setStyles({
                        position : 'fixed',
                        transform: 'translateY(-100px)'
                    });

                    // Delay 500ms for performance reasons (on page load)
                    (function () {
                        moofx(Menu).animate({
                            transform: 'translateY(0)'
                        });
                    }).delay(500);
                }

                Menu.addClass('cologne-header-fixed');
                document.body.addClass('header-fixed');
                isMenuSticky = true;
            }

            /**
             * Show search button
             */
            var showSearchBtn = function () {
                if (!SearchBtn) {
                    return;
                }

                moofx(SearchBtn).animate({
                    opacity  : 1,
                    transform: 'scale(1)'
                }, {
                    duration: 300,
                    equation: 'cubic-bezier(0.6, -0.4, 0.2, 2.11)'
                });
            };

            /**
             * Hide search button
             */
            var hideSearchBtn = function () {
                if (!SearchBtn) {
                    return;
                }

                moofx(SearchBtn).animate({
                    opacity  : 0,
                    transform: 'scale(0)'
                }, {
                    duration: 300
                });
            };

            /**
             * Set menu position to initial
             */
            var removeMenuFixed = function () {
                Menu.removeClass('cologne-header-fixed');
                Menu.setStyle('position', null);
                document.body.removeClass('header-fixed');
                isMenuSticky = false;
            };

            // check on page load if menu should stick to the top
            if (QUI.getScroll().y >= showMenuFrom) {
                if (isMenuSticky) {
                    return;
                }

                setMenuFixed(true);
                showSearchBtn();
            }

            QUI.addEvent('scroll', function () {
                if (QUI.getScroll().y >= showMenuFrom) {
                    if (isMenuSticky) {
                        return;
                    }

                    setMenuFixed(SHOW_MENU_SMOOTH);
                    showSearchBtn();
                    return;
                }

                if (!isMenuSticky) {
                    return;
                }

                removeMenuFixed();
                hideSearchBtn();
            });
        }

        /**
         * Basket
         */
        function initBasket () {
            require([
                'package/quiqqer/order/bin/frontend/controls/basket/Button'
            ], function (Basket) {
                new Basket({
                    open  : BASKET_OPEN.toInt(),
                    styles: {
                        float: 'right'
                    },
                    events: {
                        onCreate: function (Basket) {
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
        }

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
                        text  : QUILocale.get(lg, 'frontend.usericon.menuentry.logout.label'),
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
                            });
                        }).delay(50);
                    }
                }
            }).open();
        }

        /**
         * Create and open login popup
         */
        function createLoginWindow (onlyLogin = false) {
            USER_BUTTON_CLICKED = false;

            console.log("huhu")

            require([
                'Locale',
                'utils/Controls',
                'package/quiqqer/frontend-users/bin/frontend/controls/login/Window'
            ], function (QUILocale, QUIControlUtils, LoginWindow) {
                new LoginWindow({
                    class    : 'cologne-login-dialog',
                    title    : false,
                    maxHeight: 550,
                    maxWidth : 400,
                    events   : {
                        onOpen   : function (LoginWindow) {
                            if (!REGISTER_URL && !onlyLogin) {
                                return;
                            }

                            if (onlyLogin) {
                                return;
                            }

                            var Elm = LoginWindow.getElm();

                            var CreateAccountWrapper = new Element('div', {
                                'class': 'login-popup-create-account-wrapper'
                            });

                            new Element('a', {
                                href: REGISTER_URL,
                                html: QUILocale.get(lg, 'template.popup.login.registration.button'),
                            }).inject(CreateAccountWrapper);

                            CreateAccountWrapper.inject(Elm.getElement('.qui-window-popup-content'));
                        },
                        onSuccess: function () {
                            window.location.reload();
                        }
                    }
                }).open();
            });
        }

        /**
         * Menu mobile
         *
         * In mobile resolution (less than 767px) opens category menu button
         * the mobile navigation instead category navigation.
         */
        function initMobileMenu () {
            if (QUI.getWindowSize().x >= 768) {
                return;
            }

            var OpenCategoryBtn = document.getElement('.shop-category-menu-button'),
                MenuElm         = document.getElement('[data-qui="package/quiqqer/menu/bin/SlideOut"]');

            if (!OpenCategoryBtn) {
                console.error('Open Category Button ".shop-category-menu-button" not found.');
                return;
            }

            require(['utils/Controls'], function (Controls) {
                Controls.getControlByElement(MenuElm).then(function (MenuControl) {
                    OpenCategoryBtn.removeEvents('click');
                    OpenCategoryBtn.addEvent('click', function () {
                        MenuControl.toggle();
                    });
                });
            });
        }

        // end region functions

    });
});
