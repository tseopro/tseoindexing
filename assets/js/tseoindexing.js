jQuery(document).ready(function($) {

    setTimeout(function() {
        $('.updated, .error').fadeOut('slow');
    }, 3000); // 5000 milliseconds = 3 seconds
    
    $('#load-updated-urls').click(function() {
        loadUrls('URL_UPDATED');
    });

    $('#load-deleted-urls').click(function() {
        loadUrls('URL_DELETED');
    });

    $('#clear-urls').click(function() {
        $('#tseo_urls').val('');  // Limpia el contenido del textarea
    });

    function loadUrls(type) {
        $.post(ajaxurl, {
            action: 'tseoindexing_load_urls_by_type',
            type: type,
            _ajax_nonce: $('#tseoindexing_console_nonce').val()
        }, function(response) {
            if (response.success) {
                $('#tseo_urls').val(response.data.join("\n"));
                if (type === 'URL_UPDATED') {
                    $('#updated-urls-count').text(response.data.length);
                } else if (type === 'URL_DELETED') {
                    $('#deleted-urls-count').text(response.data.length);
                }
            } else {
                alert('Error: ' + response.data);
            }
        });
    }

    $('#tseoindexing-console-form').submit(function(e) {
        e.preventDefault();

        var urls = $('#tseo_urls').val().trim().split("\n");
        var nonce = $('#tseoindexing_console_nonce').val();
        var action = $('input[name="api_action"]:checked').val();

        console.log('Selected action:', action);

        if (!action) {
            alert('Please select an action.');
            return;
        }

        $('.wrap-console-response').hide();
        $('.response-action').text('');
        $('.response-url').text('');
        $('.response-title').text('');
        $('.response-message').text('');
        $('#raw-response').val('');

        $.post(ajaxurl, {
            action: 'tseoindexing_send_urls_to_google',
            urls: urls,
            api_action: action,
            _ajax_nonce: nonce
        }, function(response) {
            console.log('API Response:', response);
            if (response.success) {
                displayResponse(response.data);
            } else {
                $('#console-response').html('<p>Error: ' + response.data + '</p>');
            }
        });
    });

    function displayResponse(data) {
        var rawResponse = '';
        var responseObject = {};
        var timestamp = new Date().toLocaleTimeString();

        $.each(data, function(index, item) {
            $('.response-action').text(item.action);
            //$('.response-url').text(item.url);
            if (item.response.status === 'error') {
                $('.response-title').html('<span class="title-error"><i class="tseoindexing-cross"></i> Error ' + item.response.code + '</span>');
                $('.response-message').text(item.response.message);
            } else {
                $('.response-title').html('<span class="title-success"><i class="tseoindexing-checkmark"></i> Success</span>');
                $('.response-message').html('URL: ' + item.response.url + '<br>Type: ' + item.response.type + '<br>Notify Time: ' + item.response.notifyTime);
                //$('.response-message').html('Type: ' + item.response.type + '<br>Notify Time: ' + item.response.notifyTime);
            }
            rawResponse += item.url + ': ' + JSON.stringify(item.response) + '\n';

            responseObject[`url-${index}`] = item.response;
        });

        var formattedResponse = `${timestamp} update: (batch)\n${JSON.stringify(responseObject, null, 2)}`;
        $('#raw-response').val(formattedResponse);
        $('.wrap-console-response').show();
    }
});

jQuery(document).ready(function($) {
    $('.generate-google-merchant-title, .generate-google-merchant-description').on('click', function() {
        var field = $(this).data('field');
        var productId = $('#post_ID').val();
        var data = {
            action: 'tseoindexing_generate_content',
            field: field,
            product_id: productId,
            _ajax_nonce: tseoindexing_ajax.nonce
        };

        $.post(tseoindexing_ajax.ajax_url, data, function(response) {
            if (response.success) {
                if (field === 'title') {
                    $('#_google_merchant_title').val(response.data);
                } else if (field === 'description') {
                    $('#_google_merchant_description').val(response.data);
                }
            } else {
                alert('Error al generar contenido: ' + response.data);
            }
        });
    });
});
