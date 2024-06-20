<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Dashboard
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_dashboard_options() {
    $cards = array(
        'seo_options' => array(
            'title' => esc_html__('SEO Options', 'tseoindexing'),
            'description' => esc_html__('By activating SEO Options, you have a set of powerful tools without the need for third-party plugins: sitemaps, schema, open graph, image compressor and converter, etc.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-awards'
        ),
        'services' => array(
            'title' => esc_html__('Services', 'tseoindexing'),
            'description' => esc_html__('General settings on the various services integrated within TSEO PRO without the use of any plugins. Knowing these different sections is of utmost importance.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-admin-plugins'
        ),
        'rgpd' => array(
            'title' => esc_html__('RGPD', 'tseoindexing'),
            'description' => esc_html__('The theme comes integrated with the necessary legal documentation to get started, which you can activate from the corresponding section. For now, it is only available in Spanish or English.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-privacy'
        ),
        'woocommerce' => array(
            'title' => esc_html__('Woocommerce', 'tseoindexing'),
            'description' => esc_html__('If you use WooCommerce as an online store, activate this service to enable multiple tools to customize the store.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-cart'
        ),
        'marketing' => array(
            'title' => esc_html__('Marketing', 'tseoindexing'),
            'description' => esc_html__('Powerful marketing toolset: Notifications, Chatbot, WhatsApp, Landing Page, Google Reviews, and Banners. Stand out and grow your business!', 'tseoindexing'),
            'icon' => 'dashicons dashicons-store'
        ),
        'youtube' => array(
            'title' => esc_html__('YouTube Videos', 'tseoindexing'),
            'description' => esc_html__('Create a catalog of YouTube videos with their own covers, and you can trigger the playback of each video in a pop-up window using a custom [shortcode].', 'tseoindexing'),
            'icon' => 'dashicons dashicons-format-video'
        ),
        'documentation' => array(
            'title' => esc_html__('Documentation', 'tseoindexing'),
            'description' => esc_html__('If you have a product or service that requires technical documentation for its use, activate this service. Two new tabs will be activated: Documentation and Snippets.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-media-document'
        ),
        'pwa_web' => array(
            'title' => esc_html__('PWA Web', 'tseoindexing'),
            'description' => esc_html__('Turn the Web into an App with a powerful natural cache. It can also be installed on desktop and mobile devices. In the footer and on compatible devices, its installation is prompted.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-smartphone'
        ),
        'pwa_app' => array(
            'title' => esc_html__('PWA App (Beta)', 'tseoindexing'),
            'description' => esc_html__('This is a service under development to launch a professional App that can be downloaded and installed without the need for app stores. It works in conjunction with the main PWA Web.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-smartphone'
        ),
        'international_seo' => array(
            'title' => esc_html__('International SEO', 'tseoindexing'),
            'description' => esc_html__('When you activate international SEO, the tool automatically generates hreflang tags across all editors: Pages, Posts, Docs, and Products.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-admin-site-alt3'
        ),
        'token' => array(
            'title' => esc_html__('Token', 'tseoindexing'),
            'description' => esc_html__('We explain how you can enable the section to promote your own cryptocurrency (token) from your website with links to the Ethereum or Binance networks.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-money-alt'
        ),
        'advanced' => array(
            'title' => esc_html__('Advanced settings', 'tseoindexing'),
            'description' => esc_html__('Advanced web configuration section. In this section, we teach you how .htaccess works for Linux or web.config for Windows.', 'tseoindexing'),
            'icon' => 'dashicons dashicons-admin-settings'
        )
    );    

    ?>
    <div class="wrap-general-dashboard">
        <div class="options-cards">
            <?php foreach ($cards as $key => $card_details) : ?>
                <?php if($card_details): ?>
                    <div class="card-service">
                        <div class="card-service-body">
                            <span class="<?php echo $card_details['icon']; ?>"></span>
                            <h3><?php echo $card_details['title']; ?></h3>
                            <p><?php echo $card_details['description']; ?></p>
                        </div>
                        <div class="card-service-footer">
                            <input type="checkbox" id="<?php echo $key; ?>" disabled>
                                <label for="<?php echo $key; ?>"></label> 
                            <span> TSEO PRO PREMIUM</span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            <?php foreach ($cards as $key => $card_details) : ?>
                document.querySelector('label[for="<?php echo $key; ?>"]').addEventListener('click', function(e) {
                    window.open('<?php echo esc_url("https://tseo.pro/"); ?>', '_blank');
                });
            <?php endforeach; ?>
        });
    </script>

    <?php
}

/**
 * TSEO PRO Information Sidebar
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_info_sidebar() {
    ?>
        <h2><?php echo esc_html_e('Information','tseoindexing'); ?></h2>

        <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/img/tseopro.jpg'); ?>" alt="TSEO PRO theme">

        <h3><?php echo esc_html_e('Boost your business with our TSEO PRO theme','tseoindexing'); ?></h3>

        <p><?php echo esc_html_e("A Hassle-Free, Comprehensive Solution: If you're aiming to establish a powerful and seamless online presence, you've come to the right place. With our TSEO PRO theme and its 'Web Renting' subscription model, we offer an ideal solution for businesses and entrepreneurs. Get a high-performance website, SEO-optimized, without making significant upfront investments.",'tseoindexing'); ?></p>

        <a href="<?php echo esc_url('https://tseo.pro/'); ?>" target="_black" rel="noopener noreferrer" class="button"><?php echo esc_html_e('Visit TSEO PRO', 'tseoindexing'); ?></a>
    <?php
}