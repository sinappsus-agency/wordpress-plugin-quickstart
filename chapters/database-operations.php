<?php

/**
 * CHAPTER 7: DATABASE OPERATIONS
 * 
 * This chapter demonstrates how to interact with the WordPress database safely and efficiently.
 * WordPress provides the $wpdb class for database operations, which offers security and convenience.
 * 
 * Key concepts covered:
 * - Creating custom database tables
 * - CRUD operations (Create, Read, Update, Delete)
 * - Data sanitization and validation
 * - Proper use of WordPress database functions
 * - Security best practices
 * - Admin interface for data management
 * 
 * IMPORTANT: WordPress Database Class ($wpdb)
 * The global $wpdb object provides access to WordPress database functions:
 * - $wpdb->prefix: WordPress table prefix (usually 'wp_')
 * - $wpdb->insert(): Insert data into tables
 * - $wpdb->update(): Update existing data
 * - $wpdb->delete(): Delete data from tables
 * - $wpdb->get_results(): Get multiple rows
 * - $wpdb->get_row(): Get single row
 * - $wpdb->get_var(): Get single value
 * - $wpdb->query(): Execute raw SQL queries
 * - $wpdb->prepare(): Prepare SQL statements (prevents SQL injection)
 * - $wpdb->get_charset_collate(): Get proper charset and collation
 * 
 * WordPress Database Table Types:
 * - Core tables: posts, users, options, etc.
 * - Custom tables: Created by plugins for specific needs
 * - Meta tables: Store additional data (postmeta, usermeta, etc.)
 * 
 * Data Types for MySQL:
 * - TINYINT: -128 to 127 (or 0 to 255 unsigned)
 * - SMALLINT: -32,768 to 32,767
 * - MEDIUMINT: -8,388,608 to 8,388,607
 * - INT: -2,147,483,648 to 2,147,483,647
 * - BIGINT: Large integer values
 * - TINYTEXT: Up to 255 characters
 * - TEXT: Up to 65,535 characters
 * - MEDIUMTEXT: Up to 16,777,215 characters
 * - LONGTEXT: Up to 4,294,967,295 characters
 * - VARCHAR(n): Variable-length string, up to n characters
 * - CHAR(n): Fixed-length string, exactly n characters
 * - DATE: Date values (YYYY-MM-DD)
 * - DATETIME: Date and time values (YYYY-MM-DD HH:MM:SS)
 * - TIMESTAMP: Timestamp values
 * - DECIMAL(m,d): Fixed-point decimal numbers
 * - FLOAT: Floating-point numbers
 * - DOUBLE: Double-precision floating-point numbers
 */

// SECTION 1: TABLE CREATION
// Creating custom database tables in WordPress

/**
 * Function to create a custom table
 * 
 * This function demonstrates the proper way to create custom tables in WordPress:
 * 1. Use global $wpdb object for database access
 * 2. Use $wpdb->prefix to ensure proper table naming
 * 3. Use $wpdb->get_charset_collate() for proper character set
 * 4. Use dbDelta() for table creation (handles updates automatically)
 * 5. Include the upgrade.php file for dbDelta() function
 * 
 * Table Structure Explanation:
 * - id: Primary key, auto-increment, medium integer (up to 8 million records)
 * - name: Short text field using TINYTEXT (up to 255 characters)
 * - value: Longer text field using TEXT (up to 65,535 characters)
 * - PRIMARY KEY: Defines the unique identifier for each row
 * 
 * Alternative Column Types You Can Use:
 * - BIGINT for larger ID numbers
 * - VARCHAR(255) for fixed-length strings
 * - LONGTEXT for very long text content
 * - DATETIME for date/time fields
 * - DECIMAL(10,2) for monetary values
 * - BOOLEAN for true/false values
 * - JSON for structured data (MySQL 5.7+)
 */
function hth_create_custom_table()
{
    global $wpdb; // Access WordPress database object

    // Create table name with WordPress prefix
    // This ensures compatibility with multisite and custom prefixes
    $table_name = $wpdb->prefix . 'hth_custom_table';
    
    // Get the proper charset and collation for the site
    // This ensures proper character encoding for international characters
    $charset_collate = $wpdb->get_charset_collate();

    // SQL query to create the table
    // Note: dbDelta() is very particular about format - spaces and capitalization matter!
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        value text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Include the upgrade script that contains dbDelta()
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Execute the table creation
    // dbDelta() is WordPress's preferred method for table creation
    // It can handle both creation and updates to existing tables
    dbDelta($sql);
}

// Hook to create table when theme is switched (for demonstration)
// In production, you'd typically use plugin activation hooks
add_action('after_switch_theme', 'hth_create_custom_table');

// SECTION 2: INSERT OPERATIONS
// Adding new data to the database

/**
 * Function to insert data into the custom table
 * 
 * This function demonstrates the proper way to insert data:
 * 1. Use global $wpdb object
 * 2. Sanitize all input data before insertion
 * 3. Use $wpdb->insert() method for type-safe insertion
 * 4. Handle return values to check for success/failure
 * 
 * WordPress Sanitization Functions:
 * - sanitize_text_field(): For single-line text
 * - sanitize_textarea_field(): For multi-line text
 * - sanitize_email(): For email addresses
 * - sanitize_url(): For URLs
 * - sanitize_key(): For database keys
 * - sanitize_html_class(): For CSS classes
 * - sanitize_file_name(): For file names
 * - absint(): For positive integers
 * - intval(): For integers
 * - floatval(): For floating point numbers
 * - wp_kses(): For HTML content (allows specific tags)
 * - wp_strip_all_tags(): Remove all HTML tags
 * 
 * @param string $name The name to insert
 * @param string $value The value to insert
 * @return int|false The row ID of the inserted row, or false on failure
 */
function hth_insert_custom_data($name, $value)
{
    global $wpdb; // Access WordPress database object

    // Get table name with prefix
    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Prepare the data for insertion with proper sanitization
    // This prevents SQL injection and ensures data integrity
    $data = array(
        'name' => sanitize_text_field($name),           // Sanitize single-line text
        'value' => sanitize_textarea_field($value),     // Sanitize multi-line text
        'created_at' => current_time('mysql'),          // WordPress function for current time
        'updated_at' => current_time('mysql')           // WordPress function for current time
    );

    // Optional: Define data formats for better type safety
    // %s = string, %d = integer, %f = float
    $formats = array(
        '%s', // name
        '%s', // value
        '%s', // created_at
        '%s'  // updated_at
    );

    // Insert the data into the table
    // $wpdb->insert() returns the number of rows inserted, or false on failure
    $result = $wpdb->insert($table_name, $data, $formats);
    
    // Return the inserted row ID or false
    if ($result !== false) {
        return $wpdb->insert_id; // Get the ID of the inserted row
    }
    
    return false; // Insertion failed
}

/**
 * Function to check if the custom table exists and insert initial data
 * 
 * This demonstrates:
 * - Checking if a table exists
 * - Conditional data insertion
 * - Using WordPress database methods safely
 * 
 * Table Existence Check Methods:
 * - SHOW TABLES LIKE: Check if table exists
 * - DESCRIBE table_name: Get table structure
 * - SELECT 1 FROM table_name LIMIT 1: Check if table has data
 */
function hth_check_and_insert_initial_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Check if the table exists using SHOW TABLES
    // $wpdb->get_var() returns a single value
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if ($table_exists) {
        // Check if table already has data to avoid duplicates
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        if ($count == 0) {
            // Insert initial sample data
            hth_insert_custom_data('Sample Name', 'Sample Value');
            hth_insert_custom_data('WordPress', 'Content Management System');
            hth_insert_custom_data('PHP', 'Server-side scripting language');
        }
    }
}

// SECTION 3: READ OPERATIONS
// Retrieving data from the database

/**
 * Function to retrieve data from the custom table
 * 
 * This function demonstrates various ways to retrieve data:
 * - Using $wpdb->get_results() for multiple rows
 * - Using different output formats (ARRAY_A, OBJECT, ARRAY_N)
 * - Basic error handling
 * 
 * WordPress Database Retrieval Methods:
 * - get_results(): Returns array of rows
 * - get_row(): Returns single row
 * - get_col(): Returns single column as array
 * - get_var(): Returns single value
 * 
 * Output Formats:
 * - OBJECT (default): Returns objects with property names
 * - ARRAY_A: Returns associative arrays
 * - ARRAY_N: Returns numerical arrays
 * 
 * @param int $limit Optional. Number of records to retrieve
 * @param string $order_by Optional. Column to order by
 * @param string $order Optional. ASC or DESC
 * @return array|null Array of results or null on failure
 */
function hth_get_custom_data($limit = null, $order_by = 'id', $order = 'ASC')
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Build the query with optional parameters
    $sql = "SELECT * FROM $table_name ORDER BY $order_by $order";
    
    // Add limit if specified
    if ($limit !== null) {
        $sql .= " LIMIT " . absint($limit);
    }

    // Retrieve all data from the custom table
    // ARRAY_A returns associative arrays instead of objects
    $results = $wpdb->get_results($sql, ARRAY_A);

    return $results;
}

/**
 * Function to get a single record by ID
 * 
 * Demonstrates:
 * - Using $wpdb->prepare() to prevent SQL injection
 * - Using $wpdb->get_row() for single row retrieval
 * - Parameter validation
 * 
 * @param int $id The record ID to retrieve
 * @return array|null Single record or null if not found
 */
function hth_get_custom_data_by_id($id)
{
    global $wpdb;

    // Validate input
    $id = absint($id);
    if ($id <= 0) {
        return null;
    }

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Use $wpdb->prepare() to safely include variables in SQL
    // %d = integer, %s = string, %f = float
    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
    
    // Get single row
    $result = $wpdb->get_row($sql, ARRAY_A);

    return $result;
}

/**
 * Function to search data by name
 * 
 * Demonstrates:
 * - Using LIKE operator for partial matching
 * - Proper escaping of LIKE wildcards
 * - Case-insensitive searching
 * 
 * @param string $search_term The term to search for
 * @return array Array of matching records
 */
function hth_search_custom_data($search_term)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Escape the search term for LIKE query
    $search_term = '%' . $wpdb->esc_like($search_term) . '%';

    // Use prepare for safe LIKE queries
    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE name LIKE %s", $search_term);
    
    $results = $wpdb->get_results($sql, ARRAY_A);

    return $results;
}

// SECTION 4: UPDATE OPERATIONS
// Modifying existing data

/**
 * Function to update data in the custom table
 * 
 * This function demonstrates:
 * - Using $wpdb->update() method
 * - Proper data sanitization for updates
 * - Using WHERE conditions safely
 * - Return value handling
 * 
 * $wpdb->update() Parameters:
 * - $table: Table name
 * - $data: Array of column => value pairs to update
 * - $where: Array of column => value pairs for WHERE clause
 * - $format: Array of formats for $data values
 * - $where_format: Array of formats for $where values
 * 
 * @param int $id The record ID to update
 * @param string $name The new name value
 * @param string $value The new value
 * @return bool True on success, false on failure
 */
function hth_update_custom_data($id, $name, $value)
{
    global $wpdb;

    // Validate input
    $id = absint($id);
    if ($id <= 0) {
        return false;
    }

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Prepare the data for updating with proper sanitization
    $data = array(
        'name' => sanitize_text_field($name),
        'value' => sanitize_textarea_field($value),
        'updated_at' => current_time('mysql')
    );

    // Define WHERE condition
    $where = array('id' => $id);

    // Define data formats for type safety
    $formats = array('%s', '%s', '%s');        // Data formats
    $where_formats = array('%d');              // Where formats

    // Update the data in the table
    // Returns number of rows updated, or false on failure
    $result = $wpdb->update($table_name, $data, $where, $formats, $where_formats);

    return $result !== false;
}

// SECTION 5: DELETE OPERATIONS
// Removing data from the database

/**
 * Function to delete data from the custom table
 * 
 * This function demonstrates:
 * - Using $wpdb->delete() method
 * - Safe deletion with WHERE conditions
 * - Input validation before deletion
 * - Return value checking
 * 
 * $wpdb->delete() Parameters:
 * - $table: Table name
 * - $where: Array of column => value pairs for WHERE clause
 * - $where_format: Array of formats for $where values
 * 
 * @param int $id The record ID to delete
 * @return bool True on success, false on failure
 */
function hth_delete_custom_data($id)
{
    global $wpdb;

    // Validate input
    $id = absint($id);
    if ($id <= 0) {
        return false;
    }

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Define WHERE condition for deletion
    $where = array('id' => $id);
    
    // Define format for WHERE condition
    $where_format = array('%d');

    // Delete the data from the table
    // Returns number of rows deleted, or false on failure
    $result = $wpdb->delete($table_name, $where, $where_format);

    return $result !== false;
}

/**
 * Function to delete all data from the custom table
 * 
 * Demonstrates:
 * - Using $wpdb->query() for custom SQL
 * - TRUNCATE vs DELETE differences
 * - Bulk operations
 * 
 * TRUNCATE vs DELETE:
 * - TRUNCATE: Faster, resets auto-increment, can't use WHERE
 * - DELETE: Slower, preserves auto-increment, can use WHERE
 * 
 * @param bool $reset_auto_increment Whether to reset the auto-increment counter
 * @return bool True on success, false on failure
 */
function hth_delete_all_custom_data($reset_auto_increment = false)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    if ($reset_auto_increment) {
        // Use TRUNCATE to reset auto-increment
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
    } else {
        // Use DELETE to preserve auto-increment
        $result = $wpdb->query("DELETE FROM $table_name");
    }

    return $result !== false;
}

// SECTION 6: ADMIN INTERFACE
// Creating admin interface for data management

/**
 * Function to display custom data in the admin area
 * 
 * This function demonstrates:
 * - HTML table creation for data display
 * - Proper data escaping for output
 * - Handling empty data sets
 * - Using WordPress admin styles
 * 
 * WordPress Output Escaping Functions:
 * - esc_html(): Escape HTML entities
 * - esc_attr(): Escape HTML attributes
 * - esc_url(): Escape URLs
 * - esc_js(): Escape JavaScript
 * - esc_textarea(): Escape textarea content
 * - wp_kses(): Allow specific HTML tags
 * - wp_kses_post(): Allow post content HTML tags
 */
function hth_display_custom_data()
{
    // Get all data from the custom table
    $data = hth_get_custom_data();

    if (!empty($data)) {
        echo '<div class="wrap">';
        echo '<h2>' . __('Custom Data', 'hth-sample-plugin') . '</h2>';
        
        // Use WordPress admin table styling
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column">' . __('ID', 'hth-sample-plugin') . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Name', 'hth-sample-plugin') . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Value', 'hth-sample-plugin') . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Created', 'hth-sample-plugin') . '</th>';
        echo '<th scope="col" class="manage-column">' . __('Actions', 'hth-sample-plugin') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '<td>' . esc_html($row['value']) . '</td>';
            echo '<td>' . esc_html($row['created_at']) . '</td>';
            echo '<td>';
            echo '<a href="#" class="button button-small" onclick="editRecord(' . esc_attr($row['id']) . ')">' . __('Edit', 'hth-sample-plugin') . '</a> ';
            echo '<a href="#" class="button button-small button-link-delete" onclick="deleteRecord(' . esc_attr($row['id']) . ')">' . __('Delete', 'hth-sample-plugin') . '</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<div class="wrap">';
        echo '<h2>' . __('Custom Data', 'hth-sample-plugin') . '</h2>';
        echo '<p>' . __('No data found.', 'hth-sample-plugin') . '</p>';
        echo '</div>';
    }
}

/**
 * Function to add a menu item in the admin area
 * 
 * This function demonstrates:
 * - Adding top-level admin menu pages
 * - Using WordPress dashicons
 * - Setting menu positions
 * - Proper capability checking
 * 
 * add_menu_page() Parameters:
 * - $page_title: Page title in browser tab
 * - $menu_title: Menu title in sidebar
 * - $capability: Required user capability
 * - $menu_slug: Unique menu slug
 * - $callback: Function to display page content
 * - $icon_url: Menu icon (dashicon or URL)
 * - $position: Menu position (lower numbers = higher position)
 * 
 * Common Menu Positions:
 * - 2: Dashboard
 * - 5: Posts
 * - 10: Media
 * - 15: Links
 * - 20: Pages
 * - 25: Comments
 * - 59: Separator
 * - 60: Appearance
 * - 65: Plugins
 * - 70: Users
 * - 75: Tools
 * - 80: Settings
 * - 99: Separator
 */
function hth_add_custom_menu_item()
{
    add_menu_page(
        __('Custom Data', 'hth-sample-plugin'),    // Page title
        __('Custom Data', 'hth-sample-plugin'),    // Menu title
        'manage_options',                          // Capability required
        'hth-custom-data',                         // Menu slug
        'hth_display_custom_data',                 // Callback function
        'dashicons-database',                      // Icon (dashicon)
        6                                          // Position (after Posts)
    );
}

// Hook to add menu item to admin
add_action('admin_menu', 'hth_add_custom_menu_item');

// SECTION 7: PLUGIN LIFECYCLE HOOKS
// Functions for plugin activation and deactivation

/**
 * Function to register a custom database table on plugin activation
 * 
 * This function runs when the plugin is activated and:
 * - Creates the custom table
 * - Inserts initial data
 * - Sets up any necessary options
 * 
 * Plugin activation is the proper time to:
 * - Create database tables
 * - Set default options
 * - Create necessary directories
 * - Schedule cron jobs
 * - Clear caches
 */
function hth_activate_plugin()
{
    // Create the custom table
    hth_create_custom_table();
    
    // Insert initial data into the custom table
    // This is optional - remove if you don't want sample data
    hth_check_and_insert_initial_data();
    
    // Set plugin version for future updates
    add_option('hth_plugin_version', '1.0.0');
    
    // Set activation timestamp
    add_option('hth_plugin_activated', current_time('mysql'));
}

/**
 * Function to deactivate the plugin and remove the custom table
 * 
 * This function runs when the plugin is deactivated and:
 * - Removes the custom table
 * - Cleans up plugin options
 * - Removes scheduled cron jobs
 * 
 * Plugin deactivation cleanup:
 * - Remove custom tables (if desired)
 * - Delete plugin options
 * - Clear scheduled events
 * - Remove temporary files
 * 
 * Note: Some plugins preserve data on deactivation
 * and only remove it on uninstall
 */
function hth_deactivate_plugin()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // SQL query to drop the table
    $sql = "DROP TABLE IF EXISTS $table_name;";

    // Use $wpdb->query() for DROP TABLE operations
    // dbDelta() is not suitable for dropping tables
    $wpdb->query($sql);
    
    // Clean up plugin options
    delete_option('hth_plugin_version');
    delete_option('hth_plugin_activated');
}

/**
 * ADDITIONAL DATABASE OPERATIONS AND BEST PRACTICES:
 * 
 * 1. Advanced Query Examples:
 * 
 * // JOIN queries
 * $sql = "SELECT p.post_title, m.meta_value 
 *         FROM {$wpdb->posts} p 
 *         JOIN {$wpdb->postmeta} m ON p.ID = m.post_id 
 *         WHERE m.meta_key = '_custom_field'";
 * 
 * // COUNT queries
 * $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE name LIKE '%search%'");
 * 
 * // GROUP BY queries
 * $results = $wpdb->get_results("SELECT name, COUNT(*) as count FROM $table_name GROUP BY name");
 * 
 * // Date range queries
 * $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE created_at BETWEEN %s AND %s", 
 *                      '2023-01-01', '2023-12-31');
 * 
 * 2. Transaction Support:
 * 
 * // Start transaction
 * $wpdb->query('START TRANSACTION');
 * 
 * // Perform multiple operations
 * $result1 = $wpdb->insert($table_name, $data1);
 * $result2 = $wpdb->insert($table_name, $data2);
 * 
 * // Commit or rollback
 * if ($result1 && $result2) {
 *     $wpdb->query('COMMIT');
 * } else {
 *     $wpdb->query('ROLLBACK');
 * }
 * 
 * 3. Performance Optimization:
 * 
 * // Use indexes for frequently queried columns
 * $wpdb->query("ALTER TABLE $table_name ADD INDEX idx_name (name)");
 * 
 * // Use LIMIT for large datasets
 * $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 10 OFFSET 20");
 * 
 * // Use prepared statements for repeated queries
 * $stmt = $wpdb->prepare("SELECT * FROM $table_name WHERE name = %s", $name);
 * 
 * 4. Error Handling:
 * 
 * // Check for errors after database operations
 * if ($wpdb->last_error) {
 *     error_log('Database error: ' . $wpdb->last_error);
 * }
 * 
 * // Enable error reporting during development
 * $wpdb->show_errors();
 * 
 * 5. Security Considerations:
 * 
 * // Always use $wpdb->prepare() for dynamic queries
 * $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
 * 
 * // Validate and sanitize all input
 * $name = sanitize_text_field($_POST['name']);
 * $email = sanitize_email($_POST['email']);
 * 
 * // Use appropriate capabilities for admin functions
 * if (!current_user_can('manage_options')) {
 *     wp_die('Access denied');
 * }
 * 
 * 6. Backup and Migration:
 * 
 * // Export data
 * function export_custom_data() {
 *     global $wpdb;
 *     $table_name = $wpdb->prefix . 'hth_custom_table';
 *     $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
 *     return json_encode($data);
 * }
 * 
 * // Import data
 * function import_custom_data($json_data) {
 *     $data = json_decode($json_data, true);
 *     foreach ($data as $row) {
 *         hth_insert_custom_data($row['name'], $row['value']);
 *     }
 * }
 * 
 * 7. Database Versioning:
 * 
 * // Track database version for updates
 * function check_database_version() {
 *     $current_version = get_option('hth_db_version', '1.0');
 *     if (version_compare($current_version, '1.1', '<')) {
 *         upgrade_database_to_1_1();
 *         update_option('hth_db_version', '1.1');
 *     }
 * }
 * 
 * 8. Multisite Considerations:
 * 
 * // Use appropriate table prefix for multisite
 * $table_name = $wpdb->base_prefix . 'hth_custom_table'; // Global table
 * $table_name = $wpdb->prefix . 'hth_custom_table';      // Per-site table
 * 
 * // Check if multisite
 * if (is_multisite()) {
 *     // Handle multisite-specific logic
 * }
 */
