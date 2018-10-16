window.addEvent('domready', function () {
    "use strict";

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
    require(['qui/QUI'], function (QUI) {
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
                        new LogoutWindow().open();
                    } else {
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
                    onCreate: function (Basket) {
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

                    showBasketBegin: function (Basket, pos) {

                        var bPosition = Basket.getElm().offsetTop,
                            bHeight = Basket.getElm().getHeight();
//                        console.log(Basket.getElm().offsetTop)
//                        console.log(bHeight)

                        var winSize    = window.getSize(),
                            basketSize = Basket.getElm().getSize();

                        // beispiel
//                        pos.x = pos.x - 500;
                        // muss noch besser gemacht werden
                        pos.x = winSize.x - 350 - 20;
//                        pos.y = pos.y + 10;
//                        pos.y = bPosition  + bHeight + 12;



                        /*neu*/
                        var Header = document.getElement('.cologne-header');
                        console.log(Header.getPosition().y)
//                        console.log(Basket.getElm().getCoordinates(Header))
console.log(pos.y)
                        pos.y = Header.getPosition().y;

                        Basket.$BasketContainer.setStyles({
                            position: 'fixed'
                        })



                        Basket.$BasketContainer.setStyles({
                            border: '1px solid #ddd'
                        });

                        console.log(document.getElement('.quiqqer-order-basket-small-container').getPosition());
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
        var menuFixed    = false,
            Menu         = document.getElement('.cologne-header'),
            menuHeight   = Menu.getSize().y,
            topBarHeight = document.getElement('.topbar').getSize().y;

        var setMenuFixed = function () {
            Menu.addClass('cologne-header-fixed');
            document.getElement('body').setStyle('padding-top', menuHeight);
            menuFixed = true;
        };

        var removeMenuFixed = function () {
            Menu.removeClass('cologne-header-fixed');
            document.getElement('body').setStyle('padding-top', null);
            menuFixed = false;
        };

        if (Menu) {
            window.addEvent('scroll', function () {

                if (QUI.getScroll().y >= topBarHeight) {
                    setMenuFixed();
                    return;
                }

                removeMenuFixed();
            })
        }
    });
});