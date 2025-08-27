<?php
/**
 * Plugin Name: HTH Sample Plugin                // Required
 * Plugin URI: https://hackthehologram.com       // Optional
 * Description: A simple educational plugin.     // Required
 * Version: 1.0                                 // Required
 * Author: Jacques Artgraven                    // Required
 * Author URI: https://example.com              // Optional
 * Text Domain: hth-sample-plugin               // Optional (for translations)
 * Domain Path: /languages                      // Optional (for translations)
 * License: GPL2                                // Optional
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html // Optional
 * Requires at least: 5.0                       // Optional (WordPress version)
 * Requires PHP: 7.2                            // Optional (PHP version)
 * Update URI: https://example.com/plugin-update // Optional (for custom updates)
 * Tags: education, sample, tutorial            // Optional (for plugin directory)
 * Contributors: username                       // Optional (WordPress.org usernames)
 * Network: true                                // Optional (for multisite plugins)
 */

// Security: Prevent direct access to this file
defined('ABSPATH') or die('No script kiddies please!');

// Include WordPress functions if running standalone for testing
if (!function_exists('plugin_dir_path')) {
    // This should not happen in normal WordPress environment
    die('WordPress not loaded. This plugin requires WordPress to function.');
}

// Chapter 1: Actions and Filters (Hooks)
require_once plugin_dir_path(__FILE__) . 'chapters/actions.php';

// Chapter 2: Shortcodes
require_once plugin_dir_path(__FILE__) . 'chapters/shortcodes.php';

// Chapter 3: Custom Post Types
require_once plugin_dir_path(__FILE__) . 'chapters/custom-post-types.php';

// Chapter 4: REST API
require_once plugin_dir_path(__FILE__) . 'chapters/rest-api.php';

// Chapter 5: Scripts and Styles
require_once plugin_dir_path(__FILE__) . 'chapters/enqueue-scripts.php';

// Chapter 6: Widgets
require_once plugin_dir_path(__FILE__) . 'chapters/widgets.php';

// Chapter 7: Database Operations
require_once plugin_dir_path(__FILE__) . 'chapters/database-operations.php';
