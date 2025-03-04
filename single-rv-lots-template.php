<?php
/*
Template Name: Single RV Lot
*/

// Check if the theme is block-based (Full Site Editing)
$is_fse_theme = wp_is_block_theme();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php 
    wp_head(); // Ensures styles & scripts are loaded

    // Load global styles for block themes (necessary for FSE)
    if ($is_fse_theme) {
        $global_styles = file_get_contents(get_template_directory() . '/style.css');
        echo '<style>' . $global_styles . '</style>';
    }
    ?>
</head>
<body <?php body_class(); ?>>

<?php
// Load the correct header
if (!$is_fse_theme) {
    get_header(); // Classic Theme
} else {
    echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->'); // Block Theme Header
}
?>

<main class="container my-5">
    <?php
    // Get the current post (single RV Lot)
    $post_id = get_the_ID();
    $post = get_post($post_id);

    if ($post) {
        // Retrieve meta box data
        $rv_lots_price = get_post_meta($post_id, '_rv_lots_price', true);
        $rv_lots_guest = get_post_meta($post_id, '_rv_lots_guest', true);
        $rv_lots_images = get_post_meta($post_id, '_rv_lot_images', true);
        
        // Split the image IDs into an array
        $image_ids = !empty($rv_lots_images) ? explode(',', $rv_lots_images) : [];
        ?>
        <h2 class="text-center"><?php echo esc_html(get_the_title($post_id)); ?></h2>

        <div class="post-content">
            <?php
            // Display the post content
            the_content();
            ?>
        </div>

        <div class="meta-box-data">
            <p><strong>Price:</strong> <?php echo esc_html($rv_lots_price); ?></p>
            <p><strong>Guest Capacity:</strong> <?php echo esc_html($rv_lots_guest); ?></p>

            <!-- Display Featured Image -->
            <?php if (has_post_thumbnail($post_id)): ?>
                <div class="featured-image">
                    <?php echo get_the_post_thumbnail($post_id, 'full', ['style' => 'max-width:100%; height:auto;']); ?>
                </div>
            <?php endif; ?>

            <!-- Display Images from Meta Box -->
            <?php if (!empty($image_ids)): ?>
                <div class="image-gallery" style="display:flex; flex-wrap:wrap; margin-top:20px;">
                    <?php foreach ($image_ids as $image_id): 
                        $image_url = wp_get_attachment_image_src($image_id, 'full');
                        if ($image_url): ?>
                            <div class="image-item" style="margin: 5px;">
                                <img src="<?php echo esc_url($image_url[0]); ?>" alt="RV Lot Image" style="max-width: 200px; height: auto;">
                            </div>
                        <?php endif; 
                    endforeach; ?>
                </div>
            <?php else: ?>
                <p>No images available from the meta box.</p>
            <?php endif; ?>
        </div>
        <?php
    } else {
        ?>
        <p class="text-center">RV Lot not found.</p>
        <?php
    }
    ?>
</main>

<?php
// Load the correct footer
if (!$is_fse_theme) {
    get_footer(); // Classic Theme
} else {
    echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->'); // Block Theme Footer
}
?>

<?php wp_footer(); // Ensures scripts & footer styles load ?>
</body>
</html>