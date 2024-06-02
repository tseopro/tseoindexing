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
            ) $charset_collate;",
        'tseo_indexing_json' => "
            CREATE TABLE {$wpdb->prefix}tseo_indexing_json (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                json_content longtext NOT NULL,
                user_created varchar(255) NOT NULL,
                date_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;"
    ];

    foreach ($tables as $table_name => $sql) {
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


function tseoindexing_save_json($json_content, $user) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tseo_indexing_json';

    $sql = $wpdb->prepare("
        INSERT INTO $table_name (id, json_content, user_created, date_time)
        VALUES (1, %s, %s, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE json_content = %s, user_created = %s, date_time = CURRENT_TIMESTAMP
    ", $json_content, $user, $json_content, $user);

    $wpdb->query($sql);
}

function tseoindexing_get_json() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tseo_indexing_json';
    return $wpdb->get_row("SELECT * FROM $table_name WHERE id = 1");
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

    wp_enqueue_script('tseoindexing-loading', plugin_dir_url(dirname(__FILE__)) . 'assets/js/tseoindexing-loading.js', array(), TSEOPORTFOLIO_VERSION, true);
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