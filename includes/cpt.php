<?php

function custom_order_cpt_init() {
    register_post_type('custom_order', array(
        'labels' => array(
            'name' => __('Custom Orders', 'custom-order-sync'),
            'singular_name' => __('Custom Order', 'custom-order-sync'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'show_in_menu' => true,
    ));
}
add_action('init', 'custom_order_cpt_init');
