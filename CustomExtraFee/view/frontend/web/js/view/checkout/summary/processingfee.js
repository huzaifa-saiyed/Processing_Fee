define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'knockout',
        'Magento_Checkout/js/model/totals',
    ],
    function (Component, quote, priceUtils, ko, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Kitchen_CustomExtraFee/checkout/cart/totals/processingfee',
                isVisible: ko.observable(false) 
            },
            totals: quote.getTotals(),

            initialize: function () {
                this._super();

                this.toggleProcessingFee();

                quote.paymentMethod.subscribe(this.toggleProcessingFee.bind(this));

                return this;
            },

            toggleProcessingFee: function () {
                var percentageFeeEnable = window.checkoutConfig.fee_percentage_enable;
                if (quote.paymentMethod() && quote.paymentMethod().method === 'checkmo' && percentageFeeEnable == 1) {
                    this.isVisible(true);
                } else {
                    this.isVisible(false);
                }
            },

            getFormattedTitle: function () {
                var percentageFee = window.checkoutConfig.fee_percentage;
                return 'Processing Fee (' + percentageFee + '%)';
            },

            getFee: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('processing_fee').value;
                }
                return price;
            },

            getValue: function() {
                return this.getFormattedPrice(this.getFee());
            },

            getBaseValue: function() {
                var baseFee = this.getFee();
                return priceUtils.formatPrice(baseFee, quote.getBasePriceFormat());
            }
        });
    }
);
