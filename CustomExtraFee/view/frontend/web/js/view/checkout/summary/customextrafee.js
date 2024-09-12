/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'mage/url',
        'jquery'
    ],
    function (Component, quote, priceUtils, urlBuilder, $) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Kitchen_CustomExtraFee/checkout/summary/customextrafee'
            },
           
            getFee: function() {
                var fee = 0;
                var quoteId = quote.getQuoteId();

                $.ajax({
                    url: urlBuilder.build('fee/index/index'),
                    type: 'GET',
                    dataType: 'json',
                    data: {quote_id: quoteId},
                    async: false, 
                    success: function(response) {
                        if (response && response.custom_extra_fee) {
                            fee = parseFloat(response.custom_extra_fee);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching custom fee:', error);
                    }
                });
                return fee;
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