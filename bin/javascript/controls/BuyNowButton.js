/**
 * @module package/quiqqer/template-cologne/bin/javascript/controls/BuyNowButton
 */
define('package/quiqqer/template-cologne/bin/javascript/controls/BuyNowButton', [

    'qui/QUI',
    'qui/controls/Control',
    'package/quiqqer/order/bin/frontend/Basket',
    'package/quiqqer/order/bin/frontend/classes/Product',
    'Ajax'

], function (QUI, QUIControl, Basket, BasketProduct, QUIAjax) {
    "use strict";
    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/template-cologne/bin/javascript/controls/BuyNowButton',

        Binds: [
            '$addProductToBasket'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Input = null;
            this.$Label = null;

            this.$disabled = false;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event: on import
         */
        $onImport: function () {
            this.getElm().addEvent('click', this.$addProductToBasket);
            this.getElm().set('disabled', false);

            this.$Label = this.getElm().getElement('.add-to-basket-text');
        },

        /**
         * add the product to the basket
         */
        $addProductToBasket: function () {
            if (this.$disabled) {
                return;
            }

            this.getElm().set('disabled', true);

            var self  = this,
                count = null,
                size  = this.getElm().getSize();

            if (this.$Input) {
                count = parseInt(this.$Input.value);
            }

            if (count === null) {
                count = 1;
            }

            this.$Label.setStyle('visibility', 'hidden');

            var Loader = new Element('div', {
                'class': 'quiqqer-order-button-add-loader',
                html   : '<span class="fa fa-spinner fa-spin"></span>',
                styles : {
                    height        : '100%',
                    left          : 0,
                    position      : 'absolute',
                    top           : 0,
                    width         : '100%',
                    display       : 'flex',
                    alignItems    : 'center',
                    justifyContent: 'center'
                }
            }).inject(this.getElm());

            // is the button in a product?
            var fields         = {},
                ProductElm     = this.getElm().getParent('[data-productid]'),
                ProductControl = QUI.Controls.getById(ProductElm.get('data-quiid'));

            if ("getFieldControls" in ProductControl) {
                ProductControl.getFieldControls().each(function (Field) {
                    fields[Field.getFieldId()] = Field.getValue();
                });
            }

            var Product = new BasketProduct({
                id: ProductControl.getAttribute('productId')
            });

            Product.setFieldValues(fields).then(function () {
                return Product.setQuantity(count);
            }).then(function () {
                return Basket.addProduct(Product);
            }).then(function () {
                var Span = Loader.getElement('span');

                Span.removeClass('fa-spinner');
                Span.removeClass('fa-spin');

                Span.addClass('success');
                Span.addClass('fa-check');

                QUIAjax.get('package_quiqqer_order_ajax_frontend_basket_getOrderProcessUrl', function (url) {
                    window.location = url;
                }, {
                    'package': 'quiqqer/order'
                });
            }.bind(this));
        }
    });
});
