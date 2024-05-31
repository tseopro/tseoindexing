<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO class TSEO_Indexing
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
            [$this, 'tseoindexing_settings_page_content'],
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

    public function tseoindexing_settings_page_content() {
        ?>
        <div class="tseoindexing-admin-panel">
            <h1><?php esc_html_e('TSEO Indexing Settings', 'tseoindexing'); ?></h1>
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                    settings_fields('tseoindexing');
                    do_settings_sections('tseoindexing');
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function tseoindexing_links_page_content() {
        ?>
        <div class="tseoindexing-admin-panel">
            <h1><?php esc_html_e('TSEO Indexing Links', 'tseoindexing'); ?></h1>
            <form method="post" action="">
                <?php $this->tseoindexing_display_links_table(); ?>
            </form>
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

    public function tseoindexing_display_links_table() {
        $indexed_urls = $this->tseoindexing_get_indexed_urls();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('URL', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Indexed', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Action', 'tseoindexing'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($indexed_urls as $url => $indexed) : ?>
                    <tr>
                        <td><?php echo esc_url($url); ?></td>
                        <td><?php echo $indexed ? esc_html__('Yes', 'tseoindexing') : esc_html__('No', 'tseoindexing'); ?></td>
                        <td>
                            <input type="checkbox" name="urls_to_index[]" value="<?php echo esc_url($url); ?>" <?php checked($indexed); ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="button button-primary"><?php esc_html_e('Update Indexing Status', 'tseoindexing'); ?></button>
        <?php
    }

    public function tseoindexing_get_indexed_urls() {
        // Aquí deberías implementar la lógica para obtener las URLs indexadas y no indexadas
        // Este es solo un ejemplo simple
        return [
            'https://example.com/page1' => true,
            'https://example.com/page2' => false,
            'https://example.com/page3' => true,
        ];
    }
}

$tseoindexing_main = new TSEOIndexing_Main();