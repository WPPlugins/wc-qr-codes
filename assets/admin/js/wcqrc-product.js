/* global wcqrc_product */

jQuery(document).ready(function ($) {
    $('.wcqrc-refresh').on('click', function () {
        var self = $(this);
        var product_id = $(this).data('product_id');
        var data = {
            action : 'regenerate_qr_code',
            product_id: product_id
        };
        self.parent().css('opacity','0.5');
        $.post(wcqrc_product.ajax_url, data, function (response){
            if(response){
                $('.product-qr-code-img').attr('src',response);
                self.parent().css('opacity','1');
            }
        });
    });
});
