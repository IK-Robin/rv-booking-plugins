<?php

/* Template Name: Shopping Cart */

// Check if the theme is block-based (Full Site Editing)
$is_fse_theme = wp_is_block_theme();

// Start session
if (!session_id()) {
    session_start();
}



$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_total = 0;
?>


<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    wp_head(); // Ensures styles & scripts are loaded

    // Load global styles for block themes (necessary for FSE)
    if ($is_fse_theme) {
        $global_styles = file_get_contents(get_template_directory() . '/style.css');
        echo '<style>' . $global_styles . '</style>';
    }
    ?>
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
</head>

<body <?php body_class(); ?>>

    <?php
    // Load the correct header
    if (!$is_fse_theme) {
        get_header(); // Classic Theme
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->'); // Block Theme Header
    }
    ?>

<style>
    .container {
        max-width: 900px;
        margin: 40px auto;
        font-family: Arial, sans-serif;
    }
    h1 { 
        font-size: 28px;
        font-weight: bold;
    }
    .cart-wrapper {
        display: flex;
        gap: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .cart-items {
        flex: 2;
    }
    .cart-item {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #eee;
        padding: 15px 0;
    }
    .cart-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        background: #f0f0f0;
        margin-right: 15px;
    }
    .cart-item-details {
        flex: 1;
    }
    .cart-item h3 {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .cart-item p {
        font-size: 14px;
        color: #666;
        margin: 3px 0;
    }
    .remove-item {
        color: red;
        text-decoration: none;
        font-size: 14px;
        margin-left: 10px;
    }
    .cart-summary {
        flex: 1;
        border-left: 1px solid #eee;
        padding-left: 20px;
    }
    .cart-summary h2 {
        font-size: 20px;
        font-weight: bold;
    }
    .cart-summary p {
        font-size: 16px;
        font-weight: bold;
    }
    .checkout-btn {
        display: block;
        width: 100%;
        background: #28a745;
        color: white;
        font-size: 16px;
        padding: 10px;
        border: none;
        border-radius: 5px;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
    }
    .checkout-btn:hover {
        background: #218838;
    }
</style>

<div class="container">
    <h1>Shopping Cart</h1>
    <p><?php echo count($cart); ?> Sites</p>
    
    <?php if (!empty($cart)) : ?>
        <div class="cart-wrapper">
            <div class="cart-items">
                <?php 
                $cart_total = 0; // Initialize cart total before looping through items
                foreach ($cart as $item) : 
                    $post_id = $item['post_id'];
                    $price = floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 20.00;
                    $check_in = new DateTime($item['check_in']);
                    $check_out = new DateTime($item['check_out']);
                    $nights = $check_in->diff($check_out)->days ?: 1;
                    $subtotal = $price * $nights;
                    $cart_total += $subtotal;
                ?>
                <div class="cart-item">
                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'thumbnail') ?: 'https://via.placeholder.com/80'); ?>" alt="No Image">
                    <div class="cart-item-details">
                        <h3><?php echo esc_html($item['room_title']); ?></h3>
                        <p><?php echo esc_html($item['check_in'] . ' - ' . $item['check_out']); ?></p>
                        <p>Guests: <?php echo esc_html($item['adults'] . ' Adults, ' . $item['children'] . ' Children'); ?></p>
                        <p>Price: $<?php echo number_format($subtotal, 2); ?></p>
                        <a href="<?php echo esc_url( home_url( '/booknow' ) . '?campsite=' . urlencode( $post_id ). '&edit=true' . '&check_in=' . urlencode( $check_in->format('Y-m-d') ) . '&check_out=' . urlencode( $check_out->format('Y-m-d') ) ); ?>" class="edit-item text-decoration-none" data-id="<?php echo esc_attr( $post_id ); ?>">Edit</a>
                        <a href="#" class="remove-item" data-id="<?php echo esc_attr($post_id); ?>">Remove</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="cart-summary">
                <h2>Summary</h2>
                <p>Subtotal: $<?php echo number_format($cart_total, 2); ?></p>
                <button class="checkout-btn">Proceed to Checkout</button>
            </div>
        </div>
    <?php else : ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
