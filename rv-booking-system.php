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

if( !defined('RVBS_BOOKING_PLUGIN_DIR') ) {
    define('RVBS_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if( !defined('RVBS_BOOKING_PLUGIN_URL') ) {
    define('RVBS_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// defien the plugin vertion 
if(!defined('PLUGIN_VER')){
    define('PLUGIN_VER', '1.0');
}

// Include the main plugin class
require_once RVBS_BOOKING_PLUGIN_DIR . '/functions.php';


require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-register-required-page.php';

register_activation_hook(__FILE__, 'my_custom_plugin_create_pages');
















// Hook to run when the plugin is deactivated
register_deactivation_hook(__FILE__, 'my_custom_plugin_delete_page');

// Function to delete the page
function my_custom_plugin_delete_page() {
    $page_slug = 'my-custom-page';
    $page = get_page_by_path($page_slug);

    if ($page) {
        wp_delete_post($page->ID, true); // true forces deletion (bypass trash)
    }
}



// Hook to run when the plugin is deactivated
register_deactivation_hook(__FILE__, 'my_custom_plugin_delete_page');



// require_once RVBS_BOOKING_PLUGIN_DIR . './templates/rvbs-register-template.php';







