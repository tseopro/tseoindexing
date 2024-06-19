<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO TSEO Merchant Center Add Fields WooCommerce
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
add_filter('woocommerce_product_data_tabs', 'tseoindexing_add_merchant_center_tab');
function tseoindexing_add_merchant_center_tab($tabs) {
    $tabs['merchant_center'] = array(
        'label'    => __('TSEO Merchant', 'tseoindexing'),
        'target'   => 'merchant_center_options',
        'class'    => array('show_if_simple', 'show_if_variable'),
        'priority' => 60,
    );
    return $tabs;
}

/**
 * Display custom fields in the new tab
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
add_action('woocommerce_product_data_panels', 'tseoindexing_add_merchant_center_fields');
function tseoindexing_add_merchant_center_fields() {
    echo '<div id="merchant_center_options" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    echo '<h2>' . __('Google Merchant Center Fields by TSEO Indexing', 'tseoindexing') . '</h2>';

     woocommerce_wp_text_input(array(
        'id' => '_google_merchant_title',
        'label' => __('Title', 'tseoindexing'),
        'description' => __('Enter a title. Recommendation: 50-70 characters.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_merchant_title', true),
    ));
    echo '<p class="form-field form-field-wide">';
    echo '<button type="button" class="button generate-google-merchant-title" data-field="title">' . __('AI: Generate Title', 'tseoindexing') . '</button>';
    echo '<span class="loader_button_woo" style="display: none;">' . __('Generating...', 'tseoindexing') . '</span>';
    echo '</p>';

    woocommerce_wp_textarea_input(array(
        'id' => '_google_merchant_description',
        'label' => __('Description', 'tseoindexing'),
        'description' => __('Enter a specific description. Recommendation: 250-400 characters.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_merchant_description', true),
        'style' => 'height: 150px;',
    ));
    echo '<p class="form-field form-field-wide">';
    echo '<button type="button" class="button generate-google-merchant-description" data-field="description">' . __('AI: Generate Description', 'tseoindexing') . '</button>';
    echo '<span class="loader_button_woo" style="display: none;">' . __('Generating...', 'tseoindexing') . '</span>';
    echo '</p>';

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

    woocommerce_wp_text_input(array(
        'id' => '_google_product_category',
        'label' => __('Category', 'tseoindexing'),
        'description' => __('Official Google category for the product.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_product_category', true),
    ));

    woocommerce_wp_text_input(array(
        'id' => '_google_product_brand',
        'label' => __('Brand', 'tseoindexing'),
        'description' => __('Brand for the product.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_product_brand', true),
    ));

    woocommerce_wp_text_input(array(
        'id' => '_gtin',
        'label' => __('GTIN', 'tseoindexing'),
        'description' => __('Enter the global identification number (UPC, EAN, ISBN, etc.).', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_gtin', true),
    ));

    woocommerce_wp_text_input(array(
        'id' => '_mpn',
        'label' => __('MPN', 'tseoindexing'),
        'description' => __('Manufacturer part number (MPN).', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_mpn', true),
    ));

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

    echo '<p><strong>' . __('Attributes for Clothing', 'tseoindexing') . '</strong></p>';

    woocommerce_wp_text_input(array(
        'id' => '_google_color',
        'label' => __('Color', 'tseoindexing'),
        'description' => __('The color [color] attribute indicates the primary color of this product and is written as a name of up to 100 characters (for example, "red" or "apple cinnamon red"). If the product has more than one main color, each must be separated by a slash (for example, "red/brown"). The color should be the same as what appears on your landing page.', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_color', true),
    ));

    woocommerce_wp_text_input(array(
        'id' => '_google_size',
        'label' => __('Size', 'tseoindexing'),
        'description' => __('Enter the size of the product (XXS, XS, S, M, L, XL, 2XL, 3XL, 4XL, 5XL, or 6XL). For one size fits all: OSFA, OS, or one size. For footwear: numeric ranges 000-100 (Example: 35-39).', 'tseoindexing'),
        'desc_tip' => true,
        'value' => get_post_meta(get_the_ID(), '_google_size', true),
    ));

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
    echo '</div>';
}

/**
 * Save custom fields when saving the product
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
add_action('woocommerce_process_product_meta', 'tseoindexing_save_google_merchant_fields');
function tseoindexing_save_google_merchant_fields($product_id) {
    if (isset($_POST['_google_merchant_title'])) {
        $title = sanitize_text_field($_POST['_google_merchant_title']);
        update_post_meta($product_id, '_google_merchant_title', $title);
    }

    if (isset($_POST['_google_merchant_description'])) {
        $description = sanitize_textarea_field($_POST['_google_merchant_description']);
        update_post_meta($product_id, '_google_merchant_description', $description);
    }

    if (isset($_POST['_condition'])) {
        $condition = sanitize_text_field($_POST['_condition']);
        update_post_meta($product_id, '_condition', $condition);
    }

    if (isset($_POST['_google_product_category'])) {
        $google_product_category = sanitize_text_field($_POST['_google_product_category']);
        update_post_meta($product_id, '_google_product_category', $google_product_category);
    }

    if (isset($_POST['_google_product_brand'])) {
        $google_product_brand = sanitize_text_field($_POST['_google_product_brand']);
        update_post_meta($product_id, '_google_product_brand', $google_product_brand);
    }

    if (isset($_POST['_gtin'])) {
        $gtin = sanitize_text_field($_POST['_gtin']);
        update_post_meta($product_id, '_gtin', $gtin);
    }

    if (isset($_POST['_mpn'])) {
        $mpn = sanitize_text_field($_POST['_mpn']);
        update_post_meta($product_id, '_mpn', $mpn);
    }

    $destinations = array('shopping_ads', 'display_ads', 'free_listings');
    $selected_destinations = array();

    foreach ($destinations as $destination) {
        if (isset($_POST['_google_merchant_destinations_' . $destination]) && $_POST['_google_merchant_destinations_' . $destination] === 'yes') {
            $selected_destinations[] = $destination;
        }
    }

    update_post_meta($product_id, '_google_merchant_destinations', $selected_destinations);

    if (isset($_POST['_google_color'])) {
        $color = sanitize_text_field($_POST['_google_color']);
        update_post_meta($product_id, '_google_color', $color);
    }

    if (isset($_POST['_google_size'])) {
        $size = sanitize_text_field($_POST['_google_size']);
        update_post_meta($product_id, '_google_size', $size);
    }

    if (isset($_POST['_google_gender'])) {
        $gender = sanitize_text_field($_POST['_google_gender']);
        update_post_meta($product_id, '_google_gender', $gender);
    }

    if (isset($_POST['_google_age_group'])) {
        $age_group = sanitize_text_field($_POST['_google_age_group']);
        update_post_meta($product_id, '_google_age_group', $age_group);
    }
}

/**
 * Generates product titles and descriptions for Google Merchant Center.
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
add_action('wp_ajax_tseoindexing_generate_content', 'tseoindexing_generate_content');
function tseoindexing_generate_content() {
    check_ajax_referer('tseoindexing_nonce', '_ajax_nonce');

    if (!isset($_POST['field']) || !isset($_POST['product_id'])) {
        wp_send_json_error(esc_html__('Invalid field or product ID.', 'tseoindexing'));
        return;
    }    

    $field = sanitize_text_field($_POST['field']);
    $product_id = intval($_POST['product_id']);

    try {
        // Get the OpenAI client
        $openai = get_openai_client();
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
        return;
    }

    // Get the original title and description of the product
    $original_title = get_the_title($product_id);
    $original_description = get_post_meta($product_id, '_google_merchant_description', true);

    $messages = [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant that generates product titles and descriptions for Google Merchant Center. Generates titles and descriptions in the provided language without adding title: description: or similar.'
        ]
    ];

    if ($field === 'title') {
        $messages[] = [
            'role' => 'user',
            'content' => "Generate a new plain text title for a product with the following details:\nOriginal Title: $original_title\n"
        ];
    } elseif ($field === 'description') {
        $messages[] = [
            'role' => 'user',
            'content' => "Generate a new plain text description for a product with the following details:\n$original_title\n" . (!empty($original_description) ? "$original_description\n" : "")
        ];
    } else {
        $invalid_field_message = esc_html__('Invalid field.', 'tseoindexing');
        wp_send_json_error($invalid_field_message);
        return;
    }

    $response = $openai->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'max_tokens' => 100,
        'temperature' => 0.7
    ]);

    // Process the response
    if (isset($response['choices'][0]['message']['content'])) {
        $generated_text = trim($response['choices'][0]['message']['content'], ' "');

        if ($field === 'title') {
            $cleaned_title = preg_replace('/^(New )?Title: /i', '', $generated_text);
            wp_send_json_success(trim($cleaned_title));
        } elseif ($field === 'description') {
            $cleaned_description = preg_replace('/^(New )?(Description:|New Description:)? /i', '', $generated_text);
            $cleaned_description = str_ireplace($original_title, '', $cleaned_description);
            wp_send_json_success(trim($cleaned_description));
        }
    } else {
        wp_send_json_error(esc_html__('Failed to generate content.', 'tseoindexing'));
    }
}

/**
 * Enqueues admin scripts and styles for the TSEO Indexing plugin on WooCommerce product editing page.
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
add_action('admin_enqueue_scripts', 'tseoindexing_enqueue_admin_scripts');

function tseoindexing_enqueue_admin_scripts($hook) {
    $screen = get_current_screen();

    // Load only on WooCommerce product editing page
    if (($hook == 'post.php' || $hook == 'post-new.php') && $screen->post_type === 'product') {
        // Load your specific CSS and JS here
        wp_enqueue_style('tseoindexing-woo', plugin_dir_url(dirname(__FILE__)) . 'assets/css/tseoindexing-woo.min.css', array(), TSEOINDEXING_VERSION, 'all');
        wp_enqueue_script('tseoindexing-woo', plugin_dir_url(dirname(__FILE__)) . 'assets/js/tseoindexing-woo.js', array('jquery'), TSEOINDEXING_VERSION, true);
        
        wp_localize_script('tseoindexing-woo', 'tseoindexing_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tseoindexing_nonce')
        ));
    }
}

// Function to initialize OpenAI Client
function get_openai_client() {
    $openai_api_key = get_option('tseo_openai_api_key', '');
    if (empty($openai_api_key)) {
        throw new Exception(esc_html__('OpenAI API key is not set. Please configure it in the settings.', 'tseoindexing'));
    }
    return OpenAI::client($openai_api_key);
}
