<?php

/**
 * CHAPTER 2: SHORTCODES
 * 
 * This chapter demonstrates how to create and use shortcodes in WordPress.
 * Shortcodes allow you to embed dynamic content into posts, pages, and widgets
 * using simple, square-bracket syntax.
 * 
 * Key concepts covered:
 * - Basic shortcode creation
 * - Shortcodes with attributes
 * - Enclosing shortcodes (with content)
 * - Nested shortcodes
 * - Shortcode security and validation
 * - Advanced shortcode techniques
 * 
 * WORDPRESS SHORTCODE SYSTEM:
 * 
 * Shortcodes are a way to embed dynamic content in WordPress posts and pages.
 * They use square bracket syntax: [shortcode_name attribute="value"]
 * 
 * Types of shortcodes:
 * 1. Self-closing: [shortcode_name]
 * 2. With attributes: [shortcode_name attr1="value1" attr2="value2"]
 * 3. Enclosing: [shortcode_name]content here[/shortcode_name]
 * 
 * Shortcode Functions:
 * - add_shortcode($tag, $callback): Register a shortcode
 * - remove_shortcode($tag): Remove a shortcode
 * - shortcode_exists($tag): Check if shortcode exists
 * - do_shortcode($content): Execute shortcodes in content
 * - strip_shortcodes($content): Remove shortcodes from content
 * - shortcode_atts($pairs, $atts, $shortcode): Parse attributes
 * - shortcode_parse_atts($text): Parse attribute string
 * 
 * Best Practices:
 * - Always sanitize and validate input
 * - Use unique shortcode names with prefixes
 * - Provide sensible defaults for attributes
 * - Return content, don't echo it
 * - Handle both self-closing and enclosing formats
 * - Use proper escaping for output
 */

// SECTION 1: BASIC SHORTCODES
// Simple shortcodes without attributes

/**
 * Example 1: Simple shortcode without attributes
 * 
 * This demonstrates the most basic shortcode functionality.
 * Usage: [hth_simple_message]
 * 
 * Key points:
 * - Function must return content, not echo it
 * - Always escape output for security
 * - Use descriptive function names with prefixes
 */
function hth_simple_message_shortcode() {
    return '<div class="hth-simple-message">
                <p>🎉 This is a simple shortcode message!</p>
            </div>';
}
add_shortcode('hth_simple_message', 'hth_simple_message_shortcode');

/**
 * Example 2: Shortcode that returns dynamic content
 * 
 * This shortcode displays the current date and time.
 * Usage: [hth_current_date]
 * 
 * Demonstrates:
 * - Using PHP functions within shortcodes
 * - Returning dynamic content
 * - Proper date formatting
 */
function hth_current_date_shortcode() {
    $current_date = date('F j, Y g:i a');
    return '<span class="current-date">Current date: ' . esc_html($current_date) . '</span>';
}
add_shortcode('hth_current_date', 'hth_current_date_shortcode');

// SECTION 2: SHORTCODES WITH ATTRIBUTES
// Shortcodes that accept parameters for customization

/**
 * Example 3: Shortcode with attributes
 * 
 * This shortcode accepts attributes to customize the output.
 * Usage: [hth_custom_message message="Your custom text" color="blue"]
 * 
 * Demonstrates:
 * - Using shortcode_atts() to parse attributes
 * - Providing default values
 * - Attribute validation and sanitization
 * - CSS styling based on attributes
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function hth_custom_message_shortcode($atts) {
    // Parse attributes with defaults
    $atts = shortcode_atts(
        array(
            'message' => 'Hello, this is a custom message created by shortcode!',
            'color' => 'black',
            'size' => 'medium',
            'align' => 'left'
        ),
        $atts,
        'hth_custom_message' // Shortcode name for filtering
    );

    // Validate and sanitize attributes
    $message = sanitize_text_field($atts['message']);
    $color = sanitize_hex_color($atts['color']) ?: '#000000';
    $size = in_array($atts['size'], ['small', 'medium', 'large']) ? $atts['size'] : 'medium';
    $align = in_array($atts['align'], ['left', 'center', 'right']) ? $atts['align'] : 'left';

    // Build CSS classes based on attributes
    $css_classes = "hth-custom-message size-{$size} align-{$align}";
    
    // Build inline styles
    $inline_styles = "color: {$color};";

    // Return the HTML
    return sprintf(
        '<div class="%s" style="%s">%s</div>',
        esc_attr($css_classes),
        esc_attr($inline_styles),
        esc_html($message)
    );
}
add_shortcode('hth_custom_message', 'hth_custom_message_shortcode');

/**
 * Example 4: Shortcode with complex attributes
 * 
 * This shortcode creates a button with various styling options.
 * Usage: [hth_button text="Click Me" url="https://example.com" style="primary" target="_blank"]
 * 
 * Demonstrates:
 * - URL validation
 * - Boolean attributes
 * - Complex attribute handling
 * - Accessibility considerations
 */
function hth_button_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'text' => 'Click Here',
            'url' => '#',
            'style' => 'primary',
            'target' => '_self',
            'size' => 'medium',
            'disabled' => 'false'
        ),
        $atts,
        'hth_button'
    );

    // Sanitize and validate attributes
    $text = sanitize_text_field($atts['text']);
    $url = esc_url($atts['url']);
    $style = in_array($atts['style'], ['primary', 'secondary', 'success', 'warning', 'danger']) ? $atts['style'] : 'primary';
    $target = in_array($atts['target'], ['_self', '_blank', '_parent', '_top']) ? $atts['target'] : '_self';
    $size = in_array($atts['size'], ['small', 'medium', 'large']) ? $atts['size'] : 'medium';
    $disabled = filter_var($atts['disabled'], FILTER_VALIDATE_BOOLEAN);

    // Build button classes
    $button_classes = "hth-button btn-{$style} btn-{$size}";
    if ($disabled) {
        $button_classes .= ' disabled';
    }

    // Build additional attributes
    $additional_attrs = '';
    if ($target === '_blank') {
        $additional_attrs .= ' rel="noopener noreferrer"';
    }
    if ($disabled) {
        $additional_attrs .= ' aria-disabled="true"';
        $url = '#'; // Don't navigate if disabled
    }

    return sprintf(
        '<a href="%s" class="%s" target="%s"%s>%s</a>',
        esc_url($url),
        esc_attr($button_classes),
        esc_attr($target),
        $additional_attrs,
        esc_html($text)
    );
}
add_shortcode('hth_button', 'hth_button_shortcode');

// SECTION 3: ENCLOSING SHORTCODES
// Shortcodes that wrap around content

/**
 * Example 5: Enclosing shortcode
 * 
 * This shortcode wraps content in a styled box.
 * Usage: [hth_box style="info"]Your content here[/hth_box]
 * 
 * Demonstrates:
 * - Handling $content parameter
 * - Processing nested shortcodes
 * - Content sanitization
 * - CSS framework integration
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Content between opening and closing tags
 * @return string HTML output
 */
function hth_box_shortcode($atts, $content = '') {
    $atts = shortcode_atts(
        array(
            'style' => 'default',
            'title' => '',
            'icon' => '',
            'dismissible' => 'false'
        ),
        $atts,
        'hth_box'
    );

    // Sanitize attributes
    $style = in_array($atts['style'], ['default', 'info', 'warning', 'success', 'danger']) ? $atts['style'] : 'default';
    $title = sanitize_text_field($atts['title']);
    $icon = sanitize_text_field($atts['icon']);
    $dismissible = filter_var($atts['dismissible'], FILTER_VALIDATE_BOOLEAN);

    // Process nested shortcodes in content
    $content = do_shortcode($content);
    
    // Sanitize content while preserving some HTML
    $allowed_html = array(
        'p' => array(),
        'br' => array(),
        'strong' => array(),
        'em' => array(),
        'a' => array('href' => array(), 'title' => array()),
        'ul' => array(),
        'ol' => array(),
        'li' => array()
    );
    $content = wp_kses($content, $allowed_html);

    // Build CSS classes
    $box_classes = "hth-box box-{$style}";
    if ($dismissible) {
        $box_classes .= ' dismissible';
    }

    // Build the HTML
    $html = '<div class="' . esc_attr($box_classes) . '">';
    
    // Add title if provided
    if (!empty($title)) {
        $html .= '<div class="box-header">';
        if (!empty($icon)) {
            $html .= '<i class="' . esc_attr($icon) . '"></i> ';
        }
        $html .= '<h4>' . esc_html($title) . '</h4>';
        if ($dismissible) {
            $html .= '<button type="button" class="box-dismiss">&times;</button>';
        }
        $html .= '</div>';
    }
    
    $html .= '<div class="box-content">' . $content . '</div>';
    $html .= '</div>';

    return $html;
}
add_shortcode('hth_box', 'hth_box_shortcode');

/**
 * Example 6: Advanced enclosing shortcode with nested processing
 * 
 * This shortcode creates a tabbed interface.
 * Usage: [hth_tabs][hth_tab title="Tab 1"]Content 1[/hth_tab][hth_tab title="Tab 2"]Content 2[/hth_tab][/hth_tabs]
 * 
 * Demonstrates:
 * - Complex nested shortcode handling
 * - JavaScript interaction
 * - Multiple shortcodes working together
 * - Advanced content processing
 */
function hth_tabs_shortcode($atts, $content = '') {
    // Parse shortcode attributes with defaults
    $atts = shortcode_atts(
        array(
            'active' => '1',      // Which tab is active by default (1-based index)
            'style' => 'default'  // Optional style class
        ),
        $atts,
        'hth_tabs'
    );

    // Global variable to store tab data from nested [hth_tab] shortcodes
    global $hth_tabs_data;
    $hth_tabs_data = array();

    // Process the content to extract tab data by executing nested shortcodes
    // Each [hth_tab] will push its data into $hth_tabs_data
    do_shortcode($content);

    // If no tabs were found, show a message
    if (empty($hth_tabs_data)) {
        return '<p>No tabs found.</p>';
    }

    // Determine which tab should be active (convert to zero-based index)
    $active_tab = intval($atts['active']) - 1;
    $style = sanitize_text_field($atts['style']);

    // Generate a unique ID for this tab set to avoid conflicts
    $tab_id = 'hth-tabs-' . wp_rand(1000, 9999);

    // Build the tab navigation (list of tab titles)
    $html = '<div class="hth-tabs-container ' . esc_attr($style) . '" id="' . esc_attr($tab_id) . '">';
    $html .= '<ul class="tab-nav">';
    foreach ($hth_tabs_data as $index => $tab) {
        // Add 'active' class to the currently active tab
        $active_class = ($index === $active_tab) ? ' active' : '';
        $html .= sprintf(
            '<li class="tab-nav-item%s"><a href="#%s-tab-%d">%s</a></li>',
            $active_class,
            esc_attr($tab_id),
            $index,
            esc_html($tab['title'])
        );
    }
    $html .= '</ul>';

    // Build the tab content panes
    $html .= '<div class="tab-content">';
    foreach ($hth_tabs_data as $index => $tab) {
        // Add 'active' class to the currently active pane
        $active_class = ($index === $active_tab) ? ' active' : '';
        $html .= sprintf(
            '<div id="%s-tab-%d" class="tab-pane%s">%s</div>',
            esc_attr($tab_id),
            $index,
            $active_class,
            $tab['content'] // Content is already processed and sanitized by hth_tab_shortcode
        );
    }
    $html .= '</div>';
    $html .= '</div>';

    // Add JavaScript for tab switching functionality
    // Handles click events, toggles 'active' classes for navigation and content
    $html .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.getElementById("' . esc_js($tab_id) . '");
            const navItems = container.querySelectorAll(".tab-nav-item a");
            
            navItems.forEach(function(item) {
                item.addEventListener("click", function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all nav items and panes
                    container.querySelectorAll(".tab-nav-item").forEach(function(nav) {
                        nav.classList.remove("active");
                    });
                    container.querySelectorAll(".tab-pane").forEach(function(pane) {
                        pane.classList.remove("active");
                    });
                    
                    // Add active class to clicked nav item and corresponding pane
                    this.parentElement.classList.add("active");
                    const target = document.querySelector(this.getAttribute("href"));
                    if (target) {
                        target.classList.add("active");
                    }
                });
            });
        });
    </script>';

    // Return the complete HTML for the tabbed interface
    return $html;
}
add_shortcode('hth_tabs', 'hth_tabs_shortcode');

/**
 * Helper shortcode for individual tabs
 */
function hth_tab_shortcode($atts, $content = '') {
    $atts = shortcode_atts(
        array(
            'title' => 'Tab'
        ),
        $atts,
        'hth_tab'
    );

    global $hth_tabs_data;
    
    $hth_tabs_data[] = array(
        'title' => sanitize_text_field($atts['title']),
        'content' => do_shortcode($content)
    );

    return ''; // Return empty string as content is handled by parent shortcode
}
add_shortcode('hth_tab', 'hth_tab_shortcode');

// SECTION 4: SHORTCODES WITH WORDPRESS INTEGRATION
// Shortcodes that integrate with WordPress features

/**
 * Example 7: Shortcode that queries WordPress data
 * 
 * This shortcode displays recent posts with customizable options.
 * Usage: [hth_recent_posts count="5" category="news" show_date="true"]
 * 
 * Demonstrates:
 * - WordPress query integration
 * - Custom post queries
 * - Template-like functionality
 * - Performance considerations
 */
function hth_recent_posts_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'count' => '5',
            'category' => '',
            'show_date' => 'true',
            'show_excerpt' => 'false',
            'order' => 'DESC',
            'orderby' => 'date'
        ),
        $atts,
        'hth_recent_posts'
    );

    // Sanitize and validate attributes
    $count = intval($atts['count']);
    $count = ($count > 0 && $count <= 20) ? $count : 5; // Limit to prevent performance issues
    $category = sanitize_text_field($atts['category']);
    $show_date = filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN);
    $show_excerpt = filter_var($atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN);
    $order = in_array(strtoupper($atts['order']), ['ASC', 'DESC']) ? strtoupper($atts['order']) : 'DESC';
    $orderby = in_array($atts['orderby'], ['date', 'title', 'menu_order', 'rand']) ? $atts['orderby'] : 'date';

    // Build query arguments
    $query_args = array(
        'post_type' => 'post',
        'posts_per_page' => $count,
        'order' => $order,
        'orderby' => $orderby,
        'post_status' => 'publish'
    );

    // Add category filter if specified
    if (!empty($category)) {
        $query_args['category_name'] = $category;
    }

    // Execute the query
    $posts = get_posts($query_args);

    if (empty($posts)) {
        return '<p>No posts found.</p>';
    }

    // Build the HTML output
    $html = '<div class="hth-recent-posts">';
    
    foreach ($posts as $post) {
        $html .= '<div class="recent-post-item">';
        $html .= '<h4><a href="' . esc_url(get_permalink($post->ID)) . '">' . esc_html($post->post_title) . '</a></h4>';
        
        if ($show_date) {
            $html .= '<span class="post-date">' . esc_html(get_the_date('', $post->ID)) . '</span>';
        }
        
        if ($show_excerpt) {
            $excerpt = get_the_excerpt($post->ID);
            if (!empty($excerpt)) {
                $html .= '<p class="post-excerpt">' . esc_html($excerpt) . '</p>';
            }
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';

    return $html;
}
add_shortcode('hth_recent_posts', 'hth_recent_posts_shortcode');

/**
 * Example 8: Shortcode with user authentication
 * 
 * This shortcode shows different content based on user login status.
 * Usage: [hth_login_content]Content for logged in users[/hth_login_content]
 * 
 * Demonstrates:
 * - User authentication checks
 * - Conditional content display
 * - Security considerations
 * - Role-based content
 */
/**
 * Shortcode: [hth_login_content]
 * 
 * Displays content only to logged-in users, optionally restricted by user role.
 * If not logged in, shows a customizable message and optional login link.
 * 
 * Attributes:
 * - role: (string) Restrict content to users with this role (optional)
 * - login_message: (string) Message to show to non-logged-in users (default: "Please log in to view this content.")
 * - redirect: (bool) If true, show a login link that redirects back to current page (default: false)
 * 
 * @param array $atts Shortcode attributes
 * @param string $content Content between shortcode tags
 * @return string HTML output
 */
function hth_login_content_shortcode($atts, $content = '') {
    // Parse shortcode attributes and set defaults
    $atts = shortcode_atts(
        array(
            'role' => '', // Optional: restrict to specific user role
            'login_message' => 'Please log in to view this content.', // Message for guests
            'redirect' => 'false' // Show login link with redirect
        ),
        $atts,
        'hth_login_content'
    );

    // Step 1: Check if user is logged in
    if (!is_user_logged_in()) {
        // Sanitize the login message for output
        $login_message = sanitize_text_field($atts['login_message']);
        // Convert 'redirect' attribute to boolean
        $redirect = filter_var($atts['redirect'], FILTER_VALIDATE_BOOLEAN);
        
        // If redirect is enabled, show login link that returns to current page
        if ($redirect) {
            // Generate login URL with redirect back to current page
            $login_url = wp_login_url(get_permalink());
            return '<p>' . esc_html($login_message) . ' <a href="' . esc_url($login_url) . '">Log in here</a>.</p>';
        }
        
        // Otherwise, just show the login message
        return '<p>' . esc_html($login_message) . '</p>';
    }

    // Step 2: If 'role' attribute is set, check if current user has that role
    $required_role = sanitize_text_field($atts['role']);
    if (!empty($required_role)) {
        // Get current user object
        $current_user = wp_get_current_user();
        // Check if user has the required role
        if (!in_array($required_role, $current_user->roles)) {
            // User does not have permission
            return '<p>You do not have permission to view this content.</p>';
        }
    }

    // Step 3: User is logged in and (if required) has the correct role
    // Process nested shortcodes in content and return
    return do_shortcode($content);
}
add_shortcode('hth_login_content', 'hth_login_content_shortcode');

// SECTION 5: ADVANCED SHORTCODE TECHNIQUES
// Advanced patterns and optimization techniques

/**
 * Example 9: Shortcode with caching
 * 
 * This shortcode demonstrates how to implement caching for performance.
 * Usage: [hth_expensive_operation data="some_data"]
 * 
 * Demonstrates:
 * - Transient caching
 * - Cache invalidation
 * - Performance optimization
 * - Cache keys generation
 */
function hth_expensive_operation_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'data' => 'default',
            'cache_time' => '3600' // 1 hour default
        ),
        $atts,
        'hth_expensive_operation'
    );

    $data = sanitize_text_field($atts['data']);
    $cache_time = intval($atts['cache_time']);

    // Generate cache key
    $cache_key = 'hth_expensive_' . md5($data);

    // Try to get cached result
    $cached_result = get_transient($cache_key);
    if ($cached_result !== false) {
        return $cached_result . '<!-- Cached -->';
    }

    // Simulate expensive operation
    sleep(1); // Don't do this in production!
    
    // Generate result
    $result = '<div class="expensive-result">';
    $result .= '<h3>Expensive Operation Result</h3>';
    $result .= '<p>Data processed: ' . esc_html($data) . '</p>';
    $result .= '<p>Generated at: ' . esc_html(current_time('mysql')) . '</p>';
    $result .= '</div>';

    // Cache the result
    set_transient($cache_key, $result, $cache_time);

    return $result;
}
add_shortcode('hth_expensive_operation', 'hth_expensive_operation_shortcode');

/**
 * Example 10: Shortcode with AJAX functionality
 * 
 * This shortcode creates a form that submits via AJAX.
 * Usage: [hth_ajax_form]
 * 
 * Demonstrates:
 * - AJAX integration
 * - Nonce security
 * - Form handling
 * - JavaScript integration
 */
function hth_ajax_form_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'action' => 'hth_ajax_form_submit',
            'success_message' => 'Form submitted successfully!'
        ),
        $atts,
        'hth_ajax_form'
    );

    // Generate unique form ID
    $form_id = 'hth-ajax-form-' . wp_rand(1000, 9999);
    
    // Create nonce for security
    $nonce = wp_create_nonce('hth_ajax_form_nonce');

    $html = '<div class="hth-ajax-form-container">';
    $html .= '<form id="' . esc_attr($form_id) . '" class="hth-ajax-form">';
    $html .= '<div class="form-group">';
    $html .= '<label for="user_name">Name:</label>';
    $html .= '<input type="text" id="user_name" name="user_name" required>';
    $html .= '</div>';
    $html .= '<div class="form-group">';
    $html .= '<label for="user_email">Email:</label>';
    $html .= '<input type="email" id="user_email" name="user_email" required>';
    $html .= '</div>';
    $html .= '<div class="form-group">';
    $html .= '<label for="user_message">Message:</label>';
    $html .= '<textarea id="user_message" name="user_message" required></textarea>';
    $html .= '</div>';
    $html .= '<input type="hidden" name="action" value="' . esc_attr($atts['action']) . '">';
    $html .= '<input type="hidden" name="nonce" value="' . esc_attr($nonce) . '">';
    $html .= '<button type="submit">Submit</button>';
    $html .= '</form>';
    $html .= '<div id="' . esc_attr($form_id) . '-response"></div>';
    $html .= '</div>';

    // Add JavaScript for AJAX handling
    $html .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("' . esc_js($form_id) . '");
            const responseDiv = document.getElementById("' . esc_js($form_id) . '-response");
            
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                fetch("' . esc_js(admin_url('admin-ajax.php')) . '", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        responseDiv.innerHTML = "<p class=\"success\">" + data.data + "</p>";
                        form.reset();
                    } else {
                        responseDiv.innerHTML = "<p class=\"error\">" + data.data + "</p>";
                    }
                })
                .catch(error => {
                    responseDiv.innerHTML = "<p class=\"error\">An error occurred. Please try again.</p>";
                });
            });
        });
    </script>';

    return $html;
}
add_shortcode('hth_ajax_form', 'hth_ajax_form_shortcode');

// AJAX handler for the form
add_action('wp_ajax_hth_ajax_form_submit', 'hth_ajax_form_submit_handler');
add_action('wp_ajax_nopriv_hth_ajax_form_submit', 'hth_ajax_form_submit_handler');

function hth_ajax_form_submit_handler() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'hth_ajax_form_nonce')) {
        wp_die('Security check failed');
    }

    // Sanitize input
    $name = sanitize_text_field($_POST['user_name']);
    $email = sanitize_email($_POST['user_email']);
    $message = sanitize_textarea_field($_POST['user_message']);

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('All fields are required.');
    }

    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address.');
    }

    // Process the form (save to database, send email, etc.)
    // This is where you would add your actual form processing logic
    
    wp_send_json_success('Thank you for your message! We will get back to you soon.');
}

/**
 * SHORTCODE BEST PRACTICES AND SECURITY:
 * 
 * 1. Input Validation and Sanitization:
 *    - Always sanitize user input
 *    - Use appropriate sanitization functions
 *    - Validate attribute values against expected ranges
 * 
 * 2. Output Escaping:
 *    - Always escape output for security
 *    - Use esc_html(), esc_attr(), esc_url() appropriately
 *    - Consider using wp_kses() for controlled HTML
 * 
 * 3. Performance Considerations:
 *    - Implement caching for expensive operations
 *    - Limit query results to prevent performance issues
 *    - Avoid database queries in frequently used shortcodes
 * 
 * 4. User Experience:
 *    - Provide sensible defaults for all attributes
 *    - Handle edge cases gracefully
 *    - Provide clear error messages
 * 
 * 5. Security:
 *    - Use nonces for form submissions
 *    - Check user capabilities when necessary
 *    - Validate and sanitize all input
 * 
 * 6. Compatibility:
 *    - Test with different themes
 *    - Ensure mobile responsiveness
 *    - Test with other plugins
 * 
 * 7. Documentation:
 *    - Document usage examples
 *    - List all available attributes
 *    - Provide clear descriptions
 * 
 * COMMON SHORTCODE PATTERNS:
 * 
 * // Conditional shortcodes
 * if (is_user_logged_in()) {
 *     // Show content for logged in users
 * }
 * 
 * // Database query shortcodes
 * $posts = get_posts(array(
 *     'post_type' => 'product',
 *     'meta_key' => 'featured',
 *     'meta_value' => 'yes'
 * ));
 * 
 * // Caching shortcodes
 * $cache_key = 'shortcode_cache_' . md5(serialize($atts));
 * $cached = get_transient($cache_key);
 * if (!$cached) {
 *     $cached = expensive_operation();
 *     set_transient($cache_key, $cached, HOUR_IN_SECONDS);
 * }
 * 
 * // AJAX shortcodes
 * wp_localize_script('my-script', 'ajax_object', array(
 *     'ajax_url' => admin_url('admin-ajax.php'),
 *     'nonce' => wp_create_nonce('my_nonce')
 * ));
 */
