<?php 

// Register a widget that uses a shortcode
class HTH_Sample_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'hth_sample_widget',
            __('HTH Sample Widget', 'text_domain'),
            array('description' => __('A widget that displays a shortcode', 'text_domain'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo do_shortcode('[custom_message message="This is a message from the widget!"]');
        echo $args['after_widget'];
    }

    public function form($instance) {
        // No options for this widget
    }

    public function update($new_instance, $old_instance) {
        return $new_instance;
    }
}
// Register the widget
function hth_register_sample_widget() {
    register_widget('HTH_Sample_Widget');
}
add_action('widgets_init', 'hth_register_sample_widget');
// Register a widget area
function hth_register_widget_area() {
    register_sidebar(array(
        'name'          => __('HTH Sample Widget Area', 'text_domain'),
        'id'            => 'hth_sample_widget_area',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'hth_register_widget_area');
// Display the widget area in a template file
function hth_display_widget_area() {
    if (is_active_sidebar('hth_sample_widget_area')) {
        dynamic_sidebar('hth_sample_widget_area');
    }
}
// Add the widget area to the footer
add_action('wp_footer', 'hth_display_widget_area');