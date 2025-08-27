/**
 * HTH Sample Plugin - Frontend JavaScript
 * 
 * Main JavaScript file for frontend functionality.
 */

(function($) {
    'use strict';

    // Plugin namespace
    window.HTHPlugin = window.HTHPlugin || {};

    /**
     * Initialize the plugin
     */
    HTHPlugin.init = function() {
        console.log('HTH Sample Plugin frontend initialized');
        
        // Initialize components
        HTHPlugin.initTabs();
        HTHPlugin.initForms();
        HTHPlugin.initInteractiveElements();
    };

    /**
     * Initialize tab functionality
     */
    HTHPlugin.initTabs = function() {
        $('.hth-tabs-container').each(function() {
            var $container = $(this);
            var $navLinks = $container.find('.hth-tab-nav-link');
            var $tabPanes = $container.find('.hth-tab-pane');

            // Handle tab clicks
            $navLinks.on('click', function(e) {
                e.preventDefault();
                
                var $this = $(this);
                var target = $this.attr('href') || $this.data('target');
                
                // Remove active classes
                $navLinks.removeClass('active');
                $tabPanes.removeClass('active');
                
                // Add active class to clicked tab
                $this.addClass('active');
                
                // Show corresponding content
                $(target).addClass('active');
                
                // Trigger custom event
                $container.trigger('hth:tab:changed', [target, $this]);
            });

            // Initialize first tab if none is active
            if (!$navLinks.hasClass('active') && $navLinks.length > 0) {
                $navLinks.first().trigger('click');
            }
        });
    };

    /**
     * Initialize form functionality
     */
    HTHPlugin.initForms = function() {
        // Handle AJAX forms
        $('.hth-ajax-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('[type="submit"]');
            var originalText = $submitBtn.val() || $submitBtn.text();
            
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitBtn.val('Loading...').text('Loading...');
            $form.addClass('hth-loading');
            
            // Get form data
            var formData = new FormData(this);
            formData.append('action', 'hth_ajax_form_submit');
            
            // Add nonce if available
            if (typeof hthAjax !== 'undefined' && hthAjax.nonce) {
                formData.append('nonce', hthAjax.nonce);
            }
            
            // Submit via AJAX
            $.ajax({
                url: hthAjax ? hthAjax.ajaxUrl : ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        HTHPlugin.showMessage('success', response.data.message || 'Form submitted successfully!');
                        $form[0].reset();
                    } else {
                        HTHPlugin.showMessage('error', response.data.message || 'An error occurred.');
                    }
                },
                error: function() {
                    HTHPlugin.showMessage('error', 'Network error. Please try again.');
                },
                complete: function() {
                    // Restore button state
                    $submitBtn.prop('disabled', false);
                    $submitBtn.val(originalText).text(originalText);
                    $form.removeClass('hth-loading');
                }
            });
        });
    };

    /**
     * Initialize interactive elements
     */
    HTHPlugin.initInteractiveElements = function() {
        // Handle custom buttons
        $('.hth-button[data-action]').on('click', function(e) {
            var action = $(this).data('action');
            var target = $(this).data('target');
            
            switch (action) {
                case 'toggle':
                    $(target).toggle();
                    break;
                case 'scroll-to':
                    HTHPlugin.scrollTo(target);
                    break;
                case 'load-content':
                    HTHPlugin.loadContent($(this));
                    break;
            }
        });

        // Handle collapsible content
        $('.hth-collapsible-trigger').on('click', function() {
            var $trigger = $(this);
            var $content = $trigger.next('.hth-collapsible-content');
            
            $content.slideToggle();
            $trigger.toggleClass('expanded');
        });

        // Handle tooltips
        $('.hth-tooltip').hover(
            function() {
                var tooltip = $(this).data('tooltip');
                if (tooltip) {
                    $('<div class="hth-tooltip-popup">' + tooltip + '</div>')
                        .appendTo('body')
                        .fadeIn();
                }
            },
            function() {
                $('.hth-tooltip-popup').remove();
            }
        );
    };

    /**
     * Show message to user
     */
    HTHPlugin.showMessage = function(type, message) {
        var $message = $('<div class="hth-message hth-message-' + type + '">' + message + '</div>');
        
        // Find or create message container
        var $container = $('.hth-messages');
        if ($container.length === 0) {
            $container = $('<div class="hth-messages"></div>').prependTo('body');
        }
        
        $container.append($message);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $message.remove();
            });
        }, 5000);
    };

    /**
     * Smooth scroll to element
     */
    HTHPlugin.scrollTo = function(target) {
        var $target = $(target);
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - 100
            }, 500);
        }
    };

    /**
     * Load content via AJAX
     */
    HTHPlugin.loadContent = function($button) {
        var url = $button.data('url');
        var target = $button.data('target');
        var $target = $(target);
        
        if (!url || !$target.length) return;
        
        $target.addClass('hth-loading');
        
        $.get(url)
            .done(function(data) {
                $target.html(data);
            })
            .fail(function() {
                $target.html('<p>Error loading content.</p>');
            })
            .always(function() {
                $target.removeClass('hth-loading');
            });
    };

    /**
     * Utility functions
     */
    HTHPlugin.utils = {
        /**
         * Debounce function calls
         */
        debounce: function(func, wait) {
            var timeout;
            return function executedFunction() {
                var context = this;
                var args = arguments;
                var later = function() {
                    timeout = null;
                    func.apply(context, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Format numbers
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

        /**
         * Sanitize HTML
         */
        sanitizeHtml: function(str) {
            var temp = document.createElement('div');
            temp.textContent = str;
            return temp.innerHTML;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        HTHPlugin.init();
    });

    // Handle window resize
    $(window).on('resize', HTHPlugin.utils.debounce(function() {
        // Handle responsive adjustments
        $('.hth-tabs-container').trigger('hth:resize');
    }, 250));

})(jQuery);
