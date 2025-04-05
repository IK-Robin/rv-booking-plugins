<?php

// load the script file 
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-script.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-style.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/all-shortcode.php';


// require_once RVBS_BOOKING_PLUGIN_DIR . '/templates/rvbs-search-rv.php';



// include all db querey here 

require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-db-handel/rvbs-register-all-db-action.php';



// register the custom post type here
// require_once RVBS_BOOKING_PLUGIN_DIR . 'custom_post.php';
require_once RVBS_BOOKING_PLUGIN_DIR . 'rvbs-rv-custompost-type.php';


// load the custom menu item in the plugin

require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-custom-menu-item.php';



// load the add to chart session here
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-add-to-cart.php';


// load the checkout php here
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-db-handel/rvbs-checkout-booking.php';
// // Hook to include custom template from plugin

//add filter to add custom template check abablity and other things
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-filter.php';


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








// filete the site using site type taxonomy 




// filter the site using the feature 


// load the template for single post prevew 
function rvbs_load_custom_template($template)
{
    if (is_singular('rv-lots')) {
        // Specify the path to your custom template file in the plugin
        $custom_template = plugin_dir_path(__FILE__) . 'single-rv-lots-template.php';

        // Check if the custom template file exists, then load it
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template; // Return the default template if no custom template is found
}
add_filter('template_include', 'rvbs_load_custom_template');

// add the style support for block theam 
add_theme_support('wp-block-styles');


// add anotehr logic to book and filter rv lots 

// Enqueue scripts and localize AJAX
function rvbs_enqueue_scripts()
{
    wp_enqueue_script('jquery');

    wp_register_script('rvbs-booking', get_template_directory_uri() . './assets/js/rvbs-booking.js', array('jquery'), '1.0', true);

    wp_localize_script('rvbs-booking', 'rvbs_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rvbs_booking_nonce')
    ));

    wp_enqueue_script('rvbs-booking');
}
add_action('wp_enqueue_scripts', 'rvbs_enqueue_scripts');

// Check availability AJAX handler
function rvbs_check_availability()
{
    check_ajax_referer('rvbs_booking_nonce', 'nonce');


    global $wpdb;
    $table_lots = $wpdb->prefix . 'rvbs_rv_lots';
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';

    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);

    $filter = sanitize_text_field($_POST['filter']);


    // Query to find available lots
    $query = $wpdb->prepare(
        "
        SELECT rl.*
        FROM $table_lots rl
        WHERE rl.is_available = 1 
        AND rl.is_trash = 0
        AND rl.status = 'confirmed'
        AND NOT EXISTS (
            SELECT 1 
            FROM $table_bookings rb 
            WHERE rb.post_id = rl.post_id
            AND rb.lot_id = rl.id
            AND rb.status IN ('pending', 'confirmed')
            AND (
                (%s BETWEEN rb.check_in AND rb.check_out)
                OR (%s BETWEEN rb.check_in AND rb.check_out)
                OR (rb.check_in BETWEEN %s AND %s)
            )
        )",
        $check_in,
        $check_out,
        $check_in,
        $check_out
    );

    $available_lots = $wpdb->get_results($query);

    if ($filter == 'booknowpage' && $available_lots) {
        $output = 'available';
    } else {
        $output = 'notavailable';
    }



    wp_send_json_success(array('html' => $output));
}
add_action('wp_ajax_rvbs_check_availability', 'rvbs_check_availability');
add_action('wp_ajax_nopriv_rvbs_check_availability', 'rvbs_check_availability');




// function to add the booking abavlity check for book now page
add_action('wp_ajax_check_avablity_book_now_page', 'check_avablity_book_now_page_callback');
add_action('wp_ajax_nopriv_check_avablity_book_now_page', 'check_avablity_book_now_page_callback');

function check_avablity_book_now_page_callback() {
    check_ajax_referer('rvbs_booking_nonce', 'nonce');

    global $wpdb;
    $table_lots = $wpdb->prefix . 'rvbs_rv_lots';
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';

    // Sanitize input
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 0;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $pets = isset($_POST['pets']) ? intval($_POST['pets']) : 0;
    $length_ft = isset($_POST['length_ft']) ? intval($_POST['length_ft']) : 0;

    // Validate required fields
    if (!$post_id || !$check_in || !$check_out) {
        wp_send_json_error(array('message' => 'Missing required fields'));
        return;
    }

    // Step 1: Check date availability (same as rvbs_check_availability)
    $query = $wpdb->prepare(
        "
        SELECT rl.*
        FROM $table_lots rl
        WHERE rl.is_available = 1 
        AND rl.is_trash = 0
        AND rl.status = 'confirmed'
        AND rl.post_id = %d
        AND NOT EXISTS (
            SELECT 1 
            FROM $table_bookings rb 
            WHERE rb.post_id = rl.post_id
            AND rb.lot_id = rl.id
            AND rb.status IN ('pending', 'confirmed')
            AND (
                (%s BETWEEN rb.check_in AND rb.check_out)
                OR (%s BETWEEN rb.check_in AND rb.check_out)
                OR (rb.check_in BETWEEN %s AND %s)
            )
        )",
        $post_id,
        $check_in,
        $check_out,
        $check_in,
        $check_out
    );

    $available_lots = $wpdb->get_results($query);
    $is_date_available = !empty($available_lots);

    // Step 2: Check guest capacity (example meta fields)
    $max_adults = (int) get_post_meta($post_id, '_rv_lot_max_adults', true) ?: 4; // Default to 4 if not set
    $max_children = (int) get_post_meta($post_id, '_rv_lot_max_children', true) ?: 4; // Default to 4
    $max_pets = (int) get_post_meta($post_id, '_rv_lot_max_pets', true) ?: 2; // Default to 2
    $total_guests = $adults + $children;

    $is_guest_capacity_ok = ($adults <= $max_adults && $children <= $max_children && $pets <= $max_pets);
    $guest_error = '';
    if ($adults > $max_adults) $guest_error = "Too many adults (max $max_adults).";
    elseif ($children > $max_children) $guest_error = "Too many children (max $max_children).";
    elseif ($pets > $max_pets) $guest_error = "Too many pets (max $max_pets).";

    // Step 3: Check equipment length
    $max_length = (int) get_post_meta($post_id, '_rv_lot_max_length', true) ?: 50; // Default to 50 ft
    $is_length_ok = $length_ft <= $max_length || $length_ft === 0; // Allow 0 (not specified)
    $length_error = $length_ft > $max_length ? "Equipment length exceeds max ($max_length ft)." : '';

    // Combine results
    if ($is_date_available && $is_guest_capacity_ok && $is_length_ok) {
        wp_send_json_success(array('html' => 'available'));
    } else {
        $error_message = '';
        if (!$is_date_available) $error_message = 'Dates not available.';
        elseif (!$is_guest_capacity_ok) $error_message = $guest_error;
        elseif (!$is_length_ok) $error_message = $length_error;

        wp_send_json_success(array(
            'html' => 'unavailable',
            'message' => $error_message
        ));
    }
}

// Book lot AJAX handler
function rvbs_book_lot()
{
    check_ajax_referer('rvbs_booking_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('Please log in to book a lot.');
    }

    global $wpdb;
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';
    $table_rv_lots = $wpdb->prefix . 'rvbs_rv_lots'; // Assuming this is your custom RV lots table

    // Get data from the AJAX request
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $lot_id = isset($_POST['lot_id']) ? intval($_POST['lot_id']) : $post_id; // Use post_id if lot_id isn’t provided separately
    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    $user_id = get_current_user_id();

    // Validate required fields
    if (!$post_id || !$check_in || !$check_out) {
        wp_send_json_error('Missing required fields.');
    }

    // Check if the lot exists in wp_rvbs_rv_lots (adjust this query based on your table structure)
    $lot_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_rv_lots WHERE id = %d",
        $lot_id
    ));

    if (!$lot_exists) {
        // If lot_id doesn’t exist, try to map post_id to lot_id (adjust this based on your setup)
        $lot_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_rv_lots WHERE post_id = %d",
            $post_id
        ));

        if (!$lot_id) {
            wp_send_json_error('Invalid lot ID: The specified lot does not exist.');
        }
    }

    // Calculate total price
    $start = new DateTime($check_in);
    $end = new DateTime($check_out);
    $days = $start->diff($end)->days;

    // Fetch price from post meta (or adjust to fetch from wp_rvbs_rv_lots if stored there)
    $price_per_day = floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 50.00; // Default to 50 if not set
    $total_price = $days * $price_per_day;

    // Insert booking
    $result = $wpdb->insert(
        $table_bookings,
        array(
            'lot_id' => $lot_id,
            'post_id' => $post_id,
            'user_id' => $user_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'total_price' => $total_price,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s')
    );

    if ($result === false) {
        wp_send_json_error('Error creating booking: ' . $wpdb->last_error);
    } else {
        wp_send_json_success('Booking request submitted successfully!');
    }
}
add_action('wp_ajax_rvbs_book_lot', 'rvbs_book_lot');
add_action('wp_ajax_nopriv_rvbs_book_lot', 'rvbs_book_lot');





// add anotehr logic to book and filter rv lots 