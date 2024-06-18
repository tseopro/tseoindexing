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

        // AJAX actions
        add_action('wp_ajax_tseoindexing_load_urls_by_type', [$this, 'tseoindexing_load_urls_by_type']);
        add_action('wp_ajax_tseoindexing_send_urls_to_google', [$this, 'tseoindexing_send_urls_to_google']);
    }

    /**
     * TSEO PRO Menu
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
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
            __('Console', 'tseoindexing'),
            __('Console', 'tseoindexing'),
            'manage_options',
            'tseoindexing-console',
            [$this, 'tseoindexing_console_page_content']
        );

        add_submenu_page(
            'tseoindexing',
            __('Tools', 'tseoindexing'),
            __('Tools', 'tseoindexing'),
            'manage_options',
            'tseoindexing-tools',
            [$this, 'tseoindexing_tools_page_content']
        );

        add_submenu_page(
            'tseoindexing',
            __('Config URLs', 'tseoindexing'),
            __('Config URLs', 'tseoindexing'),
            'manage_options',
            'tseoindexing-links',
            [$this, 'tseoindexing_links_page_content']
        );

        add_submenu_page(
            'tseoindexing',
            __('Setting Indexing', 'tseoindexing'),
            __('Setting Indexing', 'tseoindexing'),
            'manage_options',
            'tseoindexing-settings',
            [$this, 'tseoindexing_settings_page_content']
        );
        
        if ( class_exists( 'WooCommerce' ) ) {
            add_submenu_page(
                'tseoindexing',
                __('Setting Merchant', 'tseoindexing'),
                __('Setting Merchant', 'tseoindexing'),
                'manage_options',
                'tseoindexing-merchant-center',
                [$this, 'tseoindexing_merchant_center_page_content']
            );

            add_submenu_page(
                'tseoindexing',
                __('Product List', 'tseoindexing'),
                __('Product List', 'tseoindexing'),
                'manage_options',
                'tseoindexing-merchant-center_list',
                [$this, 'tseoindexing_merchant_center_list_page_content']
            );
        }
    }

    /**
     * Display the dashboard page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_dashboard_page_content() {
        ?>
        <div class="tseoindexing-admin-panel">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('Dashboard', 'tseoindexing'); ?></h1>
                <?php
                    tseoindexing_success_info();
                    tseoindexing_dashboard_options(); 
                ?>
            </div>
            <div class="sidebar">
                <?php tseoindexing_display_info_sidebar(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * TSEO PRO Handle the file upload and save the API key to the database.
     * Page Settings
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
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

    // Get the saved options from the database.
    public function tseoindexing_get_saved_options() {
        return tseoindexing_get_api_key();
    }

    // Display the settings page content
    public function tseoindexing_settings_page_content() {
        $this->tseoindexing_handle_file_upload();

        $options = $this->tseoindexing_get_saved_options();
        $json_content = isset($options['json_key']) ? $options['json_key'] : '';
        $post_types = isset($options['post_types']) ? $options['post_types'] : [];

        $available_post_types = get_post_types(['public' => true], 'objects');

        ?>
        <div class="tseoindexing-admin-panel">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO Indexing Settings', 'tseoindexing'); ?></h1>
                <?php tseoindexing_danger_info() ?>
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
                    <div class="button-panel">
                        <?php submit_button(__('Upload JSON and Save Settings', 'tseoindexing')); ?>
                    </div>
                </form>
                <div>
                    <h2><?php esc_html_e('Current JSON', 'tseoindexing'); ?></h2>
                    <textarea name="tseoindexing_service_account_json" id="tseoindexing_service_account_json" rows="10" cols="50" readonly><?php echo esc_textarea($json_content); ?></textarea>
                </div>
            </div>    
            <div class="sidebar">
                <?php tseoindexing_display_info_sidebar(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Display the links (URLs) page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_links_page_content() {
        ?>
        <div class="tseoindexing-admin-panel-all">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO Indexing URLs', 'tseoindexing'); ?></h1>
                <?php $this->tseoindexing_display_links_table(); ?>
            </div>
        </div>
        <?php
    }

    // Display the table of URL records and provide options to update or delete
    public function tseoindexing_display_links_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tseo_indexing_links';

        // Obtener datos de la base de datos
        $managed_urls = $wpdb->get_results("SELECT * FROM {$table_name}", OBJECT_K);

        // Obtener URLs indexadas y eliminar las que ya están en la base de datos
        $indexed_urls = $this->tseoindexing_get_indexed_urls();
        foreach ($managed_urls as $managed_url) {
            if (isset($indexed_urls[$managed_url->url])) {
                unset($indexed_urls[$managed_url->url]);
            }
        }

        // Combinar managed_urls con indexed_urls para mostrar en la tabla
        $all_urls = array_merge($managed_urls, array_map(function($url) {
            return (object) ['url' => $url, 'status' => 'NULL', 'type' => 'NULL', 'date_time' => 'N/A'];
        }, array_keys($indexed_urls)));

        // Pagination
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $total_items = count($all_urls);
        $total_pages = ceil($total_items / $per_page);
        $offset = ($paged - 1) * $per_page;
        $urls_to_display = array_slice($all_urls, $offset, $per_page);
        ?>
        <div class="flex-table">
            <!-- Cabecera -->
            <div class="flex-row header">
                <div class="line_one">
                    <div class="flex-cell">URL</div>
                </div>
                <div class="line_two">   
                    <div class="flex-cell"><i class="tseoindexing-sphere sphere"></i></div>
                    <div class="flex-cell"><?php esc_html_e('Action', 'tseoindexing'); ?></div>
                    <div class="flex-cell"><?php esc_html_e('Status', 'tseoindexing'); ?></div>
                    <div class="flex-cell"><?php esc_html_e('Type', 'tseoindexing'); ?></div>
                    <div class="flex-cell">
                        <button type="button" id="delete-selected" class="button button-secondary">
                            <i class="tseoindexing-bin"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Filas de Datos -->
            <?php foreach ($urls_to_display as $url_data): 
                $status_class = 'status-null';
                if ($url_data->type === 'URL_UPDATED') {
                    $status_class = 'status-updated';
                } elseif ($url_data->type === 'URL_DELETED') {
                    $status_class = 'status-deleted';
                }
            ?>
            <div class="flex-row">
                <div class="line_one">
                    <div class="flex-cell"><?php echo esc_url($url_data->url); ?></div>
                </div>
                <div class="line_two">
                    <div class="flex-cell">
                        <a href="<?php echo esc_url($url_data->url); ?>" class="button-eye" target="_black" title="<?php esc_html_e('View', 'tseoindexing'); ?>">
                            <i class="tseoindexing-eye eye"></i>
                        </a>
                    </div>
                    <div class="flex-cell checkbox">
                        <input type="checkbox" id="<?php echo esc_attr($url_data->url); ?>" name="urls_to_index[]" value="<?php echo esc_url($url_data->url); ?>" <?php checked($url_data->type === 'URL_UPDATED'); ?> class="submit">
                        <label for="<?php echo esc_attr($url_data->url); ?>">
                            <?php echo $url_data->type === 'URL_UPDATED' ? esc_html__('Update', 'tseoindexing') : esc_html__('Add', 'tseoindexing'); ?>
                        </label>
                    </div>
                    <div class="flex-cell data-all status <?php echo $status_class; ?>">
                        <?php echo esc_html($url_data->type); ?>
                    </div>
                    <div class="flex-cell">
                        <?php
                            $post_id = url_to_postid($url_data->url);
                            $post_type = get_post_type($post_id);
                            echo esc_html($post_type);
                        ?>
                    </div>
                    <div class="flex-cell" id="delete-<?php echo esc_attr($url_data->url); ?>">
                        <?php if (in_array($url_data->type, ['URL_UPDATED', 'URL_DELETED'])): ?>
                            <input type="checkbox" name="urls_to_delete[]" value="<?php echo esc_url($url_data->url); ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        if ($total_pages > 1) {
            $page_links = paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $paged,
            ]);
            if ($page_links) {
                echo '<div class="tseoindexing-pagination">' . $page_links . '</div>';
            }
        }
        // JavaScript from tseoindexing-settings.php
        tseoindexing_php_script_embedded_links_table();
        // Table Infornation
        tseoindexing_table_link_info();
    }

    // Retrieves all URLs from pages, posts, products, and taxonomies
    public function tseoindexing_get_indexed_urls() {
        $indexed_urls = [];

        $pages = get_pages(['number' => 0]);
        foreach ($pages as $page) {
            $url = get_permalink($page->ID);
            $indexed_urls[$url] = true;
        }

        $posts = get_posts([
            'post_type' => 'post',
            'numberposts' => -1
        ]);
        foreach ($posts as $post) {
            $url = get_permalink($post->ID);
            $indexed_urls[$url] = true;
        }

        if (class_exists('WooCommerce')) {
            $products = get_posts([
                'post_type' => 'product',
                'numberposts' => -1
            ]);
            foreach ($products as $product) {
                $url = get_permalink($product->ID);
                $indexed_urls[$url] = true;
            }
        }

        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'number' => 0
            ]);
        
            foreach ($terms as $term) {
                $url = get_term_link($term);
                if ($url && preg_match('/^[a-zA-Z0-9-_]+$/', basename($url))) {
                    $indexed_urls[$url] = false;
                }
            }
        }

        $categories = get_categories([
            'hide_empty' => false,
            'number' => 0
        ]);
        foreach ($categories as $category) {
            $url = get_category_link($category);
            $indexed_urls[$url] = false;
        }

        return $indexed_urls;
    }

    /**
     * Inserts a URL and its status into the tseo_indexing_links table.
     *
     * @param string $url The URL to be inserted.
     * @param string $status The status of the URL.
     * @return void
     */
    public function tseoindexing_insert_url($url, $status) {
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

    /**
     * Retrieves all URLs from the database.
     *
     * @return array The array of URLs retrieved from the database.
     */
    public function tseoindexing_get_urls() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tseo_indexing_links';
        $result = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);
        return $result;
    }

    /**
     * Display the console page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_console_page_content() {
        ?>
        <div class="tseoindexing-admin-panel-all">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO Console', 'tseoindexing'); ?></h1>

                <div class="wrap-console">
                    <div class="console">
                        <?php tseoindexing_display_console(); ?>
                    </div>
                    <div class="quota">
                        <?php tseoindexing_remaining_quota(); ?>
                    </div>
                </div>

                <div class="wrap-console-response" style="display: none;">
                    <?php tseoindexing_display_console_response(); ?>
                </div>
                
            </div>
        </div>
        <?php
    }

    /**
     * Display the tools page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_tools_page_content() {
        ?>
        <div class="tseoindexing-admin-panel-all">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO tools', 'tseoindexing'); ?></h1>

                <?php tseoindexing_display_tools(); ?>
                
            </div>
        </div>
        <?php
    }

    /**
     * API Google Indexing. Publishes a URL to the Google Indexing API.
     *
     * @param string $url The URL to be published.
     * @param string $type The type of URL notification. Default is 'URL_UPDATED'.
     * @return mixed The response from the Google Indexing API if successful, false otherwise.
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function google_tseoindexing_api_publish_url($url, $type = 'URL_UPDATED') {
        $options = get_option('tseo_indexing_options_key');
        if (!$options) {
            error_log('Service account file is not set in TSEO Indexing plugin settings.');
            return false;
        }
    
        $options = maybe_unserialize($options);
        $service_account_file = isset($options['json_key']) ? json_decode($options['json_key'], true) : null;
        if (!$service_account_file) {
            error_log('Invalid service account file in TSEO Indexing plugin settings.');
            return false;
        }
    
        $client = new Google\Client();
        $client->setAuthConfig($service_account_file);
        $client->addScope('https://www.googleapis.com/auth/indexing');
    
        $indexingService = new Google\Service\Indexing($client);
    
        $postBody = new Google\Service\Indexing\UrlNotification();
        $postBody->setUrl($url);
        $postBody->setType($type);
    
        error_log('Publishing URL with type: ' . $type);
    
        try {
            $response = $indexingService->urlNotifications->publish($postBody);
            error_log('Successfully published URL: ' . $url . ' with type: ' . $type);
            return $response;
        } catch (Exception $e) {
            error_log('Error publishing URL in Google Indexing API: ' . $e->getMessage());
            return [
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Notifies Google about a new post.
     *
     * @param int $ID The ID of the post.
     * @param WP_Post $post The post object.
     * @return void
     */
    public function tseo_notify_google_about_new_post($ID, $post) {
        $url = get_permalink($ID);
        $this->google_tseoindexing_api_publish_url($url);
    }

    /**
     * Notifies Google about the removed URLs.
     *
     * This method retrieves the URLs to be removed from the options and sends a request to the Google TSEO Indexing API
     * for each URL to notify Google about the removal.
     *
     * @return void
     */
    public function tseo_notify_google_about_removed_urls() {
        $urls_to_remove = get_option('tseoindexing_remove_urls');
        if ($urls_to_remove) {
            $urls = explode(PHP_EOL, $urls_to_remove);
            foreach ($urls as $url) {
                $url = trim($url);
                if ($url) {
                    $this->google_tseoindexing_api_publish_url($url, 'URL_DELETED');
                }
            }
        }
    }

    /**
     * Callback function for the tseoindexing_field.
     *
     * This function is responsible for rendering the input field and displaying the uploaded file name.
     *
     * @param array $arguments The arguments passed to the callback function.
     * @return void
     */
    public function tseoindexing_field_callback($arguments) {
        $file_url = get_option('tseoindexing_service_account_file');
        echo '<input name="tseoindexing_service_account_file" id="tseoindexing_service_account_file" type="file" />';
        if ($file_url) {
            echo '<p>' . esc_html(basename($file_url)) . '</p>';
        }
    }

    /**
     * Callback function for the tseoindexing_remove_urls field.
     * Renders a textarea input field with the current value of the tseoindexing_remove_urls option.
     *
     * @param array $arguments The arguments passed to the callback function.
     * @return void
     */
    public function tseoindexing_field_callback_remove_urls($arguments) {
        echo '<textarea name="tseoindexing_remove_urls" id="tseoindexing_remove_urls" rows="10" cols="50">' . esc_textarea(get_option('tseoindexing_remove_urls')) . '</textarea>';
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

    public function tseoindexing_load_urls_by_type() {
        check_ajax_referer('tseoindexing_console', '_ajax_nonce');

        $type = sanitize_text_field($_POST['type']);
        $urls = $this->tseoindexing_get_urls_by_type($type);

        wp_send_json_success($urls);
    }

    public function tseoindexing_send_urls_to_google() {
        check_ajax_referer('tseoindexing_console', '_ajax_nonce');
    
        $urls = array_map('esc_url_raw', $_POST['urls']);
        $action = sanitize_text_field($_POST['api_action']);  // Get the value of api_action
    
        error_log('Received action: ' . $action);
    
        $results = [];
        foreach ($urls as $url) {
            $action_type = '';  // Initialize the action type
            $response = [];  // Initialize the response
    
            // Depending on the value of 'api_action', we send the correct request
            switch ($action) {
                case 'URL_UPDATED':
                    error_log('Publishing URL as URL_UPDATED: ' . $url);
                    $response = $this->google_tseoindexing_api_publish_url($url, 'URL_UPDATED');
                    $action_type = 'URL_UPDATED';
                    break;
                case 'URL_DELETED':
                    error_log('Publishing URL as URL_DELETED: ' . $url);
                    $response = $this->google_tseoindexing_api_publish_url($url, 'URL_DELETED');
                    $action_type = 'URL_DELETED';
                    break;
                case 'getstatus':
                    error_log('Getting status for URL: ' . $url);
                    $response = $this->get_google_tseoindexing_status($url);
                    $action_type = 'getstatus';
                    break;
                default:
                    $response = ['error' => ['code' => 400, 'message' => 'Invalid action type']];
                    $action_type = 'invalid';
                    break;
            }
    
            // Format the response to be more readable on the frontend
            $formatted_response = $this->tseoindexing_format_api_response($response);
    
            // Register the response
            error_log('API Response: ' . print_r($response, true));
    
            // Add the result to the results array
            $results[] = [
                'url' => $url,
                'action' => $action_type,
                'response' => $formatted_response,
            ];
    
            error_log('Processed action: ' . $action_type);
        }
    
        // Send the response back to the AJAX request
        wp_send_json_success($results);
    }
    
    private function tseoindexing_format_api_response($response) {
        if (isset($response['error'])) {
            return [
                'status' => 'error',
                'code' => $response['error']['code'],
                'message' => $response['error']['message']
            ];
        }
    
        // Specific format for URL_UPDATED or URL_DELETED response
        if ($response instanceof Google\Service\Indexing\PublishUrlNotificationResponse) {
            return [
                'status' => 'success',
                'url' => $response->urlNotificationMetadata->url,
                'type' => $response->urlNotificationMetadata->latestUpdate ? $response->urlNotificationMetadata->latestUpdate->type : $response->urlNotificationMetadata->latestRemove->type,
                'notifyTime' => $response->urlNotificationMetadata->latestUpdate ? $response->urlNotificationMetadata->latestUpdate->notifyTime : $response->urlNotificationMetadata->latestRemove->notifyTime
            ];
        }
    
        // Specific format for getstatus response
        if ($response instanceof Google\Service\Indexing\UrlNotificationMetadata) {
            return [
                'url' => $response->url,
                'latestUpdate' => $response->latestUpdate ? [
                    'type' => $response->latestUpdate->type,
                    'notifyTime' => $response->latestUpdate->notifyTime
                ] : null,
                'latestRemove' => $response->latestRemove ? [
                    'type' => $response->latestRemove->type,
                    'notifyTime' => $response->latestRemove->notifyTime
                ] : null
            ];
        }
    
        return $response;  // Return as is if there is no specific format.
    }
    
    public function tseoindexing_get_urls_by_type($type) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tseo_indexing_links';

        $urls = $wpdb->get_results($wpdb->prepare("SELECT url FROM {$table_name} WHERE type = %s", $type), ARRAY_A);
        return wp_list_pluck($urls, 'url');
    }

    public function tseoindexing_get_url_type($url) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tseo_indexing_links';
        return $wpdb->get_var($wpdb->prepare("SELECT type FROM {$table_name} WHERE url = %s", $url));
    }

    public function get_google_tseoindexing_status($url) {
        $options = get_option('tseo_indexing_options_key');
        if (!$options) {
            return ['error' => ['code' => 500, 'message' => 'Service account file is not set']];
        }
    
        $options = maybe_unserialize($options);
        $service_account_file = isset($options['json_key']) ? json_decode($options['json_key'], true) : null;
        if (!$service_account_file) {
            return ['error' => ['code' => 500, 'message' => 'Invalid service account file']];
        }
    
        $client = new Google\Client();
        $client->setAuthConfig($service_account_file);
        $client->addScope('https://www.googleapis.com/auth/indexing');
    
        $indexingService = new Google\Service\Indexing($client);
    
        try {
            $response = $indexingService->urlNotifications->getMetadata(['url' => $url]);
            error_log('Successfully retrieved status for URL: ' . $url);
            return $response;
        } catch (Exception $e) {
            error_log('Error retrieving status for URL in Google Indexing API: ' . $e->getMessage());
            return [
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ];
        }
    }

    
    /**
     * Display Google Merchant Center page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_merchant_center_page_content() {
        ?>
        <div class="tseoindexing-admin-panel-all">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content wrap-tools">
                <h1><?php esc_html_e('Google Merchant Center', 'tseoindexing'); ?></h1>

                <?php
                    if ( class_exists( 'WooCommerce' ) ) {
                        tseoindexing_display_merchant_center();
                        tseoindexing_openai_api_client();
                    }
                ?>
                
            </div>
        </div>
        <?php
    }

    /**
     * Display Google Merchant Center Woocommerce page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_merchant_center_list_page_content() {
        ?>
        <div class="tseoindexing-admin-panel-all">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content wrap-tools">
                <h1><?php esc_html_e('List Products WooCommerce', 'tseoindexing'); ?></h1>

                <?php
                    if ( class_exists( 'WooCommerce' ) ) {
                        tseoindexing_display_merchant_product_list();
                    }
                ?>
                
            </div>
        </div>
        <?php
    }
    
}

$tseoindexing_main = new TSEOIndexing_Main();
