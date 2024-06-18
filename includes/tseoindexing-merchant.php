<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Merchant Center
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_merchant_center() {
    // Process file upload and Merchant Center ID submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tseo_merchant_submit'])) {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'tseoindexing'));
        }

        // Verify nonce for security
        check_admin_referer('tseoindexing_merchant_nonce', '_wpnonce');

        // Process the JSON credentials file upload
        if (!empty($_FILES['merchant_center_credentials']['tmp_name'])) {
            $uploaded_file = $_FILES['merchant_center_credentials'];
            
            // Check if the uploaded file is a JSON file
            if ($uploaded_file['type'] === 'application/json') {
                $json_content = file_get_contents($uploaded_file['tmp_name']);
                $json_decoded = json_decode($json_content, true);

                // Check if the JSON content is valid
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Validate the project_id
                    if (isset($json_decoded['project_id']) && strpos($json_decoded['project_id'], 'merchant-center-') === 0) {
                        update_option('tseo_merchant_center_credentials', maybe_serialize($json_content));
                        echo '<div class="updated"><p>' . esc_html__('Credentials successfully saved.', 'tseoindexing') . '</p></div>';
                    } else {
                        echo '<div class="error"><p>' . esc_html__('Invalid Google Merchant Center credentials. Please go to Google Cloud Service Accounts to obtain your Service account for Merchant Center.', 'tseoindexing') . '</p></div>';
                    }
                } else {
                    echo '<div class="error"><p>' . esc_html__('The JSON file is not valid.', 'tseoindexing') . '</p></div>';
                }
            } else {
                echo '<div class="error"><p>' . esc_html__('Please upload a valid JSON file.', 'tseoindexing') . '</p></div>';
            }
        }

        // Process the Merchant Center ID
        if (!empty($_POST['merchant_center_id'])) {
            $merchant_center_id = sanitize_text_field($_POST['merchant_center_id']);
            // Ensure the Merchant Center ID is numeric
            if (is_numeric($merchant_center_id)) {
                update_option('tseo_merchant_center_id', $merchant_center_id);
                echo '<div class="updated"><p>' . esc_html__('Merchant Center ID successfully saved.', 'tseoindexing') . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__('Please enter a valid numeric Google Merchant Center ID.', 'tseoindexing') . '</p></div>';
            }
        }
    }

    // Retrieve the stored credentials and Merchant Center ID
    $stored_credentials = maybe_unserialize(get_option('tseo_merchant_center_credentials', ''));
    $merchant_center_id = get_option('tseo_merchant_center_id', '');

    // Verify connection status
    $is_connected = tseoindexing_merchant_verify_connection();
    $connection_status = $is_connected ? __('Connected', 'tseoindexing') : __('Disconnected', 'tseoindexing');
    $connection_class = $is_connected ? 'connect-success' : 'connect-error';

    ?>
    <!-- Form to upload credentials and enter the Merchant Center ID -->
    <form method="post" action="" enctype="multipart/form-data">

        <!-- Status message for connection to Google Merchant Center -->
        <p>
            <?php esc_html_e('Connection Status: ', 'tseoindexing'); ?>
            <span class="<?php echo $connection_class; ?>"><?php echo $connection_status; ?></span> 
        </p>

        <?php wp_nonce_field('tseoindexing_merchant_nonce'); ?>
        <input type="hidden" name="action" value="tseoindexing_upload_merchant_credentials">

        <p><?php esc_html_e('Upload the JSON file containing your Google Merchant Center credentials.', 'tseoindexing'); ?></p>
        <input type="file" name="merchant_center_credentials" accept=".json" />
        
        <?php if (!empty($stored_credentials)): ?>
            <p><?php esc_html_e('Current JSON credentials:', 'tseoindexing'); ?></p>
            <textarea readonly rows="10" cols="50"><?php echo esc_textarea($stored_credentials); ?></textarea>
        <?php endif; ?>

        <p><?php esc_html_e('Enter your Google Merchant Center ID.', 'tseoindexing'); ?></p>
        <p>
            <input type="text" name="merchant_center_id" value="<?php echo esc_attr($merchant_center_id); ?>" 
                   inputmode="numeric" pattern="\d*" title="<?php esc_attr_e('Please enter a numeric Merchant Center ID.', 'tseoindexing'); ?>" />
        </p>

        <div class="button-panel">
            <input type="submit" name="tseo_merchant_submit" class="button button-primary" id="submit" value="<?php esc_attr_e('Save Merchant Center ID', 'tseoindexing'); ?>">
        </div>
    </form>
    <?php
}

function tseoindexing_merchant_get_google_client() {
    // Retrieve stored credentials
    $credentials = maybe_unserialize(get_option('tseo_merchant_center_credentials', ''));
    
    if (!$credentials) {
        return null;
    }

    $client = new Google_Client();
    $client->setAuthConfig(json_decode($credentials, true)); // Set credentials as an array
    $client->addScope(Google_Service_ShoppingContent::CONTENT);

    return $client;
}

function tseoindexing_merchant_verify_connection() {
    $client = tseoindexing_merchant_get_google_client();

    if (!$client) {
        return false;
    }

    $service = new Google_Service_ShoppingContent($client);

    try {
        // Attempt to list products to check the connection
        $merchant_center_id = get_option('tseo_merchant_center_id', '');
        $service->products->listProducts($merchant_center_id, ['maxResults' => 1]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function tseoindexing_openai_api_client() {
    // Procesar el formulario para guardar la clave de API de OpenAI
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tseo_openai_submit'])) {
        // Verificar permisos del usuario
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'tseoindexing'));
        }

        // Verificar nonce para seguridad
        check_admin_referer('tseoindexing_openai_nonce', '_wpnonce_openai');

        // Procesar la clave de API de OpenAI
        if (!empty($_POST['openai_api_key'])) {
            $openai_api_key = sanitize_text_field($_POST['openai_api_key']);
            update_option('tseo_openai_api_key', $openai_api_key);
            echo '<div class="updated"><p>' . esc_html__('OpenAI API key successfully saved.', 'tseoindexing') . '</p></div>';
        }
    }

    // Recuperar la clave de API de OpenAI almacenada
    $openai_api_key = get_option('tseo_openai_api_key', '');

    ?>
    <!-- Formulario para ingresar la clave de API de OpenAI -->
    <form method="post" action="">
        <?php wp_nonce_field('tseoindexing_openai_nonce', '_wpnonce_openai'); ?>

        <p><?php esc_html_e('Enter your OpenAI API Key.', 'tseoindexing'); ?></p>
        <p>
            <input type="text" name="openai_api_key" value="<?php echo esc_attr($openai_api_key); ?>" size="55" />
        </p>

        <div class="button-panel">
            <input type="submit" name="tseo_openai_submit" class="button button-primary submit" value="<?php esc_attr_e('Save OpenAI API Key', 'tseoindexing'); ?>">
        </div>
    </form>
    <?php
}
