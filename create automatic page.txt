<?php
/**
 * Plugin Name: Custom Book Now Page
 * Description: Creates a custom page to display posts of a specific post type with custom styling and adds it to the menu.
 * Version: 1.0
 * Author: IK Robin
 */

// Register activation hook
register_activation_hook(__FILE__, 'cbnp_create_book_now_page');

function cbnp_create_book_now_page() {
    // Check if the "Book Now" page exists
    $page = get_page_by_path('book-now');
    if (!$page) {
        // Create the "Book Now" page
        
        $page_id = wp_insert_post([
            'post_title'   => 'Book Now',
            'post_content' => '[cbnp_book_now_posts]', // Placeholder for the custom shortcode
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        // Automatically add the page to the menu
       
    }
}
//  just add acomment 
// Register the shortcode
add_shortcode('cbnp_book_now_posts', 'cbnp_display_custom_posts');

function cbnp_display_custom_posts() {
    ob_start();

    // Query custom posts (default: 'post') 
    $query = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => -1
    ]);

    if ($query->have_posts()) {
        echo '<div class="cbnp-post-list">';
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="cbnp-post-item">
                <h2 class="cbnp-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="cbnp-post-excerpt"><?php the_excerpt(); ?></div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p>No posts found.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}

// Enqueue styles
add_action('wp_enqueue_scripts', 'cbnp_enqueue_styles');

function cbnp_enqueue_styles() {
    if (is_page('book-now')) {
        wp_enqueue_style('cbnp-styles', plugin_dir_url(__FILE__) . 'assets/styles.css');
    }
}

// Create a basic stylesheet
register_activation_hook(__FILE__, 'cbnp_create_stylesheet');

function cbnp_create_stylesheet() {
    $plugin_dir = plugin_dir_path(__FILE__) . 'assets/';
    if (!file_exists($plugin_dir)) {
        mkdir($plugin_dir);
    }

    $css_content = ".cbnp-post-list {\n    display: flex;\n    flex-wrap: wrap;\n    gap: 20px;\n}\n.cbnp-post-item {\n    border: 1px solid #ccc;\n    padding: 10px;\n    border-radius: 5px;\n    width: calc(33.333% - 20px);\n    box-sizing: border-box;\n}\n.cbnp-post-title {\n    font-size: 18px;\n    margin: 0 0 10px;\n}\n.cbnp-post-excerpt {\n    font-size: 14px;\n    color: #555;\n}";

    file_put_contents($plugin_dir . 'styles.css', $css_content);
}
