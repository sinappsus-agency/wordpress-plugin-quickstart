<?php

// Register a custom REST API endpoint to retrieve books
function hth_register_books_endpoint() {
    register_rest_route('hth/v1', '/books', array(
        'methods' => 'GET',
        'callback' => 'hth_get_books',
        'permission_callback' => '__return_true', // Allow public access for demonstration
    ));
}
add_action('rest_api_init', 'hth_register_books_endpoint');

// Callback function to retrieve books
function hth_get_books(WP_REST_Request $request) {
    $args = array(
        'post_type' => 'book',
        'posts_per_page' => -1, // Retrieve all books
    );
    $books = get_posts($args);
    
    if (empty($books)) {
        return new WP_Error('no_books', 'No books found', array('status' => 404));
    }

    $data = array();
    foreach ($books as $book) {
        $data[] = array(
            'id' => $book->ID,
            'title' => $book->post_title,
            'content' => apply_filters('the_content', $book->post_content),
            'date' => $book->post_date,
        );
    }

    return rest_ensure_response($data);
}

// Register a custom REST API endpoint to retrieve book genres
function hth_register_genres_endpoint() {
    register_rest_route('hth/v1', '/genres', array(
        'methods' => 'GET',
        'callback' => 'hth_get_genres',
        'permission_callback' => '__return_true', // Allow public access for demonstration
    ));
}

add_action('rest_api_init', 'hth_register_genres_endpoint');

// Callback function to retrieve genres
function hth_get_genres(WP_REST_Request $request) {
    $terms = get_terms(array(
        'taxonomy' => 'genre',
        'hide_empty' => false, // Show all genres, even if they have no books
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return new WP_Error('no_genres', 'No genres found', array('status' => 404));
    }

    $data = array();
    foreach ($terms as $term) {
        $data[] = array(
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        );
    }

    return rest_ensure_response($data);
}
// Register a custom REST API endpoint to retrieve a single book by ID
function hth_register_single_book_endpoint() {
    register_rest_route('hth/v1', '/books/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'hth_get_single_book',
        'permission_callback' => '__return_true', // Allow public access for demonstration
    ));
}
add_action('rest_api_init', 'hth_register_single_book_endpoint');
// Callback function to retrieve a single book by ID
function hth_get_single_book(WP_REST_Request $request) {
    $book_id = (int) $request['id'];
    $book = get_post($book_id);

    if (empty($book) || $book->post_type !== 'book') {
        return new WP_Error('no_book', 'Book not found', array('status' => 404));
    }

    $data = array(
        'id' => $book->ID,
        'title' => $book->post_title,
        'content' => apply_filters('the_content', $book->post_content),
        'date' => $book->post_date,
    );

    return rest_ensure_response($data);
}