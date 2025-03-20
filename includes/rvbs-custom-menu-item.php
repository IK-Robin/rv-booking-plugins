<?php 

/**
 * rv_booking_system plugin
 *
 * @package rv_booking_system plugin
 * 
 * @link functionn.php
 * @see includes/rvbs-custom-menu-item.php
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Flag to prevent duplicate additions (scoped per page load)
 */
$custom_menu_items_added = false;

/**
 * Register filters based on theme type
 */
function custom_register_menu_filters() {
    if (wp_is_block_theme()) {
        // Block theme: Use render_block filter
        // add_filter('render_block', 'custom_add_loginout_cart_link_block', 10, 2);
    } else {
        // Classic theme: Use wp_nav_menu_objects filter
        add_filter('wp_nav_menu_objects', 'custom_add_loginout_cart_link_classic', 10, 2);
    }
}
add_action('after_setup_theme', 'custom_register_menu_filters');

/**
 * Add Login/Logout and Custom Cart menu items for classic themes
 */
function custom_add_loginout_cart_link_classic($items, $args) {
    global $custom_menu_items_added;

    // Prevent duplicate additions
    if ($custom_menu_items_added) {
        return $items;
    }

    // Get all registered menu locations
    $menu_locations = get_nav_menu_locations();

    // Try to determine the primary menu location
    $primary_location = '';

    // Common primary menu location names
    $possible_locations = array(
        'primary',
        'main',
        'header',
        'top',
        'primary-menu',
        'main-menu',
        'header-menu'
    );

    // Check if the current menu matches any registered location
    foreach ($menu_locations as $location => $menu_id) {
        if (isset($args->menu) && $args->menu == $menu_id) {
            $primary_location = $location;
            break;
        }
    }

    // If no exact match, check against common primary menu locations
    if (empty($primary_location) && isset($args->theme_location)) {
        if (in_array($args->theme_location, $possible_locations)) {
            $primary_location = $args->theme_location;
        }
    }

    // If we found a primary menu or if theme_location is set and items not yet added
    if ((!empty($primary_location) || !empty($args->theme_location)) && !$custom_menu_items_added) {
        // 1. Add Login/Logout Menu Item
        $loginout_item = (object) array(
            'title'            => is_user_logged_in() ? __('Log Out') : __('Log In'),
            'url'              => is_user_logged_in() ? wp_logout_url(home_url()) : wp_login_url(home_url()),
            'menu_order'       => 9998,
            'ID'               => 'custom-login-logout-' . rand(1000, 9999),
            'object_id'        => 'custom-login-logout',
            'post_type'        => 'nav_menu_item',
            'object'           => 'custom',
            'type'             => 'custom',
            'type_label'       => 'Custom Link',
            'db_id'            => 0,
            'classes'          => array('custom-loginout-link'),
            'menu_item_parent' => 0,
            'target'           => '',
            'attr_title'       => '',
            'description'      => '',
            'xfn'              => '',
            'status'           => '',
            'current'          => false,
            'current_item_ancestor' => false,
            'current_item_parent' => false,
        );

        $items[] = $loginout_item;

        // 2. Add Custom Cart Menu Item
        $cart_count = get_custom_cart_count();
        $cart_total = get_custom_cart_total();
        $cart_url = home_url('/cart/'); // Replace with your cart page URL

        $cart_item = (object) array(
            'title'            => '$' . number_format($cart_total, 2) . ' <span class="cart-icon-wrap"><span class="cart-count">' . $cart_count . '</span></span>',
            'url'              => $cart_url,
            'menu_order'       => 9999,
            'ID'               => 'custom-cart-' . rand(1000, 9999),
            'object_id'        => 'custom-cart',
            'post_type'        => 'nav_menu_item',
            'object'           => 'custom',
            'type'             => 'custom',
            'type_label'       => 'Custom Link',
            'db_id'            => 0,
            'classes'          => array('custom-cart-link'),
            'menu_item_parent' => 0,
            'target'           => '',
            'attr_title'       => 'View Cart',
            'description'      => '',
            'xfn'              => '',
            'status'           => '',
            'current'          => false,
            'current_item_ancestor' => false,
            'current_item_parent' => false,
        );

        $items[] = $cart_item;

        $custom_menu_items_added = true; // Set flag to prevent further additions
    }

    return $items;
}

/**
 * Add Login/Logout and Custom Cart menu items for block themes

 * Add Login/Logout and Custom Cart menu items for block themes
 */

/**
 * Add Login/Logout and Custom Cart menu items for block themes (at the end of menu)
 */
add_filter('render_block_core/navigation', 'custom_add_loginout_cart_link_block', 10, 2);
function custom_add_loginout_cart_link_block($block_content, $block) {
    static $custom_menu_items_added = false; // Prevent multiple additions

    if ($custom_menu_items_added) {
        return $block_content;
    }

    // Ensure the block has a navigation list before modifying
    if (strpos($block_content, '<ul') !== false) {
        // Login/Logout Link
        $loginout_html = sprintf(
            '<li class="wp-block-navigation-item custom-loginout-link"><a href="%s">%s</a></li>',
            esc_url(is_user_logged_in() ? wp_logout_url(home_url()) : wp_login_url(home_url())),
            esc_html(is_user_logged_in() ? __('Log Out') : __('Log In'))
        );

        // Cart Link
        $cart_count = get_custom_cart_count();
        $cart_total = get_custom_cart_total();
        $cart_total = number_format((float) $cart_total, 2, '.', '');
        $cart_url = esc_url(home_url('/shopping-cart/')); // Replace with your cart page URL

        // Check if the theme is block-based
        if (wp_is_block_theme()) {
            // Block theme: Don't add $ sign in PHP, use JS instead
            $cart_html = sprintf(
                '<li class="wp-block-navigation-item custom-cart-link block-cart"><a href="%s"><span class="cart-total">%s</span> <span class="cart-icon-wrap"><span class="cart-count">%s</span></span></a></li>',
                $cart_url,
                esc_html($cart_total), // No $ sign in PHP
                esc_html($cart_count)
            );
        } else {
            // Classic theme: Add the $ sign directly in PHP
            $cart_html = sprintf(
                '<li class="wp-block-navigation-item custom-cart-link classic-cart"><a href="%s">$%s <span class="cart-icon-wrap"><span class="cart-count">%s</span></span></a></li>',
                $cart_url,
                esc_html($cart_total), // Add $ sign here
                esc_html($cart_count)
            );
        }

        // Append items BEFORE the closing </ul> tag to place at the end
        $block_content = preg_replace('/(<\/ul>)/', $loginout_html . $cart_html . '$1', $block_content, 1);

        $custom_menu_items_added = true; // Prevent duplication
    }

    return $block_content;
}




/**
 * Custom function to get cart count (modify as needed)
 */
function get_custom_cart_count() {
    if (!session_id()) {
        session_start();
    }
    $cart_count = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    return $cart_count;
}


/**
 * Custom function to get cart total (modify as needed)
 */
function get_custom_cart_total() {
    if (!session_id()) {
        session_start();
    }
    $cart_total = 0.0;

    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $post_id = $item['post_id'];
            $price = floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 20.00; // Default to 20 if not set
            $nights = 1; // Default to 1 night; adjust if you have check_in/check_out logic
            if (isset($item['check_in']) && isset($item['check_out'])) {
                $check_in = new DateTime($item['check_in']);
                $check_out = new DateTime($item['check_out']);
                $nights = $check_in->diff($check_out)->days ?: 1;
            }
  $cart_total += $price * $nights;
           
        }
    }

    return floatval($cart_total); // Return as float, formatting will be handled where used
}



/**
 * Add styling for both login/logout and cart
 */
add_action('wp_head', 'custom_loginout_cart_styles');
function custom_loginout_cart_styles() {
    ?>
    <style>
        /* Login/Logout Link */
        .custom-loginout-link {
            margin-left: 10px;
        }
        .custom-loginout-link a {
            text-decoration: none;
        }

        /* Cart Link */
        .custom-cart-link {
            position: relative;
            margin-left: 15px;
            display: inline-flex;
            align-items: center;
        }
        .custom-cart-link a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .custom-cart-link .cart-icon-wrap {
            position: relative;
            margin-left: 5px;
        }
        .custom-cart-link .cart-icon-wrap::before {
            content: "\f07a"; /* Font Awesome cart icon */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: 18px;
            margin-right: 5px;
        }
        .custom-cart-link .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #00cc00; /* Green badge color */
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        .custom-cart-link .cart-count:empty {
            display: none; /* Hide badge if cart is empty */
        }
    </style>
    <?php
}

/**
 * Load Font Awesome for cart icon
 */
add_action('wp_enqueue_scripts', 'custom_load_cart_icon_fonts');
function custom_load_cart_icon_fonts() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}

/**
 * Debug information (remove in production)
 */
add_action('wp_footer', 'custom_loginout_debug_info');
function custom_loginout_debug_info() {
    if (current_user_can('administrator')) {
        $locations = get_nav_menu_locations();
        $registered = get_registered_nav_menus();
        echo '<pre style="display: none;">';
        echo "Registered Menu Locations:\n";
        print_r($registered);
        echo "Assigned Menu Locations:\n";
        print_r($locations);
        echo '</pre>';
    }
}