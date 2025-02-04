jQuery(document).ready(function($) {
    $('#woocommerce_wc_shipping_swasendu_enable_work_order_generation').on('change', function() {
        if ($(this).prop('checked')) {
            $('.depend-on-work-order-generation').closest('tr').attr('style', 'visibility:visible !important');
        } else {
            $('.depend-on-work-order-generation').closest('tr').attr('style', 'visibility:collapse !important');
        }
    });

    if (!$('#woocommerce_wc_shipping_swasendu_enable_work_order_generation').prop('checked')) {
        $('.depend-on-work-order-generation').closest('tr').hide();
    }
});