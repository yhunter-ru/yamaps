<?php
/**
 * Plugin Name: YaMaps for Wordpress
 * Description: Yandex Map integration
 * Plugin URI:  www.yhunter.ru/portfolio/dev/yamaps/
 * Author URI:  www.yhunter.ru
 * Author:      Yuri Baranov
 * Version:     0.6.30
 *
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yamaps
 * Domain Path: /languages/
 *
 */

 $lang_array = array();

// Load components
require_once plugin_dir_path(__FILE__) . 'includes/init.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/api.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// Load text domain for localization
function yamaps_plugin_load_plugin_textdomain() {
    load_plugin_textdomain('yamaps', FALSE, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'yamaps_plugin_load_plugin_textdomain');

// Load options
require_once plugin_dir_path(__FILE__) . 'options.php'; 
