<?php
/*
Template Name: Book Now
*/

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <h1><?php the_title(); ?></h1>
        <p>Welcome to the booking page!</p>
        <?php
            // Fetch initial 15 posts
            $query = new WP_Query(array(
              'post_type'      => 'post',
              'posts_per_page' => 15,
              'paged'          => 1
            ));

            if ($query->have_posts()) :
              while ($query->have_posts()) : $query->the_post();
            ?>
                <div class="card mb-4">
                  <div class="row">
                    <!-- First Section (Image) -->
                    <div class="col-md-4">
                      <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php the_post_thumbnail_url(); ?>" class="img-fluid" alt="<?php the_title(); ?>">
                      <?php else : ?>
                        <div class="no-image">No Image Available</div>
                      <?php endif; ?>
                    </div>
                    <!-- Second Section (Details) -->
                    <div class="col-md-5">
                      <h5 class="card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                      </h5>
                      <p class="card-text"><?php the_excerpt(); ?></p>
                      <a href="<?php the_permalink(); ?>" class="btn btn-primary">Read More</a>
                    </div>
                    <!-- Third Section (Price) -->
                    <div class="col-md-3">
                      <p class="price">Starting at <strong>$20.00</strong> /night</p>
                    </div>
                  </div>
                </div>
            <?php
              endwhile;
              wp_reset_postdata();
            endif;
            ?>
    </main>
</div>

<?php get_footer(); ?>
