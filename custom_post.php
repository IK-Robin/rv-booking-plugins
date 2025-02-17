<?php

function create_rv_lots_post_type() {
    $labels = array(
        'name' => 'RV Lots',
        'singular_name' => 'RV Lot',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New RV Lot',
        'edit_item' => 'Edit RV Lot',
        'new_item' => 'New RV Lot',
        'view_item' => 'View RV Lot',
        'search_items' => 'Search RV Lots',
        'not_found' => 'No RV Lots found',
        'not_found_in_trash' => 'No RV Lots found in Trash',
        'menu_name' => 'RV Lots'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies' => array('category'), // default category support
        'show_in_rest' => true, // Enables Gutenberg editor
    );

    register_post_type('rv-lots', $args);
}

add_action('init', 'create_rv_lots_post_type');



function create_rv_lot_taxonomies() {
    // Site Types taxonomy
    $site_types = array(
        'name' => 'Site Types',
        'singular_name' => 'Site Type',
        'search_items' => 'Search Site Types',
        'all_items' => 'All Site Types',
        'edit_item' => 'Edit Site Type',
        'update_item' => 'Update Site Type',
        'add_new_item' => 'Add New Site Type',
        'new_item_name' => 'New Site Type Name',
        'menu_name' => 'Site Types'
    );
    register_taxonomy('site_type', 'rv-lots', array(
        'hierarchical' => true,
        'labels' => $site_types,
        'show_ui' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'site-type')
    ));

    // Park Features taxonomy
    $park_features = array(
        'name' => 'Park Features',
        'singular_name' => 'Park Feature',
        'search_items' => 'Search Park Features',
        'all_items' => 'All Park Features',
        'edit_item' => 'Edit Park Feature',
        'update_item' => 'Update Park Feature',
        'add_new_item' => 'Add New Park Feature',
        'new_item_name' => 'New Park Feature Name',
        'menu_name' => 'Park Features'
    );
    register_taxonomy('park_feature', 'rv-lots', array(
        'hierarchical' => false,
        'labels' => $park_features,
        'show_ui' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'park-feature')
    ));
}

add_action('init', 'create_rv_lot_taxonomies');

// add rv loats price metabox 


function add_rv_lot_meta_boxes() {
    add_meta_box(
        'rv_lot_price_details',
        'Price Details',
        'rv_lot_price_details_callback',
        'rv-lots',
        'normal',
        'high'
    );
}

function rv_lot_price_details_callback($post) {
    $price = get_post_meta($post->ID, '_rv_lot_price', true);
    ?>
    <label for="rv_lot_price">Price per Night</label>
    <input type="text" name="rv_lot_price" value="<?php echo esc_attr($price); ?>" class="widefat" />
    <?php
}

function save_rv_lot_price_details($post_id) {
    if (isset($_POST['rv_lot_price'])) {
        update_post_meta($post_id, '_rv_lot_price', sanitize_text_field($_POST['rv_lot_price']));
    }
}

add_action('add_meta_boxes', 'add_rv_lot_meta_boxes');
add_action('save_post', 'save_rv_lot_price_details');



// add rv lots image galary 
function add_rv_lot_images_meta_box() {
    add_meta_box(
        'rv_lot_images',
        'Images Gallery',
        'rv_lot_images_callback',
        'rv-lots',
        'normal',
        'high'
    );
}

function rv_lot_images_callback($post) {
    $images = get_post_meta($post->ID, '_rv_lot_images', true);
    ?>
    <input type="button" class="button" value="Add Images" id="upload_images_button" />
    <div id="image_preview">
        <?php
        if ($images) {
            foreach ($images as $image_id) {
                echo wp_get_attachment_image($image_id, 'thumbnail');
            }
        }
        ?>
    </div>
    <input type="hidden" name="rv_lot_images" id="rv_lot_images" value="<?php echo esc_attr(implode(',', (array) $images)); ?>" />
    <script>
        jQuery(document).ready(function($){
            $('#upload_images_button').on('click', function(e){
                e.preventDefault();
                var image_frame = wp.media({
                    title: 'Select Images',
                    button: { text: 'Use selected images' },
                    multiple: true
                });

                image_frame.on('select', function() {
                    var selection = image_frame.state().get('selection');
                    var image_ids = [];
                    selection.each(function(attachment) {
                        image_ids.push(attachment.id);
                    });
                    $('#rv_lot_images').val(image_ids.join(','));
                    $('#image_preview').html('');
                    image_ids.forEach(function(id) {
                        $('#image_preview').append('<img src="' + wp.media.attachment(id).url + '" width="100" />');
                    });
                });

                image_frame.open();
            });
        });
    </script>
    <?php
}

function save_rv_lot_images($post_id) {
    if (isset($_POST['rv_lot_images'])) {
        update_post_meta($post_id, '_rv_lot_images', explode(',', $_POST['rv_lot_images']));
    }
}

add_action('add_meta_boxes', 'add_rv_lot_images_meta_box');
add_action('save_post', 'save_rv_lot_images');



?>