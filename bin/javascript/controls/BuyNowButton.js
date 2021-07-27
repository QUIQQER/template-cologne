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
            '$addProductToBasket',
            '$getProductControl',
            '$onQuiqqerProductVariantRefresh'
        ],

        options: {
            disabled: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Input          = null;
            this.$Label          = null;
            this.$disabled       = false;
            this.$ProductControl = false;
            this.$Button         = false;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event: on import
         */
        $onImport: function () {
            this.$Label    = this.getElm().getElement('.add-to-basket-text');
            this.$disabled = this.getAttribute('disabled');

            if (this.$disabled) {
                return;
            }

            var ProductElm       = this.getElm().getParent('[data-productid]');
            this.$ProductControl = QUI.Controls.getById(ProductElm.get('data-quiid'));

            this.$ProductControl.removeEvent('onQuiqqerProductVariantRefresh', this.$onQuiqqerProductVariantRefresh);
            this.$ProductControl.addEvent('onQuiqqerProductVariantRefresh', this.$onQuiqqerProductVariantRefresh);

            this.$Button = this.getElm();

            this.$Button.addEvent('click', this.$addProductToBasket);
            this.$Button.set('disabled', false);
        },

        /**
         * Get control of the Product
         *
         * @return {Object}
         */
        $onQuiqqerProductVariantRefresh: function () {
            if (this.$ProductControl.isBuyable()) {
                this.$Button.set('disabled', false);
                this.$disabled = false;
            } else {
                this.$Button.set('disabled', true);
                this.$disabled = true;
            }
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
                count = null;

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
            var fields     = {},
                ProductElm = this.getElm().getParent('[data-productid]');

            if (ProductElm) {
                // check require fields
                var required = ProductElm.getElements('.product-data-fieldlist [required]');

                if (required) {
                    for (var i = 0, len = required.length; i < len; i++) {
                        if (!required[i].checkValidity()) {
                            //self.enableQuantityButton();
                            self.$Label.setStyle('visibility', 'visible');
                            self.addingInProcess = false;

                            Loader.destroy();
                            this.getElm().set('disabled', false);
                            this.$Label.setStyle('visibility', 'visible');

                            required[i].focus();

                            // chrome validate message
                            if ("reportValidity" in required[i]) {
                                required[i].reportValidity();
                            }
                            return;
                        }
                    }
                }
            }

            if ("getFieldControls" in this.$ProductControl) {
                this.$ProductControl.getFieldControls().each(function (Field) {
                    fields[Field.getFieldId()] = Field.getValue();
                });
            }

            var Product = new BasketProduct({
                id: this.$ProductControl.getProductId()
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
