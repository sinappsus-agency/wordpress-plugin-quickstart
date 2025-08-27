/**
 * HTH Sample Plugin - AJAX Form Functionality
 * 
 * JavaScript for AJAX form submissions in shortcodes.
 */

(function($) {
    'use strict';

    /**
     * Initialize AJAX form functionality
     */
    function initAjaxForms() {
        $('.hth-ajax-form').each(function() {
            var $form = $(this);
            
            $form.on('submit', function(e) {
                e.preventDefault();
                handleFormSubmission($form);
            });
        });
    }

    /**
     * Handle form submission
     */
    function handleFormSubmission($form) {
        var $submitBtn = $form.find('.submit-button');
        var originalText = $submitBtn.text();
        var $messageContainer = $form.find('.hth-form-messages');
        
        // Create message container if it doesn't exist
        if ($messageContainer.length === 0) {
            $messageContainer = $('<div class="hth-form-messages"></div>');
            $form.prepend($messageContainer);
        }

        // Clear previous messages
        $messageContainer.empty();

        // Validate form
        if (!validateForm($form)) {
            showMessage($messageContainer, 'error', 'Please fill in all required fields.');
            return;
        }

        // Show loading state
        $submitBtn.prop('disabled', true).text('Sending...');
        $form.addClass('submitting');

        // Prepare form data
        var formData = new FormData($form[0]);
        formData.append('action', 'hth_ajax_form_submit');
        
        // Add nonce if available
        if (typeof hthForm !== 'undefined' && hthForm.nonce) {
            formData.append('nonce', hthForm.nonce);
        }

        // Submit form
        $.ajax({
            url: hthForm ? hthForm.ajaxUrl : ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage($messageContainer, 'success', response.data.message || 'Form submitted successfully!');
                    $form[0].reset();
                    
                    // Trigger custom event
                    $form.trigger('hth:form:success', [response.data]);
                } else {
                    showMessage($messageContainer, 'error', response.data.message || 'An error occurred. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Form Error:', error);
                showMessage($messageContainer, 'error', 'Network error. Please check your connection and try again.');
            },
            complete: function() {
                // Restore button state
                $submitBtn.prop('disabled', false).text(originalText);
                $form.removeClass('submitting');
            }
        });
    }

    /**
     * Validate form fields
     */
    function validateForm($form) {
        var isValid = true;
        
        $form.find('[required]').each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            var fieldType = $field.attr('type');
            
            // Remove previous error styling
            $field.removeClass('error');
            
            if (!value) {
                $field.addClass('error');
                isValid = false;
            } else if (fieldType === 'email' && !isValidEmail(value)) {
                $field.addClass('error');
                isValid = false;
            }
        });
        
        return isValid;
    }

    /**
     * Validate email format
     */
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Show form message
     */
    function showMessage($container, type, message) {
        var $message = $('<div class="hth-form-message ' + type + '">' + message + '</div>');
        $container.html($message);
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $container.offset().top - 100
        }, 300);
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(function() {
                $message.fadeOut();
            }, 5000);
        }
    }

    /**
     * Real-time field validation
     */
    function initRealTimeValidation() {
        $(document).on('blur', '.hth-ajax-form [required]', function() {
            var $field = $(this);
            var value = $field.val().trim();
            var fieldType = $field.attr('type');
            
            $field.removeClass('error');
            
            if (!value) {
                $field.addClass('error');
            } else if (fieldType === 'email' && !isValidEmail(value)) {
                $field.addClass('error');
            }
        });
        
        // Remove error styling when user starts typing
        $(document).on('input', '.hth-ajax-form .error', function() {
            $(this).removeClass('error');
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initAjaxForms();
        initRealTimeValidation();
    });

    // Re-initialize if new content is loaded via AJAX
    $(document).on('hth:content:loaded', function() {
        initAjaxForms();
    });

})(jQuery);
