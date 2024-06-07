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
                type varchar(20) NOT NULL,
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
 * Guarda registros y obtiene datos de Google API
 * de la funcion tseoindexing_display_links_table()
 *
 * @param string $api_key JSON content to save.
 * @param array $post_types Post types to save.
 */
add_action('wp_ajax_update_tseo_url', 'update_tseo_url');
function update_tseo_url() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tseo_indexing_links';

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized user');
    }

    check_ajax_referer('update_tseo_url_nonce', '_ajax_nonce');

    $url = sanitize_text_field($_POST['url']);
    $action = sanitize_text_field($_POST['action_type']);
    $type = ($action === 'update') ? 'URL_UPDATED' : 'URL_DELETED';

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE url = %s", $url));

    if ($exists) {
        $wpdb->update(
            $table_name,
            ['type' => $type, 'date_time' => current_time('mysql')],
            ['url' => $url]
        );
    } else {
        $wpdb->insert(
            $table_name,
            ['url' => $url, 'status' => 'NULL', 'type' => $type, 'date_time' => current_time('mysql')],
            ['%s', '%s', '%s', '%s']
        );
    }

    wp_send_json_success(['type' => $type]);
}
 
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
        'tseo-indexing_page_tseoindexing-links',
        'tseo-indexing_page_tseoindexing-console'
    );

    if (!in_array($hook, $valid_hooks)) {
        return;
    }

    wp_enqueue_style('tseoindexing-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/css/tseoindexing.min.css', array(), TSEOINDEXING_VERSION, 'all');

    wp_enqueue_script('tseoindexing-loading', plugin_dir_url(dirname(__FILE__)) . 'assets/js/tseoindexing-loading.js', array(), TSEOINDEXING_VERSION, true);

    wp_enqueue_script('tseoindexing', plugin_dir_url(dirname(__FILE__)) . 'assets/js/tseoindexing.js', array(), TSEOINDEXING_VERSION, true);
}
add_action('admin_enqueue_scripts', 'tseoindexing_admin_styles');

/**
 * TSEO PRO Assets Ajax
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'tseoindexing_enqueue_scripts');

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
                <span><?php echo esc_html_e('Running service...', 'tseoindexing'); ?></span>
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

/**
 * TSEO PRO TSEO Indexing URL´s
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_table_link_info() {
    ?>
    <ul class="table_link_info">
        <li><span class="status-null">NULL:</span> <?php esc_html_e('By default, all published URLs are retrieved and marked as NULL, which means they are not selected to be sent to Google Console.', 'tseoindexing'); ?></li>
        <li><span class="status-updated">URL_UPDATED:</span> <?php esc_html_e('They are the URLs marked to be published or updated in Google Console.', 'tseoindexing'); ?></li>
        <li><span class="status-deleted">URL_DELETED:</span> <?php esc_html_e('They are the URLs marked to be removed from Google Console.', 'tseoindexing'); ?></li>
        <li class="success"><?php esc_html_e('Important: Once the table is configured with the URLs to be published/updated or removed, go to the Console tab to send the batch requests. These will be processed 20 at a time until the quota limit is reached. Increase the quota in Google Cloud API if necessary.', 'tseoindexing'); ?></li>
    </ul>
    <?php
}

/**
 * TSEO PRO TSEO Console
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_console() {
    ?>
    <form id="tseoindexing-console-form" method="post" action="">
        <?php wp_nonce_field('tseoindexing_console', 'tseoindexing_console_nonce'); ?>
        <?php esc_html_e('URLs (one per line, up to 100 for Google and 10,000 for IndexNow):', 'tseoindexing'); ?>
        
        <textarea name="tseo_urls" id="tseo_urls" rows="10" cols="50" placeholder="https://..."></textarea>
        
        <div class="buttons-group">
            <button type="button" id="load-updated-urls" class="success">
                <?php esc_html_e('Updated URLs', 'tseoindexing'); ?>
            </button>
            <button type="button" id="load-deleted-urls" class="danger">
                <?php esc_html_e('Deleted URLs', 'tseoindexing'); ?>
            </button>
            <button type="button" id="clear-urls" class="primary">
                <?php esc_html_e('Clear URLs', 'tseoindexing'); ?>
            </button>
        </div>

        <div style="padding: 1em 0em" class="url-send">
            <strong><?php esc_html_e('Action:', 'tseoindexing'); ?></strong>
            <p>
                <input type="radio" name="api_action" value="URL_UPDATED" id="send_update" checked="checked">
                <label for="send_update"><?php esc_html_e('Publish/update URL', 'tseoindexing'); ?></label>
            </p>
            <p>
                <input type="radio" name="api_action" value="URL_DELETED" id="send_remove">
                <label for="send_remove"><?php esc_html_e('Remove URL', 'tseoindexing'); ?></label>
            </p>
            <p>
                <input type="radio" name="api_action" value="getstatus" id="send_status">
                <label for="send_status"><?php esc_html_e('Get URL status', 'tseoindexing'); ?></label>
            </p>
        </div>

        <div class="url-send">
            <p class="text-success">
                <?php esc_html_e('URLs to be sent to Google Console:', 'tseoindexing'); ?> <span id="updated-urls-count">0</span>
            </p>
            <p class="text-danger">
                <?php esc_html_e('URLs to be removed from Google Console:', 'tseoindexing'); ?> <span id="deleted-urls-count">0</span>
            </p>
        </div>
        <div class="button-panel">
            <?php submit_button(__('Send to API', 'tseoindexing')); ?>
        </div>
    </form>
    <?php
}



/**
 * TSEO PRO TSEO Console response
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_console_response() {
    ?>
    <div class="response">
        <p><?php esc_html_e('Response', 'tseoindexing'); ?></p>
        <code>
            <strong class="response-action"></strong>
            <span class="response-url"></span>
        </code>
        <h2 class="response-title"></h2>
        <p class="response-message"></p>
    </div>
    <div class="show-raw-response">
        <p><?php esc_html_e('Show raw response:', 'tseoindexing'); ?></p>
        <textarea id="raw-response"></textarea>
    </div>
    <?php
}

/**
 * TSEO PRO TSEO Remaining Quota - Console
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_remaining_quota() {
    $quota_info = [
        'PublishRequestsPerDayPerProject' => '0 / 200', // Default values, you need to process $results to get actual values
        'MetadataRequestsPerMinutePerProject' => '0 / 180',
        'RequestsPerMinutePerProject' => '0 / 600'
    ];
    ?>
    <a href="<?php echo esc_url('https://developers.google.com/search/apis/indexing-api/v3/quota-pricing'); ?>" target="_blank">
        <?php esc_html_e('Google API Remaining Quota:', 'tseoindexing'); ?>
    </a>
    <ul class="table_console_info">
        <li>PublishRequestsPerDayPerProject = <?php echo esc_html($quota_info['PublishRequestsPerDayPerProject']); ?></li>
        <li>MetadataRequestsPerMinutePerProject = <?php echo esc_html($quota_info['MetadataRequestsPerMinutePerProject']); ?></li>
        <li>RequestsPerMinutePerProject = <?php echo esc_html($quota_info['RequestsPerMinutePerProject']); ?></li>
    </ul>
    <?php
}