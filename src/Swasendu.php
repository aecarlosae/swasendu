<?php

namespace Aecarlosae\Swasendu;

use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Client as HttpClient;
use WC_Logger;
use WC_Order;

class Swasendu {
    public static function run()
    {
        self::init();
        self::shipping_init();
        self::shipping_methods();
        self::provinces();
        self::order_status_changed();
        self::admin_order_data_after_shipping_address();
        self::load_script();
        self::plugin_action_links_swasendu();
        self::add_menu();
        self::delivery_date_ajax();
    }

    public static function init()
    {
        add_action('init', function () {
            load_plugin_textdomain('swasendu', false, PLUGIN_BASE_DIRNAME . '/i18n/languages/'); 

            # Regions
            register_post_type(
                'swasendu_regions',
                [
                    'label' => __('Regions', 'swasendu'),
                    'description' => __('A custom post type for storing regions names and ids.', 'swasendu'),
                    'public' => false,
                    'show_ui' => false,
                    'show_in_menu' => false,
                    'query_var' => true,
                    'rewrite' => ['slug' => 'regions'],
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 5,
                    'supports' => ['ID', 'Name'],
                    'labels' => [
                        'name' => __('Regions', 'Post Type General Name', 'swasendu'),
                        'singular_name' => __('Region', 'Post Type Singular Name', 'swasendu'),
                        'menu_name' => __('Regions', 'Admin Menu Text', 'swasendu'),
                        'name_admin_bar' => __('Region', 'Add New on Toolbar', 'swasendu'),
                        'archives' => __('Region Archives', 'Post Type Archive Label', 'swasendu'),
                        'attributes' => __('Region Attributes', 'Post Type Attribute Label', 'swasendu'),
                        'parent_item_colon' => __('Parent Region:', 'Post Type Parent Item Label', 'swasendu'),
                        'all_items' => __('All Regions', 'All Posts', 'swasendu'),
                        'add_new_item' => __('Add New Region', 'Add New Post', 'swasendu'),
                        'edit_item' => __('Edit Region', 'Edit Post', 'swasendu'),
                        'new_item' => __('New Region', 'New Post', 'swasendu'),
                        'view_item' => __('View Region', 'View Post', 'swasendu'),
                        'search_items' => __('Search Regions', 'Search Posts', 'swasendu'),
                        'not_found' => __('No Regions found.', 'No Posts Found', 'swasendu'),
                        'not_found_in_trash' => __('No Regions found in Trash.', 'No Posts Found in Trash', 'swasendu'),
                        'featured_image' => __('Featured Image', 'Overrides the default label', 'swasendu'),
                        'set_featured_image' => __('Set featured image', 'Overrides the default label', 'swasendu'),
                        'remove_featured_image' => __('Remove featured image', 'Overrides the default label', 'swasendu'),
                        'use_featured_image' => __('Use featured image', 'Overrides the default label', 'swasendu'),
                        'menu_icon' => 'dashicons-editor-ul',
                    ],
                ]
            );
    
            register_post_meta(
                'swasendu_regions',
                'name',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_regions',
                'region_id',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            # Communes
            register_post_type(
                'swasendu_communes',
                [
                    'label' => __('Communes', 'swasendu'),
                    'description' => __('A custom post type for storing Communes names and ids.', 'swasendu'),
                    'public' => false,
                    'show_ui' => true,
                    'show_in_menu' => false,
                    'query_var' => true,
                    'rewrite' => ['slug' => 'swasendu-communes'],
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 5,
                    'supports' => ['custom-fields'],
                    'labels' => [
                        'name' => __('Communes', 'Post Type General Name', 'swasendu'),
                        'singular_name' => __('Commune', 'Post Type Singular Name', 'swasendu'),
                        'menu_name' => __('Communes', 'Admin Menu Text', 'swasendu'),
                        'name_admin_bar' => __('Commune', 'Add New on Toolbar', 'swasendu'),
                        'archives' => __('Commune Archives', 'Post Type Archive Label', 'swasendu'),
                        'attributes' => __('Commune Attributes', 'Post Type Attribute Label', 'swasendu'),
                        'parent_item_colon' => __('Parent Commune:', 'Post Type Parent Item Label', 'swasendu'),
                        'all_items' => __('All Communes', 'All Posts', 'swasendu'),
                        'add_new_item' => __('Add New Commune', 'Add New Post', 'swasendu'),
                        'edit_item' => __('Edit Commune', 'Edit Post', 'swasendu'),
                        'new_item' => __('New Commune', 'New Post', 'swasendu'),
                        'view_item' => __('View Commune', 'View Post', 'swasendu'),
                        'search_items' => __('Search Communes', 'Search Posts', 'swasendu'),
                        'not_found' => __('No Communes found.', 'No Posts Found', 'swasendu'),
                        'not_found_in_trash' => __('No Communes found in Trash.', 'No Posts Found in Trash', 'swasendu'),
                        'featured_image' => __('Featured Image', 'Overrides the default label', 'swasendu'),
                        'set_featured_image' => __('Set featured image', 'Overrides the default label', 'swasendu'),
                        'remove_featured_image' => __('Remove featured image', 'Overrides the default label', 'swasendu'),
                        'use_featured_image' => __('Use featured image', 'Overrides the default label', 'swasendu'),
                        'menu_icon' => 'dashicons-editor-ul',
                    ],
                ]
            );
    
            register_post_meta(
                'swasendu_communes',
                'name',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_communes',
                'commune_id',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_communes',
                'region_id',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_communes',
                'custom_commune_cost',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            # Couriers
            register_post_type(
                'swasendu_couriers',
                [
                    'label' => __('couriers', 'swasendu'),
                    'description' => __('A custom post type for storing couriers names and ids.', 'swasendu'),
                    'public' => false,
                    'show_ui' => false,
                    'show_in_menu' => false,
                    'query_var' => true,
                    'rewrite' => ['slug' => 'couriers'],
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 5,
                    'supports' => ['ID', 'Name'],
                    'labels' => [
                        'name' => __('couriers', 'Post Type General Name', 'swasendu'),
                        'singular_name' => __('courier', 'Post Type Singular Name', 'swasendu'),
                        'menu_name' => __('couriers', 'Admin Menu Text', 'swasendu'),
                        'name_admin_bar' => __('courier', 'Add New on Toolbar', 'swasendu'),
                        'archives' => __('courier Archives', 'Post Type Archive Label', 'swasendu'),
                        'attributes' => __('courier Attributes', 'Post Type Attribute Label', 'swasendu'),
                        'parent_item_colon' => __('Parent courier:', 'Post Type Parent Item Label', 'swasendu'),
                        'all_items' => __('All couriers', 'All Posts', 'swasendu'),
                        'add_new_item' => __('Add New courier', 'Add New Post', 'swasendu'),
                        'edit_item' => __('Edit courier', 'Edit Post', 'swasendu'),
                        'new_item' => __('New courier', 'New Post', 'swasendu'),
                        'view_item' => __('View courier', 'View Post', 'swasendu'),
                        'search_items' => __('Search couriers', 'Search Posts', 'swasendu'),
                        'not_found' => __('No couriers found.', 'No Posts Found', 'swasendu'),
                        'not_found_in_trash' => __('No couriers found in Trash.', 'No Posts Found in Trash', 'swasendu'),
                        'featured_image' => __('Featured Image', 'Overrides the default label', 'swasendu'),
                        'set_featured_image' => __('Set featured image', 'Overrides the default label', 'swasendu'),
                        'remove_featured_image' => __('Remove featured image', 'Overrides the default label', 'swasendu'),
                        'use_featured_image' => __('Use featured image', 'Overrides the default label', 'swasendu'),
                        'menu_icon' => 'dashicons-editor-ul',
                    ],
                ]
            );
    
            register_post_meta(
                'swasendu_couriers',
                'name',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_couriers',
                'courier_id',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            # Tracking states
            register_post_type(
                'swasendu_status',
                [
                    'label' => __('Tracking states', 'swasendu'),
                    'description' => __('A custom post type for storing Tracking states names and ids.', 'swasendu'),
                    'public' => false,
                    'show_ui' => false,
                    'show_in_menu' => false,
                    'query_var' => true,
                    'rewrite' => ['slug' => 'Tracking states'],
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 5,
                    'supports' => ['ID', 'Name'],
                    'labels' => [
                        'name' => __('Tracking states', 'Post Type General Name', 'swasendu'),
                        'singular_name' => __('Tracking state', 'Post Type Singular Name', 'swasendu'),
                        'menu_name' => __('Tracking states', 'Admin Menu Text', 'swasendu'),
                        'name_admin_bar' => __('Tracking state', 'Add New on Toolbar', 'swasendu'),
                        'archives' => __('Tracking state Archives', 'Post Type Archive Label', 'swasendu'),
                        'attributes' => __('Tracking state Attributes', 'Post Type Attribute Label', 'swasendu'),
                        'parent_item_colon' => __('Parent Tracking state:', 'Post Type Parent Item Label', 'swasendu'),
                        'all_items' => __('All Tracking states', 'All Posts', 'swasendu'),
                        'add_new_item' => __('Add New Tracking state', 'Add New Post', 'swasendu'),
                        'edit_item' => __('Edit Tracking state', 'Edit Post', 'swasendu'),
                        'new_item' => __('New Tracking state', 'New Post', 'swasendu'),
                        'view_item' => __('View Tracking state', 'View Post', 'swasendu'),
                        'search_items' => __('Search Tracking states', 'Search Posts', 'swasendu'),
                        'not_found' => __('No Tracking states found.', 'No Posts Found', 'swasendu'),
                        'not_found_in_trash' => __('No Tracking states found in Trash.', 'No Posts Found in Trash', 'swasendu'),
                        'featured_image' => __('Featured Image', 'Overrides the default label', 'swasendu'),
                        'set_featured_image' => __('Set featured image', 'Overrides the default label', 'swasendu'),
                        'remove_featured_image' => __('Remove featured image', 'Overrides the default label', 'swasendu'),
                        'use_featured_image' => __('Use featured image', 'Overrides the default label', 'swasendu'),
                        'menu_icon' => 'dashicons-editor-ul',
                    ],
                ]
            );
    
            register_post_meta(
                'swasendu_status',
                'name',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_status',
                'status_id',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_status',
                'description',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );

            # Work orders
            register_post_type(
                'swasendu_work_orders',
                [
                    'label' => __('Work orders', 'swasendu'),
                    'description' => __('A custom post type for storing Work orders names and ids.', 'swasendu'),
                    'public' => false,
                    'show_ui' => false,
                    'show_in_menu' => false,
                    'query_var' => true,
                    'rewrite' => ['slug' => 'Work orders'],
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 5,
                    'supports' => ['ID', 'Name'],
                    'labels' => [
                        'name' => __('Work orders', 'Post Type General Name', 'swasendu'),
                        'singular_name' => __('Work order', 'Post Type Singular Name', 'swasendu'),
                        'menu_name' => __('Work orders', 'Admin Menu Text', 'swasendu'),
                        'name_admin_bar' => __('Work order', 'Add New on Toolbar', 'swasendu'),
                        'archives' => __('Work order Archives', 'Post Type Archive Label', 'swasendu'),
                        'attributes' => __('Work order Attributes', 'Post Type Attribute Label', 'swasendu'),
                        'parent_item_colon' => __('Parent Work order:', 'Post Type Parent Item Label', 'swasendu'),
                        'all_items' => __('All Work orders', 'All Posts', 'swasendu'),
                        'add_new_item' => __('Add New Work order', 'Add New Post', 'swasendu'),
                        'edit_item' => __('Edit Work order', 'Edit Post', 'swasendu'),
                        'new_item' => __('New Work order', 'New Post', 'swasendu'),
                        'view_item' => __('View Work order', 'View Post', 'swasendu'),
                        'search_items' => __('Search Work orders', 'Search Posts', 'swasendu'),
                        'not_found' => __('No Work orders found.', 'No Posts Found', 'swasendu'),
                        'not_found_in_trash' => __('No Work orders found in Trash.', 'No Posts Found in Trash', 'swasendu'),
                        'featured_image' => __('Featured Image', 'Overrides the default label', 'swasendu'),
                        'set_featured_image' => __('Set featured image', 'Overrides the default label', 'swasendu'),
                        'remove_featured_image' => __('Remove featured image', 'Overrides the default label', 'swasendu'),
                        'use_featured_image' => __('Use featured image', 'Overrides the default label', 'swasendu'),
                        'menu_icon' => 'dashicons-editor-ul',
                    ],
                ]
            );
            register_post_meta(
                'swasendu_work_orders',
                'id',
                ['single' => true, 'type' => 'int', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'weight',
                ['single' => true, 'type' => 'float', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'height',
                ['single' => true, 'type' => 'float', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'large',
                ['single' => true, 'type' => 'float', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'deep',
                ['single' => true, 'type' => 'float', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'lost_coverage',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'name',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'rut',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'phone',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'email',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'order',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'category',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'price_products',
                ['single' => true, 'type' => 'int', 'show_in_admin_column' => true,]
            );
            register_post_meta(
                'swasendu_work_orders',
                'address',
                ['single' => true, 'type' => 'string', 'show_in_admin_column' => true,]
            );

            # User shipping address data
            register_post_type(
                'swasendu_useraddress',
                [
                    'label' => __('User shipping address data', 'swasendu'),
                    'description' => __('A custom post type for storing User shipping address data names and ids.', 'swasendu'),
                    'public' => false,
                    'show_ui' => false,
                    'show_in_menu' => false,
                    'query_var' => true,
                    'rewrite' => ['slug' => 'User shipping address data'],
                    'capability_type' => 'post',
                    'has_archive' => true,
                    'hierarchical' => false,
                    'menu_position' => 5,
                    'supports' => ['custom-fields'],
                    'labels' => [
                        'name' => __('User shipping address data', 'Post Type General Name', 'swasendu'),
                        'singular_name' => __('Region', 'Post Type Singular Name', 'swasendu'),
                        'menu_name' => __('User shipping address data', 'Admin Menu Text', 'swasendu'),
                        'name_admin_bar' => __('Region', 'Add New on Toolbar', 'swasendu'),
                        'archives' => __('Region Archives', 'Post Type Archive Label', 'swasendu'),
                        'attributes' => __('Region Attributes', 'Post Type Attribute Label', 'swasendu'),
                        'parent_item_colon' => __('Parent Region:', 'Post Type Parent Item Label', 'swasendu'),
                        'all_items' => __('All User shipping address data', 'All Posts', 'swasendu'),
                        'add_new_item' => __('Add New Region', 'Add New Post', 'swasendu'),
                        'edit_item' => __('Edit Region', 'Edit Post', 'swasendu'),
                        'new_item' => __('New Region', 'New Post', 'swasendu'),
                        'view_item' => __('View Region', 'View Post', 'swasendu'),
                        'search_items' => __('Search User shipping address data', 'Search Posts', 'swasendu'),
                        'not_found' => __('No User shipping address data found.', 'No Posts Found', 'swasendu'),
                        'not_found_in_trash' => __('No User shipping address data found in Trash.', 'No Posts Found in Trash', 'swasendu'),
                        'featured_image' => __('Featured Image', 'Overrides the default label', 'swasendu'),
                        'set_featured_image' => __('Set featured image', 'Overrides the default label', 'swasendu'),
                        'remove_featured_image' => __('Remove featured image', 'Overrides the default label', 'swasendu'),
                        'use_featured_image' => __('Use featured image', 'Overrides the default label', 'swasendu'),
                        'menu_icon' => 'dashicons-editor-ul',
                    ],
                ]
            );
    
            register_post_meta(
                'swasendu_useraddress',
                'user_id',
                [
                    'single' => true,
                    'type' => 'number',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_useraddress',
                'rut',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );
    
            register_post_meta(
                'swasendu_useraddress',
                'number',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );

            register_post_meta(
                'swasendu_useraddress',
                'email',
                [
                    'single' => true,
                    'type' => 'string',
                    'show_in_admin_column' => true,
                ]
            );
        });
    }

    public static function shipping_init()
    {
        add_action('woocommerce_shipping_init', 'Aecarlosae\Swasendu\swasendu');
    }

    public static function shipping_methods() {
        add_filter('woocommerce_shipping_methods', function ($methods) {
            $methods['swasendu'] = 'Aecarlosae\Swasendu\WC_Shipping_Swasendu';
    
            return $methods;
        });
	}

    public static function provinces()
    {
        add_filter('woocommerce_states', function($states) {
            $communes = get_posts([
                'post_type' => 'swasendu_communes',
                'numberposts' => -1,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);
    
            $communeList = [];
            // Remove CL default states
            unset($states['CL']);
    
            foreach ($communes as $commune) {
                $communeList['C-' . $commune->commune_id] = $commune->name;
            }
    
            $states['CL'] = $communeList;
    
            return $states;
        });
    }

    public static function order_status_changed()
    {
        add_action(
            'woocommerce_order_status_changed',
            function($orderId, $oldStatus, $newStatus) {
                $settings = get_option('woocommerce_wc_shipping_swasendu_settings');
                
                if (
                    isset($settings['disable_work_order_generation'])
                    && $settings['disable_work_order_generation'] == 'yes'
                ) {
                    return;
                }

                if ($newStatus !== str_replace('wc-', '', $settings['order_status'])) {
                    return;
                }

                $work_order = get_posts([
                    'post_type' => 'swasendu_work_orders',
                    'meta_key' => 'order',
                    'meta_value' => $orderId,
                    'numberposts' => 1,
                ])[0] ?? null;

                if ($work_order && $work_order->id) {
                    return;
                }

                $order = wc_get_order($orderId);
                $totalWeight = 0;
                $dimensions = [];
                $cubage = 0;
                $heightDimension = 0;
                $largeDimension = 0;
                $deepDimension = 0;
                $items = $order->get_items();
                $orderAddressData = self::get_address_data($order);
                
                if (count($items) > 1) {
                    foreach ($items as $item) {
                        $product = $item->get_product();
                        $totalWeight += $item->get_quantity() * $product->get_weight();
                        $cubage += (
                            floatval($product->get_height())
                            * floatval($product->get_length())
                            * floatval($product->get_width())
                            * intval($item['quantity'])
                        );
                        array_push(
                            $dimensions,
                            $product->get_height(),
                            $product->get_length(),
                            $product->get_width()
                        );
                    }

                    $heightDimension = max($dimensions);
                    $largeDimension = sqrt(2 / 3 * $cubage / $heightDimension);
                    $deepDimension = $cubage / $heightDimension / $largeDimension;
                } else {
                    $item = $items[array_key_first($items)];
                    $product = $item->get_product();
                    $totalWeight += $item->get_quantity() * (float) $product->get_weight();
                    $heightDimension = (float) $product->get_height();
                    $largeDimension = (float) $product->get_length();
                    $deepDimension = (float) $product->get_width();
                }
                
                try {
                    $client = new HttpClient([
                        'headers' => [
                            'X-User-Email' => $settings['user_email'],
                            'X-User-Token' => $settings['user_token'],
                            'Content-Type' => 'application/json',
                        ]
                    ]);

                    $communeId = (int) str_replace('C-', '', $orderAddressData['state']);
                    $commune = get_posts([
                        'post_type' => 'swasendu_communes',
                        'meta_key' => 'commune_id',
                        'meta_value' => $communeId,
                        'numberposts' => 1,
                    ])[0];
                    $swasenduUserAddress = get_posts([
                        'post_type' => 'swasendu_useraddress',
                        'meta_key' => 'user_id',
                        'meta_value' => $order->get_customer_id(),
                        'numberposts' => 1,
                    ])[0];
                                        
                    $requestData = [
                        'work_order' => [
                            'order' => sprintf('WC%s', (string) $orderId),
                            'category' => $settings['sell_category'] ?? __('Store sell', 'swasendu'),
                            'name' => sprintf(
                                '%s %s',
                                $orderAddressData['first_name'],
                                $orderAddressData['last_name']
                            ),
                            'email' => $orderAddressData['email'],
                            'phone' => empty($orderAddressData['phone']) ? '000000000' : $orderAddressData['phone'],
                            'weight' => floatval($totalWeight),
                            'height' => floatval($heightDimension),
                            'large' => floatval($largeDimension),
                            'deep' => floatval($deepDimension),
                            'lost_coverage' => $settings['lost_coverage'] == 'no' ? false : true,
                            'price_products' => (int) $order->get_total(),
                            'rut' => $swasenduUserAddress->rut,
                            'direction' => [
                                'region_id' => (int) $commune->region_id,
                                'comuna_id' => $communeId,
                                'street' => $orderAddressData['address_1'],
                                'numeration' => $swasenduUserAddress->number,
                                'complement' => (
                                    empty($orderAddressData['address_2'])
                                    ? $orderAddressData['address_1']
                                    : $orderAddressData['address_2']
                                ),
                            ]
                        ]
                    ];
                    
                    $response = $client->request(
                        'POST',
                        sprintf('%s/%s', $settings['api_url'], 'work_orders.json'),
                        [
                            'body' => json_encode(
                                $requestData,
                                JSON_PRESERVE_ZERO_FRACTION
                            ),
                        ]
                    );

                    $responseData = $response->getBody()->getContents();
                    
                    (new WC_Logger())->log(
                        'info',
                        sprintf(
                            'Work order for woo order %s, request data: %s, response data: %s',
                            $orderId,
                            json_encode($requestData),
                            $responseData
                        )
                    );

                    $workOrder = json_decode($responseData);
                    $workOrderSchema = [
                        'post_title' => sprintf('%s %s', __('Work order', 'swasemdi'), $workOrder->id),
                        'post_type' => 'swasendu_work_orders',
                        'post_status' => 'publish',
                        'meta_input' => [
                            'id' => $workOrder->id,
                            'weight' => $workOrder->weight,
                            'height' => $workOrder->height,
                            'large' => $workOrder->large,
                            'deep' => $workOrder->deep,
                            'lost_coverage' => $workOrder->lost_coverage,
                            'name' => $workOrder->name,
                            'rut' => $workOrder->rut,
                            'phone' => $workOrder->phone,
                            'email' => $workOrder->email,
                            'order' => $workOrder->order,
                            'category' => $workOrder->category,
                            'price_products' => $workOrder->price_products,
                            'address' => $workOrder->address,
                        ]
                    ];

                    if (!wp_insert_post($workOrderSchema)) {
                        (new WC_Logger())->log(
                            'info',
                            sprintf('The work order post %s could not be saved', $workOrder->id)
                        );
                    }
                } catch (GuzzleException $e) {
                    (new WC_Logger())->log('error', $e->getMessage());
                }
            },
            10,
            3
        );
    }

    public static function get_address_data($order)
    {
        $billing_email = $order->get_billing_email();

        $shipping_first_name = $order->get_shipping_first_name();
        $shipping_last_name = $order->get_shipping_last_name();
        $shipping_phone = $order->get_shipping_phone();
        $shipping_address_1 = $order->get_shipping_address_1();
        $shipping_address_2 = $order->get_shipping_address_2();
        $shipping_state = $order->get_shipping_state();

        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name = $order->get_billing_last_name();
        $billing_phone = $order->get_billing_phone();
        $billing_address_1 = $order->get_billing_address_1();
        $billing_address_2 = $order->get_billing_address_2();
        $billing_state = $order->get_billing_state();

        return [
            'email' => $billing_email,
            'first_name' => !empty($shipping_first_name) ? $shipping_first_name : $billing_first_name,
            'last_name' => !empty($shipping_last_name) ? $shipping_last_name : $billing_last_name,
            'phone' => !empty($shipping_phone) ? $shipping_phone : $billing_phone,
            'address_1' => !empty($shipping_address_1) ? $shipping_address_1 : $billing_address_1,
            'address_2' => !empty($shipping_address_2) ? $shipping_address_2 : $billing_address_2,
            'state' => !empty($shipping_state) ? $shipping_state : $billing_state,
        ];
    }

    public static function admin_order_data_after_shipping_address()
    {
        add_action( 'woocommerce_admin_order_data_after_shipping_address', function ($order) {
            $workOrder = get_posts([
                'post_type' => 'swasendu_work_orders',
                'meta_key' => 'order',
                'meta_value' => sprintf('WC%s', $order->get_id()),
                'numberposts' => 1,
            ])[0] ?? null;

            if (!is_object($workOrder) || !$workOrder->id) {
                return;
            }
            
            echo '
                <p>
                    <strong>' . __('Sendu Work order:', 'swasendu') . '</strong>
                    <br>
                    ' . $workOrder->id . '
                </p>
            ';
        }, 10, 1 );
    }

    public static function load_script()
    {
        add_action('wp_enqueue_scripts', function () {
            $settings = get_option('woocommerce_wc_shipping_swasendu_settings');

            if (is_admin()) {
                wp_enqueue_script('swasendu-js', plugins_url('../templates/js/swasendu.js', __FILE__ ));
            } else {
                wp_enqueue_script(
                    'swasendu-front',
                    plugins_url('../templates/js/swasendu-front.js?ts=' . time(), __FILE__),
                    ['jquery']
                );
        
                wp_localize_script(
                    'swasendu-front',
                    'delivery_date', 
                    [
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'nonce' => wp_create_nonce('swasendu-delivery-date'),
                        'show_delivery_date' => $settings['show_delivery_date']
                    ]
                );
            }
        });
    }

    public static function plugin_action_links_swasendu()
    {
        add_filter('plugin_action_links_' . PLUGIN_BASENAME, function($links) {
            $url = get_admin_url() . 'admin.php?page=wc-settings&tab=shipping&section=wc_shipping_swasendu';
            $links[] = '<a href="' . $url . '">' . __('Settings', 'swasendu') . '</a>';
            
            return $links;
        });
    }

    public static function add_menu()
    {
        add_action('admin_menu', function () { 
            add_menu_page( 
                __('Sendu courrier', 'swasendu'), 
                'Sendu', 
                'manage_options', 
                'admin.php?page=wc-settings&tab=shipping&section=wc_shipping_swasendu',
                '', 
                'dashicons-screenoptions',
                40
            );
            add_submenu_page(
                'admin.php?page=wc-settings&tab=shipping&section=wc_shipping_swasendu',
                __('Communes', 'swasendu'),
                __('Communes', 'swasendu'),
                'manage_options',
                'edit.php?post_type=swasendu_communes',
                ''
            );
            add_submenu_page(
                'admin.php?page=wc-settings&tab=shipping&section=wc_shipping_swasendu',
                __('Logs', 'swasendu'),
                __('Logs', 'swasendu'),
                'manage_options',
                'admin.php?page=wc-status&tab=logs',
                ''
            );
        });
    }

    public static function delivery_date_ajax()
    {
        // TODO: change "delivery_date" name, show be a general one. See occurrencies
        add_action('wp_ajax_delivery_date', [get_called_class(), 'delivery_date_callback']);
        add_action('wp_ajax_nopriv_delivery_date', [get_called_class(), 'delivery_date_callback']);
    }

    public static function delivery_date_callback() {
        check_ajax_referer('swasendu-delivery-date', 'nonce');
        
        $transitDays = get_transient(md5('swasendu-transit-days-' . get_current_user_id()));
        $settings = get_option('woocommerce_wc_shipping_swasendu_settings');
        $totalDays = $transitDays + 5 + $settings['preparation_days'];
        $response = [
            'delivery_date' => '',
            'address_rut' => '',
            'address_number' => '',
            'user_id' => wp_get_current_user(),
        ];

        $swasenduUserAddress = null;

        if (isset($_POST['address_email']) && !empty($_POST['address_email'])) {
            $swasenduUserAddress = get_posts([
                'post_type' => 'swasendu_useraddress',
                'meta_key' => 'email',
                'meta_value' => !empty($_POST['address_email']) ? $_POST['address_email'] : null,
                'numberposts' => 1,
            ])[0];
        }

        if ($swasenduUserAddress) {
            $response['address_rut'] = $swasenduUserAddress->rut;
            $response['address_number'] = $swasenduUserAddress->number;
        }

        if (
            isset($_POST['address_rut']) && isset($_POST['address_number'])
            && !empty($_POST['address_rut']) && !empty($_POST['address_number'])
        ) {
            
            if (!$swasenduUserAddress) {
                wp_insert_post([
                    'post_title' => $_POST['address_email'],
                    'post_type' => 'swasendu_useraddress',
                    'post_status' => 'publish',
                    'meta_input' => [
                        'user_id' => get_current_user_id(),
                        'rut' => $_POST['address_rut'],
                        'number' => $_POST['address_number'],
                        'email' => $_POST['address_email'],
                    ]
                ]);
            } else {
                wp_update_post([
                    'ID' => $swasenduUserAddress->ID,
                    'post_title' => $_POST['address_email'],
                    'post_type' => 'swasendu_useraddress',
                    'post_status' => 'publish',
                    'meta_input' => [
                        'user_id' => get_current_user_id(),
                        'rut' => $_POST['address_rut'],
                        'number' => $_POST['address_number'],
                        'email' => $_POST['address_email'],
                    ]
                ]);
            }
        }

        if ($transitDays > 0) {
            $transitDaysDate = (new \DateTime())
                ->add(\DateInterval::createFromDateString($totalDays . ' days'));
            $holidays = str_replace(' ', '', $settings['holidays']);
            $transitDaysDateWithoutHolidays = Swasendu::removeHolidays(
                $transitDaysDate->format('d-m-Y'),
                isset($settings['holidays']) || !empty($settings['holidays']) ? explode(',', $holidays) : []
            );
            $splittedFormatedDate = explode(' ', $transitDaysDateWithoutHolidays->format('l d F Y'));
            if (!defined('WPLANG') || constant('WPLANG') == 'es_ES') {
                $response['delivery_date'] = sprintf(
                    '%s, %s %s %s %s %s',
                    self::translateDateElement($splittedFormatedDate[0]),
                    $splittedFormatedDate[1],
                    __('of', 'swasendu'),
                    self::translateDateElement($splittedFormatedDate[2]),
                    __('of', 'swasendu'),
                    $splittedFormatedDate[3]
                );
            } else {
                $response['delivery_date'] = $transitDaysDateWithoutHolidays->format('l d F Y');
            }
        } else {
            $response['delivery_date'] = '--';
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);

        wp_die(); 
    }

    public static function removeHolidays($startDate, $holidays = [], $finalFormat = 'd-m-Y', $returnObject = true) {
        $format = 'Y-m-d';
        $startDateObject = \DateTime::createFromFormat($format, date($format, strtotime($startDate)));
        
        if (!$startDateObject) {
            return $startDate;
        }
    
        do {
            $weekDay = (int) $startDateObject->format('w');
    
            if (in_array($startDateObject->format($finalFormat), $holidays) || $weekDay == 0 || $weekDay == 6) {
                $startDateObject->modify('+1 day');
            } else {
                break;
            }
        } while (true);
    
        if ($returnObject) {
            return $startDateObject;
        }
    
        return $startDateObject->format($finalFormat);
    }

    public static function translateDateElement($string)
    {
        $translatedString = $string;

        switch (strtolower($string)) {
            case 'monday':
                $translatedString = __('Monday', 'swasendu');
                break;
            case 'tuesday':
                $translatedString = __('Tuesday', 'swasendu');
                break;
            case 'wednesday':
                $translatedString = __('Wednesday', 'swasendu');
                break;
            case 'thursday':
                $translatedString = __('Thursday', 'swasendu');
                break;
            case 'friday':
                $translatedString = __('Friday', 'swasendu');
                break;
            case 'saturday':
                $translatedString = __('Saturday', 'swasendu');
                break;
            case 'sunday':
                $translatedString = __('Sunday', 'swasendu');
                break;
            case 'january':
                $translatedString = __('January', 'swasendu');
                break;
            case 'february':
                $translatedString = __('February', 'swasendu');
                break;
            case 'march':
                $translatedString = __('March', 'swasendu');
                break;
            case 'april':
                $translatedString = __('April', 'swasendu');
                break;
            case 'may':
                $translatedString = __('May', 'swasendu');
                break;
            case 'june':
                $translatedString = __('June', 'swasendu');
                break;
            case 'july':
                $translatedString = __('July', 'swasendu');
                break;
            case 'august':
                $translatedString = __('August', 'swasendu');
                break;
            case 'september':
                $translatedString = __('September', 'swasendu');
                break;
            case 'october':
                $translatedString = __('October', 'swasendu');
                break;
            case 'november':
                $translatedString = __('November', 'swasendu');
                break;
            case 'december':
                $translatedString = __('December', 'swasendu');
                break;
        }

        return $translatedString;
    }
}