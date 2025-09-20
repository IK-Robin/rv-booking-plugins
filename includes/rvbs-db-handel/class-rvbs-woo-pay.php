// ✅ Add this inside your plugin (not a theme template)
// e.g. at the end of rvbs-checkout-booking.php

add_action('wp_ajax_rvbs_wc_start_payment', 'rvbs_wc_start_payment');
add_action('wp_ajax_nopriv_rvbs_wc_start_payment', 'rvbs_wc_start_payment');

function rvbs_wc_start_payment() {
    if ( ! class_exists('WooCommerce') ) {
        wp_send_json_error(['message' => 'WooCommerce not active']);
    }

    if ( ! check_ajax_referer('rvbs_checkout_nonce', '_ajax_nonce', false) ) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if ( ! session_id() ) { session_start(); }
    $cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if ( empty($cart) ) {
        wp_send_json_error(['message' => 'Cart is empty']);
    }

    $subtotal = 0.0;
    foreach ($cart as $item) {
        $post_id = (int) $item['post_id'];
        $price   = (float) ( get_post_meta($post_id, '_rv_lots_price', true) ?: 20.00 );
        $nights  = 1;
        try {
            $cin  = new DateTime($item['check_in']);
            $cout = new DateTime($item['check_out']);
            $nights = max(1, $cin->diff($cout)->days);
        } catch (Exception $e) {}
        $subtotal += $price * $nights;
    }
    $campground_fees = 5.00;
    $total = $subtotal + $campground_fees;

    $order = wc_create_order();

    $fee = new WC_Order_Item_Fee();
    $fee->set_name('RV Booking Payment');
    $fee->set_amount($total);
    $fee->set_total($total);
    $order->add_item($fee);

    $order->update_meta_data('_rvbs_cart_snapshot', $cart);
    $order->update_meta_data('_rvbs_source', 'rv-booking-plugin');

    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $name  = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
    if ( $email ) { $order->set_billing_email($email); }
    if ( $name ) {
        $parts = preg_split('/\s+/', $name, 2);
        $order->set_billing_first_name($parts[0] ?? $name);
        $order->set_billing_last_name($parts[1] ?? '');
    }

    $order->set_status('pending');
    $order->calculate_totals();
    $order->save();

    wp_send_json_success([
        'redirect' => $order->get_checkout_payment_url()
    ]);
}
