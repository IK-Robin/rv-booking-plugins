<?php 

// load more post on rv filter page 

function load_more_posts() {
    $paged = $_POST['page']; // Get the page number from AJAX request

    $query = new WP_Query(array(
        'post_type'      => 'rv-lots',
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
        echo 'No more posts available';
    endif;

    wp_die(); // Stop execution
}

?>