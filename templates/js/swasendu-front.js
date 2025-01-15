window.addEventListener('load', function () {
    function swasenduShippingFieldListener(fieldName, errorMessage) {
        jQuery(document).on('click focus blur change', '#shipping-' + fieldName, function(event) {
            if (/^\s*$/.test(jQuery(this).val()) && event.type == 'focusin') {
                jQuery(this).parent().addClass('is-active');
            }
    
            if (/^\s*$/.test(jQuery(this).val()) && event.type == 'focusout') {
                jQuery(this).parent().removeClass('is-active');
            }
    
            if (/^\s*$/.test(jQuery(this).val())) {
                jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', true);
                if (jQuery('.swasendu-error-message').length == 0){
                    jQuery("#shipping-" + fieldName).parent().append(`
                        <div class="wc-block-components-validation-error swasendu-error-message" role="alert">
                            <p>` + errorMessage + `</p>
                        </div>
                    `);
                    jQuery("#shipping-" + fieldName).parent().addClass('has-error');
                }
            } else {
                jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', false);
                jQuery("#shipping-" + fieldName).parent().find('.swasendu-error-message').remove();
                jQuery("#shipping-" + fieldName).parent().removeClass('has-error');
            }
        });
    }

    jQuery("#shipping-state label, #billing-state label").text('Comuna');
    jQuery("#shipping-address_2, #billing-address_2").attr('aria-label', 'Complemento');
    jQuery("#shipping-address_2").prop('required', true);
    jQuery("#shipping-address_2 + label, #billing-address_2 + label").text('Complemento');
    jQuery(`
        #shipping-address_2 + label + .wc-block-components-validation-error,
        #billing-address_2 + label + .wc-block-components-validation-error
    `).hide();

    if (jQuery("[id*='_company_field']").length == 1) {
        jQuery(`
            <p class="form-row rut-field validate-required form-row-wide" id="shipping-swasendu_address_rut_field">
                <label for="shipping-swasendu_address_rut">RUT
                    <abbr class="required" title="obligatorio">*</abbr>
                </label>
                <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text" name="shipping-swasendu_address_rut" id="shipping-swasendu_address_rut" placeholder="RUT">
                </span>
            </p>
        `).insertBefore(
            jQuery("[id*='_company_field']").length > 0 ?
            jQuery("[id*='_company_field']") :
            (
                jQuery("[id*='_country_field']").length > 0 ?
                jQuery("[id*='_country_field']") :
                jQuery("[id*='_address_1_field']")
            )
        );

        jQuery(`
            <p class="form-row number-field validate-required form-row-wide" id="shipping-swasendu_address_number_field">
                <label for="shipping-swasendu_address_number">Numeración
                    <abbr class="required" title="obligatorio">*</abbr>
                </label>
                <span class="woocommerce-input-wrapper">
                    <input type="text" class="input-text" name="shipping-swasendu_address_number" id="shipping-swasendu_address_number" placeholder="Numeración">
                </span>
            </p>
        `).insertBefore(
            jQuery("[id*='_company_field']").length > 0 ?
            jQuery("[id*='_company_field']") :
            (
                jQuery("[id*='_country_field']").length > 0 ?
                jQuery("[id*='_country_field']") :
                jQuery("[id*='_address_1_field']")
            )
        );
    }

    jQuery(`
        <div class="wc-block-components-address-swasendu_address_rut wc-block-components-text-input">
            <input type="text" class="input-text" id="shipping-swasendu_address_rut" aria-label="RUT" aria-invalid="false">
            <label for="shipping-swasendu_address_rut">RUT</label>
        </div>
    `).insertBefore(jQuery("#shipping-address_1").parent());
    swasenduShippingFieldListener('swasendu_address_rut', 'Por favor, ingrese el RUT.');

    jQuery(`
        <div class="wc-block-components-address-swasendu_address_number wc-block-components-text-input">
            <input type="text" id="shipping-swasendu_address_number" aria-label="Numeración" aria-invalid="false">
            <label for="shipping-swasendu_address_number">Numeración</label>
        </div>
    `).insertAfter(jQuery("#shipping-address_2").parent());
    swasenduShippingFieldListener('swasendu_address_number', 'Por favor, ingrese el número.');

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
            .text('Por favor ingrese el complemento');
    }, 100);

    setInterval(
        () => {
            let address_rut = null;
            let address_number = null;

            if (
                /^\s*$/.test(jQuery('#shipping-swasendu_address_rut').val())
                || /^\s*$/.test(jQuery('#shipping-swasendu_address_number').val())
            ) {
                jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', true);

                // if (/^\s*$/.test(jQuery("#shipping-swasendu_address_rut").val())) {
                //     jQuery("#shipping-swasendu_address_rut").focus();
                // } else if (/^\s*$/.test(jQuery("#shipping-swasendu_address_number").val())) {
                //     jQuery("#shipping-swasendu_address_number").focus();
                // }
            } else {
                address_rut = jQuery(document).find('#shipping-swasendu_address_rut').val();
                address_number = jQuery(document).find('#shipping-swasendu_address_number').val();
                jQuery('.wc-block-components-checkout-place-order-button').prop('disabled', false);
            }

            jQuery.ajax({
                url: delivery_date.ajax_url,
                type: 'post',
                data: {
                    action: 'delivery_date',
                    address_email: jQuery('#email').length > 0 ? jQuery('#email').val() : jQuery('#billing_email').val(),
                    address_rut: address_rut,
                    address_number: address_number,
                    nonce: delivery_date.nonce,
                },
                success: function(response) {
                    if (response.address_rut != '' && jQuery('#shipping-swasendu_address_rut').val() == '') {
                        jQuery('#shipping-swasendu_address_rut').val(response.address_rut);
                        jQuery("#shipping-swasendu_address_rut").parent().find('.swasendu-error-message').remove();
                        jQuery("#shipping-swasendu_address_rut").parent().removeClass('has-error');
                        jQuery("#shipping-swasendu_address_rut").parent().addClass('is-active');
                    }

                    if (response.address_number != '' && jQuery('#shipping-swasendu_address_number').val() == '') {
                        jQuery('#shipping-swasendu_address_number').val(response.address_number);
                        jQuery("#shipping-swasendu_address_number").parent().addClass('is-active');
                        jQuery("#shipping-swasendu_address_number").parent().find('.swasendu-error-message').remove();
                        jQuery("#shipping-swasendu_address_number").parent().removeClass('has-error');
                    }

                    if (delivery_date.show_delivery_date == 'yes') {
                        jQuery('.swasendu-delivery-date').text(response.delivery_date);
                    }
                }
            });
        },
        2000
    );
});