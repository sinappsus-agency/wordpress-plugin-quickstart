<?php

/**
 * CHAPTER 4: REST API INTEGRATION
 * 
 * This chapter demonstrates how to create and work with WordPress REST API endpoints.
 * The REST API provides a standardized way to interact with WordPress data programmatically.
 * 
 * Key concepts covered:
 * - Registering custom REST API endpoints
 * - HTTP methods (GET, POST, PUT, DELETE)
 * - Request handling and validation
 * - Response formatting and status codes
 * - Authentication and permissions
 * - Error handling and debugging
 * - API versioning and best practices
 * 
 * WORDPRESS REST API FUNDAMENTALS:
 * 
 * The WordPress REST API is a powerful interface that allows external applications
 * to interact with WordPress data using standard HTTP methods.
 * 
 * Key Components:
 * - Endpoints: URLs that accept requests
 * - Routes: URL patterns that map to endpoints
 * - Requests: HTTP requests with methods, headers, and data
 * - Responses: JSON responses with data and status codes
 * - Authentication: Security mechanisms for protected endpoints
 * 
 * HTTP Methods:
 * - GET: Retrieve data (read operations)
 * - POST: Create new data (create operations)
 * - PUT/PATCH: Update existing data (update operations)
 * - DELETE: Remove data (delete operations)
 * 
 * API Functions:
 * - register_rest_route(): Register custom endpoints
 * - register_rest_field(): Add fields to existing endpoints
 * - rest_ensure_response(): Ensure proper response format
 * - rest_authorization_required_code(): Get authorization error code
 * - rest_validate_request_arg(): Validate request arguments
 * - rest_sanitize_request_arg(): Sanitize request arguments
 * 
 * Response Status Codes:
 * - 200: OK (success)
 * - 201: Created (resource created)
 * - 400: Bad Request (invalid request)
 * - 401: Unauthorized (authentication required)
 * - 403: Forbidden (insufficient permissions)
 * - 404: Not Found (resource not found)
 * - 500: Internal Server Error (server error)
 */

// SECTION 1: BASIC GET ENDPOINTS
// Simple endpoints for retrieving data

/**
 * Register a custom REST API endpoint to retrieve books
 * 
 * This demonstrates the basic structure of a REST API endpoint:
 * - Namespace: Organizes endpoints (hth/v1)
 * - Route: URL pattern (/books)
 * - Methods: HTTP methods accepted
 * - Callback: Function to handle the request
 * - Permission callback: Security check function
 * 
 * URL: /wp-json/hth/v1/books
 * Method: GET
 * Purpose: Retrieve all books
 */
function hth_register_books_endpoint() {
    register_rest_route('hth/v1', '/books', array(
        'methods' => 'GET',
        'callback' => 'hth_get_books',
        'permission_callback' => '__return_true', // Allow public access for demonstration
        'args' => array(
            'per_page' => array(
                'description' => 'Number of books to retrieve',
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0 && $param <= 100;
                },
                'sanitize_callback' => 'absint'
            ),
            'page' => array(
                'description' => 'Page number for pagination',
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
                'sanitize_callback' => 'absint'
            ),
            'search' => array(
                'description' => 'Search term to filter books',
                'type' => 'string',
                'validate_callback' => function($param, $request, $key) {
                    return is_string($param);
                },
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'genre' => array(
                'description' => 'Filter books by genre',
                'type' => 'string',
                'validate_callback' => function($param, $request, $key) {
                    return is_string($param);
                },
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_books_endpoint');

/**
 * Callback function to retrieve books
 * 
 * This function handles the actual request processing:
 * - Extracts parameters from the request
 * - Builds WordPress query arguments
 * - Executes the query
 * - Formats the response
 * - Handles errors appropriately
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_get_books(WP_REST_Request $request) {
    // Extract parameters from request
    $per_page = $request->get_param('per_page');
    $page = $request->get_param('page');
    $search = $request->get_param('search');
    $genre = $request->get_param('genre');

    // Build query arguments
    $args = array(
        'post_type' => 'book',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish'
    );

    // Add search functionality
    if (!empty($search)) {
        $args['s'] = $search;
    }

    // Add genre filtering
    if (!empty($genre)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'genre',
                'field' => 'slug',
                'terms' => $genre
            )
        );
    }

    // Execute the query
    $books_query = new WP_Query($args);
    $books = $books_query->posts;

    // Handle empty results
    if (empty($books)) {
        return new WP_Error('no_books', 'No books found', array('status' => 404));
    }

    // Format the response data
    $data = array();
    foreach ($books as $book) {
        // Get custom meta fields
        $author = get_post_meta($book->ID, '_book_author', true);
        $isbn = get_post_meta($book->ID, '_book_isbn', true);
        
        // Get taxonomies
        $genres = wp_get_post_terms($book->ID, 'genre', array('fields' => 'names'));
        
        // Get featured image
        $featured_image = get_the_post_thumbnail_url($book->ID, 'medium');

        $data[] = array(
            'id' => $book->ID,
            'title' => $book->post_title,
            'content' => apply_filters('the_content', $book->post_content),
            'excerpt' => $book->post_excerpt,
            'date' => $book->post_date,
            'modified' => $book->post_modified,
            'status' => $book->post_status,
            'author' => $author,
            'isbn' => $isbn,
            'genres' => $genres,
            'featured_image' => $featured_image,
            'permalink' => get_permalink($book->ID)
        );
    }

    // Build response with pagination info
    $response = rest_ensure_response($data);
    
    // Add pagination headers
    $response->header('X-WP-Total', $books_query->found_posts);
    $response->header('X-WP-TotalPages', $books_query->max_num_pages);
    
    return $response;
}

/**
 * Register endpoint for single book retrieval
 * 
 * This endpoint allows retrieval of a specific book by ID.
 * 
 * URL: /wp-json/hth/v1/books/123
 * Method: GET
 * Purpose: Retrieve a specific book
 */
function hth_register_single_book_endpoint() {
    register_rest_route('hth/v1', '/books/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'hth_get_single_book',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'description' => 'Book ID',
                'type' => 'integer',
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
                'sanitize_callback' => 'absint'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_single_book_endpoint');

/**
 * Callback function to retrieve a single book
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_get_single_book(WP_REST_Request $request) {
    $book_id = $request->get_param('id');
    
    // Check if book exists
    $book = get_post($book_id);
    if (!$book || $book->post_type !== 'book' || $book->post_status !== 'publish') {
        return new WP_Error('book_not_found', 'Book not found', array('status' => 404));
    }

    // Get additional data
    $author = get_post_meta($book_id, '_book_author', true);
    $isbn = get_post_meta($book_id, '_book_isbn', true);
    $genres = wp_get_post_terms($book_id, 'genre', array('fields' => 'names'));
    $featured_image = get_the_post_thumbnail_url($book_id, 'full');

    // Format response
    $data = array(
        'id' => $book->ID,
        'title' => $book->post_title,
        'content' => apply_filters('the_content', $book->post_content),
        'excerpt' => $book->post_excerpt,
        'date' => $book->post_date,
        'modified' => $book->post_modified,
        'status' => $book->post_status,
        'author' => $author,
        'isbn' => $isbn,
        'genres' => $genres,
        'featured_image' => $featured_image,
        'permalink' => get_permalink($book_id)
    );

    return rest_ensure_response($data);
}

// SECTION 2: TAXONOMY ENDPOINTS
// Endpoints for retrieving taxonomy data

/**
 * Register a custom REST API endpoint to retrieve book genres
 * 
 * URL: /wp-json/hth/v1/genres
 * Method: GET
 * Purpose: Retrieve all book genres
 */
function hth_register_genres_endpoint() {
    register_rest_route('hth/v1', '/genres', array(
        'methods' => 'GET',
        'callback' => 'hth_get_genres',
        'permission_callback' => '__return_true',
        'args' => array(
            'hide_empty' => array(
                'description' => 'Hide genres with no books',
                'type' => 'boolean',
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_genres_endpoint');

/**
 * Callback function to retrieve genres
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_get_genres(WP_REST_Request $request) {
    $hide_empty = $request->get_param('hide_empty');

    // Get all genres
    $genres = get_terms(array(
        'taxonomy' => 'genre',
        'hide_empty' => $hide_empty
    ));

    if (is_wp_error($genres)) {
        return new WP_Error('genres_error', 'Error retrieving genres', array('status' => 500));
    }

    if (empty($genres)) {
        return new WP_Error('no_genres', 'No genres found', array('status' => 404));
    }

    // Format the response
    $data = array();
    foreach ($genres as $genre) {
        $data[] = array(
            'id' => $genre->term_id,
            'name' => $genre->name,
            'slug' => $genre->slug,
            'description' => $genre->description,
            'count' => $genre->count,
            'link' => get_term_link($genre)
        );
    }

    return rest_ensure_response($data);
}

// SECTION 3: POST ENDPOINTS
// Endpoints for creating new data

/**
 * Register endpoint for creating books
 * 
 * URL: /wp-json/hth/v1/books
 * Method: POST
 * Purpose: Create a new book
 */
function hth_register_create_book_endpoint() {
    register_rest_route('hth/v1', '/books', array(
        'methods' => 'POST',
        'callback' => 'hth_create_book',
        'permission_callback' => 'hth_create_book_permissions',
        'args' => array(
            'title' => array(
                'description' => 'Book title',
                'type' => 'string',
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return !empty($param) && is_string($param);
                },
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'content' => array(
                'description' => 'Book content',
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'wp_kses_post'
            ),
            'excerpt' => array(
                'description' => 'Book excerpt',
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'sanitize_textarea_field'
            ),
            'author' => array(
                'description' => 'Book author',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'isbn' => array(
                'description' => 'Book ISBN',
                'type' => 'string',
                'validate_callback' => function($param, $request, $key) {
                    if (empty($param)) return true;
                    // Basic ISBN validation (simplified)
                    return preg_match('/^[0-9-]+$/', $param);
                },
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'genres' => array(
                'description' => 'Book genres (array of genre IDs)',
                'type' => 'array',
                'items' => array(
                    'type' => 'integer'
                ),
                'sanitize_callback' => function($param, $request, $key) {
                    return array_map('absint', (array) $param);
                }
            ),
            'status' => array(
                'description' => 'Book status',
                'type' => 'string',
                'default' => 'publish',
                'enum' => array('publish', 'draft', 'private'),
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_create_book_endpoint');

/**
 * Permission callback for creating books
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return bool|WP_Error True if user has permission, error otherwise
 */
function hth_create_book_permissions(WP_REST_Request $request) {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'You must be logged in to create books', array('status' => 401));
    }

    // Check if user can create books (using standard post capabilities)
    if (!current_user_can('publish_posts')) {
        return new WP_Error('rest_forbidden', 'You do not have permission to create books', array('status' => 403));
    }

    return true;
}

/**
 * Callback function to create a book
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_create_book(WP_REST_Request $request) {
    // Extract parameters
    $title = $request->get_param('title');
    $content = $request->get_param('content');
    $excerpt = $request->get_param('excerpt');
    $author = $request->get_param('author');
    $isbn = $request->get_param('isbn');
    $genres = $request->get_param('genres');
    $status = $request->get_param('status');

    // Create the post
    $post_data = array(
        'post_title' => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_type' => 'book',
        'post_status' => $status,
        'post_author' => get_current_user_id()
    );

    $book_id = wp_insert_post($post_data, true);

    // Check for errors
    if (is_wp_error($book_id)) {
        return new WP_Error('book_creation_failed', 'Failed to create book', array('status' => 500));
    }

    // Add custom meta fields
    if (!empty($author)) {
        update_post_meta($book_id, '_book_author', $author);
    }
    if (!empty($isbn)) {
        update_post_meta($book_id, '_book_isbn', $isbn);
    }

    // Add genres
    if (!empty($genres)) {
        wp_set_post_terms($book_id, $genres, 'genre');
    }

    // Return the created book data
    $created_book = get_post($book_id);
    
    $response_data = array(
        'id' => $book_id,
        'title' => $created_book->post_title,
        'content' => $created_book->post_content,
        'excerpt' => $created_book->post_excerpt,
        'status' => $created_book->post_status,
        'author' => get_post_meta($book_id, '_book_author', true),
        'isbn' => get_post_meta($book_id, '_book_isbn', true),
        'genres' => wp_get_post_terms($book_id, 'genre', array('fields' => 'names')),
        'permalink' => get_permalink($book_id)
    );

    $response = rest_ensure_response($response_data);
    $response->set_status(201); // Created status code
    
    return $response;
}

// SECTION 4: PUT/PATCH ENDPOINTS
// Endpoints for updating existing data

/**
 * Register endpoint for updating books
 * 
 * URL: /wp-json/hth/v1/books/123
 * Method: PUT
 * Purpose: Update an existing book
 */
function hth_register_update_book_endpoint() {
    register_rest_route('hth/v1', '/books/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'hth_update_book',
        'permission_callback' => 'hth_update_book_permissions',
        'args' => array(
            'id' => array(
                'description' => 'Book ID',
                'type' => 'integer',
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
                'sanitize_callback' => 'absint'
            ),
            'title' => array(
                'description' => 'Book title',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'content' => array(
                'description' => 'Book content',
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ),
            'excerpt' => array(
                'description' => 'Book excerpt',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field'
            ),
            'author' => array(
                'description' => 'Book author',
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'isbn' => array(
                'description' => 'Book ISBN',
                'type' => 'string',
                'validate_callback' => function($param, $request, $key) {
                    if (empty($param)) return true;
                    return preg_match('/^[0-9-]+$/', $param);
                },
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'genres' => array(
                'description' => 'Book genres (array of genre IDs)',
                'type' => 'array',
                'items' => array(
                    'type' => 'integer'
                ),
                'sanitize_callback' => function($param, $request, $key) {
                    return array_map('absint', (array) $param);
                }
            ),
            'status' => array(
                'description' => 'Book status',
                'type' => 'string',
                'enum' => array('publish', 'draft', 'private'),
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_update_book_endpoint');

/**
 * Permission callback for updating books
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return bool|WP_Error True if user has permission, error otherwise
 */

function hth_update_book_permissions(WP_REST_Request $request) {
    $book_id = $request->get_param('id');
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'You must be logged in to update books', array('status' => 401));
    }

    // Check if book exists
    $book = get_post($book_id);
    if (!$book || $book->post_type !== 'book') {
        return new WP_Error('book_not_found', 'Book not found', array('status' => 404));
    }

    // Check if user can edit this book
    if (!current_user_can('edit_post', $book_id)) {
        return new WP_Error('rest_forbidden', 'You do not have permission to edit this book', array('status' => 403));
    }

    return true;
}

/**
 * Callback function to update a book
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_update_book(WP_REST_Request $request) {
    $book_id = $request->get_param('id');
    
    // Get existing book
    $book = get_post($book_id);
    if (!$book || $book->post_type !== 'book') {
        return new WP_Error('book_not_found', 'Book not found', array('status' => 404));
    }

    // Build update data
    $update_data = array('ID' => $book_id);
    
    // Only update fields that are provided
    if ($request->has_param('title')) {
        $update_data['post_title'] = $request->get_param('title');
    }
    if ($request->has_param('content')) {
        $update_data['post_content'] = $request->get_param('content');
    }
    if ($request->has_param('excerpt')) {
        $update_data['post_excerpt'] = $request->get_param('excerpt');
    }
    if ($request->has_param('status')) {
        $update_data['post_status'] = $request->get_param('status');
    }

    // Update the post
    $result = wp_update_post($update_data, true);
    
    if (is_wp_error($result)) {
        return new WP_Error('book_update_failed', 'Failed to update book', array('status' => 500));
    }

    // Update custom meta fields
    if ($request->has_param('author')) {
        update_post_meta($book_id, '_book_author', $request->get_param('author'));
    }
    if ($request->has_param('isbn')) {
        update_post_meta($book_id, '_book_isbn', $request->get_param('isbn'));
    }

    // Update genres
    if ($request->has_param('genres')) {
        wp_set_post_terms($book_id, $request->get_param('genres'), 'genre');
    }

    // Return updated book data
    $updated_book = get_post($book_id);
    
    $response_data = array(
        'id' => $book_id,
        'title' => $updated_book->post_title,
        'content' => $updated_book->post_content,
        'excerpt' => $updated_book->post_excerpt,
        'status' => $updated_book->post_status,
        'author' => get_post_meta($book_id, '_book_author', true),
        'isbn' => get_post_meta($book_id, '_book_isbn', true),
        'genres' => wp_get_post_terms($book_id, 'genre', array('fields' => 'names')),
        'permalink' => get_permalink($book_id)
    );

    return rest_ensure_response($response_data);
}

// SECTION 5: DELETE ENDPOINTS
// Endpoints for deleting data

/**
 * Register endpoint for deleting books
 * 
 * URL: /wp-json/hth/v1/books/123
 * Method: DELETE
 * Purpose: Delete a book
 */
function hth_register_delete_book_endpoint() {
    register_rest_route('hth/v1', '/books/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'hth_delete_book',
        'permission_callback' => 'hth_delete_book_permissions',
        'args' => array(
            'id' => array(
                'description' => 'Book ID',
                'type' => 'integer',
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
                'sanitize_callback' => 'absint'
            ),
            'force' => array(
                'description' => 'Force delete (bypass trash)',
                'type' => 'boolean',
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_delete_book_endpoint');

/**
 * Permission callback for deleting books
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return bool|WP_Error True if user has permission, error otherwise
 */
function hth_delete_book_permissions(WP_REST_Request $request) {
    $book_id = $request->get_param('id');
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'You must be logged in to delete books', array('status' => 401));
    }

    // Check if book exists
    $book = get_post($book_id);
    if (!$book || $book->post_type !== 'book') {
        return new WP_Error('book_not_found', 'Book not found', array('status' => 404));
    }

    // Check if user can delete this book
    if (!current_user_can('delete_post', $book_id)) {
        return new WP_Error('rest_forbidden', 'You do not have permission to delete this book', array('status' => 403));
    }

    return true;
}

/**
 * Callback function to delete a book
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_delete_book(WP_REST_Request $request) {
    $book_id = $request->get_param('id');
    $force = $request->get_param('force');
    
    // Get book data before deletion
    $book = get_post($book_id);
    if (!$book || $book->post_type !== 'book') {
        return new WP_Error('book_not_found', 'Book not found', array('status' => 404));
    }

    // Store book data for response
    $book_data = array(
        'id' => $book->ID,
        'title' => $book->post_title,
        'status' => $book->post_status
    );

    // Delete the book
    $result = wp_delete_post($book_id, $force);
    
    if (!$result) {
        return new WP_Error('book_delete_failed', 'Failed to delete book', array('status' => 500));
    }

    // Return success response
    $response_data = array(
        'deleted' => true,
        'previous' => $book_data
    );

    return rest_ensure_response($response_data);
}

// SECTION 6: AUTHENTICATION AND SECURITY
// Advanced authentication examples

/**
 * Register endpoint with custom authentication
 * 
 * URL: /wp-json/hth/v1/admin/books
 * Method: GET
 * Purpose: Get books for admin users only
 */
function hth_register_admin_books_endpoint() {
    register_rest_route('hth/v1', '/admin/books', array(
        'methods' => 'GET',
        'callback' => 'hth_get_admin_books',
        'permission_callback' => 'hth_admin_permissions'
    ));
}
add_action('rest_api_init', 'hth_register_admin_books_endpoint');

/**
 * Admin permission callback
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return bool|WP_Error True if user has permission, error otherwise
 */
function hth_admin_permissions(WP_REST_Request $request) {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'Authentication required', array('status' => 401));
    }

    // Check if user has admin capabilities
    if (!current_user_can('manage_options')) {
        return new WP_Error('rest_forbidden', 'Admin access required', array('status' => 403));
    }

    return true;
}

/**
 * Callback for admin books endpoint
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_get_admin_books(WP_REST_Request $request) {
    // This endpoint can return additional data for admin users
    $args = array(
        'post_type' => 'book',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'draft', 'private') // Include all statuses for admin
    );

    $books = get_posts($args);
    
    $data = array();
    foreach ($books as $book) {
        $data[] = array(
            'id' => $book->ID,
            'title' => $book->post_title,
            'status' => $book->post_status,
            'author' => get_post_meta($book->ID, '_book_author', true),
            'isbn' => get_post_meta($book->ID, '_book_isbn', true),
            'date' => $book->post_date,
            'modified' => $book->post_modified,
            'edit_link' => get_edit_post_link($book->ID),
            'view_count' => get_post_meta($book->ID, '_view_count', true) ?: 0
        );
    }

    return rest_ensure_response($data);
}

/**
 * REST API BEST PRACTICES AND SECURITY:
 * 
 * 1. Authentication Methods:
 *    - Cookie Authentication: Default for logged-in users
 *    - Application Passwords: For external applications
 *    - JWT Tokens: For stateless authentication
 *    - API Keys: For server-to-server communication
 * 
 * 2. Permission Callbacks:
 *    - Always implement proper permission checks
 *    - Use WordPress capability system
 *    - Validate user ownership for resource access
 *    - Return appropriate HTTP status codes
 * 
 * 3. Input Validation:
 *    - Define argument schemas with validation
 *    - Use validate_callback for custom validation
 *    - Sanitize all input data
 *    - Check required fields
 * 
 * 4. Error Handling:
 *    - Use WP_Error for consistent error responses
 *    - Provide meaningful error messages
 *    - Use appropriate HTTP status codes
 *    - Log errors for debugging
 * 
 * 5. Performance Considerations:
 *    - Implement pagination for large datasets
 *    - Use caching where appropriate
 *    - Limit query complexity
 *    - Optimize database queries
 * 
 * 6. API Versioning:
 *    - Use namespace versioning (v1, v2, etc.)
 *    - Maintain backward compatibility
 *    - Document API changes
 *    - Provide migration paths
 * 
 * 7. Rate Limiting:
 *    - Implement rate limiting for public endpoints
 *    - Use WordPress transients for tracking
 *    - Return appropriate headers
 *    - Provide clear error messages
 * 
 * 8. Documentation:
 *    - Document all endpoints and parameters
 *    - Provide usage examples
 *    - Include error response formats
 *    - Maintain up-to-date documentation
 * 
 * EXAMPLE API USAGE:
 * 
 * // GET all books
 * GET /wp-json/hth/v1/books
 * 
 * // GET books with pagination
 * GET /wp-json/hth/v1/books?per_page=5&page=2
 * 
 * // GET books with search
 * GET /wp-json/hth/v1/books?search=fantasy
 * 
 * // GET single book
 * GET /wp-json/hth/v1/books/123
 * 
 * // POST new book
 * POST /wp-json/hth/v1/books
 * Content-Type: application/json
 * {
 *     "title": "New Book",
 *     "content": "Book content...",
 *     "author": "John Doe",
 *     "isbn": "978-0123456789"
 * }
 * 
 * // PUT update book
 * PUT /wp-json/hth/v1/books/123
 * Content-Type: application/json
 * {
 *     "title": "Updated Title",
 *     "status": "publish"
 * }
 * 
 * // DELETE book
 * DELETE /wp-json/hth/v1/books/123
 * 
 * ADDITIONAL FEATURES:
 * 
 * For production applications, you may want to implement:
 * - PUT/PATCH endpoints for updating books
 * - DELETE endpoints for removing books
 * - Custom fields registration with register_rest_field()
 * - Response modification with filters
 * - Webhook endpoints for notifications
 * - Rate limiting and caching
 * - CORS headers for cross-origin requests
 * - API documentation endpoints
 * 
 * Testing API Endpoints:
 * 
 * You can test these endpoints using:
 * - Browser for GET requests
 * - Postman or similar tools for all methods
 * - cURL commands from terminal
 * - JavaScript fetch() in the browser console
 * - WordPress REST API client libraries
 */

/**
 * SECTION 7: CUSTOM FIELD ENDPOINTS
 * 
 * Registering REST fields allows you to expose custom meta data or calculated fields
 * for your custom post types (like 'book') in the REST API responses.
 * 
 * - register_rest_field() lets you add, update, or format extra fields for API clients.
 * - get_callback: How to retrieve the field value for API responses.
 * - update_callback: How to update the field value via API requests (if supported).
 * - schema: Describes the field for documentation and validation.
 * 
 * This is useful for exposing custom meta (e.g., author, ISBN) or computed values (e.g., view count)
 * that are not part of the standard post object.
 */
function hth_register_book_meta_fields() {
    // Add author field to book posts
    register_rest_field('book', 'book_author', array(
        'get_callback' => 'hth_get_book_author_field',
        'update_callback' => 'hth_update_book_author_field',
        'schema' => array(
            'description' => 'The author of the book',
            'type' => 'string',
            'context' => array('view', 'edit')
        )
    ));

    // Add ISBN field to book posts
    register_rest_field('book', 'book_isbn', array(
        'get_callback' => 'hth_get_book_isbn_field',
        'update_callback' => 'hth_update_book_isbn_field',
        'schema' => array(
            'description' => 'The ISBN of the book',
            'type' => 'string',
            'context' => array('view', 'edit')
        )
    ));

    // Add view count field (read-only)
    register_rest_field('book', 'view_count', array(
        'get_callback' => 'hth_get_book_view_count_field',
        'schema' => array(
            'description' => 'Number of times the book has been viewed',
            'type' => 'integer',
            'context' => array('view', 'edit'),
            'readonly' => true
        )
    ));
}
add_action('rest_api_init', 'hth_register_book_meta_fields');

/**
 * Get book author field callback
 * 
 * @param array $object The post object
 * @param string $field_name The field name
 * @param WP_REST_Request $request The REST API request object
 * @return string The book author
 */
function hth_get_book_author_field($object, $field_name, $request) {
    return get_post_meta($object['id'], '_book_author', true);
}

/**
 * Update book author field callback
 * 
 * @param string $value The field value
 * @param WP_Post $object The post object
 * @param string $field_name The field name
 * @return bool True on success, false on failure
 */
function hth_update_book_author_field($value, $object, $field_name) {
    if (!$value) {
        return delete_post_meta($object->ID, '_book_author');
    }
    return update_post_meta($object->ID, '_book_author', sanitize_text_field($value));
}

/**
 * Get book ISBN field callback
 * 
 * @param array $object The post object
 * @param string $field_name The field name
 * @param WP_REST_Request $request The REST API request object
 * @return string The book ISBN
 */
function hth_get_book_isbn_field($object, $field_name, $request) {
    return get_post_meta($object['id'], '_book_isbn', true);
}

/**
 * Update book ISBN field callback
 * 
 * @param string $value The field value
 * @param WP_Post $object The post object
 * @param string $field_name The field name
 * @return bool True on success, false on failure
 */
function hth_update_book_isbn_field($value, $object, $field_name) {
    if (!$value) {
        return delete_post_meta($object->ID, '_book_isbn');
    }
    
    // Basic ISBN validation
    if (!preg_match('/^[0-9-]+$/', $value)) {
        return false;
    }
    
    return update_post_meta($object->ID, '_book_isbn', sanitize_text_field($value));
}

/**
 * Get book view count field callback
 * 
 * @param array $object The post object
 * @param string $field_name The field name
 * @param WP_REST_Request $request The REST API request object
 * @return int The view count
 */
function hth_get_book_view_count_field($object, $field_name, $request) {
    $count = get_post_meta($object['id'], '_view_count', true);
    return $count ? intval($count) : 0;
}

// SECTION 8: RESPONSE MODIFICATION
// Modifying existing endpoint responses

/**
 * Add custom data to book REST API responses
 * 
 * This demonstrates how to modify existing endpoint responses
 */
function hth_modify_book_rest_response($response, $post, $request) {
    // Only modify book post type responses
    if ($post->post_type !== 'book') {
        return $response;
    }

    $data = $response->get_data();
    
    // Add custom fields
    $data['custom_data'] = array(
        'reading_time' => hth_calculate_reading_time($post->post_content),
        'word_count' => str_word_count(strip_tags($post->post_content)),
        'last_updated' => get_the_modified_time('c', $post->ID),
        'featured_image_url' => get_the_post_thumbnail_url($post->ID, 'full'),
        'excerpt_formatted' => wp_trim_words($post->post_excerpt, 30, '...')
    );

    // Add genre information
    $genres = wp_get_post_terms($post->ID, 'genre');
    if (!is_wp_error($genres) && !empty($genres)) {
        $data['genres'] = array_map(function($genre) {
            return array(
                'id' => $genre->term_id,
                'name' => $genre->name,
                'slug' => $genre->slug,
                'description' => $genre->description,
                'count' => $genre->count
            );
        }, $genres);
    }

    $response->set_data($data);
    return $response;
}
add_filter('rest_prepare_book', 'hth_modify_book_rest_response', 10, 3);

/**
 * Calculate estimated reading time
 * 
 * @param string $content The content to calculate reading time for
 * @return int Reading time in minutes
 */
function hth_calculate_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_speed = 200; // Average words per minute
    $minutes = ceil($word_count / $reading_speed);
    return max(1, $minutes); // Minimum 1 minute
}

// SECTION 9: WEBHOOK ENDPOINTS
// Creating webhook-style endpoints for notifications

/**
 * Register webhook endpoint for book notifications
 * 
 * Webhook endpoints differ from normal REST endpoints:
 * - Normal REST endpoints are usually called by clients (users, apps) to fetch or change data.
 * - Webhook endpoints are called by other servers or services automatically when an event occurs elsewhere.
 * - Your WordPress site acts as a receiver, processing incoming event notifications (payloads).
 * - Webhooks are useful for integrations, automation, and real-time updates.
 * - They often require authentication (e.g., API key) to ensure only trusted sources can trigger them.
 * 
 * URL: /wp-json/hth/v1/webhooks/book-updated
 * Method: POST
 * Purpose: Receive notifications when books are updated
 */
function hth_register_book_webhook_endpoint() {
    register_rest_route('hth/v1', '/webhooks/book-updated', array(
        'methods' => 'POST',
        'callback' => 'hth_handle_book_webhook',
        'permission_callback' => 'hth_webhook_permissions',
        'args' => array(
            'action' => array(
                'description' => 'The action that triggered the webhook',
                'type' => 'string',
                'required' => true,
                'enum' => array('created', 'updated', 'deleted'),
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'book_id' => array(
                'description' => 'The ID of the book',
                'type' => 'integer',
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
                'sanitize_callback' => 'absint'
            ),
            'timestamp' => array(
                'description' => 'When the action occurred',
                'type' => 'string',
                'format' => 'date-time',
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'user_id' => array(
                'description' => 'The ID of the user who performed the action',
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            )
        )
    ));
}
add_action('rest_api_init', 'hth_register_book_webhook_endpoint');

/**
 * Permission callback for webhook endpoints
 * 
 * Checks for a valid API key in the request headers (X-API-Key).
 * Only requests with a recognized key are allowed to trigger webhook actions.
 * This secures your webhook from unauthorized access.
 */
function hth_webhook_permissions(WP_REST_Request $request) {
    // Check for API key in headers
    $api_key = $request->get_header('X-API-Key');
    
    if (!$api_key) {
        return new WP_Error('missing_api_key', 'API key required', array('status' => 401));
    }

    // Verify API key (in production, store these securely)
    $valid_api_keys = get_option('hth_webhook_api_keys', array());
    
    if (!in_array($api_key, $valid_api_keys)) {
        return new WP_Error('invalid_api_key', 'Invalid API key', array('status' => 403));
    }

    return true;
}

/**
 * Handle book webhook notifications
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function hth_handle_book_webhook(WP_REST_Request $request) {
    $action = $request->get_param('action');
    $book_id = $request->get_param('book_id');
    $timestamp = $request->get_param('timestamp');
    $user_id = $request->get_param('user_id');

    // Log the webhook event
    error_log("Book webhook received: Action={$action}, Book ID={$book_id}, Timestamp={$timestamp}");

    // Process the webhook based on action
    switch ($action) {
        case 'created':
            $result = hth_process_book_created_webhook($book_id, $user_id, $timestamp);
            break;
        case 'updated':
            $result = hth_process_book_updated_webhook($book_id, $user_id, $timestamp);
            break;
        case 'deleted':
            $result = hth_process_book_deleted_webhook($book_id, $user_id, $timestamp);
            break;
        default:
            return new WP_Error('invalid_action', 'Invalid webhook action', array('status' => 400));
    }

    if (is_wp_error($result)) {
        return $result;
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Webhook processed successfully',
        'action' => $action,
        'book_id' => $book_id,
        'processed_at' => current_time('mysql')
    ));
}

/**
 * Process book created webhook
 * 
 * @param int $book_id The book ID
 * @param int $user_id The user ID
 * @param string $timestamp The timestamp
 * @return bool|WP_Error True on success, error on failure
 */
function hth_process_book_created_webhook($book_id, $user_id, $timestamp) {
    // Example: Send notification emails
    $book = get_post($book_id);
    if (!$book) {
        return new WP_Error('book_not_found', 'Book not found', array('status' => 404));
    }

    // Get subscribers for new book notifications
    $subscribers = get_option('hth_book_subscribers', array());
    
    foreach ($subscribers as $subscriber_email) {
        wp_mail(
            $subscriber_email,
            'New Book Added: ' . $book->post_title,
            'A new book has been added to the library: ' . $book->post_title
        );
    }

    // Update statistics
    $stats = get_option('hth_book_stats', array('created' => 0, 'updated' => 0, 'deleted' => 0));
    $stats['created']++;
    update_option('hth_book_stats', $stats);

    return true;
}

/**
 * Process book updated webhook
 * 
 * @param int $book_id The book ID
 * @param int $user_id The user ID
 * @param string $timestamp The timestamp
 * @return bool|WP_Error True on success, error on failure
 */
function hth_process_book_updated_webhook($book_id, $user_id, $timestamp) {
    // Clear related caches
    wp_cache_delete("book_{$book_id}", 'books');
    wp_cache_delete('all_books', 'books');

    // Update statistics
    $stats = get_option('hth_book_stats', array('created' => 0, 'updated' => 0, 'deleted' => 0));
    $stats['updated']++;
    update_option('hth_book_stats', $stats);

    return true;
}

/**
 * Process book deleted webhook
 * 
 * @param int $book_id The book ID
 * @param int $user_id The user ID
 * @param string $timestamp The timestamp
 * @return bool|WP_Error True on success, error on failure
 */
function hth_process_book_deleted_webhook($book_id, $user_id, $timestamp) {
    // Clean up related data
    wp_cache_delete("book_{$book_id}", 'books');
    wp_cache_delete('all_books', 'books');

    // Update statistics
    $stats = get_option('hth_book_stats', array('created' => 0, 'updated' => 0, 'deleted' => 0));
    $stats['deleted']++;
    update_option('hth_book_stats', $stats);

    return true;
}

// SECTION 10: ADVANCED FEATURES
// Rate limiting, caching, and other advanced features

/**
 * Rate limiting for REST API endpoints
 * 
 * This demonstrates how to implement basic rate limiting
 */
function hth_rate_limit_rest_requests($response, $handler, $request) {
    // Only apply rate limiting to our custom endpoints
    if (strpos($request->get_route(), '/hth/v1/') !== 0) {
        return $response;
    }

    $client_ip = $_SERVER['REMOTE_ADDR'];
    $rate_limit_key = 'hth_rate_limit_' . md5($client_ip);
    
    // Get current request count
    $request_count = get_transient($rate_limit_key);
    
    if ($request_count === false) {
        // First request in this time window
        set_transient($rate_limit_key, 1, 60); // 1 minute window
        $request_count = 1;
    } else {
        // Increment request count
        $request_count++;
        set_transient($rate_limit_key, $request_count, 60);
    }

    // Check if rate limit exceeded
    $rate_limit = 100; // 100 requests per minute
    if ($request_count > $rate_limit) {
        return new WP_Error(
            'rate_limit_exceeded',
            'Rate limit exceeded. Please try again later.',
            array('status' => 429)
        );
    }

    // Add rate limit headers to response
    if (is_wp_error($response)) {
        return $response;
    }

    $response->header('X-RateLimit-Limit', $rate_limit);
    $response->header('X-RateLimit-Remaining', max(0, $rate_limit - $request_count));
    $response->header('X-RateLimit-Reset', time() + 60);

    return $response;
}
add_filter('rest_request_after_callbacks', 'hth_rate_limit_rest_requests', 10, 3);

/**
 * Caching for REST API responses
 * 
 * This demonstrates how to implement caching for API responses
 */
function hth_cache_rest_response($response, $handler, $request) {
    // Only cache GET requests to our endpoints
    if ($request->get_method() !== 'GET' || strpos($request->get_route(), '/hth/v1/') !== 0) {
        return $response;
    }

    // Generate cache key based on request
    $cache_key = 'hth_api_' . md5($request->get_route() . serialize($request->get_params()));
    
    // Try to get cached response
    $cached_response = wp_cache_get($cache_key, 'hth_api');
    
    if ($cached_response !== false) {
        // Return cached response
        return rest_ensure_response($cached_response);
    }

    // Cache the response for 5 minutes
    if (!is_wp_error($response)) {
        wp_cache_set($cache_key, $response->get_data(), 'hth_api', 300);
    }

    return $response;
}
add_filter('rest_request_after_callbacks', 'hth_cache_rest_response', 5, 3);

/**
 * CORS (Cross-Origin Resource Sharing) support
 * 
 * This demonstrates how to add CORS headers for API access
 */
function hth_add_cors_headers() {
    // Only add CORS headers for our API endpoints
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/hth/v1/') !== false) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Access-Control-Max-Age: 3600');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            status_header(200);
            exit;
        }
    }
}
add_action('init', 'hth_add_cors_headers');

/**
 * API documentation endpoint
 * 
 * This endpoint provides a machine-readable summary of all available API endpoints,
 * their parameters, authentication requirements, and error codes.
 * 
 * Use this to help developers understand how to interact with your API.
 */
function hth_register_api_docs_endpoint() {
    register_rest_route('hth/v1', '/docs', array(
        'methods' => 'GET',
        'callback' => 'hth_get_api_docs',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'hth_register_api_docs_endpoint');

/**
 * Get API documentation
 * 
 * @param WP_REST_Request $request The REST API request object
 * @return WP_REST_Response Response object
 */
function hth_get_api_docs(WP_REST_Request $request) {
    $docs = array(
        'version' => '1.0',
        'base_url' => rest_url('hth/v1'),
        'endpoints' => array(
            'books' => array(
                'GET /books' => array(
                    'description' => 'Get all books',
                    'parameters' => array(
                        'per_page' => 'Number of books per page (1-100)',
                        'page' => 'Page number',
                        'search' => 'Search term',
                        'genre' => 'Filter by genre'
                    )
                ),
                'GET /books/{id}' => array(
                    'description' => 'Get single book',
                    'parameters' => array(
                        'id' => 'Book ID'
                    )
                ),
                'POST /books' => array(
                    'description' => 'Create new book',
                    'authentication' => 'required',
                    'parameters' => array(
                        'title' => 'Book title (required)',
                        'content' => 'Book content',
                        'excerpt' => 'Book excerpt',
                        'author' => 'Book author',
                        'isbn' => 'Book ISBN',
                        'genres' => 'Array of genre IDs',
                        'status' => 'Book status (publish, draft, private)'
                    )
                ),
                'PUT /books/{id}' => array(
                    'description' => 'Update existing book',
                    'authentication' => 'required',
                    'parameters' => array(
                        'id' => 'Book ID',
                        'title' => 'Book title',
                        'content' => 'Book content',
                        'excerpt' => 'Book excerpt',
                        'author' => 'Book author',
                        'isbn' => 'Book ISBN',
                        'genres' => 'Array of genre IDs',
                        'status' => 'Book status'
                    )
                ),
                'DELETE /books/{id}' => array(
                    'description' => 'Delete book',
                    'authentication' => 'required',
                    'parameters' => array(
                        'id' => 'Book ID',
                        'force' => 'Force delete (bypass trash)'
                    )
                )
            ),
            'genres' => array(
                'GET /genres' => array(
                    'description' => 'Get all genres',
                    'parameters' => array(
                        'hide_empty' => 'Hide empty genres'
                    )
                )
            ),
            'admin' => array(
                'GET /admin/books' => array(
                    'description' => 'Get all books (admin only)',
                    'authentication' => 'admin required'
                )
            ),
            'webhooks' => array(
                'POST /webhooks/book-updated' => array(
                    'description' => 'Webhook for book updates',
                    'authentication' => 'API key required',
                    'parameters' => array(
                        'action' => 'Action type (created, updated, deleted)',
                        'book_id' => 'Book ID',
                        'timestamp' => 'Timestamp',
                        'user_id' => 'User ID'
                    )
                )
            )
        ),
        'authentication' => array(
            'cookie' => 'Standard WordPress authentication for logged-in users',
            'application_password' => 'Application passwords for external applications',
            'api_key' => 'API keys for webhooks (X-API-Key header)'
        ),
        'rate_limiting' => array(
            'limit' => '100 requests per minute per IP',
            'headers' => array(
                'X-RateLimit-Limit' => 'Request limit',
                'X-RateLimit-Remaining' => 'Remaining requests',
                'X-RateLimit-Reset' => 'Reset time'
            )
        ),
        'error_codes' => array(
            '400' => 'Bad Request - Invalid request data',
            '401' => 'Unauthorized - Authentication required',
            '403' => 'Forbidden - Insufficient permissions',
            '404' => 'Not Found - Resource not found',
            '429' => 'Too Many Requests - Rate limit exceeded',
            '500' => 'Internal Server Error - Server error'
        )
    );

    return rest_ensure_response($docs);
}