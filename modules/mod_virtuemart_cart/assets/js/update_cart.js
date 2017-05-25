;
(function (jQuery) {

    jQuery.fn.updateVirtueMartCartModule = function (arg) {

        var options = jQuery.extend({}, jQuery.fn.updateVirtueMartCartModule.defaults, arg);

        return this.each(function () {

            // Local Variables
            var $this = jQuery(this);

            jQuery.ajaxSetup({ cache: false })
            jQuery.getJSON(window.vmSiteurl + "index.php?option=com_virtuemart&nosef=1&view=cart&task=viewJS&format=json" + window.vmLang,
                function (datas, textStatus) {
                    if (datas.totalProduct > 0) {
                        $this.find(".vm_cart_products").html("");
                        jQuery.each(datas.products, function (key, val) {
                            //jQuery("#hiddencontainer .vmcontainer").clone().appendTo(".vmcontainer .vm_cart_products");
                            jQuery("#hiddencontainer .vmcontainer").clone().appendTo(".vmCartModule .vm_cart_products");
                            jQuery.each(val, function (key, val) {
                                if (jQuery("#hiddencontainer .vmcontainer ." + key)) $this.find(".vm_cart_products ." + key + ":last").html(val);
                            });
                        });
                    }
                    $this.find(".show_cart").html(datas.cart_show);
                    $this.find(".total_products").html(datas.totalProductTxt);
                    $this.find(".total").html(datas.billTotal);
                }
            );
        });
    };

    // Definition Of Defaults
    jQuery.fn.updateVirtueMartCartModule.defaults = {
        name1: 'value1'
    };

})(jQuery);