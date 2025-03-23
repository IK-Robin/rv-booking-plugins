<?php 


// Initialize the plugin   
// Hook to run when the plugin is activated


/**
 * rv_booking_system plugin
 *
 * @package rv_booking_system plugin
 * 
 * @link all template files
 * @see
 */


function my_custom_plugin_create_pages() {
    $pages = [
        [
            'title'   => 'Search RV',
            'slug'    => 'search-rv',
            'content' => 'This is a custom page created by the plugin for searching RVs.',
            'template' => '../templates/search-rv.php'
        ],
        [
            'title'   => 'Book Now',
            'slug'    => 'booknow',
            'content' => 'This is a custom page created by the plugin for booking RVs.',
            'template' => '../templates/rvbs-book-now.php'
        ],
        [
            'title'   => 'shopping-cart',
            'slug'    => 'shopping-cart',
            'content' => 'This is a custom page created by the plugin for booking RVs.',
            'template' => '../templates/shopping-cart.php'
        ],
        [
            'title'   => 'checkout',
            'slug'    => 'checkout',
            'content' => 'This is a custom page created by the plugin for booking RVs.',
            'template' => '../templates/rvbs-checkout.php'
        ],
        [
            'title'   => 'Booking Confirmation',
            'slug'    => 'booking-confirmation',
            'content' => 'This is a custom page created by the plugin for booking RVs.',
            'template' => '../templates/booking-confirmation.php'
        ],
    ];

    foreach ($pages as $page) {
        if (!get_page_by_path($page['slug'])) {
            $page_id = wp_insert_post([
                'post_title'   => $page['title'],
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_type'    => 'page',
                'post_name'    => $page['slug'],
            ]);

            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page['template']);
            }
        }
    }
}


// Hook to include custom templates from the plugin
add_filter('theme_page_templates', 'my_custom_plugin_add_templates');

function my_custom_plugin_add_templates($templates) {
    $templates['../templates/search-rv.php'] = 'Search RV';
    $templates['../templates/rvbs-book-now.php'] = 'Book Now';
    $templates['../templates/shopping-cart.php'] = 'Shopping Cart';
    $templates['../templates/rvbs-checkout.php'] = 'Checkout';
    $templates['../templates/booking-confirmation.php'] = 'Booking Confirmation';
    // $templates['../templates/catagory_filter.php'] = 'Category Filter';
    return $templates;
}


// Load the correct template for the pages 
add_filter('template_include', 'my_custom_plugin_load_template');

function my_custom_plugin_load_template($template) {
    if (is_page('search-rv')) {
        $plugin_template = plugin_dir_path(__FILE__) . '../templates/search-rv.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    if (is_page('booknow')) {
        $plugin_template = plugin_dir_path(__FILE__) . '../templates/rvbs-book-now.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    if (is_page('shopping-cart')) {
        $plugin_template = plugin_dir_path(__FILE__) . '../templates/shopping-cart.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    if (is_page('checkout')) {
        $plugin_template = plugin_dir_path(__FILE__) . '../templates/rvbs-checkout.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}







?>