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
                    <th><?php esc_html_e('Category', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Brand', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('GTIN', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('MPN', 'tseoindexing'); ?></th>
                    <th><?php esc_html_e('Google Cat', 'tseoindexing'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1
                );

                $products = get_posts($args);

                // Recuperar productos seleccionados anteriormente
                $selected_products = get_option('tseo_selected_products', array());

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
                
                    $category_names = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
                    $categories = implode(', ', $category_names);
                
                    $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
                    $brand = !empty($tags) ? $tags[0] : 'No Brand';
                
                    $gtin = get_post_meta($product->get_id(), '_gtin', true) ?: 'No GTIN';
                    
                    $mpn = get_post_meta($product->get_id(), '_mpn', true) ?: 'No MPN';
                
                    $google_product_category = get_post_meta($product->get_id(), '_google_product_category', true) ?: 'No Category';
                
                    // Verificar si el producto está en la lista de seleccionados
                    $checked = in_array($product->get_id(), $selected_products) ? 'checked' : '';
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
                        <td><?php echo esc_html($categories); ?></td>
                        <td><?php echo esc_html($brand); ?></td>
                        <td><?php echo esc_html($gtin); ?></td>
                        <td><?php echo esc_html($mpn); ?></td>
                        <td><?php echo esc_html($google_product_category); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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

/**
 * Updates the option for sending data automatically and returns a JSON response.
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