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

/**
 * Check if the constant 'TSEOINDEXING_VERSION' is not defined and define it with the value '1.0.0'.
 */
if (!defined('TSEOINDEXING_VERSION')) {
    define('TSEOINDEXING_VERSION', '1.0.0');
}

/**
 * Loads the text domain for the TSEO Indexing plugin.
 *
 * This function is hooked to the 'plugins_loaded' action, which is fired after all plugins have been loaded.
 * It loads the translation files for the plugin's text domain, allowing for localization of plugin strings.
 *
 * @since 1.0.0
 */
function tseoindexing_load_textdomain() {
    load_plugin_textdomain('tseoindexing', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'tseoindexing_load_textdomain');


/**
 * This line of code includes the 'tseoindexing-settings.php' file from the 'inc' directory.
 * The 'plugin_dir_path' function is used to get the absolute path of the current file,
 * and then the 'inc/tseoindexing-settings.php' file is included using the 'require_once' statement.
 * 
 * @see plugin_dir_path()
 * @link https://developer.wordpress.org/reference/functions/plugin_dir_path/
 */
require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-settings.php';

/**
 * TEO PRO class TSEO_Indexing
 * 
 * This file is the main plugin file for TSEO Indexing.
 * It requires the necessary dependencies and initializes the plugin.
 * 
 * @package TSEOIndexing
 * @version 1.0.0
 */

require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Indexing;

require_once plugin_dir_path(__FILE__) . 'inc/tseoindexing-class.php';

/**
 * Adds a settings link to the plugin action links on the WordPress plugins page.
 *
 * This function is used to add a settings link to the plugin action links displayed on the WordPress plugins page.
 * The settings link will redirect the user to the plugin settings page.
 *
 * @param array $links An array of existing plugin action links.
 * @return array The modified array of plugin action links.
 */
function tseoindexing_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=tseoindexing-settings">' . esc_html__('Settings', 'tseoindexing') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tseoindexing_add_settings_link');
