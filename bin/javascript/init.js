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
            Link = document.getElements('.topbar-right'),
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
                    width: 100
                },
                events: {
                    onCreate: function (Basket) {
                        var BasketNode = Basket.getElm();

                        // beispiel
                        BasketNode.set('html', 'huhu');
                    },

                    showBasketBegin: function (Basket, pos) {
                        console.warn(pos);

                        // beispiel
                        pos.x = pos.x - 200;
                    }
                }
            }).inject(document.getElement('.cologne-header-control-basket'));
        });
    });

});
