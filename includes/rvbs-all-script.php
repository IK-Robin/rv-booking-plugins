<?php 


function rvbs_add_all_script() {
    // Add Bootstrap JS with dependencies
    // wp_enqueue_script('rvbs-bootstrap-js', RVBS_BOOKING_PLUGIN_URL . 'assets/js/rvbs-bootstrap.min.js', [], PLUGIN_VER, true);
    wp_enqueue_script('rvbs-bootstrap-js', plugin_dir_url(__FILE__) . 'assets/js/rvbs-bootstrap.min.js', array('jquery'), PLUGIN_VER, true);

}
add_action('wp_enqueue_scripts', 'rvbs_add_all_script');



?>