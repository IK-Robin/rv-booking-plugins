<?php

function rvbs_add_all_script()
{



    // add the jquery support 
    wp_enqueue_script('jquery');
    // Add Bootstrap JS with dependencies
    // 
    wp_enqueue_script('rvbs-bootstrap-js', plugin_dir_url(__FILE__) . '../assets/js/rvbs-bootstrap.min.js', array('jquery'), RVBS_PLUGIN_VER, true);

    if (is_page('search-rv')) {
        wp_enqueue_script('rvbs-search-rv', plugin_dir_url(__FILE__) . '../assets/js/rvbs-search-rv.js', array('jquery'), RVBS_PLUGIN_VER, true);
        // enqueue_flatpickr() js 
        // wp_enqueue_script('flatpickr', plugin_dir_url(__FILE__) . '../assets/js/rvbs-flatpicke.min.js', array('jquery'), RVBS_PLUGIN_VER, true);
        wp_localize_script('rvbs-search-rv', 'rvbs_serch_rv', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'action' => 'load_more_posts',
            'filter_rv_lots' => 'filter_rv_lots',
            
            'nonce' => wp_create_nonce('load_more_posts_nonce'),
        ]);
    }




    // get the toplavel page and enqueue the script for only the book now page 
    //add the add to chart js

    if (is_page('shopping-cart') ||is_page('booknow') || is_page('checkout')) {
        wp_enqueue_script('rvbs-add-to-cart-js', plugin_dir_url(__FILE__) . '../assets/js/custom-add-to-cart-ajax.js', array('jquery'), RVBS_PLUGIN_VER, true);
        //add the add to chart js


        // localize the script 
        wp_localize_script('rvbs-add-to-cart-js', 'rvbs_add_to_cart', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rvbs_add_to_cart_nonce'),
            'add_to_cart' => 'add_to_cart',
            'remove_from_cart' => 'remove_from_cart',
        ));
    }



    // add the checkout js 


    wp_enqueue_script('rvbs-checkout-js', plugin_dir_url(__FILE__) . '../assets/js/rvbs-checkout.js', array('jquery'), RVBS_PLUGIN_VER, true);
    wp_localize_script('jquery', 'rvbs_checkout', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rvbs_checkout_nonce')
    ));

        // add booking checkout page js for testin woo checkout
        wp_enqueue_script('rvbs-woo-checkout-page',plugin_dir_url(__FILE__) . '../assets/js/rvbs-booking-checkout.js',['jquery'],RVBS_PLUGIN_VER,true);

    //add the book now js


    if (is_page('booknow')) {
        wp_enqueue_script('rvbs-book-now-script', plugin_dir_url(__FILE__) . '../assets/js/rvbs-book-now.js', array('jquery'), '1.0', true);
    
        $post_id = isset($_GET['campsite']) ? intval($_GET['campsite']) : 0;
        $check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
        $check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
        $edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';
    
        if (!session_id()) {
            session_start();
        }
    
        $session_data = [];
        $use_session_data = false;
    
        if ((isset($_GET['campsite']) || $edit_mode) &&
            isset($_SESSION['cart']) &&
            is_array($_SESSION['cart']) &&
            isset($_SESSION['cart'][$post_id])
        ) {
            $session_data = $_SESSION['cart'][$post_id];
            $use_session_data = true;
        }
    
        $adults = $use_session_data && isset($session_data['adults']) ? intval($session_data['adults']) : (isset($_GET['adults']) ? intval($_GET['adults']) : 1);
        $children = $use_session_data && isset($session_data['children']) ? intval($session_data['children']) : (isset($_GET['children']) ? intval($_GET['children']) : 0);
        $pets = $use_session_data && isset($session_data['pets']) ? intval($session_data['pets']) : (isset($_GET['pets']) ? intval($_GET['pets']) : 0);
        $check_in = $use_session_data && isset($session_data['check_in']) ? sanitize_text_field($session_data['check_in']) : $check_in;
        $check_out = $use_session_data && isset($session_data['check_out']) ? sanitize_text_field($session_data['check_out']) : $check_out;
    
        $price = $post_id ? floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 20.00 : 20.00;
    
        wp_localize_script('rvbs-book-now-script', 'bookingData', array(
            'editMode' => $edit_mode,
            'useSessionData' => $use_session_data,
            'nightlyRate' => (float) $price,
            'campgroundFees' => 5.00,
            'campsiteId' => $post_id,
            'initialCheckIn' => $check_in,
            'initialCheckOut' => $check_out,
            'adults' => $adults,
            'children' => $children,
         
            'pets' => $pets,
            'checkAvailabilityAction' => 'check_avablity_book_now_page', // this action connected in the function.php file
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rvbs_booking_nonce'),
        ));
    }




}
add_action('wp_enqueue_scripts', 'rvbs_add_all_script');



function enqueue_flatpickr()
{
    wp_enqueue_style('flatpickr-css', 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css');
    wp_enqueue_script('flatpickr-js', 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_flatpickr');


//
