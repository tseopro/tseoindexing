<?php
/**
 * TSEO Indexing
 *
 * @package           TSEOIndexing
 * @developer         TSEO team
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       TSEO Indexing
 * Plugin URI:        https://tseo.pro/indexing/
 * Description:       Plugin to notify Google Indexing API about new or updated posts, and to request removal of certain pages.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      8.0
 * Author:            TSEO team
 * Author URI:        https://tseo.pro/
 * Text Domain:       tseoindexing
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') or die('No script kiddies please!');

if (!defined('TSEOINDEXING_VERSION')) {
    define('TSEOINDEXING_VERSION', '1.0.0');
}

function tseoindexing_load_textdomain() {
    load_plugin_textdomain('tseoindexing', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'tseoindexing_load_textdomain');

require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Indexing;

require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-class.php';

if (!class_exists('TSEOIndexing_Main')) {
    $tseoindexing_main = new TSEOIndexing_Main();
}

/**
 * TSEO PRO admin style
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_admin_styles($hook) {
    if ('tseoindexing_page_tseoindexing-settings' != $hook) {
        return;
    }
    wp_enqueue_style('tseoindexing-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/css/tseoindexing.min.css', array(), TSEOINDEXING_VERSION, 'all');
}
add_action('admin_enqueue_scripts', 'tseoindexing_admin_styles');

function tseoindexing_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=tseoindexing-settings">' . esc_html__('Settings', 'tseoindexing') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tseoindexing_add_settings_link');
