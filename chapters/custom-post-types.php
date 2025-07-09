<?php 

// creating a custom post type called 'book'

function hth_register_book_post_type() {
    $labels = array(
        'name'               => _x('Books', 'post type general name'),
        'singular_name'      => _x('Book', 'post type singular name'),
        'menu_name'          => _x('Books', 'admin menu'),
        'name_admin_bar'     => _x('Book', 'add new on admin bar'),
        'add_new'            => _x('Add New', 'book'),
        'add_new_item'       => __('Add New Book'),
        'new_item'           => __('New Book'),
        'edit_item'          => __('Edit Book'),
        'view_item'          => __('View Book'),
        'all_items'          => __('All Books'),
        'search_items'       => __('Search Books'),
        'not_found'          => __('No books found.'),
        'not_found_in_trash' => __('No books found in Trash.')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'book'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'editor', 'thumbnail')
    );

    register_post_type('book', $args);
}

add_action('init', 'hth_register_book_post_type');



// creating a custom taxonomy called 'genre' for the 'book' post type

function hth_register_book_genre_taxonomy() {
    $labels = array(
        'name'              => _x('Genres', 'taxonomy general name'),
        'singular_name'     => _x('Genre', 'taxonomy singular name'),
        'search_items'      => __('Search Genres'),
        'all_items'         => __('All Genres'),
        'parent_item'       => __('Parent Genre'),
        'parent_item_colon' => __('Parent Genre:'),
        'edit_item'         => __('Edit Genre'),
        'update_item'       => __('Update Genre'),
        'add_new_item'      => __('Add New Genre'),
        'new_item_name'     => __('New Genre Name'),
        'menu_name'         => __('Genre')
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'genre')
    );

    register_taxonomy('genre', array('book'), $args);
}

add_action('init', 'hth_register_book_genre_taxonomy');

// creating a custom meta box for the 'book' post type

function hth_add_book_meta_box() {
    add_meta_box(
        'book_details',
        __('Book Details', 'hth-sample-plugin'),
        'hth_render_book_meta_box',
        'book',
        'normal',
        'high'
    );
}

add_action('add_meta_boxes', 'hth_add_book_meta_box');

function hth_render_book_meta_box($post) {
    wp_nonce_field('hth_save_book_details', 'hth_book_details_nonce');

    $author = get_post_meta($post->ID, '_book_author', true);
    $isbn = get_post_meta($post->ID, '_book_isbn', true);

    echo '<label for="book_author">' . __('Author:', 'hth-sample-plugin') . '</label>';
    echo '<input type="text" id="book_author" name="book_author" value="' . esc_attr($author) . '" class="widefat" />';

    echo '<label for="book_isbn">' . __('ISBN:', 'hth-sample-plugin') . '</label>';
    echo '<input type="text" id="book_isbn" name="book_isbn" value="' . esc_attr($isbn) . '" class="widefat" />';
}

function hth_save_book_details($post_id) {
    if (!isset($_POST['hth_book_details_nonce']) || !wp_verify_nonce($_POST['hth_book_details_nonce'], 'hth_save_book_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['book_author'])) {
        update_post_meta($post_id, '_book_author', sanitize_text_field($_POST['book_author']));
    }

    if (isset($_POST['book_isbn'])) {
        update_post_meta($post_id, '_book_isbn', sanitize_text_field($_POST['book_isbn']));
    }
}

add_action('save_post', 'hth_save_book_details');

// creating a custom column for the 'book' post type in the admin list view
function hth_add_book_columns($columns) {
    $columns['book_author'] = __('Author', 'hth-sample-plugin');
    $columns['book_isbn'] = __('ISBN', 'hth-sample-plugin');
    return $columns;
}

add_filter('manage_book_posts_columns', 'hth_add_book_columns');

function hth_render_book_columns($column, $post_id) {
    switch ($column) {
        case 'book_author':
            $author = get_post_meta($post_id, '_book_author', true);
            echo esc_html($author);
            break;
        case 'book_isbn':
            $isbn = get_post_meta($post_id, '_book_isbn', true);
            echo esc_html($isbn);
            break;
    }
}

add_action('manage_book_posts_custom_column', 'hth_render_book_columns', 10, 2);

// making the custom columns sortable
function hth_sortable_book_columns($columns) {
    $columns['book_author'] = 'book_author';
    $columns['book_isbn'] = 'book_isbn';
    return $columns;
}
add_filter('manage_edit-book_sortable_columns', 'hth_sortable_book_columns');
// adding custom query vars for sorting
function hth_book_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') === 'book') {
        if ($query->get('orderby') === 'book_author') {
            $query->set('meta_key', '_book_author');
            $query->set('orderby', 'meta_value');
        } elseif ($query->get('orderby') === 'book_isbn') {
            $query->set('meta_key', '_book_isbn');
            $query->set('orderby', 'meta_value');
        }
    }
}

add_action('pre_get_posts', 'hth_book_orderby');

