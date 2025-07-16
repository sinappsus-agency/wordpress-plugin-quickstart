<?php 

/**
 * CHAPTER 3: CUSTOM POST TYPES
 * 
 * This chapter demonstrates how to create custom post types in WordPress.
 * Custom post types allow you to create content types beyond the default posts and pages.
 * 
 * Key concepts covered:
 * - Registering custom post types with register_post_type()
 * - Creating custom taxonomies for organization
 * - Adding custom meta boxes for additional fields
 * - Customizing admin columns and making them sortable
 * - Proper data sanitization and security practices
 * 
 * WordPress provides several built-in post types:
 * - post (blog posts)
 * - page (static pages)
 * - attachment (media files)
 * - revision (post revisions)
 * - nav_menu_item (navigation menu items)
 * 
 * Custom post types are useful for:
 * - Products in an e-commerce site
 * - Portfolio items for creative professionals
 * - Team members for company websites
 * - Events for event management
 * - Books, movies, recipes, etc.
 */

// SECTION 1: REGISTERING A CUSTOM POST TYPE
// Creating a custom post type called 'book' to demonstrate various features

/**
 * Function to register a custom post type called 'book'
 * 
 * This function demonstrates the complete process of registering a custom post type.
 * It uses register_post_type() which is the core WordPress function for this purpose.
 * 
 * Key parameters explained:
 * - $labels: Array of labels for the UI (plural, singular, menu names, etc.)
 * - $args: Array of arguments that define the post type's capabilities and features
 * 
 * Important $args parameters:
 * - 'public': true = visible on frontend and admin
 * - 'publicly_queryable': true = can be queried on frontend
 * - 'show_ui': true = show admin interface
 * - 'show_in_menu': true = show in admin menu
 * - 'query_var': true = enable query_var for custom queries
 * - 'rewrite': array('slug' => 'book') = custom URL structure
 * - 'capability_type': 'post' = use same capabilities as posts
 * - 'has_archive': true = enable archive page
 * - 'hierarchical': false = not hierarchical like pages
 * - 'menu_position': 5 = position in admin menu
 * - 'supports': array of features (title, editor, thumbnail, etc.)
 * 
 * Additional supports options you can use:
 * - 'title': post title
 * - 'editor': post content editor
 * - 'author': post author
 * - 'thumbnail': featured image
 * - 'excerpt': post excerpt
 * - 'trackbacks': trackback support
 * - 'custom-fields': custom fields meta box
 * - 'comments': comment support
 * - 'revisions': post revisions
 * - 'page-attributes': page attributes meta box
 * - 'post-formats': post format support
 */
function hth_register_book_post_type() {
    // Labels define how the post type appears in the admin interface
    // These are translatable strings for internationalization
    $labels = array(
        'name'               => _x('Books', 'post type general name'), // Plural name for the post type
        'singular_name'      => _x('Book', 'post type singular name'), // Singular name
        'menu_name'          => _x('Books', 'admin menu'), // Name in admin menu
        'name_admin_bar'     => _x('Book', 'add new on admin bar'), // Name in admin bar
        'add_new'            => _x('Add New', 'book'), // "Add New" button text
        'add_new_item'       => __('Add New Book'), // "Add New Item" page title
        'new_item'           => __('New Book'), // New item label
        'edit_item'          => __('Edit Book'), // Edit item label
        'view_item'          => __('View Book'), // View item label
        'all_items'          => __('All Books'), // All items label
        'search_items'       => __('Search Books'), // Search items label
        'not_found'          => __('No books found.'), // Not found message
        'not_found_in_trash' => __('No books found in Trash.') // Not found in trash message
    );

    // Arguments array defines the post type's capabilities and features
    $args = array(
        'labels'             => $labels, // The labels array defined above
        'public'             => true, // Make post type public (visible on frontend and admin)
        'publicly_queryable' => true, // Allow queries on frontend
        'show_ui'            => true, // Show admin interface
        'show_in_menu'       => true, // Show in admin menu
        'query_var'          => true, // Enable query_var for custom queries
        'rewrite'            => array('slug' => 'book'), // Custom URL structure (/book/post-name)
        'capability_type'    => 'post', // Use same capabilities as regular posts
        'has_archive'        => true, // Enable archive page (/book/ will show all books)
        'hierarchical'       => false, // Not hierarchical (like posts, not pages)
        'menu_position'      => 5, // Position in admin menu (5 = below Posts)
        'supports'           => array('title', 'editor', 'thumbnail') // Supported features
    );

    // Register the post type with WordPress
    register_post_type('book', $args);
}

// Hook the function to WordPress 'init' action - this is when post types should be registered
add_action('init', 'hth_register_book_post_type');


// SECTION 2: CUSTOM TAXONOMIES
// Creating a custom taxonomy called 'genre' for the 'book' post type
// Taxonomies are used to group and organize post types (like categories and tags for posts)

/**
 * Function to register a custom taxonomy called 'genre'
 * 
 * Taxonomies provide a way to group posts. WordPress comes with two built-in taxonomies:
 * - Categories (hierarchical)
 * - Tags (non-hierarchical)
 * 
 * Custom taxonomies can be:
 * - Hierarchical (like categories) - can have parent/child relationships
 * - Non-hierarchical (like tags) - flat structure
 * 
 * Common use cases for custom taxonomies:
 * - Product categories for e-commerce
 * - Portfolio categories for creative work
 * - Event types for event management
 * - Book genres (as shown in this example)
 * 
 * Key taxonomy parameters:
 * - 'hierarchical': true = like categories, false = like tags
 * - 'show_ui': true = show in admin interface
 * - 'show_admin_column': true = show column in post list
 * - 'query_var': true = enable query_var for custom queries
 * - 'rewrite': array('slug' => 'genre') = custom URL structure
 */
function hth_register_book_genre_taxonomy() {
    // Labels for the taxonomy interface
    $labels = array(
        'name'              => _x('Genres', 'taxonomy general name'), // Plural name
        'singular_name'     => _x('Genre', 'taxonomy singular name'), // Singular name
        'search_items'      => __('Search Genres'), // Search items label
        'all_items'         => __('All Genres'), // All items label
        'parent_item'       => __('Parent Genre'), // Parent item label (for hierarchical)
        'parent_item_colon' => __('Parent Genre:'), // Parent item label with colon
        'edit_item'         => __('Edit Genre'), // Edit item label
        'update_item'       => __('Update Genre'), // Update item label
        'add_new_item'      => __('Add New Genre'), // Add new item label
        'new_item_name'     => __('New Genre Name'), // New item name label
        'menu_name'         => __('Genre') // Menu name
    );

    // Arguments for the taxonomy
    $args = array(
        'hierarchical'      => true, // true = like categories, false = like tags
        'labels'            => $labels, // The labels array defined above
        'show_ui'           => true, // Show in admin interface
        'show_admin_column' => true, // Show as column in post list table
        'query_var'         => true, // Enable query_var for custom queries
        'rewrite'           => array('slug' => 'genre') // Custom URL structure (/genre/fantasy)
    );

    // Register the taxonomy and associate it with the 'book' post type
    // First parameter: taxonomy name
    // Second parameter: array of post types to associate with
    // Third parameter: arguments array
    register_taxonomy('genre', array('book'), $args);
}

// Hook the function to 'init' action
add_action('init', 'hth_register_book_genre_taxonomy');

// SECTION 3: CUSTOM META BOXES
// Adding custom fields to the post editing interface

/**
 * Function to add custom meta box to the book post type
 * 
 * Meta boxes are sections on the post editing screen that contain custom fields.
 * They allow you to add additional data to posts beyond the standard title and content.
 * 
 * add_meta_box() parameters:
 * - $id: Unique identifier for the meta box
 * - $title: Title displayed in the meta box header
 * - $callback: Function that renders the meta box content
 * - $screen: Post type(s) where the meta box should appear
 * - $context: Where on the page the meta box should appear
 *   - 'normal': main content area
 *   - 'side': sidebar
 *   - 'advanced': below normal
 * - $priority: Priority within the context
 *   - 'high': higher priority
 *   - 'core': core priority
 *   - 'default': default priority
 *   - 'low': lower priority
 */
function hth_add_book_meta_box() {
    add_meta_box(
        'book_details', // Unique ID for the meta box
        __('Book Details', 'hth-sample-plugin'), // Title shown in meta box header
        'hth_render_book_meta_box', // Callback function to render content
        'book', // Post type where this meta box should appear
        'normal', // Context (normal, side, advanced)
        'high' // Priority (high, core, default, low)
    );
}

// Hook to add_meta_boxes action - this is when meta boxes are added
add_action('add_meta_boxes', 'hth_add_book_meta_box');

/**
 * Function to render the content of the book meta box
 * 
 * This function creates the HTML form fields that appear in the meta box.
 * It demonstrates:
 * - Security nonce fields for form validation
 * - Retrieving existing meta data
 * - Creating form fields with proper escaping
 * 
 * @param WP_Post $post The post object being edited
 */
function hth_render_book_meta_box($post) {
    // Add nonce field for security verification
    // This prevents CSRF attacks and ensures the form submission is legitimate
    wp_nonce_field('hth_save_book_details', 'hth_book_details_nonce');

    // Retrieve existing meta values
    // get_post_meta() parameters:
    // - $post_id: ID of the post
    // - $key: Meta key to retrieve
    // - $single: true = return single value, false = return array
    $author = get_post_meta($post->ID, '_book_author', true);
    $isbn = get_post_meta($post->ID, '_book_isbn', true);

    // Create form fields with proper labeling and escaping
    // Note: Meta keys starting with underscore (_) are "private" and won't show in custom fields UI
    echo '<p>';
    echo '<label for="book_author">' . __('Author:', 'hth-sample-plugin') . '</label><br>';
    echo '<input type="text" id="book_author" name="book_author" value="' . esc_attr($author) . '" class="widefat" />';
    echo '</p>';

    echo '<p>';
    echo '<label for="book_isbn">' . __('ISBN:', 'hth-sample-plugin') . '</label><br>';
    echo '<input type="text" id="book_isbn" name="book_isbn" value="' . esc_attr($isbn) . '" class="widefat" />';
    echo '</p>';
}

/**
 * Function to save the custom meta box data
 * 
 * This function handles saving the custom field data when the post is saved.
 * It demonstrates important security practices:
 * - Nonce verification to prevent CSRF attacks
 * - Checking for autosave to prevent data loss
 * - Proper data sanitization before saving
 * 
 * @param int $post_id The ID of the post being saved
 */
function hth_save_book_details($post_id) {
    // Security check: verify nonce
    if (!isset($_POST['hth_book_details_nonce']) || !wp_verify_nonce($_POST['hth_book_details_nonce'], 'hth_save_book_details')) {
        return;
    }

    // Don't save during autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user capabilities (optional - add if needed)
    // if (!current_user_can('edit_post', $post_id)) {
    //     return;
    // }

    // Save the author field
    if (isset($_POST['book_author'])) {
        // update_post_meta() parameters:
        // - $post_id: ID of the post
        // - $meta_key: Meta key to save
        // - $meta_value: Value to save (sanitized)
        update_post_meta($post_id, '_book_author', sanitize_text_field($_POST['book_author']));
    }

    // Save the ISBN field
    if (isset($_POST['book_isbn'])) {
        update_post_meta($post_id, '_book_isbn', sanitize_text_field($_POST['book_isbn']));
    }
}

// Hook to save_post action - this runs when any post is saved
add_action('save_post', 'hth_save_book_details');

// SECTION 4: CUSTOM ADMIN COLUMNS
// Adding custom columns to the admin post list table

/**
 * Function to add custom columns to the book post type admin list
 * 
 * This function modifies the columns shown in the admin post list table.
 * It allows you to display custom field data directly in the post list.
 * 
 * @param array $columns Existing columns array
 * @return array Modified columns array
 */
function hth_add_book_columns($columns) {
    // Add custom columns to the existing columns array
    // Key = column ID, Value = column title
    $columns['book_author'] = __('Author', 'hth-sample-plugin');
    $columns['book_isbn'] = __('ISBN', 'hth-sample-plugin');
    return $columns;
}

// Hook to manage_{post_type}_posts_columns filter
add_filter('manage_book_posts_columns', 'hth_add_book_columns');

/**
 * Function to render content for custom columns
 * 
 * This function populates the custom columns with actual data.
 * It's called for each post in the admin list table.
 * 
 * @param string $column The column ID
 * @param int $post_id The post ID
 */
function hth_render_book_columns($column, $post_id) {
    switch ($column) {
        case 'book_author':
            $author = get_post_meta($post_id, '_book_author', true);
            echo esc_html($author); // Always escape output for security
            break;
        case 'book_isbn':
            $isbn = get_post_meta($post_id, '_book_isbn', true);
            echo esc_html($isbn);
            break;
    }
}

// Hook to manage_{post_type}_posts_custom_column action
add_action('manage_book_posts_custom_column', 'hth_render_book_columns', 10, 2);

// SECTION 5: SORTABLE COLUMNS
// Making the custom columns sortable

/**
 * Function to make custom columns sortable
 * 
 * This function tells WordPress which columns should be sortable.
 * Users can click column headers to sort the post list.
 * 
 * @param array $columns Array of sortable columns
 * @return array Modified sortable columns array
 */
function hth_sortable_book_columns($columns) {
    // Make the custom columns sortable
    // Key = column ID, Value = orderby parameter
    $columns['book_author'] = 'book_author';
    $columns['book_isbn'] = 'book_isbn';
    return $columns;
}

// Hook to manage_edit-{post_type}_sortable_columns filter
add_filter('manage_edit-book_sortable_columns', 'hth_sortable_book_columns');

/**
 * Function to handle custom column sorting
 * 
 * This function modifies the query to sort by custom meta fields
 * when a user clicks on a sortable column header.
 * 
 * @param WP_Query $query The WordPress query object
 */
function hth_book_orderby($query) {
    // Only modify admin queries for the main query
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    // Only modify queries for the book post type
    if ($query->get('post_type') === 'book') {
        $orderby = $query->get('orderby');
        
        // Handle sorting by book author
        if ($orderby === 'book_author') {
            $query->set('meta_key', '_book_author');
            $query->set('orderby', 'meta_value'); // Sort by meta value alphabetically
        } 
        // Handle sorting by book ISBN
        elseif ($orderby === 'book_isbn') {
            $query->set('meta_key', '_book_isbn');
            $query->set('orderby', 'meta_value'); // Sort by meta value alphabetically
        }
    }
}

// Hook to pre_get_posts action - this modifies queries before they run
add_action('pre_get_posts', 'hth_book_orderby');

/**
 * ADDITIONAL LEARNING RESOURCES AND TIPS:
 * 
 * 1. Query Custom Post Types:
 *    - Use WP_Query with 'post_type' parameter
 *    - Example: $query = new WP_Query(array('post_type' => 'book'));
 * 
 * 2. Display Custom Post Types on Frontend:
 *    - Create template files: single-book.php, archive-book.php
 *    - Use get_post_meta() to display custom fields
 * 
 * 3. Custom Post Type Capabilities:
 *    - Use 'capability_type' => 'book' for custom capabilities
 *    - Create custom capabilities: edit_book, read_book, delete_book
 * 
 * 4. REST API Support:
 *    - Add 'show_in_rest' => true to post type args
 *    - Enables Gutenberg editor and REST API access
 * 
 * 5. Custom Post Type Templates:
 *    - single-{post_type}.php for single posts
 *    - archive-{post_type}.php for archive pages
 *    - taxonomy-{taxonomy}.php for taxonomy pages
 * 
 * 6. Advanced Meta Box Features:
 *    - Use wp_editor() for rich text fields
 *    - Add media upload buttons with wp_enqueue_media()
 *    - Create repeatable fields with JavaScript
 * 
 * 7. Performance Considerations:
 *    - Use meta_query carefully (can be slow)
 *    - Consider custom database tables for complex data
 *    - Use caching for frequently accessed data
 * 
 * 8. Security Best Practices:
 *    - Always use nonces for form submissions
 *    - Sanitize input data before saving
 *    - Escape output data for display
 *    - Check user capabilities before saving
 */

