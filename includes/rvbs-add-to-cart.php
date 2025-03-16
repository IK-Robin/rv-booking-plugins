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

    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        wp_send_json_error(['message' => 'Missing product data.']);
        return;
    }

    session_start(); // Ensure session is started

    var_dump($_POST);
    // $product_id = intval($_POST['product_id']);
    // $quantity = intval($_POST['quantity']);
    wp_send_json_success([
        'cart_count' => 'hello', // Total quantity
        // 'unique_count' => $unique_count, // Unique products
        // 'cart' => $_SESSION['cart'],
    ]);
    // if ($product_id > 0 && $quantity > 0) {
    //     if (!isset($_SESSION['cart'])) {
    //         $_SESSION['cart'] = [];
    //     }

    //     // // Check if it's a new unique product
    //     // $is_new_product = !isset($_SESSION['cart'][$product_id]);

    //     // // Add to cart session
    //     // if ($is_new_product) {
    //     //     $_SESSION['cart'][$product_id] = $quantity;
    //     // } else {
    //     //     $_SESSION['cart'][$product_id] += $quantity;
    //     // }

    //     // // Update total items and unique count
    //     // $_SESSION['cart_total_items'] = array_sum($_SESSION['cart']);
    //     // $unique_count = count($_SESSION['cart']);

    //     // // 🔍 Debugging: Log session data
    //     // error_log(print_r($_SESSION, true));

    //     // ✅ Send JSON response with updated cart count & unique count
      
    // } else {
    //     wp_send_json_error(['message' => 'Invalid product ID or quantity.']);
    // }
}


add_action('wp_ajax_add_to_cart', 'handle_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'handle_add_to_cart');






// Debug: Display session data in the footer
function debug_session_cart() {
    //add value to the session cart 
  
        

            $_SESSION['cart']['id'] = '434';
            $_SESSION['cart']['i0o'] = '434';
    
    if (isset($_SESSION['cart'])) {
        echo '<pre>';
        var_dump($_SESSION['cart']);
        echo '</pre>';
    }
}
add_action('wp_footer', 'debug_session_cart');
