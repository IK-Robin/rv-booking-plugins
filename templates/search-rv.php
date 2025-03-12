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

    <!-- Flatpickr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

    <script>
        window.openCalendar = function() {
            if (window.fpInstance && typeof window.fpInstance.open === 'function') {
                window.fpInstance.open();
            } else {
                console.error('Flatpickr instance not initialized or open method not available.');
            }
        };

        jQuery(document).ready(function($) {
            // Ensure flatpickr is defined
            if (typeof flatpickr === 'undefined') {
                console.error('Flatpickr is not loaded.');
                return;
            }

            // Initialize Flatpickr
            window.fpInstance = flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                onClose: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        const formatDate = date => date.toLocaleDateString('en-US', {
                            weekday: 'short',
                            month: 'short',
                            day: '2-digit'
                        });
                        document.querySelector("#checkInText").textContent = formatDate(selectedDates[0]);
                        document.querySelector("#checkOutText").textContent = formatDate(selectedDates[1]);
                        document.querySelector("#dateDisplay").classList.add("active");

                        const getISODate = date => {
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            return `${year}-${month}-${day}`;
                        };

                        $('#checkin_date').val(getISODate(selectedDates[0]));
                        $('#checkout_date').val(getISODate(selectedDates[1]));

                        // Trigger filter and update UI
                        filterRVLots();
                        check_room_aval();
                        updateResetButtonsVisibility();
                    }
                },
                appendTo: document.querySelector('.calendar-container')
            });

            // Attach click event to dateDisplay
            $('#dateDisplay').on('click', function() {
                window.openCalendar();
            });

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
                        price_range: priceRange
                    },
                    beforeSend: function() {
                        $('#show_aval_room').html('<p class="text-center">Loading...</p>');
                    },
                    success: function(response) {
                        $('#show_aval_room').html(response);
                        updateResetButtonsVisibility(); // Update visibility of all reset buttons
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            }

            // Check filter states and update reset button visibility
            function updateResetButtonsVisibility() {
                // Dates
                if ($('#checkin_date').val() || $('#checkout_date').val()) {
                    $('#reset_avail').show();
                } else {
                    $('#reset_avail').hide();
                }

                // Guests
                if (($('#adult').val() && $('#adult').val() !== '1') || ($('#children').val() && $('#children').val() !== '0')) {
                    $('#guest_reset').show();
                } else {
                    $('#guest_reset').hide();
                }

                // Price
                if ($('input[name="price_range"]:checked').length > 0) {
                    $('#reset_price').show();
                } else {
                    $('#reset_price').hide();
                }

                // Features
                if ($('.feature-filter:checked').length > 0) {
                    $('#reset_features').show();
                } else {
                    $('#reset_features').hide();
                }

                // Amenities
                if ($('.aminets-filter:checked').length > 0) {
                    $('#reset_amenities').show();
                } else {
                    $('#reset_amenities').hide();
                }
            }

            // Handle Site Type Filter Click
            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                filterRVLots();
            });

            // Handle all filter changes
            $('.feature-filter, .aminets-filter, .price-filter, #adult, #children').on('change', function() {
                filterRVLots();
            });

            // Guest filter function
            window.guest_filter = function() {
                filterRVLots();
            };

            // Clear guest function
            window.clear_guest = function() {
                $('#adult').val('1');
                $('#children').val('1');
                filterRVLots();
            };

            // Clear price function
            window.clear_price = function() {
                $('input[name="price_range"]').prop('checked', false);
                filterRVLots();
            };

            // Clear date function
            window.clear_date = function() {
                document.querySelector("#checkInText").textContent = 'Check In';
                document.querySelector("#checkOutText").textContent = 'Check Out';
                document.querySelector("#dateDisplay").classList.remove("active");
                $('#checkin_date').val('');
                $('#checkout_date').val('');
                $('#nights_display').remove();
                filterRVLots();
            };

            // Clear features function
            window.clear_features = function() {
                $('.feature-filter').prop('checked', false);
                filterRVLots();
            };

            // Clear amenities function
            window.clear_amenities = function() {
                $('.aminets-filter').prop('checked', false);
                filterRVLots();
            };

            // Check room availability
            window.check_room_aval = function() {
                var checkIn = $('#checkin_date').val();
                var checkOut = $('#checkout_date').val();
                if (checkIn && checkOut) {
                    var checkInDate = new Date(checkIn);
                    var checkOutDate = new Date(checkOut);
                    if (checkOutDate > checkInDate) {
                        var timeDiff = checkOutDate - checkInDate;
                        var nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
                        $('#nights_display').remove();
                        $('.rvbs-search-rv').prepend(
                            '<div id="nights_display" class="text-center mb-3">Booked for ' + nights + ' night' + (nights > 1 ? 's' : '') + '</div>'
                        );
                    }
                }
            };

            // Initial check for reset buttons visibility
            updateResetButtonsVisibility();
        });
    </script>

    <?php wp_footer(); // Ensures scripts & footer styles load 
    ?>
</body>

</html>