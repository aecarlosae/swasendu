window.addEventListener('load', function () {
    jQuery("label[for='shipping-company'], label[for='billing-company']").text('RUT');
    jQuery("#shipping-state label, #billing-state label").text('Comuna');
    setInterval(
        () => {
            jQuery.ajax({
                url: delivery_date.ajax_url,
                type: 'post',
                data: {
                    action: 'delivery_date',
                    nonce: delivery_date.nonce,
                },
                success: function(response) {
                    jQuery('.swasendu-delivery-date').text(response);
                }
            });
        },
        2000
    );
});