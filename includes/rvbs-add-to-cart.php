<?php

// Start the session if not already started
function start_custom_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_custom_session', 1);

// Creat

// get the session array and count the items in the cart




// Cart count function
function handle_rvbs_get_cart_count_ajax() {
    if (!session_id()) {
        session_start();
    }
    $cart_count = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    wp_send_json_success(['cart_count' => $cart_count]);
}
add_action('wp_ajax_rvbs_get_cart_count', 'handle_rvbs_get_cart_count_ajax');
add_action('wp_ajax_nopriv_rvbs_get_cart_count', 'handle_rvbs_get_cart_count_ajax');





function handle_add_to_cart() {
    // Verify AJAX request & nonce
    if (!check_ajax_referer('rvbs_add_to_cart_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
        return;
    }

    // Define required fields
    $required_fields = [
        'campsite' => 'Campsite ID',
        'check_in' => 'Check-in date',
        'check_out' => 'Check-out date',
        'adults' => 'Number of adults',
        'equipment_type' => 'Equipment type',
        'length_ft' => 'Equipment length',
        'slide_outs' => 'Number of slide-outs',
        'site_location' => 'Site location',
        'post_id' => 'Post ID',
        'room_title' => 'Room title'
    ];

    // Check for missing fields
    foreach ($required_fields as $field => $label) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field])) && $_POST[$field] !== '0') { // Allow '0' as valid
            wp_send_json_error(['message' => "Missing or invalid $label"]);
            return;
        }
    }

    // Sanitize and validate input data
    $rv_booking = [
        'campsite'       => intval($_POST['campsite']),
        'check_in'       => sanitize_text_field($_POST['check_in']),
        'check_out'      => sanitize_text_field($_POST['check_out']),
        'guests'         => isset($_POST['guests']) ? intval($_POST['guests']) : (intval($_POST['adults']) + intval($_POST['children'])),
        'adults'         => intval($_POST['adults']),
        'children'       => isset($_POST['children']) ? intval($_POST['children']) : 0,
        'pets'           => isset($_POST['pets']) ? intval($_POST['pets']) : 0,
        'equipment_type' => sanitize_text_field($_POST['equipment_type']),
        'length_ft'      => intval($_POST['length_ft']),
        'slide_outs'     => intval($_POST['slide_outs']),
        'site_location'  => sanitize_text_field($_POST['site_location']),
        'post_id'        => intval($_POST['post_id']),
        'room_title'     => sanitize_text_field($_POST['room_title']),
    ];

    // Additional backend validation
    if ($rv_booking['adults'] < 1) {
        wp_send_json_error(['message' => 'At least one adult is required']);
        return;
    }

    if ($rv_booking['length_ft'] <= 0) {
        wp_send_json_error(['message' => 'Equipment length must be greater than zero']);
        return;
    }

    // Verify the post exists and is an RV lot
    $lot = get_post($rv_booking['post_id']);
    if (!$lot || $lot->post_type !== 'rv-lots') {
        wp_send_json_error(['message' => 'Invalid RV lot']);
        return;
    }

    // Add to cart
    $_SESSION['cart'][$rv_booking['post_id']] = $rv_booking;
    $_SESSION['cart_total_items'] = count($_SESSION['cart']);

    // Debugging: Log session data (Remove in production)
    error_log(print_r($_SESSION, true));

    // Send success response
    wp_send_json_success([
        'cart_count' => $_SESSION['cart_total_items'],
        'cart'       => $_SESSION['cart'],
        'message'    => 'Added to cart successfully'
    ]);
}

// Register AJAX actions
add_action('wp_ajax_add_to_cart', 'handle_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'handle_add_to_cart');

// Debug: Display session data in the footer
function debug_session_cart() {
    if (isset($_SESSION['cart'])) {
        echo '<pre>';
        var_dump($_SESSION['cart']);
        echo '</pre>';
    }
}
add_action('wp_footer', 'debug_session_cart');