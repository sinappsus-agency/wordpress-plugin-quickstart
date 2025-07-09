<?php

// Enqueue scripts and styles for the plugin
function hth_enqueue_scripts() {
    // Enqueue a custom stylesheet
    wp_enqueue_style('hth-sample-plugin-style', plugin_dir_url(__FILE__) . 'css/style.css');

    // Enqueue a custom script
    wp_enqueue_script('hth-sample-plugin-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'hth_enqueue_scripts');


// Enqueue admin scripts and styles
function hth_enqueue_admin_scripts() {
    // Enqueue a custom admin stylesheet
    wp_enqueue_style('hth-sample-plugin-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');

    // Enqueue a custom admin script
    wp_enqueue_script('hth-sample-plugin-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'hth_enqueue_admin_scripts');


// Enqueue scripts and styles for the REST API
function hth_enqueue_rest_api_scripts() {
    // Enqueue a custom REST API script
    wp_enqueue_script('hth-sample-plugin-rest-api-script', plugin_dir_url(__FILE__) . 'js/rest-api.js', array('jquery'), null, true);

    // Localize the script with new data
    wp_localize_script('hth-sample-plugin-rest-api-script', 'hthRestApi', array(
        'nonce' => wp_create_nonce('wp_rest'),
        'apiUrl' => esc_url(rest_url('hth/v1/')),
    ));
}
add_action('rest_api_init', 'hth_enqueue_rest_api_scripts');

// Enqueue scripts and styles for the custom post type
function hth_enqueue_custom_post_type_scripts() {
    if (is_singular('book')) {
        // Enqueue a custom script for the book post type
        wp_enqueue_script('hth-sample-plugin-book-script', plugin_dir_url(__FILE__) . 'js/book.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'hth_enqueue_custom_post_type_scripts');
// Enqueue scripts and styles for the custom taxonomy
function hth_enqueue_custom_taxonomy_scripts() {
    if (is_tax('genre')) {
        // Enqueue a custom script for the genre taxonomy
        wp_enqueue_script('hth-sample-plugin-genre-script', plugin_dir_url(__FILE__) . 'js/genre.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'hth_enqueue_custom_taxonomy_scripts');

// Enqueue scripts and styles for the shortcode
function hth_enqueue_shortcode_scripts() {
    if (is_page() && has_shortcode(get_post()->post_content, 'custom_message')) {
        // Enqueue a custom script for the shortcode
        wp_enqueue_script('hth-sample-plugin-shortcode-script', plugin_dir_url(__FILE__) . 'js/shortcode.js', array('jquery'), null, true);
    }
}

add_action('wp_enqueue_scripts', 'hth_enqueue_shortcode_scripts');

