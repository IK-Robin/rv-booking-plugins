<?php 
/**
 * rv_booking_system plugin 
 *
 * @package rv_booking_system
 * 
 * @link see in the function.php
 * @see work on the template rvbs-checkout.php
 */

 // AJAX handler for checkout
add_action('wp_ajax_rvbs_process_checkout', 'rvbs_process_checkout');
add_action('wp_ajax_nopriv_rvbs_process_checkout', 'rvbs_process_checkout');
function rvbs_process_checkout() {
    global $wpdb;

    // Verify nonce
    check_ajax_referer('rvbs_checkout_nonce', '_ajax_nonce');

    // Start session
    if (!session_id()) {
        session_start();
    }

    // Check if cart exists
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        wp_send_json_error(['message' => 'Cart is empty']);
        wp_die();
    }

    // Get form data
    $full_name = sanitize_text_field($_POST['full_name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $address_line_1 = sanitize_text_field($_POST['address_line_1']);
    $address_line_2 = sanitize_text_field($_POST['address_line_2']);
    $country = sanitize_text_field($_POST['country']);
    $postal_code = sanitize_text_field($_POST['postal_code']);

    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($address_line_1) || empty($country) || empty($postal_code)) {
        wp_send_json_error(['message' => 'Please fill in all required fields']);
        wp_die();
    }

    // Check if user exists by email
    $user = get_user_by('email', $email);
    if (!$user) {
        // Create a new user
        $username = sanitize_user(str_replace(' ', '_', strtolower($full_name)));
        $password = wp_generate_password();
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Failed to create user: ' . $user_id->get_error_message()]);
            wp_die();
        }

        // Set user role to Subscriber
        $user = new WP_User($user_id);
        $user->set_role('subscriber');

        // Update user meta with additional info
        update_user_meta($user_id, 'first_name', $full_name);
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'billing_address_1', $address_line_1);
        update_user_meta($user_id, 'billing_address_2', $address_line_2);
        update_user_meta($user_id, 'billing_country', $country);
        update_user_meta($user_id, 'billing_postcode', $postal_code);
    } else {
        $user_id = $user->ID;
    }

    // Process each cart item as a booking
    $cart = $_SESSION['cart'];
    $total_price = 0;
    $booking_ids = [];

    foreach ($cart as $item) {
        $lot_id = $item['post_id']; // Assuming post_id is the lot_id
        $post_id = $item['post_id'];
        $check_in = $item['check_in'];
        $check_out = $item['check_out'];
        $price = floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 20.00;
        $nights = (new DateTime($check_in))->diff(new DateTime($check_out))->days ?: 1;
        $subtotal = $price * $nights;
        $total_price += $subtotal;

        // Insert into wp_rvbs_bookings
        $result = $wpdb->insert(
            $wpdb->prefix . 'rvbs_bookings',
            [
                'lot_id' => $lot_id,
                'post_id' => $post_id,
                'user_id' => $user_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_price' => $subtotal,
                'status' => 'confirmed',
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error(['message' => 'Failed to create booking']);
            wp_die();
        }

        $booking_id = $wpdb->insert_id;
        $booking_ids[] = $booking_id;

        // Update booking counts in wp_rvbs_booking_counts
        $counts_table = $wpdb->prefix . 'rvbs_booking_counts';
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $counts_table WHERE user_id = %d AND lot_id = %d AND post_id = %d",
                $user_id,
                $lot_id,
                $post_id
            )
        );

        if ($existing) {
            // Update existing count
            $wpdb->update(
                $counts_table,
                [
                    'booking_count' => $existing->booking_count + 1,
                    'last_booked_at' => current_time('mysql')
                ],
                [
                    'user_id' => $user_id,
                    'lot_id' => $lot_id,
                    'post_id' => $post_id
                ],
                ['%d', '%s'],
                ['%d', '%d', '%d']
            );
        } else {
            // Insert new count
            $wpdb->insert(
                $counts_table,
                [
                    'user_id' => $user_id,
                    'lot_id' => $lot_id,
                    'post_id' => $post_id,
                    'booking_count' => 1,
                    'last_booked_at' => current_time('mysql')
                ],
                ['%d', '%d', '%d', '%d', '%s']
            );
        }
    }

    // Add campground fees to total price
    $total_price += 5.00;

    // Clear the cart
    unset($_SESSION['cart']);
    $_SESSION['cart_total_items'] = 0;

    // Return success response
    wp_send_json_success([
        'message' => 'Booking confirmed successfully!',
        'booking_id' => $booking_ids[0] // Return the first booking ID for redirection
    ]);

    wp_die();
}