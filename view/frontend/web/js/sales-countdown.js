/**
 * Copyright (c) 2024 Attila Sagi
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

define([
    'jquery',
    'mage/url',
    'mage/storage',
    'domReady!'
], function ($, urlBuilder, storage) {
    'use strict';

    $.widget('mage.salesCountdown', {

        defaults: {
            selectorId: '#sales-countdown'
        },

        /**
         * Product purchase count
         * @private
         */
        _create: function () {
            const productId = this.options.productId || null;
            const storeCode = this.options.storeCode || null;
            const hasSpecialPriceToDate = parseInt(this.options.hasSpecialPriceToDate) || false;

            if (productId && storeCode && hasSpecialPriceToDate) {
                const serviceUrl = urlBuilder.build(
                    '/rest/' + storeCode + '/V1/specialPriceCalculate/' + productId
                );
                this._callApi(serviceUrl);
            }
        },

        /**
         * Call API
         *
         * @param {String} serviceUrl
         * @private
         * @return void
         */
        _callApi: function (serviceUrl) {
            storage.get(
                serviceUrl,
                true,
                'application/json',
                {}
            ).done(function (result) {
                this._displayMessage(result);
            }.bind(this)).fail(function (response) {
                // Enable for debug
                //console.log(response);
            });
        },

        /**
         * Display message
         *
         * @param {Object} result
         * @private
         * @return void
         */
        _displayMessage: function (result) {
            const divSelector = this.options.selectorId || this.defaults.selectorId;

            if (typeof $(divSelector) !== "undefined"
                && typeof result === "object"
                && result.hasOwnProperty('countdown_end_date')
                && result.countdown_end_date !== ''
            ) {
                $(divSelector).html(result.countdown_end_date);
                $(divSelector).removeClass('no-display');
            }
        }
    });

    return $.mage.salesCountdown;
});
