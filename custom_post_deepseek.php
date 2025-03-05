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


    // Equipment Types Allowed
    $equipment_types_labels = array(
        'name'              => _x('Equipment Types Allowed', 'taxonomy general name', 'rv-booking-plugin'),
        'singular_name'     => _x('Equipment Type Allowed', 'taxonomy singular name', 'rv-booking-plugin'),
        'search_items'      => __('Search Equipment Types Allowed', 'rv-booking-plugin'),
        'all_items'         => __('All Equipment Types Allowed', 'rv-booking-plugin'),
        'parent_item'       => __('Parent Equipment Type', 'rv-booking-plugin'),
        'parent_item_colon' => __('Parent Equipment Type:', 'rv-booking-plugin'),
        'edit_item'         => __('Edit Equipment Type', 'rv-booking-plugin'),
        'update_item'       => __('Update Equipment Type', 'rv-booking-plugin'),
        'add_new_item'      => __('Add New Equipment Type', 'rv-booking-plugin'),
        'new_item_name'     => __('New Equipment Type Name', 'rv-booking-plugin'),
        'menu_name'         => __('Equipment Types Allowed', 'rv-booking-plugin'),
    );
    $equipment_types_args = array(
        'hierarchical'      => true,
        'labels'            => $equipment_types_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'equipment-type'),
        'show_in_rest'      => true,
    );
    register_taxonomy('equipment_type', array('rv-lots'), $equipment_types_args);

    // Add default equipment types to taxonomy
    if (!term_exists('Fifth-Wheel', 'equipment_type')) {
        wp_insert_term('Fifth-Wheel', 'equipment_type');
    }
    if (!term_exists('Motorhome', 'equipment_type')) {
        wp_insert_term('Motorhome', 'equipment_type');
    }
    if (!term_exists('Pop-Up', 'equipment_type')) {
        wp_insert_term('Pop-Up', 'equipment_type');
    }
    if (!term_exists('Travel Trailer', 'equipment_type')) {
        wp_insert_term('Travel Trailer', 'equipment_type');
    }
    if (!term_exists('Truck Camper', 'equipment_type')) {
        wp_insert_term('Truck Camper', 'equipment_type');
    }
    if (!term_exists('Van', 'equipment_type')) {
        wp_insert_term('Van', 'equipment_type');
    }

}
add_action('init', 'rvbs_register_custom_taxonomies', 0);

// Add all meta boxes
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
        'rv-lots_guest',
        __('Guest Details', 'rv-booking-plugin'),
        'rv_lots_guest_callback',
        'rv-lots',
        'normal',
        'high'
    );

    // Add Images Gallery meta box
    // add_meta_box(
    //     'rv_lot_images',
    //     __('Images Gallery', 'rv-booking-plugin'),
    //     'rv_lot_images_callback',
    //     'rv-lots',
    //     'normal',
    //     'high'
    // );
}
add_action('add_meta_boxes', 'rvbs_add_rv_lots_meta_boxes');

// Callback for Images Gallery meta box
// Callback for Images Gallery meta box







// Save meta box data for Price Details, Additional Images, and Guest Details.
function rvbs_save_rv_lots_meta($post_id)
{
    // Verify nonces.
    if (
        !isset($_POST['rv_lots_price_nonce']) || !wp_verify_nonce($_POST['rv_lots_price_nonce'], 'rv_lots_price_nonce') ||
        !isset($_POST['rv_lots_guest_nonce']) || !wp_verify_nonce($_POST['rv_lots_guest_nonce'], 'rv_lots_guest_nonce')
    ) {
        return;
    }

    // Check autosave.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save Price Details.
    if (isset($_POST['rv_lots_price'])) {
        update_post_meta($post_id, '_rv_lots_price', sanitize_text_field($_POST['rv_lots_price']));
    }

    // Save Guest Details.
    if (isset($_POST['rv_lots_guest'])) {
        update_post_meta($post_id, '_rv_lots_guest', sanitize_textarea_field($_POST['rv_lots_guest']));
    }

    // Save Images Gallery.
    // if (isset($_POST['rv_lot_images'])) {
    //     $images = array_filter(array_map('trim', explode(',', sanitize_text_field($_POST['rv_lot_images']))));
    //     update_post_meta($post_id, '_rv_lot_images', $images);
    // }
}
add_action('save_post', 'rvbs_save_rv_lots_meta');


// Callback for Price Details meta box
function rv_lots_price_callback($post)
{
    wp_nonce_field('rv_lots_price_nonce', 'rv_lots_price_nonce');
    $price = get_post_meta($post->ID, '_rv_lots_price', true);
    echo '<label for="rv_lots_price">' . __('Price', 'rv-booking-plugin') . '</label>';
    echo '<input type="text" id="rv_lots_price" name="rv_lots_price" value="' . esc_attr($price) . '" size="25" />';
}


// Callback for Guest Details meta box
function rv_lots_guest_callback($post)
{
    wp_nonce_field('rv_lots_guest_nonce', 'rv_lots_guest_nonce');
    $guest_details = get_post_meta($post->ID, '_rv_lots_guest', true);
    echo '<label for="rv_lots_guest">' . __('Guest Details', 'rv-booking-plugin') . '</label>';
    echo '<input id="rv_lots_guest" name="rv_lots_guest" type="number" value="' . esc_attr($guest_details) . '" />';
    echo '<p class="description">' . __('Enter guest-related details such as maximum occupancy, rules, etc.', 'rv-booking-plugin') . '</p>';
}




// add metabox to save the image gallery 

// Add Meta Box
function rvbs_add_rv_lots_meta_box() {
    add_meta_box(
        'rv_lot_images_meta',
        __('Select Images', 'rv-booking-plugin'),
        'rv_lot_images_meta_callback',
        'rv-lots',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rvbs_add_rv_lots_meta_box');

// Meta Box Callback Function
function rv_lot_images_meta_callback($post) {
    $images = get_post_meta($post->ID, '_rv_lot_images', true);
    $images = !empty($images) ? explode(',', $images) : array();
    ?>
    <div>
        <button class="button select-images">Select Images</button>
        <div id="image_preview" style="margin-top:10px;">
            <?php foreach ($images as $image_id):
                $image_url = wp_get_attachment_image_src($image_id, 'thumbnail');
                if ($image_url): ?>
                    <div class="image-item" data-id="<?php echo esc_attr($image_id); ?>" style="display:inline-block; position:relative; margin:5px;">
                        <img src="<?php echo esc_url($image_url[0]); ?>" width="100" height="100" style="display:block;" />
                        <span class="remove-image" style="position:absolute; top:0; right:0; background:#f00; color:#fff; cursor:pointer; padding:2px 5px;">&times;</span>
                    </div>
                <?php endif; endforeach; ?>
        </div>
        <input type="hidden" name="rv_lot_images" id="rv_lot_images" value="<?php echo esc_attr(implode(',', $images)); ?>" />
    </div>
    <script>
        jQuery(document).ready(function($) {
            var frame;
            $('.select-images').on('click', function(e) {
                e.preventDefault();
                
                var imageIDs = $('#rv_lot_images').val() ? $('#rv_lot_images').val().split(',') : [];
                frame = wp.media({
                    title: 'Select Images',
                    button: { text: 'Use selected images' },
                    multiple: true
                });
                
                frame.on('select', function() {
                    var selection = frame.state().get('selection');
                    var previewDiv = $('#image_preview');
                    
                    selection.each(function(attachment) {
                        attachment = attachment.toJSON();
                        if ($.inArray(attachment.id.toString(), imageIDs) === -1) {
                            imageIDs.push(attachment.id);
                            previewDiv.append('<div class="image-item" data-id="' + attachment.id + '" style="display:inline-block; position:relative; margin:5px;">' +
                                '<img src="' + attachment.sizes.thumbnail.url + '" width="100" height="100" style="display:block;" />' +
                                '<span class="remove-image" style="position:absolute; top:0; right:0; background:#f00; color:#fff; cursor:pointer; padding:2px 5px;">&times;</span>' +
                                '</div>');
                        }
                    });
                    
                    $('#rv_lot_images').val(imageIDs.join(','));
                });
                frame.open();
            });
            
            $('#image_preview').on('click', '.remove-image', function() {
                var $parent = $(this).closest('.image-item');
                var id = $parent.data('id').toString();
                $parent.remove();
                var imageIDs = $('#rv_lot_images').val().split(',');
                imageIDs = imageIDs.filter(function(item) { return item !== id; });
                $('#rv_lot_images').val(imageIDs.join(','));
            });
        });
    </script>
    <?php
}

// Save Meta Box Data
function save_rv_lot_images_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['rv_lot_images'])) return;
    $image_ids = sanitize_text_field($_POST['rv_lot_images']);
    if (!empty($image_ids)) {
        update_post_meta($post_id, '_rv_lot_images', $image_ids);
    } else {
        delete_post_meta($post_id, '_rv_lot_images');
    }
}
add_action('save_post_rv-lots', 'save_rv_lot_images_meta');

// Enqueue WordPress Media Uploader
function rvbs_enqueue_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) return;
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'rvbs_enqueue_admin_scripts');

// add metabox to save the image gallery 


// add and hook to add new row in the booking data table 

// Hook into the save_post_rv-lots action
add_action('save_post_rv-lots', 'rvbs_add_rv_lot_to_bookings', 10, 3);

function rvbs_add_rv_lot_to_bookings($post_id, $post, $update) {
    global $wpdb;

    // Only run this for new posts, not updates
    if ($update) {
        return;
    }

    // Ensure the post type is 'rv-lots'
    if ($post->post_type !== 'rv-lots') {
        return;
    }

    // Define the bookings table name
    $table_name = $wpdb->prefix . 'rvbs_rv_lots';

    // Insert a new row with default values
    $wpdb->insert(
        $table_name,
        [
            'post_id' => $post_id,
            'is_available' => 1, // Mark as available by default
            'status' => 'pending', // Default status
            'created_at' => current_time('mysql', 1)
        ],
        ['%d', '%d', '%s', '%s'] // Data format: INT, INT, ENUM, TIMESTAMP
    );

    // Log errors if any
    if (!empty($wpdb->last_error)) {
        error_log('Error inserting RV lot into bookings: ' . $wpdb->last_error);
    } else {
        error_log("New RV lot (ID: $post_id) added to bookings.");
    }
}


// Hook when a post is moved to trash
add_action('wp_trash_post', 'mark_rv_lot_unavailable');

function mark_rv_lot_unavailable($post_id) {
    global $wpdb;

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'rv-lots') {
        return;
    }

    $table_name = $wpdb->prefix . 'rvbs_rv_lots';

    // Update 'is_available' and 'is_trash' columns correctly
    $wpdb->update(
        $table_name,
        ['is_available' => 0, 'is_trash' => 1], // Columns to update
        ['post_id' => $post_id], // Where condition
        ['%d', '%d'], // Data formats
        ['%d'] // Where format
    );

    error_log("RV lot (ID: $post_id) moved to trash and marked unavailable.");
}


// Hook when a post is restored from trash
add_action('untrash_post', 'restore_rv_lot_availability');

function restore_rv_lot_availability($post_id) {
    global $wpdb;

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'rv-lots') {
        return;
    }

    $table_name = $wpdb->prefix . 'rvbs_rv_lots';
    
    // Mark as available again
    $wpdb->update(
        $table_name,
        ['is_available' => 1,'is_trash' => 0],
        ['post_id' => $post_id],
        ['%d'],
        ['%d'],
        ['%d'],
    );

    error_log("RV lot (ID: $post_id) restored and marked available.");
}

// Hook when a post is permanently deleted
add_action('before_delete_post', 'delete_rv_lot_from_bookings');

function delete_rv_lot_from_bookings($post_id) {
    global $wpdb;

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'rv-lots') {
        return;
    }

    $table_name = $wpdb->prefix . 'rvbs_rv_lots';

    // Delete the row permanently
    $wpdb->delete(
        $table_name,
        ['post_id' => $post_id],
        ['%d']
    );

    error_log("RV lot (ID: $post_id) permanently deleted from bookings.");
}
