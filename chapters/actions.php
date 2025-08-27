<?php

/**
 * CHAPTER 1: ACTIONS AND FILTERS (HOOKS)
 * 
 * This chapter demonstrates WordPress hooks system - the foundation of WordPress extensibility.
 * Hooks allow you to "hook into" WordPress at specific points to execute custom code.
 * 
 * Key concepts covered:
 * - Action hooks: Execute custom code at specific points
 * - Filter hooks: Modify data before it's displayed or processed
 * - Hook priority and parameters
 * - Creating custom hooks
 * - Removing hooks
 * - Conditional hook execution
 * 
 * THE WORDPRESS HOOKS SYSTEM:
 * 
 * WordPress uses an event-driven architecture where code execution happens at specific
 * points called "hooks". There are two types of hooks:
 * 
 * 1. ACTION HOOKS (do_action):
 *    - Execute custom code at specific points
 *    - Don't return values
 *    - Used for: adding functionality, outputting content, performing tasks
 * 
 * 2. FILTER HOOKS (apply_filters):
 *    - Modify data before it's used
 *    - Must return modified data
 *    - Used for: changing content, modifying settings, altering behavior
 * 
 * Hook Functions:
 * - add_action($hook, $function, $priority, $args): Attach function to action hook
 * - add_filter($hook, $function, $priority, $args): Attach function to filter hook
 * - remove_action($hook, $function, $priority): Remove function from action hook
 * - remove_filter($hook, $function, $priority): Remove function from filter hook
 * - do_action($hook, $args...): Execute all functions attached to action hook
 * - apply_filters($hook, $value, $args...): Apply all functions attached to filter hook
 * - has_action($hook, $function): Check if function is attached to action hook
 * - has_filter($hook, $function): Check if function is attached to filter hook
 * - current_action(): Get the current action hook being executed
 * - current_filter(): Get the current filter hook being executed
 * - doing_action($hook): Check if specific action is currently being executed
 * - doing_filter($hook): Check if specific filter is currently being executed
 * 
 * Hook Parameters:
 * - $hook: The hook name (string)
 * - $function: Function name or array(object, method) or closure
 * - $priority: Execution priority (integer, default 10, lower = earlier)
 * - $args: Number of arguments the function accepts (default 1)
 */

// SECTION 1: ACTION HOOKS
// Actions execute code at specific points without returning values

/**
 * Example 1: Simple action hook
 * 
 * This action runs when the footer of the site is loaded and displays a message.
 * 
 * wp_footer hook is called in the footer.php template of most themes.
 * It's commonly used for:
 * - Adding tracking codes (Google Analytics, etc.)
 * - Inserting JavaScript
 * - Adding footer widgets or content
 * - Loading scripts that should run after page content
 */
add_action('wp_footer', 'hth_footer_message');
function hth_footer_message() {
    // Only show on frontend, not in admin
    if (!is_admin()) {
        echo "<p style='text-align:center; margin-top:20px; font-style:italic;'>Thanks for visiting our site!</p>";
    }
}

/**
 * Example 2: Action with priority
 * 
 * This demonstrates how priority affects execution order.
 * Lower priority numbers execute first.
 * 
 * Default priority is 10. Common priorities:
 * - 1-5: Very early execution
 * - 10: Default priority
 * - 15-20: Later execution
 * - 99-100: Very late execution
 */
add_action('wp_head', 'hth_early_head_content', 5);
function hth_early_head_content() {
    echo "<!-- Early head content (priority 5) -->\n";
}

add_action('wp_head', 'hth_late_head_content', 15);
function hth_late_head_content() {
    echo "<!-- Late head content (priority 15) -->\n";
}

/**
 * Example 3: Action with multiple parameters
 * 
 * This demonstrates how to accept multiple parameters in hook functions.
 * The fourth parameter in add_action() specifies how many arguments to accept.
 */
add_action('save_post', 'hth_post_saved_notification', 10, 3);
function hth_post_saved_notification($post_id, $post, $update) {
    // Only run for published posts, not drafts or auto-saves
    if ($post->post_status === 'publish' && !wp_is_post_revision($post_id)) {
        // Log the post save event
        error_log("Post saved: {$post->post_title} (ID: {$post_id}, Updated: " . ($update ? 'Yes' : 'No') . ")");
    }
}

/**
 * COMMONLY USED ACTION HOOKS:
 * 
 * WordPress Lifecycle:
 * - init: After WordPress loads, before headers sent
 * - wp_loaded: After WordPress fully loads
 * - template_redirect: Before template is loaded
 * - wp_head: In HTML <head> section
 * - wp_footer: In HTML footer
 * - wp_enqueue_scripts: Proper place to enqueue scripts/styles
 * - shutdown: When PHP execution ends
 * 
 * Admin Hooks:
 * - admin_init: Every admin page load
 * - admin_menu: Add admin menu items
 * - admin_enqueue_scripts: Enqueue admin scripts/styles
 * - admin_notices: Display admin notices
 * - admin_footer: Admin footer
 * 
 * Post/Page Hooks:
 * - save_post: When post is saved
 * - delete_post: When post is deleted
 * - wp_insert_post: When new post is created
 * - before_delete_post: Before post deletion
 * - transition_post_status: When post status changes
 * 
 * User Hooks:
 * - wp_login: When user logs in
 * - wp_logout: When user logs out
 * - user_register: When new user registers
 * - profile_update: When user profile is updated
 * - delete_user: When user is deleted
 * 
 * Comment Hooks:
 * - comment_post: When comment is posted
 * - wp_insert_comment: When comment is inserted
 * - delete_comment: When comment is deleted
 * - comment_approved: When comment is approved
 * 
 * Theme Hooks:
 * - after_setup_theme: After theme loads
 * - switch_theme: When theme is switched
 * - widgets_init: When widgets are initialized
 * 
 * AJAX Hooks:
 * - wp_ajax_{action}: AJAX for logged-in users
 * - wp_ajax_nopriv_{action}: AJAX for non-logged-in users
 * 
 * Cron Hooks:
 * - wp_scheduled_delete: Clean up scheduled posts
 * - wp_scheduled_auto_draft_delete: Clean up auto-drafts
 * - {custom_cron_hook}: Custom scheduled events
 */

// SECTION 2: FILTER HOOKS
// Filters modify data before it's displayed or processed

/**
 * Example 1: Content filter
 * 
 * This filter modifies post content before it's displayed.
 * Filters must always return the modified data.
 * 
 * the_content filter is one of the most commonly used filters.
 * It's applied to post content before display.
 */
add_filter('the_content', 'hth_append_text_to_post');
function hth_append_text_to_post($content) {
    // Only modify content on single posts, not pages or archives
    if (is_single() && in_the_loop() && is_main_query()) {
        $content .= '<div class="post-footer-note">';
        $content .= '<p><em>Thank you for reading! Share your thoughts in the comments below.</em></p>';
        $content .= '</div>';
    }
    return $content; // Always return the content!
}

/**
 * Example 2: Title filter with parameters
 * 
 * This filter demonstrates:
 * - Using multiple parameters in filters
 * - Conditional filtering based on context
 * - Modifying data based on post type
 */
add_filter('the_title', 'hth_modify_post_title', 10, 2);
function hth_modify_post_title($title, $post_id) {
    // Only modify titles on frontend single posts
    if (is_single() && !is_admin() && in_the_loop()) {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'post') {
            $title = '📖 ' . $title; // Add an emoji prefix
        }
    }
    return $title;
}

/**
 * Example 3: Query modification filter
 * 
 * This demonstrates how to modify WordPress queries before they execute.
 * This is very powerful for changing how content is displayed.
 */
add_filter('pre_get_posts', 'hth_modify_main_query');
function hth_modify_main_query($query) {
    // Only modify the main query on the frontend
    if (!is_admin() && $query->is_main_query()) {
        // On the home page, show only 5 posts
        if ($query->is_home()) {
            $query->set('posts_per_page', 5);
        }
        
        // On category pages, exclude posts from category ID 1
        if ($query->is_category()) {
            $query->set('cat', '-1');
        }
    }
}

/**
 * COMMONLY USED FILTER HOOKS:
 * 
 * Content Filters:
 * - the_content: Modify post/page content before display
 * - the_title: Modify post/page titles
 * - the_excerpt: Modify post excerpts
 * - wp_title: Modify page title in <title> tag
 * - get_the_excerpt: Modify excerpt retrieval
 * - wp_trim_excerpt: Modify excerpt trimming
 * - excerpt_length: Modify excerpt word count
 * - excerpt_more: Modify "read more" text
 * 
 * Template Filters:
 * - body_class: Add classes to body tag
 * - post_class: Add classes to post containers
 * - single_template: Modify single post template
 * - page_template: Modify page template
 * - archive_template: Modify archive template
 * - search_template: Modify search template
 * - 404_template: Modify 404 template
 * 
 * Navigation Filters:
 * - nav_menu_css_class: Modify menu CSS classes
 * - wp_nav_menu_items: Modify menu items
 * - wp_page_menu: Modify page menu
 * - wp_list_pages: Modify page listing
 * 
 * Query Filters:
 * - pre_get_posts: Modify queries before execution
 * - posts_per_page: Modify posts per page
 * - query_vars: Add custom query variables
 * - request: Modify query request
 * 
 * User & Authentication Filters:
 * - authenticate: Modify user authentication
 * - wp_login_errors: Modify login error messages
 * - login_redirect: Modify login redirect URL
 * - logout_redirect: Modify logout redirect URL
 * - user_contactmethods: Modify user contact methods
 * - get_avatar: Modify user avatars
 * 
 * Admin Filters:
 * - admin_footer_text: Modify admin footer text
 * - update_footer: Modify admin version text
 * - plugin_action_links: Modify plugin action links
 * - manage_posts_columns: Modify post list columns
 * - manage_pages_columns: Modify page list columns
 * 
 * Media Filters:
 * - upload_mimes: Modify allowed file types
 * - wp_get_attachment_url: Modify attachment URLs
 * - image_send_to_editor: Modify image insertion
 * - media_upload_tabs: Modify media upload tabs
 * - attachment_fields_to_edit: Modify attachment fields
 * 
 * Email Filters:
 * - wp_mail: Modify email parameters
 * - wp_mail_from: Modify sender email
 * - wp_mail_from_name: Modify sender name
 * - wp_mail_content_type: Modify email content type
 * 
 * Security Filters:
 * - sanitize_file_name: Modify uploaded file names
 * - wp_die_handler: Modify error handling
 * - map_meta_cap: Modify capability mapping
 * - editable_roles: Modify available user roles
 * 
 * Widget Filters:
 * - widget_title: Modify widget titles
 * - dynamic_sidebar_params: Modify sidebar parameters
 * - widget_display_callback: Modify widget display
 * 
 * Internationalization Filters:
 * - locale: Modify site locale
 * - gettext: Modify translation strings
 * - gettext_with_context: Modify contextual translations
 */

// SECTION 3: REMOVING HOOKS
// Sometimes you need to remove hooks added by WordPress core or other plugins

/**
 * Example of removing actions and filters
 * 
 * This demonstrates how to remove existing hooks.
 * This is useful when you want to disable default WordPress behavior.
 */
function hth_remove_default_hooks() {
    // Remove the WordPress version from head
    remove_action('wp_head', 'wp_generator');
    
    // Remove RSD link from head
    remove_action('wp_head', 'rsd_link');
    
    // Remove Windows Live Writer manifest link
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove automatic paragraph formatting from excerpts
    remove_filter('the_excerpt', 'wpautop');
    
    // Remove capital P filter (changes "wordpress" to "WordPress")
    remove_filter('the_content', 'capital_P_dangit', 11);
    remove_filter('the_title', 'capital_P_dangit', 11);
}

// Hook the removal function to init
add_action('init', 'hth_remove_default_hooks');

// SECTION 4: CUSTOM HOOKS
// Creating your own action and filter hooks for extensibility

/**
 * Example of creating a custom action hook
 * 
 * This demonstrates how to create your own hooks that other developers
 * (or your own code) can hook into.
 */
function hth_display_custom_content() {
    echo '<div class="custom-content">';
    
    // Fire a custom action before content
    do_action('hth_before_custom_content');
    
    echo '<h2>Custom Content Section</h2>';
    echo '<p>This is some custom content.</p>';
    
    // Fire a custom action after content
    do_action('hth_after_custom_content');
    
    echo '</div>';
}

/**
 * Example of creating a custom filter hook
 * 
 * This demonstrates how to create filter hooks that allow
 * modification of your plugin's data.
 */
function hth_get_greeting_message($name = 'User') {
    $message = "Hello, {$name}!";
    
    // Apply a custom filter to allow modification
    $message = apply_filters('hth_greeting_message', $message, $name);
    
    return $message;
}

// Examples of hooking into our custom hooks
add_action('hth_before_custom_content', 'hth_add_before_content');
function hth_add_before_content() {
    echo '<p><em>This content was added via custom hook!</em></p>';
}

add_filter('hth_greeting_message', 'hth_modify_greeting', 10, 2);
function hth_modify_greeting($message, $name) {
    return "🎉 " . $message . " Welcome to our site!";
}

// SECTION 5: CONDITIONAL HOOKS
// Executing hooks only under specific conditions

/**
 * Example of conditional hook execution
 * 
 * This demonstrates how to conditionally add hooks based on
 * various WordPress conditions.
 */
function hth_conditional_hooks() {
    // Only add footer script on single posts
    if (is_single()) {
        add_action('wp_footer', 'hth_single_post_footer');
    }

    // Only modify titles on the home page
    if (is_home()) {
        add_filter('the_title', 'hth_home_title_modifier');
    }

    // Only add admin styles in admin area
    if (is_admin()) {
        add_action('admin_enqueue_scripts', 'hth_admin_styles');
    }

    // Only run on specific post types
    if (is_singular('book')) {
        add_action('wp_head', 'hth_book_meta_tags');
    }

    // Only on archive pages
    if (is_archive()) {
        add_action('wp_footer', 'hth_archive_footer');
    }

    // Only on category archive
    if (is_category()) {
        add_action('wp_head', 'hth_category_head');
    }

    // Only on tag archive
    if (is_tag()) {
        add_action('wp_head', 'hth_tag_head');
    }

    // Only on author archive
    if (is_author()) {
        add_action('wp_footer', 'hth_author_footer');
    }

    // Only on search results page
    if (is_search()) {
        add_filter('the_content', 'hth_search_content_modifier');
    }

    // Only on 404 page
    if (is_404()) {
        add_action('wp_footer', 'hth_404_footer');
    }

    // Only on front page
    if (is_front_page()) {
        add_action('wp_head', 'hth_front_page_head');
    }

    // Only on page (not post)
    if (is_page()) {
        add_action('wp_footer', 'hth_page_footer');
    }

    // Only on attachment pages
    if (is_attachment()) {
        add_action('wp_head', 'hth_attachment_head');
    }

    // Only for logged-in users
    if (is_user_logged_in()) {
        add_action('wp_footer', 'hth_logged_in_footer');
    }

    // Only for specific user role (e.g., administrator)
    if (current_user_can('edit_posts')) {
        // multiple roles 
        $user = wp_get_current_user();
        $allowed_roles = array('editor', 'administrator', 'author');
        if (array_intersect($allowed_roles, $user->roles)) {

            add_action('admin_notices', 'hth_admin_notice');
        }
        
    }
}

// Hook to template_redirect (runs after query is determined)
add_action('template_redirect', 'hth_conditional_hooks');

// SECTION 6: ADVANCED HOOK TECHNIQUES
// Advanced patterns and techniques for working with hooks

/**
 * Example of hook priority and execution order
 * 
 * This demonstrates how priority affects execution order
 * and how to ensure your hooks run at the right time.
 */
add_action('wp_head', 'hth_very_early_head', 1);
function hth_very_early_head() {
    echo "<!-- Very early head content -->\n";
}

add_action('wp_head', 'hth_early_head', 5);
function hth_early_head() {
    echo "<!-- Early head content -->\n";
}

add_action('wp_head', 'hth_default_head'); // Default priority 10
function hth_default_head() {
    echo "<!-- Default priority head content -->\n";
}

add_action('wp_head', 'hth_late_head', 20);
function hth_late_head() {
    echo "<!-- Late head content -->\n";
}

/**
 * Example of using closures with hooks
 * 
 * This demonstrates how to use anonymous functions (closures)
 * with WordPress hooks.
 */
add_action('wp_footer', function() {
    echo '<script>console.log("Closure hook executed!");</script>';
});

/**
 * Example of using class methods with hooks
 * 
 * This demonstrates how to hook class methods to WordPress hooks.
 */
class HTH_Hook_Example {
    
    public function __construct() {
        // Hook instance method
        add_action('init', array($this, 'init_method'));
        
        // Hook static method
        add_action('wp_head', array(__CLASS__, 'static_method'));
    }
    
    public function init_method() {
        // Instance method hooked to init
        error_log('Instance method called on init');
    }
    
    public static function static_method() {
        // Static method hooked to wp_head
        echo "<!-- Static method output -->\n";
    }
}

// Instantiate the class to register hooks
new HTH_Hook_Example();

/**
 * BEST PRACTICES FOR HOOKS:
 * 
 * 1. Naming Conventions:
 *    - Prefix all function names with your plugin/theme prefix
 *    - Use descriptive names that explain what the hook does
 *    - Example: 'hth_modify_post_title' instead of 'modify_title'
 * 
 * 2. Hook Priority:
 *    - Use default priority (10) unless you have a specific reason to change it
 *    - Lower numbers run earlier, higher numbers run later
 *    - Common priorities: 1 (very early), 5 (early), 10 (default), 15 (late), 20 (very late)
 * 
 * 3. Performance Considerations:
 *    - Avoid heavy processing in frequently called hooks
 *    - Cache expensive operations when possible
 *    - Use conditional checks to limit hook execution
 * 
 * 4. Security:
 *    - Always sanitize input data in hooks
 *    - Validate user permissions before executing admin hooks
 *    - Escape output data properly
 * 
 * 5. Debugging:
 *    - Use error_log() for debugging hook execution
 *    - Check if hooks are running with has_action() and has_filter()
 *    - Use current_action() and current_filter() to identify context
 * 
 * 6. Documentation:
 *    - Document your custom hooks for other developers
 *    - Include parameter descriptions and examples
 *    - Mention when hooks are fired and what they do
 * 
 * 7. Testing:
 *    - Test hooks in different contexts (admin, frontend, AJAX)
 *    - Test with different user roles and capabilities
 *    - Test hook removal and modification
 * 
 * 8. Compatibility:
 *    - Check if functions exist before using them
 *    - Be aware of WordPress version compatibility
 *    - Consider multisite compatibility
 * 
 * DEBUGGING HOOKS:
 * 
 * // Check if a hook exists
 * if (has_action('init', 'your_function')) {
 *     // Hook is registered
 * }
 * 
 * // Get current action/filter
 * $current_action = current_action();
 * $current_filter = current_filter();
 * 
 * // Check if action is currently executing
 * if (doing_action('init')) {
 *     // We're currently in the init action
 * }
 * 
 * // List all hooks for debugging
 * global $wp_filter;
 * var_dump($wp_filter['init']); // Shows all functions hooked to init
 */
