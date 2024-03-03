window.wp.blocks.registerBlockType('swasendu/delivery-date', {
    title: 'SendU Delivery Date',
    description: 'SendU Delivery Date for woocommerce',
    icon: 'calendar-alt',
    category: 'woocommerce',
    parent: ['woocommerce/checkout-order-summary-block'],
    attributes: {},
    edit() {
        return React.createElement("p", null, "Fecha estimada de entrega");
    }
});
