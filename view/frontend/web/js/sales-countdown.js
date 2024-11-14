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
            selectorId: '#sales-countdown',
            replaceString: '%c'
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
                && result.hasOwnProperty('countdown_message')
                && result.countdown_end_date !== ''
                && result.countdown_message !== ''
            ) {
                this._displayCountdown(
                    result.countdown_end_date,
                    result.countdown_message,
                    divSelector
                );
                $(divSelector).removeClass('no-display');
            }
        },

        /**
         * Display countdown
         *
         * @param {String} countdownEndDate
         * @param {String} countdownMessage
         * @param {String} divSelector
         * @private
         * @return void
         */
        _displayCountdown: function (countdownEndDate, countdownMessage, divSelector) {
            const isShowCountdown = parseInt(this.options.isShowCountdown) || false;
            if (isShowCountdown) {
                const countDownDate = new Date(countdownEndDate).getTime();
                if (countdownMessage.includes(this.defaults.replaceString)) {
                    $(divSelector).html(
                        countdownMessage.replace(
                            this.defaults.replaceString,
                            this._calculateCountdown(countDownDate).countdownMessage
                        )
                    );
                } else {
                    $(divSelector).html(this._calculateCountdown(countDownDate).countdownMessage);
                }

                let interVal = setInterval(function () {
                    let calculatedCountdownMessage = this._calculateCountdown(countDownDate);
                    if (countdownMessage.includes(this.defaults.replaceString)) {
                        $(divSelector).html(
                            countdownMessage.replace(
                                this.defaults.replaceString,
                                calculatedCountdownMessage.countdownMessage
                            )
                        );
                    } else {
                        $(divSelector).html(calculatedCountdownMessage.countdownMessage);
                    }
                    if (calculatedCountdownMessage.distance < 0) {
                        clearInterval(interVal);
                        $(divSelector).addClass('no-display');
                    }
                }.bind(this), 1000);
            } else {
                //TODO: do not show message if out of date
                $(divSelector).html(countdownMessage);
            }
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
                countdownMessage: `${ days }d ${ hours }h ${ minutes }m ${ seconds }s`,
                distance: distance
            };
        }
    });

    return $.mage.salesCountdown;
});
