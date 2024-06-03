<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO class TSEO_Indexing_Main
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */

class TSEOIndexing_Main {

    /**
     * TSEO PRO Constructor
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'tseoindexing_create_menu']);
        add_action('admin_init', [$this, 'tseoindexing_setup_sections']);
        add_action('admin_init', [$this, 'tseoindexing_setup_fields']);
        add_action('publish_post', [$this, 'tseo_notify_google_about_new_post'], 10, 2);

        // Hook to notify Google about URLs to be removed upon plugin activation or update
        register_activation_hook(__FILE__, [$this, 'tseo_notify_google_about_removed_urls']);
        register_deactivation_hook(__FILE__, [$this, 'tseo_notify_google_about_removed_urls']);
    }

    public function tseoindexing_create_menu() {
        add_menu_page(
            'TSEO Indexing',
            'TSEO Indexing',
            'manage_options',
            'tseoindexing',
            [$this, 'tseoindexing_dashboard_page_content'],
            'dashicons-admin-site',
            6
        );

        add_submenu_page(
            'tseoindexing',
            __('Settings', 'tseoindexing'),
            __('Settings', 'tseoindexing'),
            'manage_options',
            'tseoindexing-settings',
            [$this, 'tseoindexing_settings_page_content']
        );

        add_submenu_page(
            'tseoindexing',
            __('Links', 'tseoindexing'),
            __('Links', 'tseoindexing'),
            'manage_options',
            'tseoindexing-links',
            [$this, 'tseoindexing_links_page_content']
        );
    }

    public function tseoindexing_handle_file_upload() {
        if (isset($_FILES['tseoindexing_service_account_file']) && check_admin_referer('tseoindexing_settings')) {
            $uploaded_file = $_FILES['tseoindexing_service_account_file'];
            
            if ($uploaded_file['error'] == UPLOAD_ERR_OK && $uploaded_file['type'] == 'application/json') {
                $json_content = file_get_contents($uploaded_file['tmp_name']);
                $post_types = isset($_POST['tseoindexing_post_types']) ? (array)$_POST['tseoindexing_post_types'] : [];
                tseoindexing_save_api_key($json_content, $post_types);
                echo '<div class="updated"><p>' . esc_html__('API Key and settings saved successfully.', 'tseoindexing') . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__('Error uploading file. Please ensure it is a valid JSON file.', 'tseoindexing') . '</p></div>';
            }
        }
    }

    public function get_saved_options() {
        return tseoindexing_get_api_key();
    }

    public function tseoindexing_settings_page_content() {
        $this->tseoindexing_handle_file_upload(); // Handle the file upload if it exists.

        $options = $this->get_saved_options();
        $json_content = isset($options['json_key']) ? $options['json_key'] : '';
        $post_types = isset($options['post_types']) ? $options['post_types'] : [];

        $available_post_types = get_post_types(['public' => true], 'objects');

        ?>
        <div class="tseoindexing-admin-panel">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO Indexing Settings', 'tseoindexing'); ?></h1>
                <div class="danger">
                    <p><?php esc_html_e('Warning: This is a development version of the plugin. Do not use it in a production environment.', 'tseoindexing'); ?></p>
                </div>
                <form method="post" enctype="multipart/form-data" action="">
                    <?php wp_nonce_field('tseoindexing_settings'); ?>
                    <h2><?php esc_html_e('Google Service Account JSON', 'tseoindexing'); ?></h2>
                    <input type="file" name="tseoindexing_service_account_file" id="tseoindexing_service_account_file" accept="application/json" />
                    <h2><?php esc_html_e('Submit Posts to Google', 'tseoindexing'); ?></h2>
                    <p><?php esc_html_e("Submit the following post types automatically to Google's Instant Indexing API when a post is published, edited, or deleted.", 'tseoindexing'); ?></p>
                    <?php foreach ($available_post_types as $post_type) : ?>
                        <label>
                            <input type="checkbox" name="tseoindexing_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $post_types)); ?> />
                            <?php echo esc_html($post_type->label); ?>
                        </label><br/>
                    <?php endforeach; ?>
                    <?php submit_button(__('Upload JSON and Save Settings', 'tseoindexing')); ?>
                </form>
                <form method="post" action="">
                    <h2><?php esc_html_e('Current JSON', 'tseoindexing'); ?></h2>
                    <textarea name="tseoindexing_service_account_json" id="tseoindexing_service_account_json" rows="10" cols="50" readonly><?php echo esc_textarea($json_content); ?></textarea>
                </form>
            </div>    
            <div class="sidebar">
                <?php tseoindexing_display_info_sidebar(); ?>
            </div>
        </div>
        <?php
    }

    public function tseoindexing_setup_sections() {
        add_settings_section('tseoindexing_section', '', null, 'tseoindexing');
    }

    public function tseoindexing_setup_fields() {
        add_settings_field(
            'tseoindexing_service_account_file',
            'Service Account File',
            [$this, 'tseoindexing_field_callback'],
            'tseoindexing',
            'tseoindexing_section'
        );
        add_settings_field(
            'tseoindexing_remove_urls',
            'URLs to Remove (one per line)',
            [$this, 'tseoindexing_field_callback_remove_urls'],
            'tseoindexing',
            'tseoindexing_section'
        );
        register_setting('tseoindexing', 'tseoindexing_service_account_file');
        register_setting('tseoindexing', 'tseoindexing_remove_urls');
    }

    public function tseo_notify_google_about_new_post($ID, $post) {
        $url = get_permalink($ID);
        $this->google_tseoindexing_api_publish_url($url);
    }

    public function tseo_notify_google_about_removed_urls() {
        $urls_to_remove = get_option('tseoindexing_remove_urls');
        if ($urls_to_remove) {
            $urls = explode(PHP_EOL, $urls_to_remove);
            foreach ($urls as $url) {
                $url = trim($url);
                if ($url) {
                    $this->google_tseoindexing_api_publish_url($url, 'URL_REMOVED');
                }
            }
        }
    }

    public function tseoindexing_dashboard_page_content() {
        ?>
        <div class="tseoindexing-admin-panel">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('Dashboard', 'tseoindexing'); ?></h1>
                <div class="success">
                    <p><?php esc_html_e('Take advantage of the power of TSEO PRO and do your own SEO like a true professional with multiple optimized tools without depending on external plugins.', 'tseoindexing'); ?></p>
                </div>
                <?php tseoindexing_dashboard_options(); ?>
            </div>    
            <div class="sidebar">
                <?php tseoindexing_display_info_sidebar(); ?>
            </div>
        </div>
        <?php
    }

    public function tseoindexing_links_page_content() {
        ?>
        <div class="tseoindexing-admin-panel">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO Indexing Links', 'tseoindexing'); ?></h1>
                <form method="post" action="">
                    <?php $this->tseoindexing_display_links_table(); ?>
                </form>
            </div>
            <div class="sidebar">
                <?php tseoindexing_display_info_sidebar(); ?>
            </div>
        </div>
        <?php
    }

    public function tseoindexing_field_callback($arguments) {
        $file_url = get_option('tseoindexing_service_account_file');
        echo '<input name="tseoindexing_service_account_file" id="tseoindexing_service_account_file" type="file" />';
        if ($file_url) {
            echo '<p>' . esc_html(basename($file_url)) . '</p>';
        }
    }

    public function tseoindexing_field_callback_remove_urls($arguments) {
        echo '<textarea name="tseoindexing_remove_urls" id="tseoindexing_remove_urls" rows="10" cols="50">' . esc_textarea(get_option('tseoindexing_remove_urls')) . '</textarea>';
    }

    public function google_tseoindexing_api_publish_url($url, $type = 'URL_UPDATED') {
        $service_account_file = get_option('tseoindexing_service_account_file');
        if (!$service_account_file) {
            error_log('Service account file is not set in TSEO Indexing plugin settings.');
            return false;
        }

        $client = new Client();
        $client->setAuthConfig($service_account_file);
        $client->addScope('https://www.googleapis.com/auth/indexing');

        $indexingService = new Indexing($client);

        $postBody = new Indexing\UrlNotification();
        $postBody->setUrl($url);
        $postBody->setType($type);

        try {
            $response = $indexingService->urlNotifications->publish($postBody);
            return $response;
        } catch (Exception $e) {
            error_log('Error al publicar URL en Google Indexing API: ' . $e->getMessage());
            return false;
        }
    }

    public function tseoindexing_display_links_table() {
        $indexed_urls = $this->tseoindexing_get_indexed_urls();
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $total_items = count($indexed_urls);
        $total_pages = ceil($total_items / $per_page);
        $offset = ($paged - 1) * $per_page;
        $urls_to_display = array_slice($indexed_urls, $offset, $per_page, true);
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:70%"><?php esc_html_e('URL', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Indexed', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Action', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Type', 'tseoindexing'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urls_to_display as $url => $indexed) : ?>
                    <tr>
                        <td><?php echo esc_url($url); ?></td>
                        <td><?php echo $indexed ? esc_html__('Yes', 'tseoindexing') : esc_html__('No', 'tseoindexing'); ?></td>
                        <td class="checkbox">
                            <input type="checkbox" id="<?php echo $url; ?>" name="urls_to_index[]" value="<?php echo esc_url($url); ?>" <?php checked($indexed); ?>>
                            <label for="<?php echo $url; ?>"></label>
                        </td>
                        <td>
                            <?php
                                $post_id = url_to_postid($url);
                                $post_type = get_post_type($post_id);
                                echo esc_html($post_type);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        if ($total_pages > 1) {
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $paged,
            ));
            if ($page_links) {
                echo '<div class="tseoindexing-pagination">' . $page_links . '</div>';
            }
        }
        submit_button();
        ?>
        </form>
        <?php
    }

    public function tseoindexing_get_indexed_urls() {
        $indexed_urls = [];

        // Get all pages
        $pages = get_pages();
        foreach ($pages as $page) {
            $url = get_permalink($page->ID);
            $indexed_urls[$url] = true;
        }

        // Get all posts
        $posts = get_posts(['post_type' => 'post']);
        foreach ($posts as $post) {
            $url = get_permalink($post->ID);
            $indexed_urls[$url] = true;
        }

        // Get all product pages (assuming WooCommerce is installed)
        if (class_exists('WooCommerce')) {
            $products = get_posts(['post_type' => 'product']);
            foreach ($products as $product) {
                $url = get_permalink($product->ID);
                $indexed_urls[$url] = true;
            }
        }

        // Get all custom taxonomies
        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms($taxonomy);
            foreach ($terms as $term) {
                $url = get_term_link($term);
                $indexed_urls[$url] = true;
            }
        }

        // Get all categories
        $categories = get_categories();
        foreach ($categories as $category) {
            $url = get_category_link($category);
            $indexed_urls[$url] = true;
        }

        // Add other custom post types here if needed

        return $indexed_urls;
    }

    public function insert_url($url, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tseo_indexing_links';
    
        $wpdb->insert(
            $table_name,
            [
                'url' => $url,
                'status' => $status
            ],
            [
                '%s',
                '%s'
            ]
        );
    }
    
    public function get_urls() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tseo_indexing_links';
        $result = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);
        return $result;
    }
}

$tseoindexing_main = new TSEOIndexing_Main();