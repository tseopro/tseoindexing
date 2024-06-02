/**
 * Initializes the popup windows save functionality.
 * Hides the loading overlay on page load.
 * Shows the loading overlay when the submit button is clicked.
 * Hides the loading overlay when an AJAX request is completed.
 */
jQuery(document).ready(function($) {
    $('#tseoindexing-loading-overlay').hide();

    /**
     * Event handler for the submit button click event.
     * Shows the loading overlay.
     */
    $('#submit').on('click', function() {
      $('#tseoindexing-loading-overlay').show();
    });

    /**
     * Event handler for the completion of an AJAX request.
     * Hides the loading overlay.
     */
    $(document).ajaxStop(function() {
      $('#tseoindexing-loading-overlay').hide();
    });
});