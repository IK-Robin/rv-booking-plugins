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
    global $wpdb;
    $table_lots = $wpdb->prefix . 'rvbs_rv_lots';
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';

    $site_type = isset($_POST['site_type']) ? sanitize_text_field($_POST['site_type']) : '';
    $selected_features = isset($_POST['features']) ? $_POST['features'] : array();
    $selected_aminetis = isset($_POST['aminets']) ? $_POST['aminets'] : array();
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 0;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $price_range = isset($_POST['price_range']) ? sanitize_text_field($_POST['price_range']) : '';

    // Base WP_Query args
    $args = array(
        'post_type'      => 'rv-lots',
        'posts_per_page' => 15,
    );

    // Filter by site type
    if ($site_type !== 'all' && !empty($site_type)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'site_type',
            'field'    => 'slug',
            'terms'    => $site_type,
        );
    }

    // Filter by park features
    if (!empty($selected_features)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'park_feature',
            'field'    => 'slug',
            'terms'    => $selected_features,
            'operator' => 'AND',
        );
    }

    // Filter by amenities
    if (!empty($selected_aminetis)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'site_amenity',
            'field'    => 'slug',
            'terms'    => $selected_aminetis,
            'operator' => 'AND',
        );
    }

// Filter by guest capacity
if ($adults > 0 || $children > 0) {
    $args['meta_query'] = array(
        'relation' => 'AND'
    );
    
    if ($adults > 0) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => 'max_adults',
                'value'   => $adults,
                'compare' => '>=',
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => 'max_adults',
                'compare' => 'NOT EXISTS'
            )
        );
    }
    
    if ($children > 0) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => 'max_children',
                'value'   => $children,
                'compare' => '>=',
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => 'max_children',
                'compare' => 'NOT EXISTS'
            )
        );
    }
}


// filter by price 
// Filter by price range
if (!empty($price_range)) {
    if (!isset($args['meta_query'])) {
        $args['meta_query'] = array('relation' => 'AND');
    }

    switch ($price_range) {
        case '0-25':
            $args['meta_query'][] = array(
                'key'     => '_rv_lots_price',
                'value'   => 25,
                'compare' => '<',
                'type'    => 'NUMERIC'
            );
            break;
        case '25-50':
            $args['meta_query'][] = array(
                'key'     => '_rv_lots_price',
                'value'   => array(25, 50),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC'
            );
            break;
        case '50-75':
            $args['meta_query'][] = array(
                'key'     => '_rv_lots_price',
                'value'   => array(50, 75),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC'
            );
            break;
        case '75+':
            $args['meta_query'][] = array(
                'key'     => '_rv_lots_price',
                'value'   => 75,
                'compare' => '>=',
                'type'    => 'NUMERIC'
            );
            break;
    }
}

    // Get posts that match taxonomy and guest filters first
    $initial_query = new WP_Query($args);
    $available_post_ids = array();

    if ($initial_query->have_posts()) {
        // Get all post IDs from initial query
        $post_ids = wp_list_pluck($initial_query->posts, 'ID');
        
        // If dates are provided, filter for availability
        if (!empty($check_in) && !empty($check_out)) {
            $query = $wpdb->prepare("
                SELECT DISTINCT rl.post_id
                FROM $table_lots rl
                WHERE rl.post_id IN (" . implode(',', array_fill(0, count($post_ids), '%d')) . ")
                AND rl.is_available = 1 
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
                array_merge(
                    $post_ids,
                    array($check_in, $check_out, $check_in, $check_out)
                )
            );

            $available_post_ids = $wpdb->get_col($query);
            
            if (empty($available_post_ids)) {
                echo '<p class="text-center">No RV lots available for these dates.</p>';
                wp_die();
            }
            
            $args['post__in'] = $available_post_ids;
        } else {
            $args['post__in'] = $post_ids;
        }
    } else {
        echo '<p class="text-center">No RV lots found matching your criteria.</p>';
        wp_die();
    }

   // Final query with available lots
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
                           <a href="<?php echo home_url('/booknow?post_id=' . get_the_ID() . '&check_in=' . $check_in . '&check_out=' . $check_out . '&adults=' . $adults . '&children=' . $children); ?>">
                               <?php the_title(); ?>
                           </a>
                       </h5>
                       <p class="card-text"><?php the_excerpt(); ?></p>
                       <?php 
                       $features = get_the_terms(get_the_ID(), 'park_feature');
                       if ($features && !is_wp_error($features)) :
                           echo '<p><strong>Features:</strong> ' . implode(', ', wp_list_pluck($features, 'name')) . '</p>';
                       endif;
                       $amenities = get_the_terms(get_the_ID(), 'site_amenity');
                       if ($amenities && !is_wp_error($amenities)) :
                           echo '<p><strong>Amenities:</strong> ' . implode(', ', wp_list_pluck($amenities, 'name')) . '</p>';
                       endif;
                       // Display guest capacity
                       $max_adults = get_post_meta(get_the_ID(), 'max_adults', true);
                       $max_children = get_post_meta(get_the_ID(), 'max_children', true);
                       if ($max_adults || $max_children) {
                           echo '<p><strong>Capacity:</strong> ';
                           if ($max_adults) echo $max_adults . ' adults';
                           if ($max_adults && $max_children) echo ', ';
                           if ($max_children) echo $max_children . ' children';
                           echo '</p>';
                       }
                       ?>
                       <a href="<?php echo home_url('/booknow?post_id=' . get_the_ID() . '&check_in=' . $check_in . '&check_out=' . $check_out . '&adults=' . $adults . '&children=' . $children); ?>" 
                          class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                          Book Now
                       </a>
                   </div>
                   <div class="col-md-3">
                       <?php
                       $price = get_post_meta(get_the_ID(), '_rv_lots_price', true);
                       ?>
                       <p class="price">Starting at <strong>$<?php echo esc_html($price ? $price : '20.00'); ?></strong> /night</p>
                       <?php
                       if ($check_in && $check_out) {
                           $date1 = new DateTime($check_in);
                           $date2 = new DateTime($check_out);
                           $nights = $date1->diff($date2)->days;
                           echo "<p>{$nights} night" . ($nights > 1 ? 's' : '') . "</p>";
                       }
                       if ($adults || $children) {
                           echo "<p>Guests: " . $adults . " adults" . ($children ? ", " . $children . " children" : "") . "</p>";
                       }
                       ?>
                   </div>
               </div>
           </div>
       <?php endwhile;
       wp_reset_postdata();
   else :
       echo '<p class="text-center">No RV lots available.</p>';
   endif;
    wp_die();
}
add_action('wp_ajax_filter_rv_lots', 'filter_rv_lots');
add_action('wp_ajax_nopriv_filter_rv_lots', 'filter_rv_lots');

// filter the site using the feature 


// load the template for single post prevew 
function rvbs_load_custom_template($template)
{
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
function rvbs_enqueue_scripts()
{
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
function rvbs_check_availability()
{
    check_ajax_referer('rvbs_booking_nonce', 'nonce');


    global $wpdb;
    $table_lots = $wpdb->prefix . 'rvbs_rv_lots';
    $table_bookings = $wpdb->prefix . 'rvbs_bookings';

    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);

    // Query to find available lots
    $query = $wpdb->prepare(
        "
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
function rvbs_book_lot()
{
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