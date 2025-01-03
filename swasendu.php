<?php
/**
 * Plugin Name: Sendu
 * Plugin URI: https://www.softwareagil.com
 * Description: Sendu for woocommerce
 * Version: 1.1.1
 * Author: Software Agil Ltda
 * Author URI: https://www.softwareagil.com
 * Text Domain: swasendu
 * Domain Path: /i18n/languages/
 * Requires at least: 6.3
 * Requires PHP: 7.4
*/

defined( 'ABSPATH' ) || exit;

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    require 'vendor/autoload.php';
    define('PLUGIN_BASENAME', plugin_basename(__FILE__));
    define('PLUGIN_BASE_DIRNAME', dirname(PLUGIN_BASENAME));
    
    Aecarlosae\Swasendu\Swasendu::run(__FILE__);

    register_uninstall_hook(__FILE__, 'swasendu_uninstall');
    
    function swasendu_uninstall() {
        delete_option('woocommerce_wc_shipping_swasendu_settings');
        
        $posts = get_posts([
            'post_type' => 'swasendu_communes',
            'numberposts' => -1
        ]);
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
        unregister_post_type('swasendu_communes');
    }

    $settings = get_option('woocommerce_wc_shipping_swasendu_settings');

    if (isset($settings['show_delivery_date']) && $settings['show_delivery_date'] == 'yes') {
        add_action('init', function() {
            wp_register_script(
                'swasendu-delivery-date',
                plugins_url('templates/js/swasendu.js', __FILE__ ),
                ['wp-blocks']
            );
            register_block_type(
                'swasendu/delivery-date',
                [
                    'render_callback' => function () {
                        return '
                            <div>
                                <div style="border-top: 1px solid hsla(0,0%,7%,.11);padding: 16px 0;">
                                    <div style="padding-left: 16px; padding-right: 16px;display:flex;">
                                        <span style="flex-grow: 1;">' . __('Delivery date', 'swasendu') . '</span>
                                        <span
                                            style="font-weight: 700;white-space: nowrap;"
                                            class="swasendu-delivery-date">
                                            --
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ';
                    },
                    'editor_script' => 'swasendu-delivery-date'
                ]
            );
        });
    
        
    }
}
