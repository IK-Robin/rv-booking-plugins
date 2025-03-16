<?php 


// Start the session if not already started
function start_custom_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_custom_session', 1);

// Create a session array 
function create_session_array() {
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}
add_action('init', 'create_session_array', 1);


function handle_add_to_cart() {
    // Verify AJAX request & nonce
    if (!check_ajax_referer('rvbs_add_to_cart_nonce', '_ajax_nonce', false)) {
        wp_send_json_error(['message' => 'Nonce verification failed.']);
        return;
    }

    session_start(); // Start session

    // Collect RV booking details
    $required_fields = ['campsite', 'check_in', 'check_out', 'guests', 'adults', 'children', 'site_location', 'post_id', 'room_title'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            wp_send_json_error(['message' => 'Missing required fields.']);
            return;
        }
    }

    // Sanitize input data
    $rv_booking = [
        'campsite'       => intval($_POST['campsite']),
        'check_in'       => sanitize_text_field($_POST['check_in']),
        'check_out'      => sanitize_text_field($_POST['check_out']),
        'guests'         => intval($_POST['guests']),
        'adults'         => intval($_POST['adults']),
        'children'       => intval($_POST['children']),
        'equipment_type' => sanitize_text_field($_POST['equipment_type']),
        'length_ft'      => intval($_POST['length_ft']),
        'slide_outs'     => intval($_POST['slide_outs']),
        'site_location'  => sanitize_text_field($_POST['site_location']),
        'post_id'        => intval($_POST['post_id']),
        'room_title'     => sanitize_text_field($_POST['room_title']),
    ];

    // Initialize cart session if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add RV booking data to cart (using post_id as the unique identifier)
    $_SESSION['cart'][$rv_booking['post_id']] = $rv_booking;

    // Update cart count
    $_SESSION['cart_total_items'] = count($_SESSION['cart']);

    // ✅ Debugging: Log session data (Remove in production)
    error_log(print_r($_SESSION, true));

    // ✅ Send JSON response with updated cart data
    wp_send_json_success([
        'cart_count' => $_SESSION['cart_total_items'],
        'cart'       => $_SESSION['cart'],
    ]);
}

// Register AJAX actions
add_action('wp_ajax_add_to_cart', 'handle_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'handle_add_to_cart');







// Debug: Display session data in the footer
function debug_session_cart() {
    //add value to the session cart 
  
        
    
    if (isset($_SESSION['cart'])) {
        echo '<pre>';
        var_dump($_SESSION['cart']);
        echo '</pre>';
    }
}
add_action('wp_footer', 'debug_session_cart');
