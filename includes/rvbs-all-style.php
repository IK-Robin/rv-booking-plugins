<?php 

function rvbs_add_all_style() {
    // Correct Bootstrap CSS Path
    wp_enqueue_style('rvbs-bootstrap-css', RVBS_BOOKING_PLUGIN_URL . 'assets/css/bootstrap.min.css', [], PLUGIN_VER, 'all');
    wp_enqueue_style('rvbs-main-style', RVBS_BOOKING_PLUGIN_URL . 'assets/css/style.css', [], PLUGIN_VER, 'all');

}
add_action('wp_enqueue_scripts', 'rvbs_add_all_style');

// Admin Enqueue Script
function rvbs_add_all_admin_script() {
    // Correct Bootstrap CSS Path for Admin
    wp_enqueue_style('rvbs-bootstrap-css-admin', RVBS_BOOKING_PLUGIN_URL . 'assets/css/bootstrap.min.css', [], PLUGIN_VER, 'all');
}
add_action('admin_enqueue_scripts', 'rvbs_add_all_admin_script');

?>