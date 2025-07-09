<?php

// Interacting with the WordPress database

// Function to create a custom table
function hth_create_custom_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL query to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        value text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action('after_switch_theme', 'hth_create_custom_table');

// Function to insert data into the custom table
function hth_insert_custom_data($name, $value)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Prepare the data for insertion
    $data = array(
        'name' => sanitize_text_field($name),
        'value' => sanitize_textarea_field($value),
    );

    // Insert the data into the table
    $wpdb->insert($table_name, $data);
}

// Function to check if the custom table exists and if so to insert initial data
function hth_check_and_insert_initial_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        // Insert initial data if the table exists
        hth_insert_custom_data('Sample Name', 'Sample Value');
    }
}


// Function to retrieve data from the custom table
function hth_get_custom_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Retrieve all data from the custom table
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    return $results;
}

// Function to update data in the custom table
function hth_update_custom_data($id, $name, $value)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Prepare the data for updating
    $data = array(
        'name' => sanitize_text_field($name),
        'value' => sanitize_textarea_field($value),
    );

    // Update the data in the table
    $where = array('id' => intval($id));
    $wpdb->update($table_name, $data, $where);
}

// Function to delete data from the custom table
function hth_delete_custom_data($id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // Delete the data from the table
    $where = array('id' => intval($id));
    $wpdb->delete($table_name, $where);
}

// Function to display custom data in the admin area
function hth_display_custom_data()
{
    $data = hth_get_custom_data();

    if (!empty($data)) {
        echo '<table class="widefat fixed">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Value</th></tr></thead>';
        echo '<tbody>';

        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['id']) . '</td>';
            echo '<td>' . esc_html($row['name']) . '</td>';
            echo '<td>' . esc_html($row['value']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No data found.</p>';
    }
}
// Function to add a menu item in the admin area
function hth_add_custom_menu_item()
{
    add_menu_page(
        __('Custom Data', 'hth-sample-plugin'),
        __('Custom Data', 'hth-sample-plugin'),
        'manage_options',
        'hth-custom-data',
        'hth_display_custom_data',
        'dashicons-database',
        6
    );
}
add_action('admin_menu', 'hth_add_custom_menu_item');
// Function to register a custom database table on plugin activation
function hth_activate_plugin()
{
    hth_create_custom_table();
    // Insert initial data into the custom table
    // This is optional, you can remove it if you don't want to insert initial data
    hth_check_and_insert_initial_data();
}
// Function to deactivate the plugin and remove the custom table
function hth_deactivate_plugin()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'hth_custom_table';

    // SQL query to drop the table
    $sql = "DROP TABLE IF EXISTS $table_name;";

    // Use $wpdb->query() instead of dbDelta() for dropping tables
    $wpdb->query($sql);
}
