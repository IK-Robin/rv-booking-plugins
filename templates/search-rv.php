<?php
/*
Template Name: Search RV
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
        if ($global_styles !== false) {
            echo '<style>' . $global_styles . '</style>';
        }
    }
    ?>
</head>
<body <?php body_class(); ?>>

<?php
// Load the header
if (!$is_fse_theme) {
    get_header(); // Classic Theme: Loads header.php with logo, menu, etc.
} else {
    // Block Theme: Try to load the 'header' template part
    $header_content = do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
    if (!empty($header_content) && $header_content !== '<!-- wp:template-part {"slug":"header"} /-->') {
        echo $header_content; // Render block theme header if it exists
    } else {
        // Fallback header for block themes without a defined header template part
        ?>
        <header class="site-header fallback-header" style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #ddd;">
            <div class="container d-flex justify-content-between align-items-center">
                <?php
                // Logo (uses site logo if available, otherwise site name)
                if (function_exists('has_custom_logo') && has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo '<h1 class="site-title"><a href="' . esc_url(home_url('/')) . '">' . get_bloginfo('name') . '</a></h1>';
                }
                ?>
                <!-- Navigation Menu -->
                <nav class="main-navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'fallback_cb'    => false,
                        'container'      => false,
                    ));
                    ?>
                </nav>
            </div>
        </header>
        <?php
    }
}
?>

<main class="rvbs-search-rv">
    <style>
        .content {
            background: rgb(200, 200, 200);
            height: 300px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>

    <!-- Filter by Site Type -->
    <div class="bg-light rounded mb-3 p-3">
        <h6 class="mb-3">Filter by Site Type</h6>
        <ul class="list-unstyled">
            <li><button class="btn btn-link filter-btn" data-filter="all">All Sites</button></li>
            <?php
            $site_types = get_terms(array(
                'taxonomy' => 'site_type',
                'hide_empty' => false,
            ));
            foreach ($site_types as $type) {
                echo '<li><button class="btn btn-link filter-btn" data-filter="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</button></li>';
            }
            ?>
        </ul>
    </div>

    <!-- the request go to the functon.php  -->
    <!-- AJAX Filtering Script -->
    <script>
    jQuery(document).ready(function($) {
        function filterRVLots() {
            var siteType = $('.filter-btn.active').data('filter') || 'all';
            var selectedFeatures = [];
            $('.feature-filter:checked').each(function() {
                selectedFeatures.push($(this).val());
            });
            var selectedAmenities = [];
            $('.aminets-filter:checked').each(function() {
                selectedAmenities.push($(this).val());
            });

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                data: {
                    action: 'filter_rv_lots',
                    site_type: siteType,
                    features: selectedFeatures,
                    aminets: selectedAmenities,
                },
                beforeSend: function() {
                    $('#show_aval_room').html('<p class="text-center">Loading...</p>');
                },
                success: function(response) {
                    $('#show_aval_room').html(response);
                }
            });
        }

        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            filterRVLots();
        });

        $('.feature-filter').on('change', function() {
            filterRVLots();
        });
        $('.aminets-filter').on('change', function() {
            filterRVLots();
        });
    });
    </script>

    <!-- Filter by Park Features -->
    <div class="bg-light rounded mb-3 p-3">
        <h6 class="mb-3">Filter by Park Features</h6>
        <ul class="list-unstyled">
            <?php
            $features = get_terms(array(
                'taxonomy' => 'park_feature',
                'hide_empty' => false,
            ));
            foreach ($features as $feature) {
                echo '<li>
                    <input type="checkbox" class="feature-filter" value="' . esc_attr($feature->slug) . '" id="feature-' . esc_attr($feature->slug) . '">
                    <label for="feature-' . esc_attr($feature->slug) . '">' . esc_html($feature->name) . '</label>
                </li>';
            }
            ?>
        </ul>
    </div>

    <!-- Filter by Amenities -->
    <div class="bg-light rounded mb-5 p-4">
        <h6 class="mb-3">Filter by Amenities</h6>
        <ul class="list-unstyled">
            <?php
            $amenities = get_terms(array(
                'taxonomy' => 'site_amenity',
                'hide_empty' => false,
            ));
            foreach ($amenities as $amenity) {
                echo '<li>
                    <input type="checkbox" class="aminets-filter" value="' . esc_attr($amenity->slug) . '" id="amenity-' . esc_attr($amenity->slug) . '">
                    <label for="amenity-' . esc_attr($amenity->slug) . '">' . esc_html($amenity->name) . '</label>
                </li>';
            }
            ?>
        </ul>
    </div>

    <!-- Our Rooms Section -->
    <div class="py-5 px-4 bg-light">
        <h2 class="mt-4 mb-1 pt-4 text-center font-bold merinda">OUR ROOMS</h2>
        <div class="h-line bg-dark mb-5"></div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 filster_section" id="filter_section">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
                    <div class="container d-flex flex-lg-column align-items-stretch">
                        <h6 class="mt-2">Filters</h6>
                        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" 
                            data-bs-target="#rooms_filter" aria-controls="rooms_filter" 
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse flex-lg-column mt-2 align-items-stretch" id="rooms_filter">
                            <!-- Date Filters -->
                            <div class="bg-light rounded mb-3 p-3">
                                <h6 class="mb-3 d-flex justify-content-between">Dates
                                    <span id="reset_avail" onclick="clear_date()" class="text-secondary btn btn-sm">Reset</span>
                                </h6>
                                <div>
                                    <label for="checkin_date" class="form-label">Check In Date</label>
                                    <input type="date" onchange="check_room_aval()" name="checkin_date" class="form-control shadow-none" id="checkin_date" />
                                </div>
                                <div>
                                    <label for="checkout_date" class="form-label">Check Out Date</label>
                                    <input type="date" class="form-control shadow-none" id="checkout_date" name="checkout_date" onchange="check_room_aval()" />
                                </div>
                            </div>

                            <!-- Guest Filters -->
                            <div class="bg-light rounded mb-3 p-3">
                                <h6 class="mb-3 d-flex justify-content-between">GUEST
                                    <span id="guest_reset" onclick="clear_guest()" class="text-secondary btn btn-sm">Reset</span>
                                </h6>
                                <div class="mb-2 d-flex">
                                    <div class="guest me-3">
                                        <label for="adult" class="form-label">Adults</label>
                                        <input min="1" type="number" oninput="guest_filter()" class="form-control shadow-none" id="adult" />
                                    </div>
                                    <div class="guest">
                                        <label for="children" class="form-label">Children</label>
                                        <input min="0" type="number" oninput="guest_filter()" class="form-control shadow-none" id="children" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Room Cards -->
            <div class="col-lg-9 col-md-12" id="room_data">
                <div id="show_aval_room_container">
                    <div id="show_aval_room">
                        <?php
                        $query = new WP_Query(array(
                            'post_type' => 'rv-lots',
                            'posts_per_page' => 10,
                            'paged' => 1
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
                                                <a href="<?php echo home_url('/booknow?post_id=' . get_the_ID() . '&date=' . get_the_date('Y-m-d')); ?>"><?php the_title(); ?></a>
                                            </h5>
                                            <p class="card-text"><?php the_excerpt(); ?></p>
                                            <?php 
                                            $features = get_the_terms(get_the_ID(), 'park_feature');
                                            if ($features && !is_wp_error($features)) :
                                                echo '<p><strong>Features:</strong> ';
                                                $feature_names = wp_list_pluck($features, 'name');
                                                echo implode(', ', $feature_names);
                                                echo '</p>';
                                            endif;
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
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </div>

                    <!-- Load More Button -->
                    <div class="text-center">
                        <button id="load_more" class="btn btn-secondary" data-page="2">Load More</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Load the footer
if (!$is_fse_theme) {
    get_footer(); // Classic Theme: Loads footer.php
} else {
    // Block Theme: Try to load the 'footer' template part
    $footer_content = do_blocks('<!-- wp:template-part {"slug":"footer"} /-->');
    if (!empty($footer_content) && $footer_content !== '<!-- wp:template-part {"slug":"footer"} /-->') {
        echo $footer_content; // Render block theme footer if it exists
    } else {
        // Fallback footer for block themes without a defined footer template part
        ?>
        <footer class="site-footer fallback-footer" style="padding: 20px; background: #f8f9fa; border-top: 1px solid #ddd; text-align: center;">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            </div>
        </footer>
        <?php
    }
}
?>

<?php wp_footer(); // Ensures scripts & footer styles load ?>
</body>
</html>