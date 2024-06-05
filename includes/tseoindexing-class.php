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
            __('Settings', 'tseoindexing'),
            __('Settings', 'tseoindexing'),
            'manage_options',
            'tseoindexing-settings',
            [$this, 'tseoindexing_settings_page_content']
        );

        add_submenu_page(
            'tseoindexing',
            __('Config URL´s', 'tseoindexing'),
            __('Config URL´s', 'tseoindexing'),
            'manage_options',
            'tseoindexing-links',
            [$this, 'tseoindexing_links_page_content']
        );
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
    public function get_saved_options() {
        return tseoindexing_get_api_key();
    }

    // Display the settings page content
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
                <?php tseoindexing_danger_info(); ?>
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

    /**
     * API Google Indexing
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function google_tseoindexing_api_publish_url($url, $type = 'URL_UPDATED') {
        $service_account_file = get_option('tseo_indexing_options_key');
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
                    $this->google_tseoindexing_api_publish_url($url, 'URL_DELETED');
                }
            }
        }
    }

    // TENGO DUDAS DE ESTAS FUNCIONES
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
    // #TENGO DUDAS DE ESTAS FUNCIONES

    
    /**
     * Display the links page content
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function tseoindexing_links_page_content() {
        ?>
        <div class="tseoindexing-admin-panel-all">
            <?php tseoindexing_loading_overlay(); ?>
            <div class="main-content">
                <h1><?php esc_html_e('TSEO Indexing URL´s', 'tseoindexing'); ?></h1>
                <?php $this->tseoindexing_display_links_table(); ?>
            </div>
        </div>
        <?php
    }

    // Muestra la tabla de registros de URLs y da opción de actualizar o eliminar
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
    
        // Paginación
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $total_items = count($all_urls);
        $total_pages = ceil($total_items / $per_page);
        $offset = ($paged - 1) * $per_page;
        $urls_to_display = array_slice($all_urls, $offset, $per_page);
        ?>
        <?php tseoindexing_table_link_info(); ?>
        <table class="wp-list-table widefat fixed striped form-table">
            <thead>
                <tr>
                    <th style="width:60%"><?php esc_html_e('URL', 'tseoindexing'); ?></th>
                    <th style="width:15%"><?php esc_html_e('Action', 'tseoindexing'); ?></th>
                    <th style="width:10%"><?php esc_html_e('Status', 'tseoindexing'); ?></th>
                    <th style="width:15%"><?php esc_html_e('Type', 'tseoindexing'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urls_to_display as $url_data): 
                    $status_class = 'status-null';
                    if ($url_data->type === 'URL_UPDATED') {
                        $status_class = 'status-updated';
                    } elseif ($url_data->type === 'URL_DELETED') {
                        $status_class = 'status-deleted';
                    }
                ?>
                    <tr>
                        <td class="url"><?php echo esc_url($url_data->url); ?></td>
                        <td class="checkbox data-all">
                            <input type="checkbox" id="<?php echo esc_attr($url_data->url); ?>" name="urls_to_index[]" value="<?php echo esc_url($url_data->url); ?>" <?php checked($url_data->type === 'URL_UPDATED'); ?> class="submit">
                            <label for="<?php echo esc_attr($url_data->url); ?>">
                                <?php echo $url_data->type === 'URL_UPDATED' ? esc_html__('Update', 'tseoindexing') : esc_html__('Add', 'tseoindexing'); ?>
                            </label>
                        </td>
                        <td class="data-all status <?php echo $status_class; ?>">
                            <?php echo esc_html($url_data->type); ?>
                        </td>
                        <td class="data-all">
                            <?php
                                $post_id = url_to_postid($url_data->url);
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
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var checkboxes = document.querySelectorAll('.checkbox input[type="checkbox"]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.addEventListener("change", function() {
                        var label = this.nextElementSibling;
                        var action = this.checked ? 'update' : 'remove';
                        label.innerText = this.checked ? "<?php echo esc_html__('Update', 'tseoindexing'); ?>" : "<?php echo esc_html__('Add', 'tseoindexing'); ?>";
                        label.classList.toggle("update", this.checked);
                        label.classList.toggle("add", !this.checked);
    
                        // Enviar petición AJAX
                        jQuery.post(ajaxurl, {
                            action: 'update_tseo_url',
                            url: this.value,
                            action_type: action,
                            _ajax_nonce: '<?php echo wp_create_nonce('update_tseo_url_nonce'); ?>'
                        }, function(response) {
                            if (!response.success) {
                                alert('Error: ' + response.data);
                            } else {
                                // Actualizar el campo `status` en la tabla
                                var statusCell = checkbox.closest('tr').querySelector('.status');
                                statusCell.innerText = response.data.type;
                                statusCell.className = 'data-all status ' + (response.data.type === 'URL_UPDATED' ? 'status-updated' : (response.data.type === 'URL_DELETED' ? 'status-deleted' : 'status-null'));
                            }
                        });
                    });
                });
            });
        </script>
        <?php
    }
    

    // Obtiene todas las URLs de las páginas, posts, productos y taxonomías
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
                $indexed_urls[$url] = false;
            }
        }

        // Get all categories
        $categories = get_categories();
        foreach ($categories as $category) {
            $url = get_category_link($category);
            $indexed_urls[$url] = false;
        }

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