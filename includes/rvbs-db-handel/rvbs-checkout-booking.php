<?php
/**
 * Plugin Name: RV Booking Payment System
 * Description: Bridges RV Booking cart to WooCommerce checkout, shows a styled Order Summary, clears the cart after purchase, and writes bookings to custom tables when payment succeeds.
 * Version: 1.3.4
 * Author: Ik Robin
 * License: GPL-2.0-or-later
 * Text Domain: rv-booking-payment-system
 */

// if ( ! defined( 'ABSPATH' ) ) exit;

// // define( 'RVBPS_DIR', plugin_dir_path( __FILE__ ) );
// // define( 'RVBPS_URL', plugin_dir_url( __FILE__ ) );
// define( 'RVBPS_PRODUCT_OPTION', 'rvbps_virtual_product_id' );
// if ( ! defined( 'WP_DEBUG_LOG' ) ) { define( 'WP_DEBUG_LOG', true ); } // ensure logging
// if ( ! defined( 'RVBPS_DEBUG' ) ) { define( 'RVBPS_DEBUG', true ); }

// /* ---------------- helpers ---------------- */
// // function rvbps_log( $msg ) {
// // 	if ( RVBPS_DEBUG ) error_log( '[RVBPS] ' . ( is_string($msg) ? $msg : print_r($msg, true) ) );
// // }

// /* ---------------- Session ---------------- */


// /* --------- Create a hidden virtual product once --------- */
// // register_activation_hook( __FILE__, function () {
// // 	if ( ! function_exists('wc_get_product') ) return;

// // 	$product_id = (int) get_option( RVBPS_PRODUCT_OPTION, 0 );
// // 	$needs_new  = $product_id ? ( get_post_status( $product_id ) ? false : true ) : true;

// // 	if ( $needs_new ) {
// // 		$p = new WC_Product_Simple();
// // 		$p->set_name( 'RV Booking Payment (hidden)' );
// // 		$p->set_status( 'publish' );
// // 		$p->set_catalog_visibility( 'hidden' );
// // 		$p->set_virtual( true );
// // 		$p->set_downloadable( false );
// // 		$p->set_sold_individually( true );
// // 		$p->set_regular_price( 0 );
// // 		$p->set_tax_status( 'none' );
// // 		$id = $p->save();
// // 		if ( $id ) update_option( RVBPS_PRODUCT_OPTION, $id );
// // 		rvbps_log('Created hidden product id=' . $id);
// // 	}
// // });

// /* --------- Always purchasable (safety) --------- */
// add_filter( 'woocommerce_is_purchasable', function( $purchasable, $product ) {
// 	$p_id = (int) get_option( RVBPS_PRODUCT_OPTION, 0 );
// 	return ( $p_id && $product && (int) $product->get_id() === $p_id ) ? true : $purchasable;
// }, 10, 2 );

// /* ---------------- Assets ---------------- */
// add_action( 'wp_enqueue_scripts', function () {
// 	// Button bridge (finds #ikr_proceed_to_checkout)
// 	wp_enqueue_script(
// 		'rvbps-bridge',
// 		RVBPS_URL . 'assets/rvbps-bridge.js',
// 		array(),
// 		'1.3.0',
// 		true
// 	);

// 	if ( function_exists('wc_get_checkout_url') ) {
// 		wp_localize_script( 'rvbps-bridge', 'RVBPS_DATA', array(
// 			'checkoutUrl' => wc_get_checkout_url(),
// 		));
// 	}
// }, 20 );

// /* ------------- Transfer session cart → Woo cart on /checkout ------------- */
// add_action( 'template_redirect', function () {
// 	if ( ! function_exists('WC') || ! function_exists('is_checkout') ) return;
// 	if ( ! is_checkout() ) return;

// 	$rv_cart = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : array();
// 	rvbps_log('template_redirect on checkout, session cart count=' . count($rv_cart));
// 	if ( empty($rv_cart) ) return;

// 	$cart_total = 0.0;
// 	$first      = reset($rv_cart);
// 	$first_title = $first_details = $first_guests = '';

// 	foreach ( $rv_cart as $item ) {
// 		if ( empty($item['post_id']) ) continue;

// 		$post_id = (int) $item['post_id'];
// 		$price   = floatval( get_post_meta( $post_id, '_rv_lots_price', true ) );
// 		if ( $price <= 0 ) $price = 20.00;

// 		try { $ci = new DateTime( $item['check_in'] ?? 'now' ); $co = new DateTime( $item['check_out'] ?? 'now' ); $n  = max(1,$ci->diff($co)->days); }
// 		catch ( \Throwable $e ) { $n = 1; }

// 		$cart_total += ( $price * $n );

// 		if ( $first === $item ) {
// 			$first_title   = ! empty($item['room_title']) ? $item['room_title'] : ( get_the_title($post_id) ?: __('Campsite','rv-booking-payment-system') );
// 			$first_details = sprintf('%s - %s (%d %s)',$ci->format('D, M j'),$co->format('D, M j'),$n,_n('Night','Nights',$n,'rv-booking-payment-system'));
// 			$first_guests  = sprintf('%d %s%s',intval($item['adults'] ?? 0),__('Adults','rv-booking-payment-system'),! empty($item['children']) ? ', ' . intval($item['children']).' '.__('Children','rv-booking-payment-system') : '' );
// 		}
// 	}

// 	if ( $cart_total <= 0 ) return;

// 	if ( WC()->session ) {
// 		WC()->session->set( 'rvbps_total', $cart_total );
// 		WC()->session->set( 'rvbps_payload', $rv_cart );
// 		WC()->session->set( 'rvbps_meta', array( 'title'=>$first_title,'details'=>$first_details,'guests'=>$first_guests ) );
// 		rvbps_log('Saved payload into WC session; total=' . $cart_total);
// 	}

// 	if ( WC()->cart ) WC()->cart->empty_cart( true );

// 	$product_id = (int) get_option( RVBPS_PRODUCT_OPTION, 0 );
// 	if ( ! $product_id ) { rvbps_log('No hidden product id set'); return; }

// 	$cart_item_data = array(
// 		'rvbps_payment_item' => 1,
// 		'rvbps_total'        => (float) $cart_total,
// 		'rvbps_title'        => (string) $first_title,
// 		'rvbps_details'      => (string) $first_details,
// 		'rvbps_guests'       => (string) $first_guests,
// 	);
// 	WC()->cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );
// 	rvbps_log('Added hidden product to cart');
// });

// /* --------- Price + Name on the line item --------- */
// add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
// 	if ( ! $cart || ( is_admin() && ! defined('DOING_AJAX') ) ) return;
// 	foreach ( $cart->get_cart() as $key => $item ) {
// 		if ( empty( $item['rvbps_payment_item'] ) || empty( $item['data'] ) || ! is_object( $item['data'] ) ) continue;
// 		$total = isset($item['rvbps_total']) ? (float) $item['rvbps_total'] : 0.0;
// 		$name  = ! empty($item['rvbps_title']) ? $item['rvbps_title'] : __( 'RV Booking Payment', 'rv-booking-payment-system' );
// 		if ( $total > 0 && method_exists( $item['data'], 'set_price' ) ) $item['data']->set_price( $total );
// 		if ( method_exists( $item['data'], 'set_name' ) ) $item['data']->set_name( $name );
// 	}
// }, 10 );

// /* --------- Meta under the line item (dates / guests) --------- */
// add_filter( 'woocommerce_get_item_data', function( $data, $cart_item ) {
// 	if ( empty( $cart_item['rvbps_payment_item'] ) ) return $data;
// 	$details = ! empty($cart_item['rvbps_details']) ? $cart_item['rvbps_details'] : '';
// 	$guests  = ! empty($cart_item['rvbps_guests'])  ? $cart_item['rvbps_guests']  : '';
// 	if ( $details ) $data[] = array( 'key' => '', 'value' => wp_kses_post( $details ) );
// 	if ( $guests )  $data[] = array( 'key' => '', 'value' => wp_kses_post( $guests ) . ' 🐾' );
// 	return $data;
// }, 10, 2 );

// /* --------- Persist payload & totals to the ORDER meta --------- */
// add_action( 'woocommerce_checkout_create_order', function( $order, $data ) {
// 	if ( function_exists('WC') && WC()->session ) {
// 		$payload = WC()->session->get( 'rvbps_payload' );
// 		$total   = WC()->session->get( 'rvbps_total' );
// 		$meta    = WC()->session->get( 'rvbps_meta' );
// 		if ( $payload ) $order->update_meta_data( '_rvbps_payload', wp_json_encode( $payload ) );
// 		if ( $total )   $order->update_meta_data( '_rvbps_total', $total );
// 		if ( $meta )    $order->update_meta_data( '_rvbps_meta', wp_json_encode( $meta ) );
// 		rvbps_log('checkout_create_order: copied payload to order meta');
// 	}
// }, 10, 2 );

// add_action( 'woocommerce_checkout_create_order_line_item', function( $item, $key, $values ) {
// 	if ( empty( $values['rvbps_payment_item'] ) ) return;
// 	$total = isset( $values['rvbps_total'] ) ? (float) $values['rvbps_total'] : 0.0;
// 	$title = ! empty( $values['rvbps_title'] ) ? $values['rvbps_title'] : __( 'RV Booking Payment', 'rv-booking-payment-system' );
// 	$item->set_name( $title );
// 	$item->set_subtotal( $total );
// 	$item->set_total( $total );
// 	$item->set_taxes( array() );
// 	if ( ! empty( $values['rvbps_details'] ) ) $item->add_meta_data( 'Dates', $values['rvbps_details'], true );
// 	if ( ! empty( $values['rvbps_guests'] ) )  $item->add_meta_data( 'Guests', $values['rvbps_guests'], true );
// }, 10, 3 );

// /* --------- Create bookings in custom tables once payment succeeds --------- */
// function rvbps_resolve_payload_for_order( $order ) {
// 	// 1) order meta
// 	$payload_json = $order->get_meta( '_rvbps_payload' );
// 	if ( ! empty( $payload_json ) ) {
// 		$payload = json_decode( $payload_json, true );
// 		if ( is_array( $payload ) && ! empty( $payload ) ) {
// 			rvbps_log('payload from order meta count=' . count($payload));
// 			return $payload;
// 		}
// 	}
// 	// 2) WC session (if still present)
// 	if ( function_exists('WC') && WC()->session ) {
// 		$p = WC()->session->get('rvbps_payload');
// 		if ( is_array($p) && ! empty($p) ) {
// 			rvbps_log('payload from WC session count=' . count($p));
// 			return $p;
// 		}
// 	}
// 	// 3) PHP session as last resort
// 	if ( isset($_SESSION['cart']) && is_array($_SESSION['cart']) && ! empty($_SESSION['cart']) ) {
// 		rvbps_log('payload from PHP session cart count=' . count($_SESSION['cart']));
// 		return $_SESSION['cart'];
// 	}
// 	rvbps_log('NO payload found for order #' . $order->get_id());
// 	return array();
// }

// function rvbps_create_bookings_on_payment( $order_id ) {
// 	$order = wc_get_order( $order_id );
// 	if ( ! $order ) return;

// 	rvbps_log('Create bookings for order #' . $order_id . ' status=' . $order->get_status());

// 	// Avoid running twice
// 	if ( $order->get_meta( '_rvbps_bookings_created' ) ) {
// 		rvbps_log('Already created bookings, skipping');
// 		return;
// 	}

// 	$payload = rvbps_resolve_payload_for_order( $order );
// 	if ( empty( $payload ) ) return;

// 	/* capture billing */
// 	$billing_first  = $order->get_billing_first_name();
// 	$billing_last   = $order->get_billing_last_name();
// 	$full_name      = trim( $billing_first . ' ' . $billing_last );
// 	$email          = $order->get_billing_email();
// 	$phone          = $order->get_billing_phone();
// 	$address_line_1 = $order->get_billing_address_1();
// 	$address_line_2 = $order->get_billing_address_2();
// 	$country        = $order->get_billing_country();
// 	$postal_code    = $order->get_billing_postcode();

// 	$order->update_meta_data( '_rvbps_form_full_name', $full_name );
// 	$order->update_meta_data( '_rvbps_form_email', $email );
// 	$order->update_meta_data( '_rvbps_form_phone', $phone );
// 	$order->update_meta_data( '_rvbps_form_address_1', $address_line_1 );
// 	$order->update_meta_data( '_rvbps_form_address_2', $address_line_2 );
// 	$order->update_meta_data( '_rvbps_form_country', $country );
// 	$order->update_meta_data( '_rvbps_form_postcode', $postal_code );
// 	$order->save_meta_data();

// 	/* Determine / create user if needed */
// 	$user_id = (int) $order->get_user_id();
// 	if ( ! $user_id ) {
// 		$maybe = $email ? get_user_by('email',$email) : false;
// 		if ( $maybe ) {
// 			$user_id = (int) $maybe->ID;
// 		} else {
// 			$base  = $email ? sanitize_user( current( explode('@',$email) ) ) : 'rv_guest';
// 			$uname = $base; $i=1; while ( username_exists($uname) ) { $uname = $base . $i++; }
// 			$pwd   = wp_generate_password(12);
// 			$user_id = wp_create_user( $uname, $pwd, $email );
// 			if ( ! is_wp_error( $user_id ) ) { (new WP_User($user_id))->set_role('subscriber'); }
// 			else { $user_id = 0; }
// 		}
// 	}
// 	if ( $user_id ) {
// 		update_user_meta( $user_id, 'first_name', $billing_first );
// 		update_user_meta( $user_id, 'last_name',  $billing_last );
// 		update_user_meta( $user_id, 'billing_first_name', $billing_first );
// 		update_user_meta( $user_id, 'billing_last_name',  $billing_last );
// 		update_user_meta( $user_id, 'billing_email',      $email );
// 		update_user_meta( $user_id, 'billing_phone',      $phone );
// 		update_user_meta( $user_id, 'billing_address_1',  $address_line_1 );
// 		update_user_meta( $user_id, 'billing_address_2',  $address_line_2 );
// 		update_user_meta( $user_id, 'billing_country',    $country );
// 		update_user_meta( $user_id, 'billing_postcode',   $postal_code );
// 	}

// 	global $wpdb;
// 	$bookings_created = 0;

// 	foreach ( $payload as $item ) {
// 		if ( empty($item['post_id']) || empty($item['check_in']) || empty($item['check_out']) ) {
// 			rvbps_log('Skipping payload item, missing fields: ' . print_r($item,true));
// 			continue;
// 		}
// 		$post_id = (int) $item['post_id'];
// 		if ( ! get_post( $post_id ) ) { rvbps_log("Post $post_id not found"); continue; }

// 		$lot_id = $wpdb->get_var( $wpdb->prepare(
// 			"SELECT id FROM {$wpdb->prefix}rvbs_rv_lots WHERE post_id = %d", $post_id
// 		) );

// 		if ( ! $lot_id ) { rvbps_log("No lot for post_id $post_id"); continue; }

// 		try { $ci = new DateTime( $item['check_in'] ); $co = new DateTime( $item['check_out'] ); }
// 		catch ( \Throwable $e ) { rvbps_log('Bad dates: ' . $e->getMessage()); continue; }
// 		if ( $ci >= $co ) { rvbps_log('check_out not after check_in'); continue; }

// 		$price    = floatval( get_post_meta( $post_id, '_rv_lots_price', true ) );
// 		if ( $price <= 0 ) $price = 20.00;
// 		$nights   = max(1, $ci->diff($co)->days );
// 		$subtotal = $price * $nights;

// 		$ins = $wpdb->insert(
// 			$wpdb->prefix . 'rvbs_bookings',
// 			array(
// 				'lot_id'      => (int) $lot_id,
// 				'post_id'     => (int) $post_id,
// 				'user_id'     => (int) $user_id,
// 				'check_in'    => $ci->format('Y-m-d'),
// 				'check_out'   => $co->format('Y-m-d'),
// 				'total_price' => (float) $subtotal,
// 				'status'      => 'confirmed',
// 				'created_at'  => current_time('mysql'),
// 			),
// 			array( '%d','%d','%d','%s','%s','%f','%s','%s' )
// 		);

// 		if ( false === $ins ) {
// 			rvbps_log('DB insert failed: ' . $wpdb->last_error);
// 			continue;
// 		}
// 		$bookings_created++;

// 		$counts_table = $wpdb->prefix . 'rvbs_booking_counts';
// 		$existing = $wpdb->get_row( $wpdb->prepare(
// 			"SELECT * FROM $counts_table WHERE user_id = %d AND lot_id = %d AND post_id = %d",
// 			(int) $user_id, (int) $lot_id, (int) $post_id
// 		) );

// 		if ( $existing ) {
// 			$wpdb->update(
// 				$counts_table,
// 				array( 'booking_count' => (int) $existing->booking_count + 1, 'last_booked_at' => current_time('mysql') ),
// 				array( 'user_id' => (int) $user_id, 'lot_id' => (int) $lot_id, 'post_id' => (int) $post_id ),
// 				array( '%d','%s' ),
// 				array( '%d','%d','%d' )
// 			);
// 		} else {
// 			$wpdb->insert(
// 				$counts_table,
// 				array(
// 					'user_id'       => (int) $user_id,
// 					'lot_id'        => (int) $lot_id,
// 					'post_id'       => (int) $post_id,
// 					'booking_count' => 1,
// 					'last_booked_at'=> current_time('mysql'),
// 				),
// 				array( '%d','%d','%d','%d','%s' )
// 			);
// 		}
// 	}

// 	$order->update_meta_data( '_rvbps_bookings_created', 1 );
// 	$order->add_order_note( sprintf( 'RV Booking records created: %d', $bookings_created ) );
// 	$order->save();
// 	rvbps_log("Finished creating bookings: $bookings_created");
// }

/* Fire on successful payments + common alternatives */
// add_action( 'woocommerce_payment_complete',           'rvbps_create_bookings_on_payment', 10, 1 );
// add_action( 'woocommerce_order_status_processing',    'rvbps_create_bookings_on_payment', 10, 1 );
// add_action( 'woocommerce_order_status_completed',     'rvbps_create_bookings_on_payment', 10, 1 );
// add_action( 'woocommerce_order_status_on-hold',       'rvbps_create_bookings_on_payment', 10, 1 );
// // transitions that gateways often use
// add_action( 'woocommerce_order_status_pending_to_processing', 'rvbps_create_bookings_on_payment', 10, 1 );
// add_action( 'woocommerce_order_status_pending_to_completed',  'rvbps_create_bookings_on_payment', 10, 1 );
// add_action( 'woocommerce_order_status_failed_to_processing',  'rvbps_create_bookings_on_payment', 10, 1 );

/* --------- Clear booking session on Thank-you (after bookings created) --------- */
// add_action( 'woocommerce_thankyou', function ( $order_id ) {
// 	rvbps_log('thankyou for order #' . $order_id . ' — clearing sessions');
// 	if ( session_id() ) {
// 		unset( $_SESSION['cart'] );
// 		$_SESSION['cart_total_items'] = 0;
// 	}
// 	if ( function_exists('WC') && WC()->session ) {
// 		WC()->session->__unset('rvbps_total');
// 		WC()->session->__unset('rvbps_payload');
// 		WC()->session->__unset('rvbps_meta');
// 	}
// }, 10, 1 );

// /* --------- Thank-you copy tweaks (optional) --------- */
// add_filter( 'woocommerce_thankyou_order_received_text', function ( $text ) {
// 	return esc_html__( 'Thank you for your booking. Your reservation has been received!', 'rv-booking-payment-system' );
// }, 10 );

// /* ---- Force product name on checkout/TY (Blocks safety) ---- */
// add_filter( 'woocommerce_product_get_name', function( $product_name, $product ) {
// 	try {
// 		if ( ! $product || ! method_exists( $product, 'get_id' ) ) return $product_name;
// 		$target_id = (int) get_option( RVBPS_PRODUCT_OPTION, 0 );
// 		if ( ! $target_id || (int) $product->get_id() !== $target_id ) return $product_name;
// 		$is_checkout  = function_exists( 'is_checkout' ) && is_checkout();
// 		$is_thankyou  = function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
// 		if ( $is_checkout || $is_thankyou ) return __( 'RV Booking Payment', 'rv-booking-payment-system' );
// 	} catch ( \Throwable $e ) {}
// 	return $product_name;
// }, 10, 2 );
