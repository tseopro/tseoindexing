<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Uninstall
 *
 * Uninstalling TseoIndexing deletes tables and options.
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$options_to_delete = array(
    'tseoindexing_options_key',
    'tseoindexing_merchant_center_id',
    'tseoindexing_merchant_center_credentials',
    'tseoindexing_merchant_send_automatically',
    'tseoindexing_selected_products',
    'tseoindexing_openai_api_key'
);

// Check if the delete_option and delete_site_option functions exist
if (function_exists('delete_option') && function_exists('delete_site_option')) {
    foreach ($options_to_delete as $option) {
        delete_option($option);
        delete_site_option($option);
    }
} else {
    error_log('Error: The delete_option or delete_site_option functions do not exist.');
}

// Delete the custom table
global $wpdb;
$table_name = $wpdb->prefix . 'tseo_indexing_links';

if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
} else {
    error_log("Error: The table $table_name does not exist or has already been deleted.");
}