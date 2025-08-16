<?php
/*
Template Name: Search RV
link: rvbs-search-rv.js
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
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
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

    <main class="rvbs-search-rv container my-5">
        <!-- Our Rooms Section -->
        <div class="bg-light rvbs-search-rv">
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
                                    <!-- Date Filters (with Flatpickr) -->
                                    <div class="bg-light rounded mb-3 p-3">
                                        <h6 class="mb-3 d-flex justify-content-between">Dates
                                            <span id="reset_avail" onclick="clear_date()" class="text-secondary btn btn-sm" style="display: none;">Reset</span>
                                        </h6>
                                        <div class="calendar-container">
                                            <div id="dateDisplay" class="date-display">
                                                <span id="checkInText">Check In</span>
                                                <span style='color: black;'>→</span>
                                                <span id="checkOutText">Check Out</span>
                                            </div>
                                            <input type="text" id="dateRange" style="position: absolute; opacity: 0; height: 0; width: 0; padding: 0; border: none;">
                                            <div class="hidden-inputs">
                                                <input type="hidden" id="checkin_date" name="checkin_date">
                                                <input type="hidden" id="checkout_date" name="checkout_date">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Filter by Site Type (No Reset Button) -->
                                    <div class="bg-light rounded mb-3 p-3">
                                        <h6 class="mb-3">Filter by Site Type</h6>
                                        <ul class="list-unstyled">
                                            <li><button class="btn btn-link filter-btn active" data-filter="all">All Sites</button></li>
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

                                    <!-- Guest Filters -->
                                    <div class="bg-light rounded mb-3 p-3">
                                        <h6 class="mb-3 d-flex justify-content-between">GUEST
                                            <span id="guest_reset" onclick="clear_guest()" class="text-secondary btn btn-sm" style="display: none;">Reset</span>
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

                                    <!-- Price Filter -->
                                    <div class="bg-light rounded mb-3 p-3">
                                        <h6 class="mb-3 d-flex justify-content-between">
                                            Starting Nightly Price
                                            <span id="reset_price" onclick="clear_price()" class="text-secondary btn btn-sm" style="display: none;">Reset</span>
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li>
                                                <input type="radio" name="price_range" class="price-filter" value="0-25" id="price-less-25">
                                                <label for="price-less-25">Less than $25</label>
                                            </li>
                                            <li>
                                                <input type="radio" name="price_range" class="price-filter" value="25-50" id="price-25-50">
                                                <label for="price-25-50">$25 to $50</label>
                                            </li>
                                            <li>
                                                <input type="radio" name="price_range" class="price-filter" value="50-75" id="price-50-75">
                                                <label for="price-50-75">$50 to $75</label>
                                            </li>
                                            <li>
                                                <input type="radio" name="price_range" class="price-filter" value="75+" id="price-over-75">
                                                <label for="price-over-75">Over $75</label>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Filter by Park Features -->
                                    <div class="bg-light rounded mb-3 p-3">
                                        <h6 class="mb-3 d-flex justify-content-between">Filter by Park Features
                                            <span id="reset_features" onclick="clear_features()" class="text-secondary btn btn-sm" style="display: none;">Reset</span>
                                        </h6>
                                        <ul class="list-unstyled">
                                            <?php
                                            $features = get_terms(array(
                                                'taxonomy'   => 'park_feature',
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
                                        <h6 class="mb-3 d-flex justify-content-between">Filter by Amenities
                                            <span id="reset_amenities" onclick="clear_amenities()" class="text-secondary btn btn-sm" style="display: none;">Reset</span>
                                        </h6>
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
                                </div>
                            </div>
                        </nav>
                    </div>

                    <!-- Room Cards -->
                    <div class="col-lg-9 col-md-12" id="room_data">
                        <div id="show_aval_room_container">
                            <div id="show_aval_room">
                                <?php
                                // Fetch initial 10 posts
                                $query = new WP_Query(array(
                                    'post_type'      => 'rv-lots',
                                    'posts_per_page' => 5,
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
                                                        <a href="<?php echo home_url('/booknow?campsite=' . get_the_ID() . '&check_in=' . date('Y-m-d') . '&check_out=' . date('Y-m-d', strtotime('+1 day'))); ?>"><?php the_title(); ?></a>
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
                                                    ?>
                                                    <?php
                                                    $amenities = get_the_terms(get_the_ID(), 'site_amenity');
                                                    if ($amenities && !is_wp_error($amenities)) :
                                                        echo '<p><strong>Amenities:</strong> ';
                                                        $amenity_names = wp_list_pluck($amenities, 'name');
                                                        echo implode(', ', $amenity_names);
                                                        echo '</p>';
                                                    endif;
                                                    ?>
                                                    <a href="<?php echo home_url('/booknow?campsite=' . get_the_ID() . '&check_in=' . date('Y-m-d') . '&check_out=' . date('Y-m-d', strtotime('+1 day'))); ?>"
                                                        class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                                                        Book Now
                                                    </a>
                                                </div>
                                                <!-- Third Section (Price) -->
                                                <div class="col-md-3">
                                                    <?php
                                                    $price = get_post_meta(get_the_ID(), '_rv_lots_price', true);
                                                    ?>
                                                    <p class="price">Starting at <strong>$<?php echo esc_html($price ? $price : '20.00'); ?></strong> /night</p>
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
        </div>
    </main>

    <?php
    // Load the correct footer
    if (!$is_fse_theme) {
        get_footer(); // Classic Theme
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->'); // Block Theme Footer
    }
    ?>

    

    <?php wp_footer(); // Ensures scripts & footer styles load 
    ?>
</body>

</html>