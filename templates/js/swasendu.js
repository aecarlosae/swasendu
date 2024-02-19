window.addEventListener('load', function () {
    jQuery("label[for='shipping-company'], label[for='billing-company']").text('RUT');
    jQuery("#shipping-state label, #billing-state label").text('Comuna');
});