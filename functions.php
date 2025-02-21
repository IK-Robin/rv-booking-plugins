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
require_once RVBS_BOOKING_PLUGIN_DIR . 'custom_post_deepseek.php';


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
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
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

                        <a href="<?php echo home_url('/book-now?post_id=' . get_the_ID()); ?>" class="btn btn-primary">
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



?>
