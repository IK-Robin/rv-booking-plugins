<?php
/*
Plugin Name: RV Booking Plugin
Description: A plugin to manage RV bookings.
Version: 1.0
Author: Robin
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('RVBS_BOOKING_PLUGIN_DIR')) {
    define('RVBS_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('RVBS_BOOKING_PLUGIN_URL')) {
    define('RVBS_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Define the plugin version
if (!defined('PLUGIN_VER')) {
    define('PLUGIN_VER', '1.0');
}

// Include necessary files
require_once RVBS_BOOKING_PLUGIN_DIR . '/functions.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-register-required-page.php';

// Register activation hook
register_activation_hook(__FILE__, 'rvbs_plugin_activate');

function rvbs_plugin_activate() {
    create_rv_bookings_table(); 
    my_custom_plugin_create_pages();
}

/**
 * Create the database table for RV bookings
 */
function create_rv_bookings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rvbs_bookings';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table already exists
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s", $table_name
    ));

    if ($table_exists === $table_name) {
        return; // Table already exists, no need to create it again
    }

    // SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        post_id INT NOT NULL,
        user_id INT NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        is_available TINYINT(1) NOT NULL DEFAULT 1,
        status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY post_id (post_id)
    ) $charset_collate;";

    // Include the upgrade.php file for dbDelta()
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Execute the SQL query
    dbDelta($sql);

    // Check for errors
    if (!empty($wpdb->last_error)) {
        error_log('Error creating RV bookings table: ' . $wpdb->last_error);
    } else {
        error_log('RV bookings table created successfully.');
    }

    // Add foreign key constraint separately (if needed)
    $foreign_key_sql = "ALTER TABLE $table_name
        ADD CONSTRAINT fk_post_id
        FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID)
        ON DELETE CASCADE;";

    // Execute the foreign key query
    $wpdb->query($foreign_key_sql);

    // Check for errors in foreign key creation
    if (!empty($wpdb->last_error)) {
        error_log('Error adding foreign key constraint: ' . $wpdb->last_error);
    } else {
        error_log('Foreign key constraint added successfully.');
    }
}

// Register activation hook to create the database table
register_activation_hook(__FILE__, 'create_rv_bookings_table');

