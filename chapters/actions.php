<?php


//hooks

// This action will run when the footer of the site is loaded.
// It will display a custom message at the bottom of the page.
add_action('wp_footer', 'map_footer_message');
function map_footer_message() {
    echo "<p style='text-align:center;'>Thanks for visiting!</p>";
}



//filters

// This filter will modify the content of a post before it is displayed.
// It appends a custom message to the end of the post content.
add_filter('the_content', 'append_text_to_post');
function append_text_to_post($content) {
    if (is_single()) {
        $content .= '<p><em>Here we are adding some content at teh end of a post.</em></p>';
    }
    return $content;
}
