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
                var payEyeCartUpdateEvent = new CustomEvent('payeye-cart-update', {detail: updatedPayEyeData});
                document.dispatchEvent(payEyeCartUpdateEvent);
            }, this);
        },

        prepareDataFromCart: function (data) {
            if (!data.deepLink && !data.cart) return null;

            return {
                apiVersion: data.apiVersion,
                deepLink: data.deepLink,
                cart: data.cart
            }
        }
    });
});
