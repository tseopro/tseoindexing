<?php
/**
 * TSEO Indexing
 *
 * @package           TSEOIndexing
 * @author            TSEO team
 * @copyright         2023 TSEO PRO
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       TSEO Indexing
 * Plugin URI:        https://tseo.pro/indexing/
 * Description:       Plugin to notify Google Indexing API about new or updated posts, and to request removal of certain pages.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            TSEO team
 * Author URI:        https://tseo.pro/
 * Text Domain:       tseoindexing
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 
defined('ABSPATH') or die('No script kiddies please!');

if ( ! defined( 'TSEOINDEXING_VERSION' ) ) {
	define( 'TSEOINDEXING_VERSION', '1.0.0' );
}

function tseoindexing_load_textdomain() {
    load_plugin_textdomain('tseoindexing', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'tseoindexing_load_textdomain');

require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Indexing;
require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-class.php';

// Hook to notify Google about URLs to be removed upon plugin activation or update
register_activation_hook(__FILE__, ['TSEOIndexing', 'notify_google_about_removed_urls']);
register_deactivation_hook(__FILE__, ['TSEOIndexing', 'notify_google_about_removed_urls']);
