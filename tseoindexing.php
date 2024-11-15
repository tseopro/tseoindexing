<?php
/**
 * TSEO Indexing
 *
 * @package           TSEOIndexing
 * @developer         TSEO team
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       TSEO Indexing
 * Plugin URI:        https://tseo.pro/indexing/
 * Description:       This plugin notifies the Google Indexing API about new or updated posts and can request page removals. It integrates with Google Merchant Center to easily set up and submit product listings, featuring AI to generate optimized titles and descriptions. WooCommerce is required for Merchant Center features.
 * Version:           1.0.1
 * Requires at least: 5.5
 * Requires PHP:      8.1.0
 * Author:            TSEO team
 * Author URI:        https://tseo.pro/
 * Text Domain:       tseoindexing
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

/*
* Copyright (C) 2024 TSEO Developer, S.L.
*
* TSEO Indexing is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* TSEO Indexing is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with TSEO Indexing. If not, see <http://www.gnu.org/licenses/>.
*/

defined('ABSPATH') or die('No script kiddies please!');

if (!defined('TSEOINDEXING_VERSION')) {
    define('TSEOINDEXING_VERSION', '1.0.1');
}

// Autoload Google Client and OpenAI-PHP
require 'vendor/autoload.php';
use Google\Client;
use Google\Service\Indexing; // API Google Indexing
use Google\Service\ShoppingContent; // API Google Merchant Center
use OpenAI\Client as OpenAIClient; // API OpenAI-PHP

// Load Text Domain
function tseoindexing_load_textdomain() {
    load_plugin_textdomain('tseoindexing', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'tseoindexing_load_textdomain');

// Include Required Files
require_once plugin_dir_path(__FILE__) . 'includes/tseoindexing-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/tseoindexing-class.php';
require_once plugin_dir_path(__FILE__) . 'includes/tseoindexing-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/tseoindexing-tools.php';
require_once plugin_dir_path(__FILE__) . 'includes/tseoindexing-merchant.php';
require_once plugin_dir_path(__FILE__) . 'includes/tseoindexing-merchant-list.php';

// Register Activation Hook
register_activation_hook(__FILE__, 'tseoindexing_create_tables');

// Add Settings Link
function tseoindexing_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=tseoindexing-settings">' . esc_html__('Settings', 'tseoindexing') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tseoindexing_add_settings_link');

// Add Doc Link
function tseoindexing_add_doc_link($links) {
    $settings_link = '<a href="admin.php?page=tseoindexing">' . esc_html__('Doc', 'tseoindexing') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tseoindexing_add_doc_link');