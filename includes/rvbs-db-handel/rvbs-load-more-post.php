<?php 
// Hook the function to WordPress AJAX actions
add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

function load_more_posts() {
    // Security check
    check_ajax_referer('load_more_posts_nonce', 'nonce'); // Expect 'nonce' in POST data
    
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $posts_per_page = 2;

    $args = array(
        'post_type'      => 'rv-lots',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'post_status'    => 'publish'
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        ob_start();
        
        while ($query->have_posts()) : $query->the_post(); ?>
            <div class="card mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" class="img-fluid" alt="<?php the_title(); ?>">
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
        <?php endwhile;
        
        $output = ob_get_clean();
        wp_send_json_success(array(
            'html' => $output,
            'max_pages' => $query->max_num_pages
        ));
    else :
        wp_send_json_error('No more posts available');
    endif;

    wp_reset_postdata();
    wp_die();
}