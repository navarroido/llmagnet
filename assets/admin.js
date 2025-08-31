/**
 * LLMS.txt Generator Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle "Generate Now" button click
        $('#llms-txt-generate-now').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $spinner = $button.next('.spinner');
            const $container = $button.parent();
            
            // Remove any existing status messages
            $('.llms-txt-status-message').remove();
            
            // Disable button and show spinner
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            
            // Send AJAX request
            $.ajax({
                url: window.llmagnetLlmsTxtAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'llmagnet_ai_seo_generate_now',
                    nonce: window.llmagnetLlmsTxtAdmin.nonce
                },
                success: function(response) {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    
                    if (response.success) {
                        // Update timestamp display
                        $('strong:contains("Last Generated:")').parent().html(
                            '<strong>' + window.llmagnetLlmsTxtAdmin.lastGeneratedLabel + '</strong> ' + response.data.timestamp
                        );
                        
                        // Show success message
                        $container.append(
                            $('<span class="llms-txt-status-message success"></span>').text(response.data.message)
                        );
                    } else {
                        // Show error message
                        $container.append(
                            $('<span class="llms-txt-status-message error"></span>').text(response.data.message)
                        );
                    }
                    
                    // Hide message after 5 seconds
                    setTimeout(function() {
                        $('.llms-txt-status-message').fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                },
                error: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    
                    // Show error message
                    $container.append(
                        $('<span class="llms-txt-status-message error"></span>').text(window.llmagnetLlmsTxtAdmin.error)
                    );
                    
                    // Hide message after 5 seconds
                    setTimeout(function() {
                        $('.llms-txt-status-message').fadeOut(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });
        });
    });
})(jQuery); 