<?php 
/**
 * rv_booking_system plugin menu page  
 *
 * @package rv_booking_system
 * 
 * @link  functons.php 
 * @see functon page rv_booking_system_menu_page()
 */


 // redister menu pages 
 function rv_booking_system_menu_pages() {
    add_menu_page('rv-booking-system','RV Booking','manage_options','rv-booking-system','rv_booking_system_menu_register','','11');
    // add subment to show all shortcode 
    add_submenu_page('rv-booking-system',' Importent Shortcode',' Importent  Shortcode','manage_options','rv-booking-shortcode','rv_booking_system_all_shortcode','');
}

add_action('admin_menu','rv_booking_system_menu_pages');

function rv_booking_system_menu_register (){
    echo 'robin tui koi ';
}
 
/**
 * @link  functons.php
 * @see importent_shortcode.php
 */
require RV_BOOKING_STYTEM_PATH . '/includes/importent_shortcordes.php';
