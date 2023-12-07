var lg                  = 'quiqqer/template-cologne',
    USER_BUTTON_CLICKED = false;

window.addEvent('domready', function () {
    "use strict";

    require([
        'qui/QUI',
        'qui/utils/System'
    ], function (QUI, QUISystemUtils) {

        // change default loader
        QUI.setAttribute('control-loader-type', 'fa-circle-o-notch');

        initMobileMenu();
        initScrollToAnchor();

        if (QUISystemUtils.iOSversion()) {
            document.body.classList.add('iosFix');
        }

        if (document.body.hasClass('type-quiqqer-products-types-category')) {
            initExpandCategoryContent();
            setSidebarPosition();
        }

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
        if (TopBar) {
            require([
                'Locale',
                'package/quiqqer/tooltips/bin/html5tooltips'
            ], function (QUILocale, html5tooltips) {
                var Logo       = Menu ? Menu.getElement('.logo') : null,
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
                SearchInput  = TopBar ? TopBar.getElement('.template-search input[type="search"]') : null,
                MenuWrapper  = document.querySelector('.cologne-header-menu-wrapper'),
                menuPos      = MenuWrapper ? MenuWrapper.offsetTop : 0;

            if (SHOW_MENU_AFTER_SCROLL_POS && SHOW_MENU_AFTER_SCROLL_POS.toInt() > 0) {
                showMenuFrom = SHOW_MENU_AFTER_SCROLL_POS.toInt();
            }

            if (SearchBtn && SearchInput) {
                const ActionSearchBtn = document.getElement('.quiqqer-products-search-suggest-form-button');

                if (!ActionSearchBtn) {
                    return;
                }

                let clickEvent = function () {
                    new Fx.Scroll(window, {
                        onComplete: function () {
                            SearchInput.focus();
                        }
                    }).toTop();
                };

                if (QUI.getWindowSize().x < 767) {
                    // https://dev.quiqqer.com/quiqqer/template-cologne/-/issues/104
                    ActionSearchBtn.removeAttribute('type');

                    clickEvent = function () {
                        ActionSearchBtn.click();
                    };
                }

                SearchBtn.addEvent('click', clickEvent);
            }

            /**
             * Stick menu to the top
             *
             * @param smooth {boolean} - helpful on page reload when the page is already scrolled
             */
            function setMenuFixed (smooth) {
                smooth = !!smooth;

                if (smooth === true) {
                    Menu.setStyles({
                        position : 'fixed',
                        transform: 'translateY(-150px)'
                    });

                    moofx(Menu).animate({
                        transform: 'translateY(0)'
                    });
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
            // delay 500ms for performance reasons on page load
            setTimeout(() => {
                if (QUI.getScroll().y > showMenuFrom) {
                    if (isMenuSticky) {
                        return;
                    }

                    let showMenuSmooth = false;

                    if (MenuWrapper && QUI.getScroll().y > MenuWrapper.offsetHeight + MenuWrapper.offsetTop) {
                        showMenuSmooth = true;
                    }

                    setMenuFixed(showMenuSmooth);
                    showSearchBtn();
                }
            }, 500)

            QUI.addEvent('scroll', function () {
                if (QUI.getScroll().y > showMenuFrom) {
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


                // set menu position back on menu initial position
                if (SET_MENU_POS_BACK_ON_INIT === true) {
                    if (QUI.getScroll().y <= menuPos) {

                        removeMenuFixed();
                        hideSearchBtn();

                    }

                    return;
                }

                // set menu position back depend on "show menu from" setting
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

            let OpenCategoryBtn = document.getElement('.shop-category-menu-button'),
                MenuElm         = document.getElement('[data-qui="package/quiqqer/menu/bin/SlideOut"]');

            if (!MenuElm) {
                MenuElm         = document.getElement('[data-qui="package/quiqqer/menu/bin/SlideoutAdvanced"]');
            }

            if (!MenuElm) {
                console.debug('Neither SlideOut nor SlideoutAdvanced found.');
                return;
            }

            if (!OpenCategoryBtn) {
                console.debug('Open Category Button ".shop-category-menu-button" not found.');
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

        /**
         * Show content of category site after click on button
         */
        function initExpandCategoryContent () {
            var ButtonContainer = document.getElement('.quiqqer-category-content-button'),
                Wrapper         = document.getElement('.quiqqer-category-content-inner'),
                Body            = document.getElement('.quiqqer-category-content-inner-body'),
                Bg              = document.getElement('.quiqqer-category-content-inner-body__bg');

            if (!ButtonContainer || !Wrapper || !Body) {
                return;
            }

            var Button     = ButtonContainer.getElement('button'),
                realHeight = Body.getSize().y;

            if (!Button) {
                return;
            }


            if (Wrapper.getSize().y >= realHeight) {
                // content is small, no button needed
                Wrapper.setStyle('max-height', null);
                Button.destroy();

                if (Bg) {
                    Bg.destroy();
                }

                return;
            }

            (function () {
                Button.setStyle('display', null);

                moofx(Button).animate({
                    opacity: 1
                }, {
                    duration: 200
                });
            }).delay(500);

            Button.addEvent('click', function (event) {
                event.stop();

                moofx(Wrapper).animate({
                    maxHeight: realHeight
                }, {
                    callback: function () {
                        moofx(ButtonContainer).animate({
                            opacity: 0
                        }, {
                            duration: 200,
                            callback: function () {
                                ButtonContainer.destroy();
                            }
                        });

                        if (Bg) {
                            moofx(Bg).animate({
                                opacity: 0
                            }, {
                                duration: 200,
                                callback: function () {
                                    Bg.destroy();
                                }
                            });
                        }

                        Wrapper.setStyle('maxHeight', null);
                    }
                });
            });
        }

        /**
         * Change position sticky to initial
         */
        function setSidebarPosition () {
            var Sidebar = document.getElement('.quiqqer-product-sidebar');

            if (!Sidebar) {
                return;
            }

            var sidebarHeight = Sidebar.getSize().y,
                windowHeight  = QUI.getWindowSize().y;

            if (sidebarHeight > windowHeight - 60) {
                Sidebar.setStyle('position', 'initial');
            }
        }

        /**
         * Find all anchors and set click event to smoothly scroll to the element.
         * It works both with all HTML elements.
         * Every element witch is not an <a> tag, needs "data-qui-target-id" or "data-qui-target-class" attribute.
         * Target for <a> elements is always a # (hash) string
         *
         * Anchor settings:
         *   scrollToLink - [required] only elements with this css class will be considered
         *   data-qui-target="myTargetElement" - [optional] every valid css selector
         *   data-qui-offset="60" - [optional] scroll offset
         *
         * Examples:
         * <a class="scrollToLink" href="#myElement">Scroll to myElement</a>
         * <button class="scrollToLink" data-qui-target"#myElement">Scroll to element with ID myElement</button>
         * <span class="scrollToLink" data-qui-target=".exampleParagraph" data-qui-offset="150">Scroll to element with CSS class exampleParagraph</span>
         */
        function initScrollToAnchor () {
            let links = document.querySelectorAll('.scrollToLink');

            let getTarget = function (Link) {
                if (Link.get('data-qui-target')) {
                    return document.querySelector(Link.get('data-qui-target'));
                }

                let href = Link.href;

                if (!href || href.indexOf('#') === -1) {
                    return false;
                }

                let targetString = href.substring(href.indexOf('#') + 1);

                if (targetString.length < 1) {
                    return false;
                }

                let TargetElm = document.getElementById(targetString);

                if (!TargetElm) {
                    return false;
                }

                return TargetElm;
            };

            let clickEvent = function (Target, offset) {
                new Fx.Scroll(window, {
                    offset: {
                        y: -offset
                    }
                }).toElement(Target);
            };

            for (let Link of links) {
                let TargetElm = getTarget(Link);

                if (!TargetElm) {
                    continue;
                }

                let offset = Link.get('data-qui-offset');

                if (!offset) {
                    offset = window.SCROLL_OFFSET ? window.SCROLL_OFFSET : 80;
                }

                Link.addEvent('click', function (event) {
                    event.stop();
                    clickEvent(TargetElm, offset);
                });
            }
        }

        // end region functions

    });
});
