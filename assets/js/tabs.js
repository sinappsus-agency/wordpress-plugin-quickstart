/**
 * HTH Sample Plugin - Tab Functionality
 * 
 * JavaScript for tab shortcode functionality.
 */

(function($) {
    'use strict';

    /**
     * Initialize tab functionality for shortcodes
     */
    function initShortcodeTabs() {
        $('.hth-shortcode-tabs').each(function() {
            var $tabContainer = $(this);
            var $navLinks = $tabContainer.find('.tab-nav a');
            var $tabPanes = $tabContainer.find('.tab-pane');

            // Handle tab clicks
            $navLinks.on('click', function(e) {
                e.preventDefault();
                
                var $clickedTab = $(this);
                var targetId = $clickedTab.attr('href');

                // Remove active classes
                $navLinks.removeClass('active');
                $tabPanes.removeClass('active');

                // Add active class to clicked tab and show content
                $clickedTab.addClass('active');
                $(targetId).addClass('active');

                // Trigger custom event
                $tabContainer.trigger('hth:tab:activated', [targetId]);
            });

            // Initialize first tab if none is active
            if (!$navLinks.hasClass('active') && $navLinks.length > 0) {
                $navLinks.first().trigger('click');
            }
        });
    }

    /**
     * Handle keyboard navigation for tabs
     */
    function initTabKeyboardNavigation() {
        $('.hth-shortcode-tabs .tab-nav').on('keydown', 'a', function(e) {
            var $current = $(this);
            var $tabs = $current.closest('.tab-nav').find('a');
            var currentIndex = $tabs.index($current);
            var $target;

            switch (e.keyCode) {
                case 37: // Left arrow
                    e.preventDefault();
                    $target = currentIndex > 0 ? $tabs.eq(currentIndex - 1) : $tabs.last();
                    $target.focus().trigger('click');
                    break;
                    
                case 39: // Right arrow
                    e.preventDefault();
                    $target = currentIndex < $tabs.length - 1 ? $tabs.eq(currentIndex + 1) : $tabs.first();
                    $target.focus().trigger('click');
                    break;
                    
                case 13: // Enter
                case 32: // Space
                    e.preventDefault();
                    $current.trigger('click');
                    break;
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initShortcodeTabs();
        initTabKeyboardNavigation();
    });

    // Re-initialize if new content is loaded via AJAX
    $(document).on('hth:content:loaded', function() {
        initShortcodeTabs();
        initTabKeyboardNavigation();
    });

})(jQuery);
