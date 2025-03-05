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
require_once RVBS_BOOKING_PLUGIN_DIR . 'admin/database/rvbs-bookings-table.php';
// Register activation hook to create the database table
register_activation_hook(__FILE__, 'create_rv_bookings_table');

