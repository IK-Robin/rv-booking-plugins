<?php

function sync_wc_product_on_custom_post_save($post_id, $post, $update) {
    // Only for custom_order post type
    if ($post->post_type !== 'custom_order' || $update) return;

    // Get the order total from post meta
    $order_total = get_post_meta($post_id, 'order_total', true);

    // Create WooCommerce product
    $product = new WC_Product_Simple();
    $product->set_name($post->post_title);
    $product->set_regular_price($order_total);
    $product->save();

    // Save WooCommerce Product ID to custom post meta
    update_post_meta($post_id, 'wc_product_id', $product->get_id());
}
add_action('wp_insert_post', 'sync_wc_product_on_custom_post_save', 10, 3);

function redirect_to_checkout($custom_order_id) {
    $wc_product_id = get_post_meta($custom_order_id, 'wc_product_id', true);
    if ($wc_product_id) {
        $url = wc_get_checkout_url() . "?add-to-cart=$wc_product_id";
        wp_redirect($url);
        exit;
    }
}

// Optional: Add a "Purchase" button in the custom post type view
function add_purchase_button_to_custom_order($content) {
    if (get_post_type() === 'custom_order') {
        $custom_order_id = get_the_ID();
        $button = '<a href="' . esc_url(admin_url("admin-post.php?action=purchase_custom_order&id=$custom_order_id")) . '" class="button">Purchase</a>';
        return $content . $button;
    }
    return $content;
}
add_filter('the_content', 'add_purchase_button_to_custom_order');
