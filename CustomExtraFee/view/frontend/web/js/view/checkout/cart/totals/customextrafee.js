define(
    [
        'Kitchen_CustomExtraFee/js/view/checkout/summary/customextrafee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            /**
             * @override
             * use to define amount is display setting
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);