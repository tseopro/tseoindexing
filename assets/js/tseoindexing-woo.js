jQuery(document).ready(function($) {
    // Add click event for AI generation buttons
    $('.generate-google-merchant-title, .generate-google-merchant-description').on('click', function() {
        var $button = $(this);
        var field = $button.data('field');
        var productId = $('#post_ID').val();
        var $loader = $button.siblings('.loader_button_woo');
        $loader.show();
        $button.prop('disabled', true);
        $.ajax({
            url: tseoindexing_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tseoindexing_generate_content',
                field: field,
                product_id: productId,
                _ajax_nonce: tseoindexing_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (field === 'title') {
                        $('input#_google_merchant_title').val(response.data);
                    } else if (field === 'description') {
                        $('textarea#_google_merchant_description').val(response.data);
                    }
                } else {
                    alert(response.data || 'Error generating content.');
                }
            },
            error: function() {
                alert('Error generating content.');
            },
            complete: function() {
                $loader.hide();
                $button.prop('disabled', false);
            }
        });
    });
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
                alert('Error generating content: ' + response.data);
            }
        });
    });
});