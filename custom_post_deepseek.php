<?php
function rvbs_register_rv_lots_post_type()
{
    $labels = array(
        'name'                  => _x('RV Lots', 'Post Type General Name', 'rv-booking-plugin'),
        'singular_name'         => _x('RV Lot', 'Post Type Singular Name', 'rv-booking-plugin'),
        'menu_name'             => __('RV Lots', 'rv-booking-plugin'),
        'name_admin_bar'        => __('RV Lot', 'rv-booking-plugin'),
        'archives'              => __('RV Lot Archives', 'rv-booking-plugin'),
        'attributes'            => __('RV Lot Attributes', 'rv-booking-plugin'),
        'parent_item_colon'     => __('Parent RV Lot:', 'rv-booking-plugin'),
        'all_items'             => __('All RV Lots', 'rv-booking-plugin'),
        'add_new_item'          => __('Add New RV Lot', 'rv-booking-plugin'),
        'add_new'               => __('Add New', 'rv-booking-plugin'),
        'new_item'              => __('New RV Lot', 'rv-booking-plugin'),
        'edit_item'             => __('Edit RV Lot', 'rv-booking-plugin'),
        'update_item'           => __('Update RV Lot', 'rv-booking-plugin'),
        'view_item'             => __('View RV Lot', 'rv-booking-plugin'),
        'view_items'            => __('View RV Lots', 'rv-booking-plugin'),
        'search_items'          => __('Search RV Lot', 'rv-booking-plugin'),
        'not_found'             => __('Not found', 'rv-booking-plugin'),
        'not_found_in_trash'    => __('Not found in Trash', 'rv-booking-plugin'),
        'featured_image'        => __('Featured Image', 'rv-booking-plugin'),
        'set_featured_image'    => __('Set featured image', 'rv-booking-plugin'),
        'remove_featured_image' => __('Remove featured image', 'rv-booking-plugin'),
        'use_featured_image'    => __('Use as featured image', 'rv-booking-plugin'),
        'insert_into_item'      => __('Insert into RV Lot', 'rv-booking-plugin'),
        'uploaded_to_this_item' => __('Uploaded to this RV Lot', 'rv-booking-plugin'),
        'items_list'            => __('RV Lots list', 'rv-booking-plugin'),
        'items_list_navigation' => __('RV Lots list navigation', 'rv-booking-plugin'),
        'filter_items_list'     => __('Filter RV Lots list', 'rv-booking-plugin'),
    );
    $args = array(
        'label'                 => __('RV Lot', 'rv-booking-plugin'),
        'description'           => __('Post Type for RV Lots', 'rv-booking-plugin'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'), // Ensure 'editor' is included
        'taxonomies'            => array('category', 'post_tag'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Enable Gutenberg/block editor support
    );
    register_post_type('rv-lots', $args);
}
add_action('init', 'rvbs_register_rv_lots_post_type', 0);

function rvbs_register_custom_taxonomies()
{
    // Site Types
    $site_types_labels = array(
        'name'              => _x('Site Types', 'taxonomy general name', 'rv-booking-plugin'),
        'singular_name'     => _x('Site Type', 'taxonomy singular name', 'rv-booking-plugin'),
        'search_items'      => __('Search Site Types', 'rv-booking-plugin'),
        'all_items'         => __('All Site Types', 'rv-booking-plugin'),
        'parent_item'       => __('Parent Site Type', 'rv-booking-plugin'),
        'parent_item_colon' => __('Parent Site Type:', 'rv-booking-plugin'),
        'edit_item'         => __('Edit Site Type', 'rv-booking-plugin'),
        'update_item'       => __('Update Site Type', 'rv-booking-plugin'),
        'add_new_item'      => __('Add New Site Type', 'rv-booking-plugin'),
        'new_item_name'     => __('New Site Type Name', 'rv-booking-plugin'),
        'menu_name'         => __('Site Types', 'rv-booking-plugin'),
    );
    $site_types_args = array(
        'hierarchical'      => true,
        'labels'            => $site_types_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'site-type'),
        'show_in_rest'      => true,
    );
    register_taxonomy('site_type', array('rv-lots'), $site_types_args);

    // Park Features
    $park_features_labels = array(
        'name'              => _x('Park Features', 'taxonomy general name', 'rv-booking-plugin'),
        'singular_name'     => _x('Park Feature', 'taxonomy singular name', 'rv-booking-plugin'),
        'search_items'      => __('Search Park Features', 'rv-booking-plugin'),
        'all_items'         => __('All Park Features', 'rv-booking-plugin'),
        'parent_item'       => __('Parent Park Feature', 'rv-booking-plugin'),
        'parent_item_colon' => __('Parent Park Feature:', 'rv-booking-plugin'),
        'edit_item'         => __('Edit Park Feature', 'rv-booking-plugin'),
        'update_item'       => __('Update Park Feature', 'rv-booking-plugin'),
        'add_new_item'      => __('Add New Park Feature', 'rv-booking-plugin'),
        'new_item_name'     => __('New Park Feature Name', 'rv-booking-plugin'),
        'menu_name'         => __('Park Features', 'rv-booking-plugin'),
    );
    $park_features_args = array(
        'hierarchical'      => true,
        'labels'            => $park_features_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'park-feature'),
        'show_in_rest'      => true,
    );
    register_taxonomy('park_feature', array('rv-lots'), $park_features_args);

    // Site Amenities
    $site_amenities_labels = array(
        'name'              => _x('Site Amenities', 'taxonomy general name', 'rv-booking-plugin'),
        'singular_name'     => _x('Site Amenity', 'taxonomy singular name', 'rv-booking-plugin'),
        'search_items'      => __('Search Site Amenities', 'rv-booking-plugin'),
        'all_items'         => __('All Site Amenities', 'rv-booking-plugin'),
        'parent_item'       => __('Parent Site Amenity', 'rv-booking-plugin'),
        'parent_item_colon' => __('Parent Site Amenity:', 'rv-booking-plugin'),
        'edit_item'         => __('Edit Site Amenity', 'rv-booking-plugin'),
        'update_item'       => __('Update Site Amenity', 'rv-booking-plugin'),
        'add_new_item'      => __('Add New Site Amenity', 'rv-booking-plugin'),
        'new_item_name'     => __('New Site Amenity Name', 'rv-booking-plugin'),
        'menu_name'         => __('Site Amenities', 'rv-booking-plugin'),
    );
    $site_amenities_args = array(
        'hierarchical'      => true,
        'labels'            => $site_amenities_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'site-amenity'),
        'show_in_rest'      => true,
    );
    register_taxonomy('site_amenity', array('rv-lots'), $site_amenities_args);
}
add_action('init', 'rvbs_register_custom_taxonomies', 0);




function rvbs_add_rv_lots_meta_boxes()
{
    add_meta_box(
        'rv_lots_price',
        __('Price Details', 'rv-booking-plugin'),
        'rv_lots_price_callback',
        'rv-lots',
        'normal',
        'high'
    );

    add_meta_box(
        'rv_lots_images',
        __('Additional Images', 'rv-booking-plugin'),
        'rv_lots_images_callback',
        'rv-lots',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rvbs_add_rv_lots_meta_boxes');

function rv_lots_price_callback($post)
{
    wp_nonce_field('rv_lots_price_nonce', 'rv_lots_price_nonce');
    $price = get_post_meta($post->ID, '_rv_lots_price', true);
    echo '<label for="rv_lots_price">' . __('Price', 'rv-booking-plugin') . '</label>';
    echo '<input type="text" id="rv_lots_price" name="rv_lots_price" value="' . esc_attr($price) . '" size="25" />';
}

function rv_lots_images_callback($post)
{
    wp_nonce_field('rv_lots_images_nonce', 'rv_lots_images_nonce');
    $images = get_post_meta($post->ID, '_rv_lots_images', true);
    echo '<label for="rv_lots_images">' . __('Additional Images', 'rv-booking-plugin') . '</label>';
    echo '<input type="text" id="rv_lots_images" name="rv_lots_images" value="' . esc_attr($images) . '" size="25" />';
    echo '<p class="description">' . __('Enter image URLs separated by commas.', 'rv-booking-plugin') . '</p>';
}

function rvbs_save_rv_lots_meta($post_id)
{
    if (!isset($_POST['rv_lots_price_nonce']) || !wp_verify_nonce($_POST['rv_lots_price_nonce'], 'rv_lots_price_nonce')) {
        return;
    }

    if (!isset($_POST['rv_lots_images_nonce']) || !wp_verify_nonce($_POST['rv_lots_images_nonce'], 'rv_lots_images_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['rv_lots_price'])) {
        update_post_meta($post_id, '_rv_lots_price', sanitize_text_field($_POST['rv_lots_price']));
    }

    if (isset($_POST['rv_lots_images'])) {
        update_post_meta($post_id, '_rv_lots_images', sanitize_text_field($_POST['rv_lots_images']));
    }
}
add_action('save_post', 'rvbs_save_rv_lots_meta');

function rvbs_enqueue_admin_scripts($hook)
{
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('rvbs-admin-script', RVBS_BOOKING_PLUGIN_URL . 'js/admin.js', array('jquery'), PLUGIN_VER, true);
}
add_action('admin_enqueue_scripts', 'rvbs_enqueue_admin_scripts');
