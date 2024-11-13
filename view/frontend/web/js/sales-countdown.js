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
                this._displayCountdown(result.countdown_end_date, divSelector);
                $(divSelector).removeClass('no-display');
            }
        },

        /**
         * Display countdown
         *
         * @param {String} countdownEndDate
         * @param {String} divSelector
         * @private
         * @return void
         */
        _displayCountdown: function (countdownEndDate, divSelector) {
            const countDownDate = new Date(countdownEndDate).getTime();
            $(divSelector).html(this._calculateCountdown(countDownDate).countdownMessage);

            let interVal = setInterval(function () {
                let countdownMessage = this._calculateCountdown(countDownDate);
                $(divSelector).html(countdownMessage.countdownMessage);
                if (countdownMessage.distance < 0) {
                    clearInterval(interVal);
                    $(divSelector).removeClass('no-display');
                }
            }.bind(this), 1000);
        },

        /**
         * Calculate countdown
         *
         * @param {Number} countDownDate
         * @return {Object} {{countdownMessage: string, distance: number}}
         * @private
         */
        _calculateCountdown: function (countDownDate) {
            const now = new Date().getTime();
            const distance = countDownDate - now;
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            return {
                countdownMessage: `${days}d ${hours}h ${minutes}m ${seconds}s`,
                distance: distance
            };
        }
    });

    return $.mage.salesCountdown;
});
