<?php
/**
 * Template Name: Category Filter
 */
get_header();
?>

<div class="container">
    <h2 class="mt-4">Filter Posts by Category</h2>
    
    <!-- Filter Form -->
    <form method="GET" action="">
        <div class="row">
            <?php
            $categories = get_categories(); // Get all categories
            foreach ($categories as $category) :
            ?>
                <div class="col-md-3">
                    <input type="checkbox" name="categories[]" value="<?php echo $category->term_id; ?>" 
                    <?php if (isset($_GET['categories']) && in_array($category->term_id, $_GET['categories'])) echo 'checked'; ?>>
                    <?php echo $category->name; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Filter</button>
    </form>

    <hr>

    <!-- Display Filtered Posts -->
    <div class="row">
        <?php
        $query_args = [
            'post_type' => 'post',
            'posts_per_page' => 10,
        ];

        if (isset($_GET['categories']) && !empty($_GET['categories'])) {
            // Filter by selected categories
            $category_ids = array_map('intval', $_GET['categories']);
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => $category_ids,
                    'operator' => 'IN',
                ],
            ];
        }

        // Create a custom query
        $query = new WP_Query($query_args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
                            <p class="card-text"><?php the_excerpt(); ?></p>
                        </div>
                    </div>
                </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo "<p>No posts found for the selected categories.</p>";
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>
