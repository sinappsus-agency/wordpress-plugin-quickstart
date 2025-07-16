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
}

// WordPress Filter Hooks (some of the most commonly used):
// - the_content: Modifies post/page content before display
// - the_title: Modifies post/page titles
// - the_excerpt: Modifies post excerpts
// - wp_title: Modifies the page title in the <title> tag
// - body_class: Adds classes to the body tag
// - post_class: Adds classes to post containers
// - nav_menu_css_class: Modifies navigation menu CSS classes
// - wp_nav_menu_items: Modifies navigation menu items
// - comment_text: Modifies comment text before display
// - get_comment_author: Modifies comment author name
// - login_redirect: Modifies login redirect URL
// - logout_redirect: Modifies logout redirect URL
// - wp_mail: Modifies email parameters before sending
// - upload_mimes: Modifies allowed file types for uploads
// - intermediate_image_sizes: Modifies available image sizes
// - posts_per_page: Modifies number of posts per page
// - pre_get_posts: Modifies query parameters before execution
// - query_vars: Adds custom query variables
// - rewrite_rules: Modifies URL rewrite rules
// - admin_footer_text: Modifies admin footer text
// - update_footer: Modifies admin footer version text
// - locale: Modifies the site locale
// - sanitize_file_name: Modifies uploaded file names
// - wp_get_attachment_url: Modifies attachment URLs
// - excerpt_length: Modifies excerpt length
// - excerpt_more: Modifies "read more" text for excerpts
// - comment_form_defaults: Modifies comment form defaults
// - authenticate: Modifies user authentication
// - wp_dropdown_pages: Modifies page dropdown options
// - wp_dropdown_cats: Modifies category dropdown options
// - get_avatar: Modifies user avatars
// - single_template: Modifies single post template
// - page_template: Modifies page template
// - archive_template: Modifies archive template
// - search_template: Modifies search results template
// - 404_template: Modifies 404 error template
// - attachment_link: Modifies attachment page links
// - wp_redirect: Modifies redirect behavior
// - wp_die_handler: Modifies error handling
// - script_loader_tag: Modifies script tags
// - style_loader_tag: Modifies style tags
// - wp_get_current_user: Modifies current user object
// - user_contactmethods: Modifies user contact methods
// - show_admin_bar: Controls admin bar visibility
// - wp_mail_from: Modifies email sender address
// - wp_mail_from_name: Modifies email sender name
// - wp_mail_content_type: Modifies email content type
// - widget_title: Modifies widget titles
// - dynamic_sidebar_params: Modifies sidebar parameters
// - get_search_query: Modifies search query
// - get_search_form: Modifies search form HTML
// - wp_trim_excerpt: Modifies excerpt trimming
// - map_meta_cap: Modifies capability mapping
// - editable_roles: Modifies available user roles
// - role_has_cap: Modifies role capabilities
// - gettext: Modifies translation strings
// - plugin_action_links: Modifies plugin action links
// - media_upload_tabs: Modifies media upload tabs
// - attachment_fields_to_edit: Modifies attachment edit fields
// - image_send_to_editor: Modifies image insertion into editor
// - richedit_pre: Modifies content before rich editor
// - wp_editor_settings: Modifies editor settings
