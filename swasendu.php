<?php
/**
 * Plugin Name: Sendu by Software Agíl
 * Plugin URI: https://softwareagil.com
 * Description: Sendu courier
 * Version: 1.0.0
 * Author: Carlos Espinoza
 * Author URI: https://github.com/aecarlosae
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
    
    Aecarlosae\Swasendu\Swasendu::run();
}
