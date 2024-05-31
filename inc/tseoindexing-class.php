<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO class TSEO_Indexing
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */

class TSEOIndexing {

    /**
     * TSEO PRO Constructor
     *
     * @package TSEOIndexing
     * @version 1.0.0
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'create_settings_page']);
        add_action('admin_init', [$this, 'setup_sections']);
        add_action('admin_init', [$this, 'setup_fields']);
        add_action('publish_post', [$this, 'notify_google_about_new_post'], 10, 2);
    }

    public function create_settings_page() {
        add_options_page(
            'TSEO Indexing Settings',
            'TSEO Indexing',
            'manage_options',
            'tseoindexing',
            [$this, 'settings_page_content']
        );
    }

    public function settings_page_content() {
        ?>
        <div class="wrap">
            <h1>TSEO Indexing Settings</h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields('tseoindexing');
                    do_settings_sections('tseoindexing');
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function setup_sections() {
        add_settings_section('tseoindexing_section', '', null, 'tseoindexing');
    }

    public function setup_fields() {
        add_settings_field(
            'tseoindexing_service_account_file',
            'Service Account File',
            [$this, 'field_callback'],
            'tseoindexing',
            'tseoindexing_section'
        );
        add_settings_field(
            'tseoindexing_remove_urls',
            'URLs to Remove (one per line)',
            [$this, 'field_callback_remove_urls'],
            'tseoindexing',
            'tseoindexing_section'
        );
        register_setting('tseoindexing', 'tseoindexing_service_account_file');
        register_setting('tseoindexing', 'tseoindexing_remove_urls');
    }

    public function field_callback($arguments) {
        echo '<input name="tseoindexing_service_account_file" id="tseoindexing_service_account_file" type="text" value="' . get_option('tseoindexing_service_account_file') . '" />';
    }

    public function field_callback_remove_urls($arguments) {
        echo '<textarea name="tseoindexing_remove_urls" id="tseoindexing_remove_urls" rows="10" cols="50">' . esc_textarea(get_option('tseoindexing_remove_urls')) . '</textarea>';
    }

    public function google_indexing_api_publish_url($url, $type = 'URL_UPDATED') {
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

    public function notify_google_about_new_post($ID, $post) {
        $url = get_permalink($ID);
        $this->google_indexing_api_publish_url($url);
    }

    public function notify_google_about_removed_urls() {
        $urls_to_remove = get_option('tseoindexing_remove_urls');
        if ($urls_to_remove) {
            $urls = explode(PHP_EOL, $urls_to_remove);
            foreach ($urls as $url) {
                $url = trim($url);
                if ($url) {
                    $this->google_indexing_api_publish_url($url, 'URL_REMOVED');
                }
            }
        }
    }
}

new TSEOIndexing();