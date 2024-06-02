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

/*
TSEO Indexing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

TSEO Indexing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with TSEO Indexing. If not, see https://tseo.pro/.
*/

defined('ABSPATH') or die('No script kiddies please!');

if (!defined('TSEOINDEXING_VERSION')) {
    define('TSEOINDEXING_VERSION', '1.0.0');
}

/**
 * TEO PRO Language
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_load_textdomain() {
    load_plugin_textdomain('tseoindexing', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'tseoindexing_load_textdomain');


/**
 * TEO PRO Settings
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-settings.php';
register_activation_hook(__FILE__, 'tseoindexing_create_tables');

/**
 * TEO PRO class TSEO_Indexing
 * 
 * @package TSEOIndexing
 * @version 1.0.0
 */
require 'vendor/autoload.php';
use Google\Client;
use Google\Service\Indexing;
require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-class.php';

/**
 * TEO PRO Dashboard
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-dashboard.php';

/**
 * TEO PRO Link settings list plugin
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=tseoindexing-settings">' . esc_html__('Settings', 'tseoindexing') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tseoindexing_add_settings_link');
