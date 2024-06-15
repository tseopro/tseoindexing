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
 * Deletes TSEO URLs via AJAX request.
 *
 * This function is hooked to the 'wp_ajax_delete_tseo_urls' action and is used to delete TSEO URLs from the database.
 * It checks the AJAX referer, user capabilities, and receives the URLs to delete from the AJAX request.
 * If the user has the necessary permissions and URLs are provided, it deletes the URLs from the database.
 *
 * @since 1.0.0
 */
add_action('wp_ajax_delete_tseo_urls', 'delete_tseo_urls_callback');
function delete_tseo_urls_callback() {
    check_ajax_referer('delete_tseo_urls_nonce', '_ajax_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('No tienes permiso para realizar esta acción.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'tseo_indexing_links';

    // Recibir las URLs a eliminar desde la solicitud AJAX
    $urls_to_delete = isset($_POST['urls']) ? $_POST['urls'] : [];

    if (!empty($urls_to_delete)) {
        // Eliminar las URLs de la base de datos
        foreach ($urls_to_delete as $url) {
            $wpdb->delete($table_name, ['url' => $url], ['%s']);
        }

        wp_send_json_success('URLs eliminadas exitosamente.');
    } else {
        wp_send_json_error('No se recibió ninguna URL para eliminar.');
    }
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
        'tseo-indexing_page_tseoindexing-console',
        'tseo-indexing_page_tseoindexing-tools',
        'tseo-indexing_page_tseoindexing-merchant-center',
        'tseo-indexing_page_tseoindexing-merchant-center_list'
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
            <?php esc_html_e('Warning: This is a version of TSEO Indexing in development. Use at your own risk.', 'tseoindexing'); ?>
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
        <li class="success"><?php esc_html_e('Important: Once the table is configured with the URLs to be published/updated or removed, go to the Console tab to send the batch requests. These will be processed until the quota limit is reached. Increase the quota in Google Cloud API Indexing if necessary.', 'tseoindexing'); ?></li>
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
        <?php esc_html_e('URLs (one per line, up to 100 for Google Indexing):', 'tseoindexing'); ?>
        
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
    <script>
        let timeout = null;
        let lastEventWasPaste = false;
        document.getElementById('tseo_urls').addEventListener('paste', function(e) {
            lastEventWasPaste = true;
        });
        document.getElementById('tseo_urls').addEventListener('input', function(e) {
            if (timeout !== null) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(function() {
                let text = e.target.value;
                let urlPattern = /(http|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/g;
                let lines = text.split('\n');
                for (let i = 0; i < lines.length; i++) {
                    if (lines[i].trim() !== '' && !lines[i].startsWith('http') && !urlPattern.test(lines[i])) {
                        if (lastEventWasPaste) {
                            e.target.value = '<?php esc_html_e('First clear the list of URLs in the Tools tab and try again here.', 'tseoindexing'); ?>';
                        } else {
                            e.target.value = '<?php esc_html_e('Please enter a valid URL.', 'tseoindexing'); ?>';
                        }
                        return;
                    }
                }
                timeout = null;
                lastEventWasPaste = false;
            }, 500);
        });
    </script>
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
 * TSEO PRO TSEO <script> embedded in the links table
 *
 * This function generates a JavaScript script that handles the updating and deleting of URLs.
 * It attaches event listeners to checkboxes and a delete button, and makes AJAX requests to update or delete URLs.
 * The function is embedded in a PHP file located at /c:/laragon/www/tseo/wp-content/plugins/tseoindexing/includes/tseoindexing-settings.php.
 * 
 * @package TSEOIndexing
 * @version 1.0.0
 * 
 */
function tseoindexing_php_script_embedded_links_table() {
?>
    <script>
        // Update or remove URLs
        document.addEventListener("DOMContentLoaded", function() {
            var checkboxes = document.querySelectorAll('.checkbox input[type="checkbox"]');
            
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener("change", function() {
                    var label = this.nextElementSibling;
                    var action = this.checked ? 'update' : 'remove';
                    label.innerText = this.checked ? "<?php echo esc_html__('Update', 'tseoindexing'); ?>" : "<?php echo esc_html__('Add', 'tseoindexing'); ?>";
                    label.classList.toggle("update", this.checked);
                    label.classList.toggle("add", !this.checked);

                    // Enviar solicitud AJAX para actualizar el estado de la URL
                    jQuery.post(ajaxurl, {
                        action: 'update_tseo_url',
                        url: this.value,
                        action_type: action,
                        _ajax_nonce: '<?php echo wp_create_nonce('update_tseo_url_nonce'); ?>'
                    }, function(response) {
                        if (!response.success) {
                            alert('Error: ' + response.data);
                        } else {
                            // Encontrar la fila (.flex-row) más cercana
                            var flexRow = checkbox.closest('.flex-row');
                            var statusCell = flexRow.querySelector('.status');
                            
                            // Actualizar el contenido y la clase de la celda de estado
                            statusCell.innerText = response.data.type;
                            statusCell.className = 'flex-cell status ' + (response.data.type === 'URL_UPDATED' ? 'status-updated' : (response.data.type === 'URL_DELETED' ? 'status-deleted' : 'status-null'));

                            // Lógica adicional para mostrar u ocultar el checkbox de eliminación
                            var deleteCellId = 'delete-' + checkbox.value;
                            var deleteCell = document.getElementById(deleteCellId);

                            if (deleteCell) {
                                // Mostrar el checkbox de eliminación si el tipo es URL_UPDATED o URL_DELETED
                                if (response.data.type === 'URL_UPDATED' || response.data.type === 'URL_DELETED') {
                                    deleteCell.innerHTML = '<input type="checkbox" name="urls_to_delete[]" value="' + checkbox.value + '">';
                                } else {
                                    deleteCell.innerHTML = '';
                                }
                            }
                        }
                    });
                });
            });
        });
        // Delete selected URLs
        document.addEventListener("DOMContentLoaded", function() {
            var deleteButton = document.getElementById('delete-selected');
            deleteButton.addEventListener("click", function() {
                var checkboxes = document.querySelectorAll('input[name="urls_to_delete[]"]:checked');
                var urlsToDelete = [];
                checkboxes.forEach(function(checkbox) {
                    urlsToDelete.push(checkbox.value);
                });
                if (urlsToDelete.length > 0) {
                    if (confirm('<?php echo esc_html__('Do you want to remove these URLs from the list?', 'tseoindexing'); ?>')) {
                        jQuery.post(ajaxurl, {
                            action: 'delete_tseo_urls',
                            urls: urlsToDelete,
                            _ajax_nonce: '<?php echo wp_create_nonce('delete_tseo_urls_nonce'); ?>'
                        }, function(response) {
                            if (response.success) {
                                alert('<?php echo esc_html__('URLs successfully deleted!', 'tseoindexing'); ?>');
                                location.reload();
                            } else {
                                alert('<?php echo esc_html__('Error deleting URLs: ', 'tseoindexing'); ?>' + response.data);
                            }
                        });
                    }
                } else {
                    alert('<?php echo esc_html__('Please select at least one URL to delete.', 'tseoindexing'); ?>');
                }
            });
        });
    </script>
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
    <a href="<?php echo esc_url('https://console.cloud.google.com/apis/api/indexing.googleapis.com/quotas'); ?>" target="_blank">
        <?php esc_html_e('Google API Remaining Quota:', 'tseoindexing'); ?>
    </a>
    <ul class="table_console_info">
        <li>PublishRequestsPerDayPerProject = <?php echo esc_html($quota_info['PublishRequestsPerDayPerProject']); ?></li>
        <li>MetadataRequestsPerMinutePerProject = <?php echo esc_html($quota_info['MetadataRequestsPerMinutePerProject']); ?></li>
        <li>RequestsPerMinutePerProject = <?php echo esc_html($quota_info['RequestsPerMinutePerProject']); ?></li>
    </ul>
    <?php
}


/**
 * TSEO PRO TSEO Merchant Center - Scripts
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_php_script_embedded_merchant_table() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Manejar el cambio de estado de la opción de envío automático
            $('#tseo_send_automatically').on('change', function() {
                var isChecked = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: ajaxurl, // Variable global de WordPress para las solicitudes AJAX
                    type: 'POST',
                    data: {
                        action: 'tseoindexing_update_send_automatically',
                        send_automatically: isChecked,
                        _wpnonce: '<?php echo wp_create_nonce("tseoindexing_merchant_product_nonce"); ?>' // Generar un nonce para seguridad
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#tseo_auto_send_status').text(response.data.message);

                            // Cambiar la clase del estado de activado/desactivado
                            if (isChecked) {
                                $('#tseo_auto_send_status').removeClass('connect-send-error').addClass('connect-send-success');
                                $('#tseo_merchant_product_submit').hide();
                            } else {
                                $('#tseo_auto_send_status').removeClass('connect-send-success').addClass('connect-send-error');
                                $('#tseo_merchant_product_submit').show();
                            }
                        } else {
                            alert(response.data.message);
                        }
                    }
                });
            });

            // Manejar la selección y deselección de productos
            $('.check-column input[type="checkbox"]').on('change', function() {
                updateSelectedProductsJson();
                var selectedProducts = [];
                $('.check-column input[type="checkbox"]:checked').each(function() {
                    selectedProducts.push($(this).val());
                });

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tseoindexing_save_selected_products',
                        selected_products: selectedProducts,
                        _wpnonce: '<?php echo wp_create_nonce("tseo_save_selected_products_nonce"); ?>'
                    }
                });
            });

            // Función para actualizar la vista previa en JSON
            function updateSelectedProductsJson() {
                var selectedProducts = [];
                $('input.product-checkbox:checked').each(function() {
                    var productId = $(this).val();

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tseoindexing_get_product_data',
                            product_id: productId,
                            _wpnonce: '<?php echo wp_create_nonce("tseo_get_product_data_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                selectedProducts.push(response.data.product);
                            } else {
                                console.error('Error:', response.data.message);
                            }

                            // Actualizar el textarea con el JSON formateado
                            $('#selected_products_json').val(JSON.stringify(selectedProducts, null, 4));
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                });

                // Si no hay productos seleccionados, limpiar el textarea
                if (selectedProducts.length === 0) {
                    $('#selected_products_json').val('');
                }
            }

            // Agregar la clase .product-checkbox a los checkboxes en la tabla de productos
            $('input[name="selected_products[]"]').addClass('product-checkbox');

            // Actualizar JSON al cambiar la selección de los productos
            $('.product-checkbox').on('change', updateSelectedProductsJson);
            
            // Inicializar con el estado actual
            updateSelectedProductsJson();
        });
    </script>
    <?php
}

/**
 * TSEO PRO TSEO Merchant Center - Retrieves product data via AJAX request.
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
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

        // Obtener la descripción específica para Google Merchant Center desde el meta del producto
        $description = get_post_meta($product->get_id(), '_google_merchant_description', true);

        // Si la descripción específica no está definida, dejarla vacía o manejar el caso según lo requieras
        if (empty($description)) {
            $description = '';
        }

        // Obtener la primera etiqueta del producto como marca
        //$tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
        //$brand = !empty($tags) ? $tags[0] : '';

        // Obtener el GTIN, MPN y la categoría de producto de Google
        $gtin = get_post_meta($product->get_id(), '_gtin', true) ?: '';
        $mpn = get_post_meta($product->get_id(), '_mpn', true) ?: '';
        $brand = get_post_meta($product->get_id(), '_google_product_brand', true) ?: '';
        $googleProductCategory = get_post_meta($product->get_id(), '_google_product_category', true) ?: '';

        // Obtener los destinos seleccionados para el producto
        $destinations = get_post_meta($product->get_id(), '_google_merchant_destinations', true) ?: array('free_listings');

        // Obtener el idioma configurado en WordPress
        $content_language = substr(get_locale(), 0, 2);
        // Obtener el país objetivo de WooCommerce
        $target_country = WC()->countries->get_base_country();

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
            'contentLanguage' => $content_language, // Ajustado a ISO 639-1
            'targetCountry' => $target_country // Añadido campo targetCountry
        );

        wp_send_json_success(array('product' => $product_data));
    } else {
        wp_send_json_error(array('message' => __('Product ID not provided.', 'tseoindexing')));
    }
}

/**
 * TSEO PRO TSEO Merchant Center Add Fields WooCommerce
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
// Añadir una nueva pestaña en la interfaz de producto
function tseoindexing_add_merchant_center_tab($tabs) {
    $tabs['merchant_center'] = array(
        'label'    => __('TSEO Merchant', 'tseoindexing'),
        'target'   => 'merchant_center_options',
        'class'    => array('show_if_simple', 'show_if_variable'),
        'priority' => 60,
    );
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'tseoindexing_add_merchant_center_tab');

// Mostrar los campos personalizados en la nueva pestaña
function tseoindexing_add_merchant_center_fields() {
    echo '<div id="merchant_center_options" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    echo '<h2>' . __('Google Merchant Center Fields by TSEO Indexing', 'tseoindexing') . '</h2>';

    // Campo para la condición del producto
    woocommerce_wp_select(array(
        'id' => '_condition',
        'label' => __('Condition', 'tseoindexing'),
        'description' => __('Product condition (new, used, refurbished).', 'tseoindexing'),
        'desc_tip' => true,
        'options' => array(
            'new' => __('New', 'tseoindexing'),
            'refurbished' => __('Refurbished', 'tseoindexing'),
            'used' => __('Used', 'tseoindexing'),
        ),
        'value' => get_post_meta(get_the_ID(), '_condition', true),
    ));

    // Campo para el GTIN
    woocommerce_wp_text_input(array(
        'id' => '_gtin',
        'label' => __('GTIN', 'tseoindexing'),
        'description' => __('Enter the global identification number (UPC, EAN, ISBN, etc.).', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_gtin', true),
    ));

    // Campo para el MPN
    woocommerce_wp_text_input(array(
        'id' => '_mpn',
        'label' => __('MPN', 'tseoindexing'),
        'description' => __('Manufacturer part number (MPN).', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_mpn', true),
    ));

    // Campo para la categoría de producto de Google
    woocommerce_wp_text_input(array(
        'id' => '_google_product_category',
        'label' => __('Category', 'tseoindexing'),
        'description' => __('Official Google category for the product.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_product_category', true),
    ));

    // Campo para la descripción específica de Google Merchant Center
    woocommerce_wp_textarea_input(array(
        'id' => '_google_merchant_description',
        'label' => __('Description', 'tseoindexing'),
        'description' => __('Enter a specific description for Google Merchant Center.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_merchant_description', true),
    ));

    // Campo para la marca del producto
    woocommerce_wp_text_input(array(
        'id' => '_google_product_brand',
        'label' => __('Brand', 'tseoindexing'),
        'description' => __('Brand for the product.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_product_brand', true),
    ));

    // Campo para los destinos del producto en Google Merchant Center
    $selected_destinations = get_post_meta(get_the_ID(), '_google_merchant_destinations', true) ?: array();
    if (empty($selected_destinations)) {
        $selected_destinations = array('free_listings');
    }

    $destinations = array(
        'shopping_ads' => __('Shopping ads', 'tseoindexing'),
        'display_ads' => __('Display ads', 'tseoindexing'),
        'free_listings' => __('Free listings', 'tseoindexing'),
    );

    echo '<p><strong>' . __('Select the destination(s) for this product:', 'tseoindexing') . '</strong></p>';

    foreach ($destinations as $key => $label) {
        woocommerce_wp_checkbox(array(
            'id' => '_google_merchant_destinations_' . $key,
            'label' => $label,
            'description' => '',
            'value' => in_array($key, $selected_destinations) ? 'yes' : 'no',
            'cbvalue' => 'yes',
        ));
    }

    // Añadir campos para Ropa
    echo '<p><strong>' . __('Attributes for Clothing', 'tseoindexing') . '</strong></p>';

    // Campo para el Color
    woocommerce_wp_text_input(array(
        'id' => '_google_color',
        'label' => __('Color', 'tseoindexing'),
        'description' => __('The color [color] attribute indicates the primary color of this product and is written as a name of up to 100 characters (for example, "red" or "apple cinnamon red"). If the product has more than one main color, each must be separated by a slash (for example, "red/brown"). The color should be the same as what appears on your landing page.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_color', true),
    ));

    // Campo para el Tamaño
    woocommerce_wp_text_input(array(
        'id' => '_google_size',
        'label' => __('Size', 'tseoindexing'),
        'description' => __('Enter the size of the product (XXS, XS, S, M, L, XL, 2XL, 3XL, 4XL, 5XL, 6XL)', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_size', true),
    ));

    // Campo para el Género
    woocommerce_wp_select(array(
        'id' => '_google_gender',
        'label' => __('Gender', 'tseoindexing'),
        'description' => __('Select the gender for which the product is designed.', 'tseoindexing'),
        'desc_tip' => true,
        'options' => array(
            '' => __('Select option', 'tseoindexing'),
            'male' => __('Male', 'tseoindexing'),
            'female' => __('Female', 'tseoindexing'),
            'unisex' => __('Unisex', 'tseoindexing'),
        ),
        'value' => get_post_meta(get_the_ID(), '_google_gender', true),
    ));

    // Campo para la Edad
    woocommerce_wp_select(array(
        'id' => '_google_age_group',
        'label' => __('Age Group', 'tseoindexing'),
        'description' => __('Select the age group for the product.', 'tseoindexing'),
        'desc_tip' => true,
        'options' => array(
            '' => __('Select option', 'tseoindexing'),
            'newborn' => __('Newborn', 'tseoindexing'),
            'infant' => __('Infant', 'tseoindexing'),
            'toddler' => __('Toddler', 'tseoindexing'),
            'kids' => __('Kids', 'tseoindexing'),
            'junior' => __('Junior', 'tseoindexing'),
            'adult' => __('Adult', 'tseoindexing'),
        ),
        'value' => get_post_meta(get_the_ID(), '_google_age_group', true),
    ));

    echo '</div>';
    echo '</div>'; // Cerrar div panel
}
add_action('woocommerce_product_data_panels', 'tseoindexing_add_merchant_center_fields');

// Guardar los campos personalizados al guardar el producto
function tseoindexing_save_google_merchant_fields($product_id) {
    // Guardar la condición del producto
    if (isset($_POST['_condition'])) {
        $condition = sanitize_text_field($_POST['_condition']);
        update_post_meta($product_id, '_condition', $condition);
    }

    // Guardar el GTIN
    if (isset($_POST['_gtin'])) {
        $gtin = sanitize_text_field($_POST['_gtin']);
        update_post_meta($product_id, '_gtin', $gtin);
    }

    // Guardar el MPN
    if (isset($_POST['_mpn'])) {
        $mpn = sanitize_text_field($_POST['_mpn']);
        update_post_meta($product_id, '_mpn', $mpn);
    }

    // Guardar la categoría de producto de Google
    if (isset($_POST['_google_product_category'])) {
        $google_product_category = sanitize_text_field($_POST['_google_product_category']);
        update_post_meta($product_id, '_google_product_category', $google_product_category);
    }

    // Guardar la marca del producto
    if (isset($_POST['_google_product_brand'])) {
        $google_product_brand = sanitize_text_field($_POST['_google_product_brand']);
        update_post_meta($product_id, '_google_product_brand', $google_product_brand);
    }

    // Guardar la descripción específica de Google Merchant Center
    if (isset($_POST['_google_merchant_description'])) {
        $google_merchant_description = sanitize_text_field($_POST['_google_merchant_description']);
        update_post_meta($product_id, '_google_merchant_description', $google_merchant_description);
    }

    // Guardar los destinos seleccionados para el producto
    $destinations = array('shopping_ads', 'display_ads', 'free_listings');
    $selected_destinations = array();

    foreach ($destinations as $destination) {
        if (isset($_POST['_google_merchant_destinations_' . $destination]) && $_POST['_google_merchant_destinations_' . $destination] === 'yes') {
            $selected_destinations[] = $destination;
        }
    }

    update_post_meta($product_id, '_google_merchant_destinations', $selected_destinations);

    // Guardar el Color
    if (isset($_POST['_google_color'])) {
        $color = sanitize_text_field($_POST['_google_color']);
        update_post_meta($product_id, '_google_color', $color);
    }

    // Guardar el Tamaño
    if (isset($_POST['_google_size'])) {
        $size = sanitize_text_field($_POST['_google_size']);
        update_post_meta($product_id, '_google_size', $size);
    }

    // Guardar el Género
    if (isset($_POST['_google_gender'])) {
        $gender = sanitize_text_field($_POST['_google_gender']);
        update_post_meta($product_id, '_google_gender', $gender);
    }

    // Guardar la Edad
    if (isset($_POST['_google_age_group'])) {
        $age_group = sanitize_text_field($_POST['_google_age_group']);
        update_post_meta($product_id, '_google_age_group', $age_group);
    }
}
add_action('woocommerce_process_product_meta', 'tseoindexing_save_google_merchant_fields');