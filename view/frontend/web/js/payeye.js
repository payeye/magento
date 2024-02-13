define([
    'uiComponent',
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'PayEye_PayEye/js/payeye-widget.min'
], function (Component, $, urlBuilder, customerData) {
    'use strict';

    const SECTION_NAME = 'payeye';

    return Component.extend({
        defaults: {
            issetEventListener: false,
            lastStatus: ''
        },
        initialize: function () {
            this._super();
            var payEyeData = customerData.get(SECTION_NAME);

            customerData.reload([SECTION_NAME], true);
            payEyeData.subscribe(function (updatedData) {
                var updatedPayEyeData = this.prepareDataFromCart(updatedData);
                if (!updatedPayEyeData) return;

                var payEyeCartUpdateEvent = new CustomEvent('payeye-cart-update', {detail: updatedPayEyeData});
                document.dispatchEvent(payEyeCartUpdateEvent);

                if (!this.issetEventListener) {
                    this.addChangeEventListener(updatedPayEyeData);
                }
            }, this);
        },

        prepareDataFromCart: function (data) {
            if (!data.deepLink && !data.cart) return;

            return {
                apiVersion: data.apiVersion,
                deepLink: data.deepLink,
                cart: data.cart
            }
        },

        addChangeEventListener: function(updatedPayEyeData) {
            this.issetEventListener = true;

            if (!updatedPayEyeData.cart.id) return;

            var updateRequestInterval = setInterval(function () {
                var serviceUrl = `rest/V${updatedPayEyeData.apiVersion}/api-payeye/widget/status?cartId=${updatedPayEyeData.cart.id}`;

                $.ajax({
                    url: urlBuilder.build(serviceUrl),
                    type: 'GET',
                    cache: true
                }).done(function (response) {
                    if (!response.open) return;

                    if (response.status !== this.lastStatus) {
                        var cartSections = ['cart'];
                        customerData.invalidate(cartSections);
                        customerData.reload(cartSections, true);
                    }
                }.bind(this));
            }.bind(this), 5000);
        }
    });
});
