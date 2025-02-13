<?php

// load the script file 
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-script.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/rvbs-all-style.php';
require_once RVBS_BOOKING_PLUGIN_DIR . '/includes/all-shortcode.php';


// require_once RVBS_BOOKING_PLUGIN_DIR . '/templates/rvbs-search-rv.php';




// // Hook to include custom template from plugin
// add_filter('theme_page_templates', 'my_custom_plugin_add_template');

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


// load more post on rv filter page 

function load_more_posts() {
    $paged = $_POST['page']; // Get the page number from AJAX request

    $query = new WP_Query(array(
        'post_type'      => 'post',
        'posts_per_page' => 10, // Load 10 more posts
        'paged'          => $paged
    ));

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            ?>
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
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
                    </div>
                    <div class="col-md-3">
                        <p class="price">Starting at <strong>$20.00</strong> /night</p>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
    else :
        echo '<p>No more posts available.</p>';
    endif;

    wp_die(); // Stop execution
}

// Register AJAX actions
add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

?>
