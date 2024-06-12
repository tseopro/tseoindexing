<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Merchant Center Product List
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_merchant_product_list() {
    // Verificar permisos del usuario
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'tseoindexing'));
    }

    // Obtener la preferencia de envío del usuario
    $send_automatically = get_option('tseo_merchant_send_automatically', false);

    ?>
    <form method="post" action="">
        <?php wp_nonce_field('tseoindexing_merchant_product_nonce'); ?>
        <h2><?php esc_html_e('Select Products to Send to Google Merchant Center', 'tseoindexing'); ?></h2>
        <p><?php esc_html_e('Only products that meet the Google Merchant Center publication criteria can be selected for submission.', 'tseoindexing'); ?></p>

        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" /></th>
                    <th><?php esc_html_e('Image', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Product', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Price', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Stock', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Condition', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Brand', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('GTIN', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('MPN', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Google Cat', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Destinations', 'tseoindexing'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1
                );

                $products = get_posts($args);

                foreach ($products as $product_post):
                    $product = wc_get_product($product_post->ID);
                
                    $is_valid_product = 
                        !empty(wp_get_attachment_url($product->get_image_id())) &&
                        !empty($product->get_name()) &&
                        !empty($product->get_description()) &&
                        is_numeric($product->get_price()) && $product->get_price() > 0 &&
                        !empty(get_post_meta($product->get_id(), '_condition', true));
                
                    $condition_labels = array(
                        'new' => __('New', 'tseoindexing'),
                        'refurbished' => __('Refurbished', 'tseoindexing'),
                        'used' => __('Used', 'tseoindexing'),
                    );
                    $condition = get_post_meta($product->get_id(), '_condition', true);
                    $condition_label = isset($condition_labels[$condition]) ? $condition_labels[$condition] : __('No Condition', 'tseoindexing');
                
                    $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
                    $brand = !empty($tags) ? $tags[0] : 'No Brand';
                
                    $gtin = get_post_meta($product->get_id(), '_gtin', true) ?: 'No GTIN';
                    
                    $mpn = get_post_meta($product->get_id(), '_mpn', true) ?: 'No MPN';
                
                    $google_product_category = get_post_meta($product->get_id(), '_google_product_category', true) ?: 'No Category';

                    // Obtener los destinos seleccionados
                    $selected_destinations = get_post_meta($product->get_id(), '_google_merchant_destinations', true ) ?: array();
                    if (empty($selected_destinations)) {
                        $selected_destinations = array('free_listings'); // Free listings marcado por defecto
                    }
                    
                    // Convertir los destinos seleccionados en una lista legible
                    $destination_labels = array(
                        'shopping_ads' => __('Shopping ads', 'tseoindexing'),
                        'display_ads' => __('Display ads', 'tseoindexing'),
                        'free_listings' => __('Free listings', 'tseoindexing'),
                    );
                    $selected_destinations_labels = array_map(function($key) use ($destination_labels) {
                        return $destination_labels[$key] ?? $key;
                    }, $selected_destinations);
                    $selected_destinations_text = implode(', ', $selected_destinations_labels);

                    // Verificar si el producto está en la lista de seleccionados
                    $checked = in_array($product->get_id(), get_option('tseo_selected_products', array())) ? 'checked' : '';
                ?>
                    <tr class="<?php echo $is_valid_product ? 'valid-product' : 'invalid-product'; ?>">
                        <th class="check-column">
                            <input type="checkbox" name="selected_products[]" value="<?php echo esc_attr($product->get_id()); ?>" <?php echo $checked; ?> <?php disabled(!$is_valid_product); ?> />
                        </th>
                        <td>
                            <?php 
                            $image_id = $product->get_image_id();
                            if ($image_id) {
                                echo wp_get_attachment_image($image_id, array(45, 45));
                            } else {
                                esc_html_e('No Image', 'tseoindexing');
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo esc_html($product->get_name()); ?><br>
                            ID: <?php echo esc_html($product->get_id()); ?> | 
                            <a href="<?php echo esc_url(get_edit_post_link($product->get_id())); ?>" target="_blank">
                                <?php esc_html_e('Edit', 'tseoindexing'); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($product->get_price() . ' ' . get_woocommerce_currency()); ?></td>
                        <td><?php echo esc_html($product->is_in_stock() ? 'In Stock' : 'Out of Stock'); ?></td>
                        <td><?php echo esc_html($condition_label); ?></td>
                        <td><?php echo esc_html($brand); ?></td>
                        <td><?php echo esc_html($gtin); ?></td>
                        <td><?php echo esc_html($mpn); ?></td>
                        <td><?php echo esc_html($google_product_category); ?></td>
                        <td><?php echo esc_html($selected_destinations_text); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Textarea para mostrar los datos JSON que se van a enviar -->
        <h2><?php esc_html_e('JSON Preview', 'tseoindexing'); ?></h2>
        <textarea id="selected_products_json" rows="10" cols="100" readonly></textarea>

        <!-- Opción para seleccionar el envío automático -->
        <h2><?php esc_html_e('Send products automatically when selected', 'tseoindexing'); ?></h2>
        <p>
            <label>
                <input type="checkbox" id="tseo_send_automatically" name="tseo_send_automatically" value="1" <?php checked($send_automatically, 1); ?> />
                <span id="tseo_auto_send_status" class="<?php echo $send_automatically ? 'connect-send-success' : 'connect-send-error'; ?>">
                    <?php echo $send_automatically ? esc_html__('Activated', 'tseoindexing') : esc_html__('Deactivated', 'tseoindexing'); ?>
                </span>
            </label><br>
            <?php esc_html_e('If you select on, all selected products in the table will automatically sync with Google Merchant Center.', 'tseoindexing'); ?>
        </p>

        <div class="button-panel">
            <input type="submit" id="tseo_merchant_product_submit" name="tseo_merchant_product_submit" class="button button-primary" value="<?php esc_attr_e('Send to Merchant Center', 'tseoindexing'); ?>" <?php echo $send_automatically ? 'style="display:none;"' : ''; ?>>
        </div>
    </form>
    <?php
    // Ajax para actualizar la preferencia de envío automático
    tseoindexing_php_script_embedded_merchant_table();
}

add_action('wp_ajax_tseoindexing_get_product_data', 'tseoindexing_get_product_data');
function tseoindexing_get_product_data() {
    check_ajax_referer('tseo_get_product_data_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tseoindexing')));
    }

    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $product = wc_get_product($product_id);

        if (!$product) {
            wp_send_json_error(array('message' => __('Invalid product ID.', 'tseoindexing')));
        }

        // Limpiar HTML de la descripción y acortarla si es necesario
        $description = strip_shortcodes($product->get_description());
        $description = wp_strip_all_tags($description, true);
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);
        if (strlen($description) > 500) {
            $description = substr($description, 0, 500) . '...';
        }

        // Obtener la primera etiqueta del producto como marca
        $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
        $brand = !empty($tags) ? $tags[0] : '';

        // Obtener el GTIN, MPN y la categoría de producto de Google
        $gtin = get_post_meta($product->get_id(), '_gtin', true) ?: '';
        $mpn = get_post_meta($product->get_id(), '_mpn', true) ?: '';
        $googleProductCategory = get_post_meta($product->get_id(), '_google_product_category', true) ?: '';

        // Obtener los destinos seleccionados para el producto
        $destinations = get_post_meta($product->get_id(), '_google_merchant_destinations', true) ?: array('free_listings');

        // Construir el JSON del producto
        $product_data = array(
            'offerId' => $product->get_id(),
            'title' => $product->get_name(),
            'description' => $description,
            'link' => get_permalink($product->get_id()),
            'imageLink' => wp_get_attachment_url($product->get_image_id()),
            'price' => array(
                'value' => $product->get_price(),
                'currency' => get_woocommerce_currency()
            ),
            'availability' => $product->is_in_stock() ? 'in stock' : 'out of stock',
            'condition' => get_post_meta($product->get_id(), '_condition', true) ?: 'new',
            'brand' => $brand,
            'gtin' => $gtin,
            'mpn' => $mpn,
            'googleProductCategory' => $googleProductCategory,
            'destinations' => $destinations,
            // Añadimos los campos obligatorios
            'contentLanguage' => get_locale(), // Asumimos que el idioma de la tienda es el de WP
            'targetCountry' => 'ES', // Cambiar a la configuración real de la tienda, o extraerla de WooCommerce
        );

        wp_send_json_success(array('product' => $product_data));
    } else {
        wp_send_json_error(array('message' => __('Product ID not provided.', 'tseoindexing')));
    }
}

/**
 * Updates the option for sending merchant data automatically.
 *
 * This function is hooked to the 'wp_ajax_tseoindexing_update_send_automatically' action.
 * It checks the AJAX referer and user capabilities before updating the option.
 * If the 'send_automatically' parameter is set in the POST request, it updates the option value accordingly.
 * Returns a JSON response with success or error message.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_tseoindexing_update_send_automatically', 'tseoindexing_update_send_automatically');
function tseoindexing_update_send_automatically() {
    check_ajax_referer('tseo_merchant_auto_send_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tseoindexing')));
    }

    if (isset($_POST['send_automatically'])) {
        $send_automatically = intval($_POST['send_automatically']);
        update_option('tseo_merchant_send_automatically', $send_automatically);

        $message = $send_automatically ? __('Activated', 'tseoindexing') : __('Deactivated', 'tseoindexing');
        wp_send_json_success(array('message' => $message));
    } else {
        wp_send_json_error(array('message' => __('Invalid request.', 'tseoindexing')));
    }
}

/**
 * Save selected products via AJAX request.
 *
 * This function is hooked to the 'wp_ajax_tseoindexing_save_selected_products' action,
 * and it saves the selected products to the WordPress options table.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_tseoindexing_save_selected_products', 'tseoindexing_save_selected_products');
function tseoindexing_save_selected_products() {
    check_ajax_referer('tseo_save_selected_products_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tseoindexing')));
    }

    if (isset($_POST['selected_products'])) {
        $selected_products = array_map('intval', $_POST['selected_products']);
        update_option('tseo_selected_products', $selected_products);

        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => __('Invalid request.', 'tseoindexing')));
    }
}

/**
 * Sends products to Google Merchant Center.
 *
 * This function sends an array of products to Google Merchant Center using the Google_Service_ShoppingContent API.
 * It performs necessary data cleaning and validation before sending the products.
 *
 * @param array $products An array of products to be sent to Google Merchant Center.
 *                        Each product should be an associative array with the following keys:
 *                        - offerId: The unique identifier for the product.
 *                        - title: The title of the product.
 *                        - description: The description of the product.
 *                        - link: The URL link to the product.
 *                        - imageLink: The URL link to the product image.
 *                        - price: An associative array with 'value' and 'currency' keys representing the price of the product.
 *                        - availability: The availability status of the product.
 *                        - condition: The condition of the product.
 *                        - brand: The brand of the product.
 *                        - gtin: The GTIN (Global Trade Item Number) of the product.
 *                        - mpn: The MPN (Manufacturer Part Number) of the product.
 *                        - googleProductCategory: The Google product category of the product.
 *                        - destinations: An array of destination names for the product.
 *
 * @return void
 */
function send_products_to_merchant_center($products) {
    $client = tseoindexing_merchant_get_google_client();
    if (!$client) {
        echo '<div class="error"><p>' . esc_html__('Unable to connect to Google Merchant Center.', 'tseoindexing') . '</p></div>';
        return;
    }

    $service = new Google_Service_ShoppingContent($client);
    $merchant_center_id = get_option('tseo_merchant_center_id', '');

    foreach ($products as $product) {
        // Limpiar y ajustar la descripción
        $description = $product['description'];
        
        // Aplicar la misma lógica de limpieza que antes
        $description = strip_shortcodes($description);
        $description = wp_strip_all_tags($description, true);
        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);
        if (strlen($description) > 500) {
            $description = substr($description, 0, 500) . '...';
        }
        
        // Asignar la descripción limpia de vuelta al producto
        $product['description'] = $description;

        // Asegurarse de que los campos obligatorios no contengan valores inválidos
        $product['gtin'] = $product['gtin'] ?: '';
        $product['mpn'] = $product['mpn'] ?: '';
        $product['googleProductCategory'] = $product['googleProductCategory'] ?: '';

        try {
            // Crear el objeto de producto para la API
            $api_product = new Google_Service_ShoppingContent_Product();
            $api_product->setOfferId($product['offerId']);
            $api_product->setTitle($product['title']);
            $api_product->setDescription($product['description']);
            $api_product->setLink($product['link']);
            $api_product->setImageLink($product['imageLink']);
            $api_product->setPrice(new Google_Service_ShoppingContent_Price(array(
                'value' => $product['price']['value'],
                'currency' => $product['price']['currency']
            )));
            $api_product->setAvailability($product['availability']);
            $api_product->setCondition($product['condition']);
            $api_product->setBrand($product['brand']);
            $api_product->setGtin($product['gtin']);
            $api_product->setMpn($product['mpn']);
            $api_product->setGoogleProductCategory($product['googleProductCategory']);
            $api_product->setDestinations(array_map(function($destination) {
                return array('destinationName' => $destination, 'destinationStatus' => 'active');
            }, $product['destinations']));

            // Enviar el producto a Google Merchant Center
            $service->products->insert($merchant_center_id, $api_product);
        } catch (Exception $e) {
            echo '<div class="error"><p>' . esc_html__('Error sending product to Google Merchant Center: ', 'tseoindexing') . esc_html($e->getMessage()) . '</p></div>';
        }
    }
}


/**
 * Embeds the PHP script for updating the send automatically option via AJAX.
 *
 * This function embeds the PHP script for updating the send automatically option via AJAX.
 * It outputs the script directly to the page footer.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_tseoindexing_merchant_product_submit', 'tseoindexing_merchant_product_submit');
function tseoindexing_merchant_product_submit() {
    check_ajax_referer('tseo_merchant_auto_send_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tseoindexing')));
    }

    // Recuperar los IDs de los productos seleccionados
    if (isset($_POST['selected_products'])) {
        $selected_product_ids = array_map('intval', $_POST['selected_products']);
        
        // Obtener los datos de cada producto seleccionado
        $products = array();
        foreach ($selected_product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $description = strip_shortcodes($product->get_description());
                $description = wp_strip_all_tags($description, true);
                $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                $description = preg_replace('/\s+/', ' ', $description);
                $description = trim($description);
                if (strlen($description) > 500) {
                    $description = substr($description, 0, 500) . '...';
                }

                $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
                $brand = !empty($tags) ? $tags[0] : '';

                // Obtener el idioma de la instalación de WordPress
                $contentLanguage = substr(get_locale(), 0, 2);

                // Obtener el país de destino desde la configuración de WooCommerce
                $targetCountry = get_option('woocommerce_default_country');
                if (strpos($targetCountry, ':') !== false) {
                    $targetCountry = explode(':', $targetCountry)[0];
                }

                $products[] = array(
                    'offerId' => $product->get_id(),
                    'title' => $product->get_name(),
                    'description' => $description,
                    'link' => get_permalink($product->get_id()),
                    'imageLink' => wp_get_attachment_url($product->get_image_id()),
                    'price' => array(
                        'value' => $product->get_price(),
                        'currency' => get_woocommerce_currency()
                    ),
                    'availability' => $product->is_in_stock() ? 'in stock' : 'out of stock',
                    'condition' => get_post_meta($product->get_id(), '_condition', true) ?: 'new',
                    'brand' => $brand,
                    'gtin' => get_post_meta($product->get_id(), '_gtin', true) ?: '',
                    'mpn' => get_post_meta($product->get_id(), '_mpn', true) ?: '',
                    'googleProductCategory' => get_post_meta($product->get_id(), '_google_product_category', true) ?: '',
                    'contentLanguage' => $contentLanguage, // Añadir contentLanguage
                    'targetCountry' => $targetCountry, // Añadir targetCountry
                    'channel' => 'online', // Añadir channel, asumiendo 'online'
                    'destinations' => get_post_meta($product->get_id(), '_google_merchant_destinations', true) ?: array('free_listings')
                );
            }
        }

        // Llamar a la función que envía los productos a Google Merchant Center
        send_products_to_merchant_center($products);

        wp_send_json_success(array('message' => __('Products successfully sent to Google Merchant Center.', 'tseoindexing')));
    } else {
        wp_send_json_error(array('message' => __('No products selected.', 'tseoindexing')));
    }
}

