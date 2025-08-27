/**
 * HTH Sample Plugin - Debug JavaScript
 * 
 * Development and debugging utilities.
 */

(function($) {
    'use strict';

    // Debug namespace
    window.HTHDebug = window.HTHDebug || {};

    /**
     * Initialize debug functionality
     */
    HTHDebug.init = function() {
        if (typeof hthDebug === 'undefined' || !hthDebug.isDebugMode) {
            return;
        }

        console.log('HTH Debug Mode: Enabled');
        
        HTHDebug.createDebugPanel();
        HTHDebug.trackAjaxRequests();
        HTHDebug.setupKeyboardShortcuts();
        HTHDebug.logEnvironmentInfo();
    };

    /**
     * Create debug information panel
     */
    HTHDebug.createDebugPanel = function() {
        var debugInfo = hthDebug || {};
        
        var $panel = $('<div class="hth-debug-panel">');
        $panel.html(
            '<button class="debug-toggle">−</button>' +
            '<div class="debug-content">' +
                '<h4>HTH Debug Info</h4>' +
                '<div class="debug-item"><span class="debug-label">User:</span><span class="debug-value">' + (debugInfo.currentUser || 'Guest') + '</span></div>' +
                '<div class="debug-item"><span class="debug-label">PHP:</span><span class="debug-value">' + (debugInfo.phpVersion || 'Unknown') + '</span></div>' +
                '<div class="debug-item"><span class="debug-label">WP:</span><span class="debug-value">' + (debugInfo.wpVersion || 'Unknown') + '</span></div>' +
                '<div class="debug-item"><span class="debug-label">Plugin:</span><span class="debug-value">' + (debugInfo.pluginVersion || '1.0.0') + '</span></div>' +
                '<div class="debug-item"><span class="debug-label">Memory:</span><span class="debug-value">' + HTHDebug.formatBytes(debugInfo.memoryUsage || 0) + '</span></div>' +
                '<div class="debug-item"><span class="debug-label">Load Time:</span><span class="debug-value">' + (debugInfo.loadTime ? debugInfo.loadTime.toFixed(3) + 's' : 'Unknown') + '</span></div>' +
            '</div>'
        );
        
        $('body').append($panel);
        
        // Toggle panel
        $panel.find('.debug-toggle').on('click', function() {
            $panel.toggleClass('minimized');
            $(this).text($panel.hasClass('minimized') ? '+' : '−');
        });
    };

    /**
     * Track AJAX requests
     */
    HTHDebug.trackAjaxRequests = function() {
        var originalAjax = $.ajax;
        var activeRequests = 0;
        
        $.ajax = function(options) {
            activeRequests++;
            HTHDebug.updateAjaxTracker('loading', 'AJAX: ' + activeRequests + ' active');
            
            var originalSuccess = options.success;
            var originalError = options.error;
            var originalComplete = options.complete;
            
            options.success = function(data, textStatus, jqXHR) {
                console.log('HTH AJAX Success:', options.url || options, data);
                if (originalSuccess) originalSuccess.apply(this, arguments);
            };
            
            options.error = function(jqXHR, textStatus, errorThrown) {
                console.error('HTH AJAX Error:', options.url || options, textStatus, errorThrown);
                HTHDebug.updateAjaxTracker('error', 'AJAX Error');
                if (originalError) originalError.apply(this, arguments);
            };
            
            options.complete = function(jqXHR, textStatus) {
                activeRequests--;
                if (activeRequests === 0) {
                    HTHDebug.updateAjaxTracker('success', 'AJAX Complete');
                    setTimeout(function() {
                        $('.hth-ajax-tracker').fadeOut();
                    }, 2000);
                } else {
                    HTHDebug.updateAjaxTracker('loading', 'AJAX: ' + activeRequests + ' active');
                }
                if (originalComplete) originalComplete.apply(this, arguments);
            };
            
            return originalAjax.apply(this, arguments);
        };
    };

    /**
     * Update AJAX tracker display
     */
    HTHDebug.updateAjaxTracker = function(status, message) {
        var $tracker = $('.hth-ajax-tracker');
        
        if ($tracker.length === 0) {
            $tracker = $('<div class="hth-ajax-tracker"></div>');
            $('body').append($tracker);
        }
        
        $tracker.removeClass('loading error success').addClass(status);
        $tracker.text(message).show();
    };

    /**
     * Setup keyboard shortcuts for debugging
     */
    HTHDebug.setupKeyboardShortcuts = function() {
        $(document).on('keydown', function(e) {
            // Ctrl + Shift + D: Toggle debug info
            if (e.ctrlKey && e.shiftKey && e.keyCode === 68) {
                e.preventDefault();
                $('.hth-debug-panel').toggle();
            }
            
            // Ctrl + Shift + C: Toggle console
            if (e.ctrlKey && e.shiftKey && e.keyCode === 67) {
                e.preventDefault();
                HTHDebug.toggleConsole();
            }
            
            // Ctrl + Shift + G: Toggle grid
            if (e.ctrlKey && e.shiftKey && e.keyCode === 71) {
                e.preventDefault();
                HTHDebug.toggleGrid();
            }
            
            // Ctrl + Shift + H: Show shortcuts help
            if (e.ctrlKey && e.shiftKey && e.keyCode === 72) {
                e.preventDefault();
                HTHDebug.showShortcuts();
            }
        });
    };

    /**
     * Toggle debug console
     */
    HTHDebug.toggleConsole = function() {
        var $console = $('.hth-debug-console');
        
        if ($console.length === 0) {
            $console = $('<div class="hth-debug-console"></div>');
            $('body').append($console);
        }
        
        $console.toggleClass('active');
    };

    /**
     * Toggle debug grid
     */
    HTHDebug.toggleGrid = function() {
        var $grid = $('.hth-debug-grid');
        
        if ($grid.length === 0) {
            $grid = $('<div class="hth-debug-grid"></div>');
            $('body').append($grid);
        } else {
            $grid.remove();
        }
    };

    /**
     * Show keyboard shortcuts
     */
    HTHDebug.showShortcuts = function() {
        var $shortcuts = $('.hth-debug-shortcuts');
        
        if ($shortcuts.length === 0) {
            $shortcuts = $('<div class="hth-debug-shortcuts">');
            $shortcuts.html(
                '<h5>Debug Shortcuts</h5>' +
                '<div class="shortcut"><span class="key">Ctrl+Shift+D</span><span class="desc">Toggle Debug Panel</span></div>' +
                '<div class="shortcut"><span class="key">Ctrl+Shift+C</span><span class="desc">Toggle Console</span></div>' +
                '<div class="shortcut"><span class="key">Ctrl+Shift+G</span><span class="desc">Toggle Grid</span></div>' +
                '<div class="shortcut"><span class="key">Ctrl+Shift+H</span><span class="desc">Show This Help</span></div>'
            );
            $('body').append($shortcuts);
        }
        
        $shortcuts.toggleClass('visible');
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $shortcuts.removeClass('visible');
        }, 5000);
    };

    /**
     * Log environment information
     */
    HTHDebug.logEnvironmentInfo = function() {
        console.group('HTH Plugin Environment');
        console.log('Plugin Version:', hthDebug.pluginVersion || '1.0.0');
        console.log('WordPress Version:', hthDebug.wpVersion || 'Unknown');
        console.log('PHP Version:', hthDebug.phpVersion || 'Unknown');
        console.log('Current User:', hthDebug.currentUser || 'Guest');
        console.log('Memory Usage:', HTHDebug.formatBytes(hthDebug.memoryUsage || 0));
        console.log('Memory Limit:', hthDebug.memoryLimit || 'Unknown');
        console.log('Load Time:', hthDebug.loadTime ? hthDebug.loadTime.toFixed(3) + 's' : 'Unknown');
        console.groupEnd();
    };

    /**
     * Format bytes for display
     */
    HTHDebug.formatBytes = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    /**
     * Add log entry to debug console
     */
    HTHDebug.log = function(message, type) {
        type = type || 'info';
        
        var $console = $('.hth-debug-console');
        if ($console.length) {
            var timestamp = new Date().toLocaleTimeString();
            var $line = $('<div class="console-line ' + type + '">[' + timestamp + '] ' + message + '</div>');
            $console.append($line);
            $console.scrollTop($console[0].scrollHeight);
        }
        
        // Also log to browser console
        console.log('HTH Debug:', message);
    };

    // Initialize when document is ready
    $(document).ready(function() {
        HTHDebug.init();
    });

})(jQuery);
