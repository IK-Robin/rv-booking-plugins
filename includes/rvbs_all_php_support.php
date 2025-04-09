<?php
/**
 * rv_booking_system plugin 
 *
 * @package rv_booking_system
 * @author  Rvbs Team
 * @link    function.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class rv_booking_system_load_all_file {
    public function __construct() {
        // Automatically include files when class is instantiated
        $this->includes();
    }
    
    public function includes() {
        // Include all required plugin files
        require_once  plugin_dir_path(__FILE__). './rvbs-db-handel/rvbs-load-more-post.php';

    }
    
    public function init() {
        // Additional initialization method if needed
        $this->includes();
    }
}

// Instantiate the class
$rv_booking_system = new rv_booking_system_load_all_file();