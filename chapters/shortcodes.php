<?php

// Shortcode

// simple shortcode example
// This shortcode will display a custom message on the page where it is used.
function hth_custom_message_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'message' => 'Hello, this is a custom message! created by adding a shortcode.',
        ),
        $atts,
        'custom_message'
    );

    return '<div class="custom-message">' . esc_html($atts['message']) . '</div>';
}
add_shortcode('custom_message', 'hth_custom_message_shortcode');
