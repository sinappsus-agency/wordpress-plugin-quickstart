<?php

/**
 * CHAPTER 5: SCRIPTS AND STYLES ENQUEUING
 * 
 * This chapter demonstrates how to properly enqueue CSS and JavaScript files in WordPress.
 * Proper enqueuing ensures compatibility, prevents conflicts, and follows WordPress best practices.
 * 
 * Key concepts covered:
 * - Frontend script and style enqueuing
 * - Admin script and style enqueuing
 * - Conditional loading based on page context
 * - Dependency management
 * - Script localization for AJAX
 * - Version control and cache busting
 * 
 * WORDPRESS ENQUEUING SYSTEM:
 * 
 * WordPress provides a robust system for loading CSS and JavaScript files:
 * - Dependency management: Automatically loads required dependencies
 * - Conflict prevention: Prevents multiple loading of same files
 * - Proper placement: Scripts in footer, styles in head
 * - Version control: Helps with browser caching
 * - Conditional loading: Load only when needed
 * 
 * Key Functions:
 * - wp_enqueue_style(): Enqueue CSS files
 * - wp_enqueue_script(): Enqueue JavaScript files
 * - wp_localize_script(): Pass data from PHP to JavaScript
 * - wp_register_style(): Pre-register styles for later use
 * - wp_register_script(): Pre-register scripts for later use
 * - wp_dequeue_style(): Remove enqueued styles
 * - wp_dequeue_script(): Remove enqueued scripts
 * 
 * Hook Usage:
 * - wp_enqueue_scripts: For frontend scripts and styles
 * - admin_enqueue_scripts: For admin area scripts and styles
 * - login_enqueue_scripts: For login page scripts and styles
 * - customize_preview_init: For theme customizer preview
 */

// SECTION 1: FRONTEND ENQUEUING
// Loading scripts and styles for the public-facing side of the website

/**
 * Enqueue frontend scripts and styles
 * 
 * This function demonstrates proper frontend enqueuing:
 * - Loading styles first, then scripts
 * - Using plugin_dir_url() for correct file paths
 * - Specifying dependencies (jQuery)
 * - Loading scripts in footer for better performance
 * - Using plugin version for cache busting
 * 
 * wp_enqueue_style() parameters:
 * - $handle: Unique name for the stylesheet
 * - $src: URL to the stylesheet
 * - $deps: Array of dependency handles
 * - $ver: Version number for cache busting
 * - $media: Media type (all, screen, print, etc.)
 * 
 * wp_enqueue_script() parameters:
 * - $handle: Unique name for the script
 * - $src: URL to the script
 * - $deps: Array of dependency handles
 * - $ver: Version number for cache busting
 * - $in_footer: Whether to load in footer (true recommended)
 */
function hth_enqueue_frontend_scripts() {
    // Only load on frontend (not admin)
    if (is_admin()) {
        return;
    }

    // Enqueue main plugin stylesheet
    // Note: In a real plugin, you would create actual CSS files
    // For this educational example, we're showing the proper structure
    wp_enqueue_style(
        'hth-sample-plugin-style',                           // Handle
        plugin_dir_url(__FILE__) . '../assets/css/style.css', // URL (Note: file doesn't exist in this example)
        array(),                                             // Dependencies
        '1.0.0',                                            // Version
        'all'                                               // Media type
    );

    // Enqueue main plugin script
    wp_enqueue_script(
        'hth-sample-plugin-script',                         // Handle
        plugin_dir_url(__FILE__) . '../assets/js/script.js', // URL (Note: file doesn't exist in this example)
        array('jquery'),                                    // Dependencies (requires jQuery)
        '1.0.0',                                           // Version
        true                                               // Load in footer
    );

    // Example: Enqueue a specific script for single posts only
    if (is_single()) {
        wp_enqueue_script(
            'hth-single-post-script',
            plugin_dir_url(__FILE__) . '../assets/js/single-post.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }

    // Example: Conditional loading for specific post types
    if (is_singular('book')) {
        wp_enqueue_style(
            'hth-book-style',
            plugin_dir_url(__FILE__) . '../assets/css/book.css',
            array('hth-sample-plugin-style'), // Depends on main style
            '1.0.0'
        );
        
        wp_enqueue_script(
            'hth-book-script',
            plugin_dir_url(__FILE__) . '../assets/js/book.js',
            array('jquery', 'hth-sample-plugin-script'), // Multiple dependencies
            '1.0.0',
            true
        );
    }

    // Example: Load scripts for taxonomy archives
    if (is_tax('genre')) {
        wp_enqueue_script(
            'hth-genre-script',
            plugin_dir_url(__FILE__) . '../assets/js/genre.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'hth_enqueue_frontend_scripts');

// SECTION 2: ADMIN ENQUEUING
// Loading scripts and styles for the WordPress admin area

/**
 * Enqueue admin scripts and styles
 * 
 * This function demonstrates admin area enqueuing:
 * - Using admin_enqueue_scripts hook
 * - Page-specific loading with $hook parameter
 * - Different styling for admin interface
 * - Media upload functionality
 * - Color picker integration
 * 
 * The $hook parameter allows page-specific loading:
 * - 'edit.php': Edit posts/pages list
 * - 'post.php': Edit single post/page
 * - 'post-new.php': Add new post/page
 * - 'admin.php': Custom admin pages
 * - 'options-general.php': General settings page
 * - 'themes.php': Appearance menu
 * - 'plugins.php': Plugins page
 * 
 * @param string $hook The current admin page hook
 */
function hth_enqueue_admin_scripts($hook) {
    // Global admin styles (loaded on all admin pages)
    wp_enqueue_style(
        'hth-admin-style',
        plugin_dir_url(__FILE__) . '../assets/css/admin.css',
        array(),
        '1.0.0'
    );

    // Load on specific admin pages only
    $allowed_pages = array(
        'edit.php',           // Post list pages
        'post.php',           // Edit post page
        'post-new.php',       // New post page
        'admin.php'           // Custom admin pages
    );

    if (!in_array($hook, $allowed_pages)) {
        return; // Exit if not on allowed pages
    }

    // Enqueue admin JavaScript
    wp_enqueue_script(
        'hth-admin-script',
        plugin_dir_url(__FILE__) . '../assets/js/admin.js',
        array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
        '1.0.0',
        true
    );

    // Load only on our custom data page
    if (isset($_GET['page']) && $_GET['page'] === 'hth-custom-data') {
        // Enqueue WordPress media scripts for file uploads
        wp_enqueue_media();
        
        // Enqueue color picker
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');
        
        // Enqueue custom script for our admin page
        wp_enqueue_script(
            'hth-custom-data-script',
            plugin_dir_url(__FILE__) . '../assets/js/custom-data.js',
            array('jquery', 'wp-color-picker', 'media-upload'),
            '1.0.0',
            true
        );
    }

    // Load only for book post type
    global $post_type;
    if ($post_type === 'book') {
        wp_enqueue_script(
            'hth-book-admin-script',
            plugin_dir_url(__FILE__) . '../assets/js/book-admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Enqueue style for book meta boxes
        wp_enqueue_style(
            'hth-book-admin-style',
            plugin_dir_url(__FILE__) . '../assets/css/book-admin.css',
            array(),
            '1.0.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'hth_enqueue_admin_scripts');

// SECTION 3: SCRIPT LOCALIZATION
// Passing data from PHP to JavaScript

/**
 * Enqueue scripts with localized data for AJAX
 * 
 * This function demonstrates script localization:
 * - Passing PHP data to JavaScript
 * - Creating AJAX endpoints
 * - Security with nonces
 * - REST API integration
 * - Dynamic configuration
 * 
 * wp_localize_script() creates a JavaScript object with:
 * - PHP variables accessible in JS
 * - AJAX URLs and nonces
 * - Translations for internationalization
 * - Configuration options
 */
function hth_enqueue_ajax_scripts() {
    // Only load AJAX scripts where needed
    if (!is_single('book') && !is_tax('genre')) {
        return;
    }

    // Enqueue the AJAX script
    wp_enqueue_script(
        'hth-ajax-script',
        plugin_dir_url(__FILE__) . '../assets/js/ajax.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize script with data for AJAX calls
    wp_localize_script(
        'hth-ajax-script',     // Script handle to attach data to
        'hthAjax',             // JavaScript object name
        array(
            // AJAX configuration
            'ajaxUrl'          => admin_url('admin-ajax.php'),
            'restUrl'          => rest_url('hth/v1/'),
            'nonce'            => wp_create_nonce('hth_ajax_nonce'),
            'restNonce'        => wp_create_nonce('wp_rest'),
            
            // Current page context
            'currentPostId'    => get_queried_object_id(),
            'currentUserId'    => get_current_user_id(),
            'isUserLoggedIn'   => is_user_logged_in(),
            
            // Plugin configuration
            'pluginUrl'        => plugin_dir_url(__FILE__),
            'version'          => '1.0.0',
            
            // Text strings for internationalization
            'strings'          => array(
                'loading'          => __('Loading...', 'hth-sample-plugin'),
                'error'            => __('An error occurred. Please try again.', 'hth-sample-plugin'),
                'success'          => __('Operation completed successfully.', 'hth-sample-plugin'),
                'confirmDelete'    => __('Are you sure you want to delete this item?', 'hth-sample-plugin'),
                'noResults'        => __('No results found.', 'hth-sample-plugin')
            ),
            
            // Feature flags
            'features'         => array(
                'enableSearch'     => true,
                'enableFiltering'  => true,
                'enableSorting'    => true,
                'debugMode'        => defined('WP_DEBUG') && WP_DEBUG
            )
        )
    );
}
add_action('wp_enqueue_scripts', 'hth_enqueue_ajax_scripts', 20); // Late priority

// SECTION 4: CONDITIONAL LOADING
// Smart loading based on content and context

/**
 * Conditionally enqueue scripts based on shortcode presence
 * 
 * This function demonstrates:
 * - Checking for shortcode usage
 * - Content parsing before enqueuing
 * - Performance optimization
 * - Dynamic script loading
 */
function hth_enqueue_shortcode_scripts() {
    // Only check on singular pages (posts, pages, custom post types)
    if (!is_singular()) {
        return;
    }

    global $post;
    
    // Check if post content contains our shortcodes
    $has_hth_shortcode = (
        has_shortcode($post->post_content, 'hth_custom_message') ||
        has_shortcode($post->post_content, 'hth_button') ||
        has_shortcode($post->post_content, 'hth_box') ||
        has_shortcode($post->post_content, 'hth_tabs') ||
        has_shortcode($post->post_content, 'hth_recent_posts')
    );

    if ($has_hth_shortcode) {
        // Enqueue shortcode-specific styles
        wp_enqueue_style(
            'hth-shortcodes-style',
            plugin_dir_url(__FILE__) . '../assets/css/shortcodes.css',
            array(),
            '1.0.0'
        );

        // Check for specific shortcodes that need JavaScript
        if (has_shortcode($post->post_content, 'hth_tabs')) {
            wp_enqueue_script(
                'hth-tabs-script',
                plugin_dir_url(__FILE__) . '../assets/js/tabs.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }

        if (has_shortcode($post->post_content, 'hth_ajax_form')) {
            wp_enqueue_script(
                'hth-ajax-form-script',
                plugin_dir_url(__FILE__) . '../assets/js/ajax-form.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            // Localize for AJAX form
            wp_localize_script(
                'hth-ajax-form-script',
                'hthForm',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('hth_ajax_form_nonce')
                )
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'hth_enqueue_shortcode_scripts', 25);

// SECTION 5: THEME INTEGRATION
// Ensuring compatibility with different themes

/**
 * Enqueue theme compatibility styles
 * 
 * This function demonstrates:
 * - Theme detection and compatibility
 * - CSS framework integration
 * - Responsive design considerations
 * - Theme-specific overrides
 */
function hth_enqueue_theme_compatibility() {
    // Get current theme information
    $theme = wp_get_theme();
    $theme_name = $theme->get('Name');
    $theme_template = get_template();

    // Enqueue base compatibility styles
    wp_enqueue_style(
        'hth-theme-compat',
        plugin_dir_url(__FILE__) . '../assets/css/theme-compatibility.css',
        array(),
        '1.0.0'
    );

    // Load theme-specific overrides
    $theme_specific_styles = array(
        'twentytwentyone' => 'twenty-twenty-one.css',
        'twentytwentytwo' => 'twenty-twenty-two.css',
        'astra'           => 'astra.css',
        'oceanwp'         => 'oceanwp.css',
        'generatepress'   => 'generatepress.css'
    );

    if (isset($theme_specific_styles[$theme_template])) {
        wp_enqueue_style(
            'hth-theme-' . $theme_template,
            plugin_dir_url(__FILE__) . '../assets/css/themes/' . $theme_specific_styles[$theme_template],
            array('hth-theme-compat'),
            '1.0.0'
        );
    }

    // Check for common CSS frameworks
    if (wp_style_is('bootstrap', 'enqueued') || wp_style_is('bootstrap-css', 'enqueued')) {
        wp_enqueue_style(
            'hth-bootstrap-compat',
            plugin_dir_url(__FILE__) . '../assets/css/bootstrap-compatibility.css',
            array(),
            '1.0.0'
        );
    }

    if (wp_style_is('foundation', 'enqueued')) {
        wp_enqueue_style(
            'hth-foundation-compat',
            plugin_dir_url(__FILE__) . '../assets/css/foundation-compatibility.css',
            array(),
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'hth_enqueue_theme_compatibility', 5); // Early priority

// SECTION 6: PERFORMANCE OPTIMIZATION
// Optimizing script and style loading

/**
 * Optimize script loading with dequeue and conditional loading
 * 
 * This function demonstrates:
 * - Removing unnecessary scripts
 * - Combining similar functionality
 * - Reducing HTTP requests
 * - Improving page load times
 */
function hth_optimize_script_loading() {
    // Remove jQuery Migrate on frontend if not needed
    if (!is_admin() && !is_customize_preview()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', includes_url('/js/jquery/jquery.min.js'), array(), null, true);
        wp_enqueue_script('jquery');
    }

    // Don't load WordPress emoji scripts if not needed
    if (!is_admin()) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    // Conditionally load WordPress scripts
    if (!is_singular() || !comments_open()) {
        wp_dequeue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'hth_optimize_script_loading', 100); // Very late priority

/**
 * Add async and defer attributes to scripts
 * 
 * This function demonstrates:
 * - Adding async loading for non-critical scripts
 * - Deferring script execution
 * - Improving perceived performance
 * - Script loading optimization
 * 
 * @param string $tag The script tag
 * @param string $handle The script handle
 * @param string $src The script source URL
 * @return string Modified script tag
 */
function hth_add_async_defer_attributes($tag, $handle, $src) {
    // Scripts that can be loaded asynchronously
    $async_scripts = array(
        'hth-analytics-script',
        'hth-social-sharing',
        'hth-external-api'
    );

    // Scripts that can be deferred
    $defer_scripts = array(
        'hth-sample-plugin-script',
        'hth-tabs-script',
        'hth-ajax-form-script'
    );

    if (in_array($handle, $async_scripts)) {
        return str_replace(' src', ' async src', $tag);
    }

    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'hth_add_async_defer_attributes', 10, 3);

// SECTION 7: DEVELOPMENT AND DEBUGGING
// Tools for development and debugging

/**
 * Enqueue development scripts and styles
 * 
 * This function demonstrates:
 * - Debug-mode specific loading
 * - Development vs production differences
 * - Source maps and debugging tools
 * - Live reload functionality
 */
function hth_enqueue_development_assets() {
    // Only load in development mode
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    // Load unminified versions for debugging
    wp_enqueue_script(
        'hth-debug-script',
        plugin_dir_url(__FILE__) . '../assets/js/debug.js',
        array('jquery'),
        time(), // Use timestamp for cache busting during development
        true
    );

    // Load development styles
    wp_enqueue_style(
        'hth-debug-style',
        plugin_dir_url(__FILE__) . '../assets/css/debug.css',
        array(),
        time() // Use timestamp for cache busting during development
    );

    // Add debugging information to JavaScript
    wp_localize_script(
        'hth-debug-script',
        'hthDebug',
        array(
            'isDebugMode'    => true,
            'currentUser'    => wp_get_current_user()->user_login,
            'phpVersion'     => PHP_VERSION,
            'wpVersion'      => get_bloginfo('version'),
            'pluginVersion'  => '1.0.0',
            'memoryUsage'    => memory_get_usage(true),
            'memoryLimit'    => ini_get('memory_limit'),
            'loadTime'       => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        )
    );
}
add_action('wp_enqueue_scripts', 'hth_enqueue_development_assets', 999); // Latest priority
add_action('admin_enqueue_scripts', 'hth_enqueue_development_assets', 999);

/**
 * BEST PRACTICES AND IMPORTANT NOTES:
 * 
 * 1. File Organization:
 *    Create an assets directory structure:
 *    /assets/
 *      /css/
 *        style.css (main frontend styles)
 *        admin.css (admin area styles)
 *        shortcodes.css (shortcode-specific styles)
 *        theme-compatibility.css (theme overrides)
 *      /js/
 *        script.js (main frontend script)
 *        admin.js (admin area script)
 *        ajax.js (AJAX functionality)
 *        tabs.js (tab shortcode script)
 *      /images/
 *        icons/ (plugin icons)
 *        backgrounds/ (background images)
 * 
 * 2. Version Control:
 *    - Use semantic versioning (1.0.0, 1.0.1, 1.1.0)
 *    - Update versions when files change
 *    - Use plugin main file version as default
 *    - Consider using file modification time for development
 * 
 * 3. Dependencies:
 *    - Always declare script dependencies
 *    - Common WordPress dependencies:
 *      * jquery: jQuery library
 *      * jquery-ui-core: jQuery UI base
 *      * jquery-ui-datepicker: Date picker
 *      * wp-color-picker: WordPress color picker
 *      * media-upload: Media upload functionality
 *      * thickbox: Modal dialogs
 * 
 * 4. Performance:
 *    - Load scripts in footer when possible
 *    - Use conditional loading (only where needed)
 *    - Minimize HTTP requests
 *    - Combine and minify for production
 *    - Use async/defer for non-critical scripts
 * 
 * 5. Security:
 *    - Always use wp_localize_script() for AJAX
 *    - Include nonces for security
 *    - Sanitize any data passed to JavaScript
 *    - Validate user capabilities
 * 
 * 6. Internationalization:
 *    - Include translatable strings in localized data
 *    - Use text domain consistently
 *    - Provide context for translators
 * 
 * 7. Testing:
 *    - Test with different themes
 *    - Check for JavaScript conflicts
 *    - Verify mobile responsiveness
 *    - Test with other plugins
 * 
 * COMMON ISSUES AND SOLUTIONS:
 * 
 * Issue: Scripts not loading
 * Solution: Check file paths, ensure files exist, verify hook usage
 * 
 * Issue: JavaScript errors
 * Solution: Check dependencies, verify jQuery is loaded, check for conflicts
 * 
 * Issue: Styles not applying
 * Solution: Check CSS specificity, verify file paths, check for theme conflicts
 * 
 * Issue: AJAX not working
 * Solution: Verify nonces, check AJAX URL, ensure proper localization
 * 
 * Issue: Performance problems
 * Solution: Use conditional loading, minimize file sizes, optimize images
 */

