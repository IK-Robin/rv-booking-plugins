<?php

// load the script file 
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-script.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-style.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/all-shortcode.php';


// require_once RVBS_BOOKING_PLUGIN_DIR . '/templates/rvbs-search-rv.php';



// include all db querey here 

require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-db-handel/rvbs-register-all-db-action.php';



// register the custom post type here
// require_once RVBS_BOOKING_PLUGIN_DIR . 'custom_post.php';
require_once RVBS_BOOKING_PLUGIN_DIR . 'rvbs-rv-custompost-type.php';


// // Hook to include custom template from plugin

// function my_custom_plugin_add_template($templates) {
//     $templates['my-custom-template.php'] = 'Search Rv';
//     return $templates;
// }

// add_filter('template_include', 'my_custom_plugin_load_template');

// function my_custom_plugin_load_template($template) {
//     if (is_page('search-rv')) {
//         $plugin_template = plugin_dir_path(__FILE__) . 'templates/my-custom-template.php';
//         if (file_exists($plugin_template)) {
//             return $plugin_template;
//         }
//     }
//     return $template;
// }




// Register AJAX actions
add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

          




             

// filete the site using site type taxonomy 
function filter_rv_lots() {
    $site_type = isset($_POST['site_type']) ? sanitize_text_field($_POST['site_type']) : '';
    $selected_features = isset($_POST['features']) ? $_POST['features'] : array();
    $selected_aminetis = isset($_POST['aminets']) ? $_POST['aminets'] : array();

    $args = array(
        'post_type'      => 'rv-lots',
        'posts_per_page' => 15,
    );

    // Filter by site type if selected
    if ($site_type !== 'all' && !empty($site_type)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'site_type',
            'field'    => 'slug',
            'terms'    => $site_type,
        );
    }

    // Filter by park features if any are selected
    if (!empty($selected_features)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'park_feature',
            'field'    => 'slug',
            'terms'    => $selected_features,
            'operator' => 'AND', // Ensures the post has ALL selected features
        );
    }

    // filter by the park aminetis 

    if(!empty($selected_aminetis)){
        $args['tax_query'][] = array(
            'taxonomy' => 'site_amenity',
            'field' => 'slug',
            'terms' => $selected_aminetis,
            'operator' => 'AND',
        );

    }

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post(); ?>
            <div class="card mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url(); ?>" class="img-fluid" alt="<?php the_title(); ?>">
                        <?php else : ?>
                            <div class="no-image">No Image Available</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        <h5 class="card-title">
                            <a href="<?php echo home_url('/booknow?post_id=' . get_the_ID() . '&date=' . get_the_date('Y-m-d')); ?>"><?php the_title(); ?></a>
                        </h5>
                        <p class="card-text"><?php the_excerpt(); ?></p>

                        <!-- ✅ Show Available Features -->
                        <?php 
                        $features = get_the_terms(get_the_ID(), 'park_feature');
                        if ($features && !is_wp_error($features)) :
                            echo '<p><strong>Features:</strong> ';
                            $feature_names = wp_list_pluck($features, 'name');
                            echo implode(', ', $feature_names);
                            echo '</p>';
                        endif;
                        ?>

                        <!-- ✅ Show Available Amenities -->
                        <?php 
                          
                          $amenities = get_the_terms(get_the_ID(), 'site_amenity');
                          if ($amenities && !is_wp_error($amenities)) :
                              echo '<p><strong>Amenities:</strong> ';
                              $amenity_names = wp_list_pluck($amenities, 'name');
                              echo implode(', ', $amenity_names);
                              echo '</p>';
                          endif;
                    
                        ?>

<a href="<?php echo home_url('/booknow?post_id=' . get_the_ID() . '&date=' . get_the_date('Y-m-d')); ?>" 
                                               class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                               Book Now
                                            </a>
                    </div>
                    <div class="col-md-3">
                        <p class="price">Starting at <strong>$20.00</strong> /night</p>
                    </div>
                </div>
            </div>
        <?php endwhile;
        wp_reset_postdata();
    else :
        echo '<p class="text-center">No RV lots found.</p>';
    endif;

    wp_die();
}
add_action('wp_ajax_filter_rv_lots', 'filter_rv_lots');
add_action('wp_ajax_nopriv_filter_rv_lots', 'filter_rv_lots');





// filter the site using the feature 


// load the template for single post prevew 
function rvbs_load_custom_template($template) {
    if (is_singular('rv-lots')) {
        // Specify the path to your custom template file in the plugin
        $custom_template = plugin_dir_path(__FILE__) . 'single-rv-lots-template.php';
        
        // Check if the custom template file exists, then load it
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template; // Return the default template if no custom template is found
}
add_filter('template_include', 'rvbs_load_custom_template');

// add the style support for block theam 
add_theme_support('wp-block-styles');


// add anotehr logic to book and filter rv lots 

// Enqueue scripts and localize AJAX
function rvbs_enqueue_scripts() { 
    wp_enqueue_script('jquery');
    
    wp_register_script('rvbs-booking', get_template_directory_uri() . 'assets/js/rvbs-booking.js', array('jquery'), '1.0', true);
    
    wp_localize_script('rvbs-booking', 'rvbs_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rvbs_booking_nonce')
    ));
    
    wp_enqueue_script('rvbs-booking');
}
add_action('wp_enqueue_scripts', 'rvbs_enqueue_scripts');

// Check availability AJAX handler
function rvbs_check_availability() {
    check_ajax_referer('rvbs_booking_nonce', 'nonce');
    
    
    global $wpdb;
    $table_lots = $wpdb->prefix . 'rvbs_rv_lots';
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';
    
    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    
    // Query to find available lots
    $query = $wpdb->prepare("
        SELECT rl.*
        FROM $table_lots rl
        WHERE rl.is_available = 1 
        AND rl.is_trash = 0
        AND rl.status = 'confirmed'
        AND NOT EXISTS (
            SELECT 1 
            FROM $table_bookings rb 
            WHERE rb.post_id = rl.post_id
            AND rb.lot_id = rl.id
            AND rb.status IN ('pending', 'confirmed')
            AND (
                (%s BETWEEN rb.check_in AND rb.check_out)
                OR (%s BETWEEN rb.check_in AND rb.check_out)
                OR (rb.check_in BETWEEN %s AND %s)
            )
        )",
        $check_in,
        $check_out,
        $check_in,
        $check_out
    );
    
    $available_lots = $wpdb->get_results($query);
    
    if ($available_lots) {
        $output = '<h3>Available Lots</h3>';
        foreach ($available_lots as $lot) {
            $post_title = get_the_title($lot->post_id);
            $output .= '<div class="lot-item">';
            $output .= '<h4>' . esc_html($post_title) . '</h4>';
            $output .= '<p>Lot ID: ' . $lot->id . '</p>';
            $output .= '<button class="book-btn" data-lot-id="' . $lot->id . '" data-post-id="' . $lot->post_id . '">Book Now</button>';
            $output .= '</div>';
        }
    } else {
        $output = '<p>No lots available for these dates.</p>';
    }
    
    wp_send_json_success(array('html' => $output));
}
add_action('wp_ajax_rvbs_check_availability', 'rvbs_check_availability');
add_action('wp_ajax_nopriv_rvbs_check_availability', 'rvbs_check_availability');

// Book lot AJAX handler
function rvbs_book_lot() {
    check_ajax_referer('rvbs_booking_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('Please log in to book a lot.');
    }
    
    global $wpdb;
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';
    
    $lot_id = intval($_POST['lot_id']);
    $post_id = intval($_POST['post_id']);
    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    $user_id = get_current_user_id();
    
    // Calculate total price (example calculation, modify as needed)
    $start = new DateTime($check_in);
    $end = new DateTime($check_out);
    $days = $start->diff($end)->days;
    $price_per_day = 50; // Example price, adjust as needed
    $total_price = $days * $price_per_day;
    
    // Insert booking
    $result = $wpdb->insert(
        $table_bookings,
        array(
            'lot_id' => $lot_id,
            'post_id' => $post_id,
            'user_id' => $user_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'total_price' => $total_price,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s')
    );
    
    if ($result) {
        wp_send_json_success('Booking request submitted successfully!');
    } else {
        wp_send_json_error('Error creating booking: ' . $wpdb->last_error);
    }
}
add_action('wp_ajax_rvbs_book_lot', 'rvbs_book_lot');
add_action('wp_ajax_nopriv_rvbs_book_lot', 'rvbs_book_lot');





// add anotehr logic to book and filter rv lots 