<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Merchant Center Product List
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_merchant_product_list() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to perform this action.', 'tseoindexing'));
    }

    // Asegurar que $paged esté definido
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    // Obtener el valor de send_automatically de las opciones de WordPress
    $send_automatically = get_option('tseoindexing_merchant_send_automatically', false);

    // Verificar el estado de la conexión
    $is_connected = tseoindexing_merchant_verify_connection();
    $connection_status = $is_connected ? esc_html__('Connected', 'tseoindexing') : esc_html__('Disconnected', 'tseoindexing');
    $connection_class = $is_connected ? 'connect-success' : 'connect-error';

    ?>
    <form method="post" action="">
        <!-- Mostrar el estado de la conexión -->
        <p>
            <?php esc_html_e('Connection Status: ', 'tseoindexing'); ?>
            <span class="<?php echo $connection_class; ?>"><?php echo $connection_status; ?></span> 
        </p>

        <?php wp_nonce_field('tseoindexing_merchant_product_nonce'); ?>
        <h2><?php esc_html_e('Select Products to Send to Google Merchant Center', 'tseoindexing'); ?></h2>
        <p><?php esc_html_e('Only products that meet the Google Merchant Center publication criteria can be selected for submission.', 'tseoindexing'); ?></p>

        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" class="submit" /></th>
                    <th style="width:45px"><?php esc_html_e('Image', 'tseoindexing'); ?></th>
                    <th style="width:35%"><?php esc_html_e('Product', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Price', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Stock', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Condition', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Brand', 'tseoindexing'); ?></th>
                    <!--<th><?php //esc_html_e('GTIN', 'tseoindexing'); ?></th>
                    <th><?php //esc_html_e('MPN', 'tseoindexing'); ?></th>-->
                    <th><?php esc_html_e('Google Cat', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Dest', 'tseoindexing'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 10,
                    'paged' => $paged,
                );

                $product_query = new WP_Query($args);

                foreach ($product_query->posts as $product_post):
                    $product = wc_get_product($product_post->ID);
                
                    $is_valid_product = 
                        !empty(wp_get_attachment_url($product->get_image_id())) &&
                        !empty(get_post_meta($product->get_id(), '_google_merchant_title', true)) &&
                        !empty(get_post_meta($product->get_id(), '_google_merchant_description', true)) &&
                        is_numeric($product->get_price()) && $product->get_price() > 0 &&
                        !empty(get_post_meta($product->get_id(), '_condition', true));
                
                    $condition_labels = array(
                        'new' => esc_html__('New', 'tseoindexing'),
                        'refurbished' => esc_html__('Refurbished', 'tseoindexing'),
                        'used' => esc_html__('Used', 'tseoindexing'),
                    );
                    $condition = get_post_meta($product->get_id(), '_condition', true);
                    $condition_label = isset($condition_labels[$condition]) ? $condition_labels[$condition] : esc_html__('No Condition', 'tseoindexing');

                    $brand = get_post_meta($product->get_id(), '_google_product_brand', true) ?: 'No Brand';
                    /*
                    $gtin = get_post_meta($product->get_id(), '_gtin', true) ?: 'No GTIN';
                    $mpn = get_post_meta($product->get_id(), '_mpn', true) ?: 'No MPN';
                    */
                    $google_product_category = get_post_meta($product->get_id(), '_google_product_category', true) ?: 'No Category';

                    // Obtener los destinos seleccionados
                    $selected_destinations = get_post_meta($product->get_id(), '_google_merchant_destinations', true ) ?: array();
                    if (empty($selected_destinations)) {
                        $selected_destinations = array('free_listings'); // Free listings marcado por defecto
                    }
                    
                    // Convertir los destinos seleccionados en una lista legible
                    $destination_labels = array(
                        'shopping_ads' => esc_html__('Shopping ads', 'tseoindexing'),
                        'display_ads' => esc_html__('Display ads', 'tseoindexing'),
                        'free_listings' => esc_html__('Free listings', 'tseoindexing'),
                    );
                    $selected_destinations_labels = array_map(function($key) use ($destination_labels) {
                        return $destination_labels[$key] ?? $key;
                    }, $selected_destinations);
                    $selected_destinations_text = implode(', ', $selected_destinations_labels);

                    // Verificar si el producto está en la lista de seleccionados
                    $checked = in_array($product->get_id(), get_option('tseoindexing_selected_products', array())) ? 'checked' : '';
                ?>
                    <tr class="<?php echo $is_valid_product ? 'valid-product' : 'invalid-product'; ?>">
                        <th class="check-column">
                            <input type="checkbox" class="submit" name="selected_products[]" value="<?php echo esc_attr($product->get_id()); ?>" <?php echo $checked; ?> <?php disabled(!$is_valid_product); ?> />
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
                            <?php echo esc_html(get_post_meta($product->get_id(), '_google_merchant_title', true)); ?><br>
                            ID: <?php echo esc_html($product->get_id()); ?> | 
                            <a href="<?php echo esc_url(get_edit_post_link($product->get_id())); ?>" target="_blank">
                                <?php esc_html_e('Edit', 'tseoindexing'); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($product->get_price() . ' ' . get_woocommerce_currency()); ?></td>
                        <td><?php echo esc_html($product->is_in_stock() ? 'In Stock' : 'Out of Stock'); ?></td>
                        <td><?php echo esc_html($condition_label); ?></td>
                        <td><?php echo esc_html($brand); ?></td>
                        <!--<td><?php //echo esc_html($gtin); ?></td>
                        <td><?php //echo esc_html($mpn); ?></td>-->
                        <td><?php echo esc_html($google_product_category); ?></td>
                        <td><?php echo esc_html($selected_destinations_text); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="tseoindexing-pagination">
            <?php
            $total_pages = $product_query->max_num_pages;
            if ($total_pages > 1) {
                $current_page = max(1, $paged);
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text' => '«',
                    'next_text' => '»',
                ));
            }
            ?>
        </div>

        <!-- Textarea para mostrar los datos JSON que se van a enviar -->
        <h2><?php esc_html_e('JSON Preview', 'tseoindexing'); ?></h2>
        <?php esc_html_e('Advanced users: Verify that the data to be sent is technically correct. This JSON mirrors the Google Merchant Center Content API.', 'tseoindexing'); ?>
        <textarea id="selected_products_json" rows="10" cols="100" readonly></textarea>

        <!-- Opción para seleccionar el envío automático -->
        <h2><?php esc_html_e('Send products automatically when selected', 'tseoindexing'); ?></h2>
        <p>
            <label>
                <input type="checkbox" class="submit" id="tseo_send_automatically" name="tseo_send_automatically" value="1" <?php checked($send_automatically, 1); ?> />
                <span id="tseo_auto_send_status" class="<?php echo $send_automatically ? 'connect-send-success' : 'connect-send-error'; ?>">
                    <?php echo $send_automatically ? esc_html__('Activated', 'tseoindexing') : esc_html__('Deactivated', 'tseoindexing'); ?>
                </span>
            </label><br>
            <?php esc_html_e('If you select on, all selected products in the table will automatically sync with Google Merchant Center.', 'tseoindexing'); ?>
        </p>

        <div class="button-panel">
            <input type="submit" id="tseo_merchant_product_submit" name="tseo_merchant_product_submit" class="button button-primary submit" value="<?php esc_attr_e('Send to Merchant Center', 'tseoindexing'); ?>" <?php echo $send_automatically ? 'style="display:none;"' : ''; ?>>
        </div>

        <h2><?php esc_html_e('API Response:', 'tseoindexing'); ?></h2>
        <pre id="api_response_display" style="width: 100%;"></pre>
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('form').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var data = form.serialize();
                data += '&action=tseoindexing_merchant_product_submit';

                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        $('#api_response_display').html('<span class="success"><?php echo esc_html__('Products successfully sent to Google Merchant Center.', 'tseoindexing'); ?></span>');
                    } else {
                        var errorMessage = response.data && response.data.message ? response.data.message : '<?php echo esc_html__('An error occurred. Please try again.', 'tseoindexing'); ?>';
                        $('#api_response_display').html('<span class="error">' + errorMessage + '</span>');
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    $('#api_response_display').html('<span class="error"><?php echo esc_html__('An error occurred during the request. Please check your network connection and try again.', 'tseoindexing'); ?></span>');
                });
            });
        });
    </script>
    <?php
    // Ajax para actualizar la preferencia de envío automático
    tseoindexing_php_script_embedded_merchant_table();
}

function tseoindexing_send_selected_products_to_merchant_center($products) {
    $product_count = count($products);

    if ($product_count == 0) {
        echo '<div class="error"><p>' . esc_html__('No products to send.', 'tseoindexing') . '</p></div>';
        return;
    }

    if ($product_count == 1) {
        return tseoindexing_send_products_to_merchant_center($products);
    } else {
        return tseoindexing_send_products_to_merchant_center_in_batch($products);
    }
}

/**
 * Sends products uno a uno to Google Merchant Center.
 *
 * @return void
 */
function tseoindexing_send_products_to_merchant_center($products) {
    $client = tseoindexing_merchant_get_google_client();
    if (!$client) {
        echo '<div class="error"><p>' . esc_html__('Unable to connect to Google Merchant Center.', 'tseoindexing') . '</p></div>';
        return;
    }

    $service = new Google_Service_ShoppingContent($client);
    $merchant_center_id = get_option('tseoindexing_merchant_center_id', '');
    $response_data = array();

    $content_language = substr(get_locale(), 0, 2);
    $target_country = WC()->countries->get_base_country();

    foreach ($products as $product) {
        try {
            $api_product = new Google_Service_ShoppingContent_Product();
            $api_product->setOfferId($product['offerId']);
            $api_product->setTitle($product['title']);
            $api_product->setDescription($product['description']);
            $api_product->setLink($product['link']);
            $api_product->setImageLink($product['imageLink']);
            if (!empty($product['additionalImageLinks'])) {
                $api_product->setAdditionalImageLinks($product['additionalImageLinks']);
            }
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
            $api_product->setContentLanguage($content_language);
            $api_product->setTargetCountry($target_country);
            $api_product->setChannel('online');
            $api_product->includedDestinations = $product['destinations'];

            // Asignar los campos adicionales directamente a las propiedades del objeto
            if (!empty($product['color'])) {
                $api_product->setColor($product['color']);
            }
            if (!empty($product['size'])) {
                // Para tamaños, se espera un array de strings
                $api_product->setSizes([$product['size']]);
            }
            if (!empty($product['gender'])) {
                $api_product->setGender($product['gender']);
            }
            if (!empty($product['ageGroup'])) {
                $api_product->setAgeGroup($product['ageGroup']);
            }

            $response = $service->products->insert($merchant_center_id, $api_product);
            $response_data[] = $response;
        } catch (Exception $e) {
            echo '<div class="error"><p>' . esc_html__('Error sending product to Google Merchant Center: ', 'tseoindexing') . esc_html($e->getMessage()) . '</p></div>';
            error_log('Error sending product to Google Merchant Center: ' . $e->getMessage());
        }
    }

    return $response_data;
}

/**
 * Sends products multiples to Google Merchant Center.
 *
 * @return void
 */
function tseoindexing_send_products_to_merchant_center_in_batch($products) {
    $client = tseoindexing_merchant_get_google_client();
    if (!$client) {
        return array('error' => 'Unable to connect to Google Merchant Center.');
    }

    $service = new Google_Service_ShoppingContent($client);
    $merchant_center_id = get_option('tseoindexing_merchant_center_id', '');
    $response_data = array();

    $content_language = substr(get_locale(), 0, 2);
    $target_country = WC()->countries->get_base_country();

    $batch_request_entries = [];
    $batch_id = 0;

    foreach ($products as $product) {
        try {
            $api_product = new Google_Service_ShoppingContent_Product();
            $api_product->setOfferId($product['offerId']);
            $api_product->setTitle($product['title']);
            $api_product->setDescription($product['description']);
            $api_product->setLink($product['link']);
            $api_product->setImageLink($product['imageLink']);
            if (!empty($product['additionalImageLinks'])) {
                $api_product->setAdditionalImageLinks($product['additionalImageLinks']);
            }
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
            $api_product->setContentLanguage($content_language);
            $api_product->setTargetCountry($target_country);
            $api_product->setChannel('online');
            $api_product->includedDestinations = $product['destinations'];

            if (!empty($product['color'])) {
                $api_product->setColor($product['color']);
            }
            if (!empty($product['size'])) {
                $api_product->setSizes([$product['size']]);
            }
            if (!empty($product['gender'])) {
                $api_product->setGender($product['gender']);
            }
            if (!empty($product['ageGroup'])) {
                $api_product->setAgeGroup($product['ageGroup']);
            }

            $batch_request_entry = new Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
            $batch_request_entry->setBatchId($batch_id++);
            $batch_request_entry->setMerchantId($merchant_center_id);
            $batch_request_entry->setMethod('insert');
            $batch_request_entry->setProduct($api_product);

            $batch_request_entries[] = $batch_request_entry;
        } catch (Exception $e) {
            $response_data[] = array('error' => 'Error preparing product for batch request: ' . $e->getMessage());
        }
    }

    if (!empty($batch_request_entries)) {
        $batch_request = new Google_Service_ShoppingContent_ProductsCustomBatchRequest();
        $batch_request->setEntries($batch_request_entries);

        try {
            $batch_response = $service->products->custombatch($batch_request);
            foreach ($batch_response->getEntries() as $response) {
                $response_data[] = $response;
            }
            return $response_data;
        } catch (Exception $e) {
            return array('error' => 'Error sending batch request to Google Merchant Center: ' . $e->getMessage());
        }
    }

    return $response_data;
}

/**
 * Embeds the PHP script for updating the send automatically option via AJAX.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_tseoindexing_merchant_product_submit', 'tseoindexing_merchant_product_submit');
function tseoindexing_merchant_product_submit() {
    // Eliminar o comentar los logs innecesarios
    // error_log('Function tseoindexing_merchant_product_submit started.');

    if (!check_ajax_referer('tseoindexing_merchant_product_nonce', '_wpnonce', false)) {
        wp_send_json_error(array('message' => esc_html__('Nonce verification failed.', 'tseoindexing')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'tseoindexing')));
    }

    if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
        $selected_product_ids = array_map('intval', $_POST['selected_products']);

        $products = array();
        foreach ($selected_product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $description = get_post_meta($product->get_id(), '_google_merchant_description', true) ?: '';

                $contentLanguage = substr(get_locale(), 0, 2);
                $targetCountry = get_option('woocommerce_default_country');
                if (strpos($targetCountry, ':') !== false) {
                    $targetCountry = explode(':', $targetCountry)[0];
                }

                // Obtener los IDs de las imágenes de la galería
                $gallery_image_ids = $product->get_gallery_image_ids();
                // Limitar a 10 imágenes y convertir IDs a URLs
                $additional_image_links = array_map('wp_get_attachment_url', array_slice($gallery_image_ids, 0, 10));

                $products[] = array(
                    'offerId' => $product->get_id(),
                    'title' => get_post_meta($product->get_id(), '_google_merchant_title', true) ?: '',
                    'description' => $description,
                    'link' => get_permalink($product->get_id()),
                    'imageLink' => wp_get_attachment_url($product->get_image_id()),
                    'additionalImageLinks' => $additional_image_links,
                    'price' => array(
                        'value' => $product->get_price(),
                        'currency' => get_woocommerce_currency()
                    ),
                    'availability' => $product->is_in_stock() ? 'in stock' : 'out of stock',
                    'condition' => get_post_meta($product->get_id(), '_condition', true) ?: 'new',
                    'brand' => get_post_meta($product->get_id(), '_google_product_brand', true) ?: '',
                    'gtin' => get_post_meta($product->get_id(), '_gtin', true) ?: '',
                    'mpn' => get_post_meta($product->get_id(), '_mpn', true) ?: '',
                    'googleProductCategory' => get_post_meta($product->get_id(), '_google_product_category', true) ?: '',
                    'contentLanguage' => $contentLanguage,
                    'targetCountry' => $targetCountry,
                    'channel' => 'online',
                    'destinations' => get_post_meta($product->get_id(), '_google_merchant_destinations', true) ?: array('free_listings'),
                    'color' => get_post_meta($product->get_id(), '_google_color', true) ?: '',
                    'size' => get_post_meta($product->get_id(), '_google_size', true) ?: '',
                    'gender' => get_post_meta($product->get_id(), '_google_gender', true) ?: '',
                    'ageGroup' => get_post_meta($product->get_id(), '_google_age_group', true) ?: ''
                );
            }
        }

        // Eliminar o comentar el log de los productos
        // error_log('Products array: ' . print_r($products, true));

        $api_response = tseoindexing_send_selected_products_to_merchant_center($products);

        // Eliminar o comentar el log de la respuesta de la API
        // error_log('API response: ' . print_r($api_response, true));

        wp_send_json_success(array('message' => esc_html__('Products successfully sent to Google Merchant Center.', 'tseoindexing'), 'api_response' => $api_response));
    } else {
        wp_send_json_error(array('message' => esc_html__('No products selected or invalid format.', 'tseoindexing')));
    }
}

/**
 * Updates the option for sending merchant data automatically.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_tseoindexing_update_send_automatically', 'tseoindexing_update_send_automatically');
function tseoindexing_update_send_automatically() {
    check_ajax_referer('tseoindexing_merchant_product_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'tseoindexing')));
    }

    if (isset($_POST['send_automatically'])) {
        $send_automatically = intval($_POST['send_automatically']);
        update_option('tseoindexing_merchant_send_automatically', $send_automatically);

        $message = $send_automatically ? esc_html__('Activated', 'tseoindexing') : esc_html__('Deactivated', 'tseoindexing');
        wp_send_json_success(array('message' => $message));
    } else {
        wp_send_json_error(array('message' => esc_html__('Invalid request.', 'tseoindexing')));
    }
}

/**
 * Save selected products via AJAX request.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_tseoindexing_save_selected_products', 'tseoindexing_save_selected_products');
function tseoindexing_save_selected_products() {
    check_ajax_referer('tseo_save_selected_products_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'tseoindexing')));
    }

    if (isset($_POST['selected_products'])) {
        $selected_products = array_map('intval', $_POST['selected_products']);
        update_option('tseoindexing_selected_products', $selected_products);

        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => esc_html__('Invalid request.', 'tseoindexing')));
    }
}