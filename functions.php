<?php

// load the script file 
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-script.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-style.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/all-shortcode.php';


// require_once RVBS_BOOKING_PLUGIN_DIR . '/templates/rvbs-search-rv.php';



// include all db querey here 

require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-db-handel/rvbs-register-all-db-action.php';


// // Hook to include custom template from plugin

// function my_custom_plugin_add_template($templates) {
//     $templates['my-custom-template.php'] = 'Search Rv';
//     return $templates;
// }

// add_filter('template_include', 'my_custom_plugin_load_template');

// function my_custom_plugin_load_template($template) {
//     if (is_page('search-rv')) {
//         $plugin_template = plugin_dir_path(__FILE__) . 'templates/my-custom-template.php';
//         if (file_exists($plugin_template)) {
//             return $plugin_template;
//         }
//     }
//     return $template;
// }




// Register AJAX actions
add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

?>
