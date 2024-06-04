<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Create Table
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_create_tables() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    
    $tables = [
        'tseo_indexing_links' => "
            CREATE TABLE {$wpdb->prefix}tseo_indexing_links (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                url varchar(255) NOT NULL,
                status varchar(20) NOT NULL,
                date_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;"
    ];

    foreach ($tables as $sql) {
        if (is_multisite()) {
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                dbDelta($sql);
                restore_current_blog();
            }
        } else {
            dbDelta($sql);
        }
    }
}
//register_activation_hook(__FILE__, 'tseoindexing_create_tables');


/**
 * Save API Key
 *
 * @param string $api_key JSON content to save.
 * @param array $post_types Post types to save.
 */
function tseoindexing_save_api_key($api_key, $post_types) {
    $options = [
        'json_key' => $api_key,
        'post_types' => $post_types,
        'bing_post_types' => [],
        'indexnow_api_key' => ''
    ];
    update_option('tseo_indexing_options_key', maybe_serialize($options));
}

/**
 * Get API Key
 *
 * @return array Options array from the database.
 */
function tseoindexing_get_api_key() {
    $options = get_option('tseo_indexing_options_key');
    return maybe_unserialize($options);
}

/**
 * TSEO PRO Assets
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_admin_styles($hook) {
    $valid_hooks = array(
        'toplevel_page_tseoindexing',
        'tseo-indexing_page_tseoindexing-settings',
        'tseo-indexing_page_tseoindexing-links'
    );

    if (!in_array($hook, $valid_hooks)) {
        return;
    }

    wp_enqueue_style('tseoindexing-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/css/tseoindexing.min.css', array(), TSEOINDEXING_VERSION, 'all');

    wp_enqueue_script('tseoindexing-loading', plugin_dir_url(dirname(__FILE__)) . 'assets/js/tseoindexing-loading.js', array(), TSEOINDEXING_VERSION, true);
}
add_action('admin_enqueue_scripts', 'tseoindexing_admin_styles');

/**
 * TSEO PRO Loading Overlay
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_loading_overlay() {
    ?>
        <div id="tseoindexing-loading-overlay">
            <div class="centered-content">
                <div class="tseoindexing-loading-spinner"></div>
                <span><?php echo esc_html_e('Saving TSEO Indexing', 'tseoindexing'); ?></span>
            </div>
        </div>
    <?php
}

/**
 * TSEO PRO Information Danger
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_danger_info() {
    ?>
        <div class="danger">
            <?php esc_html_e('Warning: This is a development version of the plugin. Do not use it in a production environment.', 'tseoindexing'); ?>
        </div>
    <?php
}

/**
 * TSEO PRO Information Success
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_success_info() {
    ?>
        <div class="success">
            <?php esc_html_e('Take advantage of the power of TSEO PRO and do your own SEO like a true professional with multiple optimized tools without depending on external plugins.', 'tseoindexing'); ?>
        </div>
    <?php
}