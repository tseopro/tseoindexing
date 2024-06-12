<?php
defined('ABSPATH') or die('No script kiddies please!');

/**
 * TSEO PRO Tools
 *
 * @package TSEOIndexing
 * @version 1.0.0
 */
function tseoindexing_display_tools() {
    ?>
    <div id="tools" class="wrap-tools">
        <form id="extract-urls-form" method="post" action="">
            <?php wp_nonce_field('tseoindexing_extract_urls', 'tseoindexing_extract_urls_nonce'); ?>

            <h2><?php esc_html_e('Prepare a list of URLs to send to the Google Indexing API:', 'tseoindexing'); ?></h2>

            <p><?php esc_html_e('Paste the text or listing obtained from a sitemap or a Google Search Console listing that contains URLs and click the "Extract URLs" button to obtain a list of URLs separated by lines. This tool removes any text that is not a URL. Next, press the "Copy URLs" button to copy the result and paste it into the Console.', 'tseoindexing'); ?></p>
            
            <textarea name="convert_text" id="convert_text" rows="10" cols="50" class="textarea" placeholder="<?php esc_html_e('Paste the text here...', 'tseoindexing'); ?>"></textarea>
            
            <button type="button" id="extract-urls" class="button button-primary">
                <i class="tseoindexing-cogs"></i> <?php esc_html_e('Extract URLs', 'tseoindexing'); ?>
            </button>

            <button type="button" id="copy-urls" class="button button-secondary">
                <i class="tseoindexing-paste"></i> <?php esc_html_e('Copy URLs', 'tseoindexing'); ?>
            </button>
        </form>
    </div>
    
    <script>
        document.getElementById('extract-urls').addEventListener('click', function() {
            // Obtén el valor del textarea
            let text = document.getElementById('convert_text').value;

            // Expresión regular para coincidir con las URLs
            let urlPattern = /(http|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/g;

            // Encuentra todas las URLs
            let urls = text.match(urlPattern);

            // Si se encontraron URLs, reemplaza el contenido del textarea con las URLs, una por línea
            if (urls) {
                document.getElementById('convert_text').value = urls.join('\n');
            } else {
                document.getElementById('convert_text').value = '<?php esc_html_e('No URLs found.', 'tseoindexing'); ?>';
            }
        });

        document.getElementById('copy-urls').addEventListener('click', function() {
            // Copia el contenido del textarea al portapapeles
            let copyText = document.getElementById('convert_text');
            copyText.select();
            document.execCommand('copy');
        });
    </script>
    <?php
}