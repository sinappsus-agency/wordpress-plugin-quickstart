<?php
/**
 * Plugin Name: HTH Sample Plugin
 * Plugin URI: https://hackthehologram.com
 * Description: A simple educational plugin.
 * Version: 1.0
 * Author: Jacques Artgraven
 */

defined('ABSPATH') or die('No script kiddies please!');


// Chapter 1: Actions and Filters (Hooks)
require_once plugin_dir_path(__FILE__) . 'chapters/actions.php';

// Chapter 2: Shortcodes
require_once plugin_dir_path(__FILE__) . 'chapters/shortcodes.php';

// Chapter 3: Custom Post Types
require_once plugin_dir_path(__FILE__) . 'chapters/custom-post-types.php';

// Chapter 4: Rest API
require_once plugin_dir_path(__FILE__) . 'chapters/rest-api.php';

// Chapter 5: Widgets from shortcodes
require_once plugin_dir_path(__FILE__) . 'chapters/widgets.php';

// Chapter 6: Enqueue Scripts and Styles
require_once plugin_dir_path(__FILE__) . 'chapters/enqueue-scripts.php';

// Chapter 7: Database Operations
require_once plugin_dir_path(__FILE__) . 'chapters/database-operations.php';

register_activation_hook(__FILE__, 'hth_activate_plugin');
register_deactivation_hook(__FILE__, 'hth_deactivate_plugin');
