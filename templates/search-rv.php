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

    <!-- Filter by Park Features -->
    <div class="bg-light rounded mb-3 p-3">
        <h6 class="mb-3">Filter by Park Features</h6>
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

    <!-- AJAX Filtering Script -->
<!-- Update the existing AJAX Filtering Script -->
<!-- Update the AJAX Filtering Script in your template -->
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
        var checkInDate = $('#checkin_date').val();
        var checkOutDate = $('#checkout_date').val();
        var adults = $('#adult').val() || 1; // Default to 1 if empty
        var children = $('#children').val() || 0; // Default to 0 if empty
        var priceRange = $('input[name="price_range"]:checked').val() || ''; // Get selected price range

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: {
                action: 'filter_rv_lots',
                site_type: siteType,
                features: selectedFeatures,
                aminets: selectedAmenities,
                check_in: checkInDate,
                check_out: checkOutDate,
                adults: adults,
                children: children,
                price_range: priceRange // Add price range to the AJAX data
            }, 
            beforeSend: function() {
                $('#show_aval_room').html('<p class="text-center">Loading...</p>');
            },
            success: function(response) {
                $('#show_aval_room').html(response);
            }
        });
    }

    // Handle Site Type Filter Click
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filterRVLots();
    });

    // Handle all filter changes
    $('.feature-filter, .aminets-filter, .price-filter, #checkin_date, #checkout_date, #adult, #children').on('change', function() {
        filterRVLots();
    });

    // Guest filter function
    window.guest_filter = function() {
        filterRVLots();
    }

    // Clear guest function
    window.clear_guest = function() {
        $('#adult').val('');
        $('#children').val('');
        filterRVLots();
    }

    // Clear price function
    window.clear_price = function() {
        $('input[name="price_range"]').prop('checked', false);
        filterRVLots();
    }

    // Clear date function
    window.clear_date = function() {
        $('#checkin_date').val('');
        $('#checkout_date').val('');
        $('#nights_display').remove();
        filterRVLots();
    }

    // Check room availability
    window.check_room_aval = function() {
        filterRVLots();
        var checkIn = new Date($('#checkin_date').val());
        var checkOut = new Date($('#checkout_date').val());
        if (checkIn && checkOut && checkOut > checkIn) {
            var timeDiff = checkOut - checkIn;
            var nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
            $('#nights_display').remove();
            $('.rvbs-search-rv').prepend(
                '<div id="nights_display" class="text-center mb-3">Booked for ' + nights + ' night' + (nights > 1 ? 's' : '') + '</div>'
            );
        }
    }
});
</script>


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

                                <!-- Guest Filters (Consolidated from duplicates) -->
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


                                <!-- price filter  -->
                                 <!-- Filter by Price -->
<div class="bg-light rounded mb-3 p-3">
    <h6 class="mb-3 d-flex justify-content-between">
        Starting Nightly Price
        <span id="reset_price" onclick="clear_price()" class="text-secondary btn btn-sm">Reset</span>
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
                                'posts_per_page' => 10,
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
                                            <a href="<?php echo home_url('/booknow?post_id=' . get_the_ID() . '&date=' . get_the_date('Y-m-d')); ?>" 
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

<?php wp_footer(); // Ensures scripts & footer styles load ?>
</body>
</html>