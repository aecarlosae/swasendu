window.addEventListener('load', function () {
    jQuery("label[for='shipping-company'], label[for='billing-company']").text('RUT');
    jQuery("#shipping-state label, #billing-state label").text('Comuna');

    jQuery("#shipping-address_2, #billing-address_2").attr('aria-label', 'Numeración y complemento');
    jQuery("#shipping-address_2").prop('required', true);
    jQuery("#shipping-address_2 + label, #billing-address_2 + label").text('Numeración y complemento');
    jQuery(`
        #shipping-address_2 + label + .wc-block-components-validation-error,
        #billing-address_2 + label + .wc-block-components-validation-error
    `).hide();


    jQuery("#shipping-address_1, #billing-address_1").attr('aria-label', 'Nombre de la calle');
    jQuery("#shipping-address_1 + label, #billing-address_1 + label").text('Nombre de la calle');

    let swasendu_num_compl_error = false;
    jQuery('#shipping-address_2').on('change blur', function() {
        if (/^\s*$/.test(jQuery('#shipping-address_2'))) {
            swasendu_num_compl_error = true;
            jQuery('#shipping-address_2')
                .parent()
                .addClass('has-error');
            jQuery('#shipping-address_2').prop('required', true);
        }
    })
    
    setInterval(() => {
        jQuery('#shipping-address_2')
            .parent()
            .find('.wc-block-components-validation-error p')
            .text('Por favor ingrese la numeración y el complemento');
    }, 100);

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