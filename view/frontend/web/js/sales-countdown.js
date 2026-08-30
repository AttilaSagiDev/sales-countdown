/**
 * Copyright (c) 2026 Attila Sagi
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
        options: {
            selectorId: '#sales-countdown',
            replaceString: '%c',
            productId: null,
            storeCode: null,
            hasSpecialPriceToDate: false,
            isShowCountdown: false,
            isShowSeconds: false
        },

        /**
         * Initialize the widget
         * @private
         */
        _create: function () {
            const { productId, storeCode, hasSpecialPriceToDate } = this.options;

            if (productId && storeCode) {
                const endpoint = parseInt(hasSpecialPriceToDate)
                    ? `/rest/${storeCode}/V1/specialPriceCalculate/${productId}`
                    : `/rest/${storeCode}/V1/salesCountdownRule/${productId}`;

                this._callApi(urlBuilder.build(endpoint));
            }
        },

        /**
         * Fetch countdown data from API
         * @param {String} serviceUrl
         * @private
         */
        _callApi: function (serviceUrl) {
            storage.get(serviceUrl, true, 'application/json', {})
                .done(this._displayMessage.bind(this))
                .fail(function (response) {
                    // Enable for debug
                    // console.error(response);
                });
        },

        /**
         * Process and display the countdown message
         * @param {Object} result
         * @private
         */
        _displayMessage: function (result) {
            const $element = $(this.options.selectorId);

            if ($element.length &&
                result &&
                result.countdown_end_date &&
                result.countdown_message
            ) {
                this._startCountdown(
                    result.countdown_end_date,
                    result.countdown_message,
                    $element
                );
            }
        },

        /**
         * Start the countdown timer
         * @param {String} endDate
         * @param {String} message
         * @param {jQuery} $element
         * @private
         */
        _startCountdown: function (endDate, message, $element) {
            const isShowCountdown = !!parseInt(this.options.isShowCountdown);
            const endTime = new Date(endDate).getTime();

            if (this._isExpired(endTime)) {
                return;
            }

            if (isShowCountdown && message.includes(this.options.replaceString)) {
                const update = () => {
                    const timer = this._calculateCountdown(endTime);

                    if (timer.distance < 0) {
                        this._stopCountdown($element);
                        return;
                    }

                    $element.html(message.replace(this.options.replaceString, timer.text));
                };

                update();
                $element.removeClass('no-display');
                this.timerInterval = setInterval(update, 1000);
            } else {
                $element.html(message).removeClass('no-display');
            }
        },

        /**
         * Calculate time components and formatted string
         * @param {Number} endTime
         * @return {Object}
         * @private
         */
        _calculateCountdown: function (endTime) {
            const distance = endTime - Date.now();
            const isShowSeconds = !!parseInt(this.options.isShowSeconds);

            if (distance < 0) {
                return { text: '', distance };
            }

            const days = Math.floor(distance / 86400000);
            const hours = Math.floor((distance % 86400000) / 3600000);
            const minutes = Math.floor((distance % 3600000) / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);

            let text;
            const s = isShowSeconds ? ` ${seconds}s` : '';

            if (days > 0) {
                text = `${days}d ${hours}h ${minutes}m${s}`;
            } else if (hours > 0) {
                text = `${hours}h ${minutes}m${s}`;
            } else {
                text = `${minutes}m${s}`;
            }

            return { text, distance };
        },

        /**
         * Stop countdown and hide element
         * @param {jQuery} $element
         * @private
         */
        _stopCountdown: function ($element) {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            $element.addClass('no-display');
        },

        /**
         * Check if date is in the past
         * @param {Number} endTime
         * @return {Boolean}
         * @private
         */
        _isExpired: function (endTime) {
            return endTime <= Date.now();
        },

        /**
         * Cleanup on widget destroy
         * @private
         */
        _destroy: function () {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            this._super();
        }
    });

    return $.mage.salesCountdown;
});
