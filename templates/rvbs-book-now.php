<?php
/*
Template Name: Book Now
*/

// Check if required URL parameters are present and valid
$post_id = isset($_GET['campsite']) ? intval($_GET['campsite']) : 0;
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';

// If any required parameter is missing or invalid, redirect to Search RV page
if (!$post_id || !$check_in || !$check_out) {
    wp_redirect(home_url('/search-rv/'));
    exit;
}

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

    $adults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
    $children = isset($_GET['children']) ? intval($_GET['children']) : 0;

    // Fetch the RV lot post
    $lot = get_post($post_id);
    if (!$lot || $lot->post_type !== 'rv-lots') {
        wp_die('Invalid RV lot.');
    }

    // Get amenities and features
    $amenities = get_the_terms($post_id, 'site_amenity');
    $features = get_the_terms($post_id, 'park_feature');

    // Get price
    $price = get_post_meta($post_id, '_rv_lots_price', true);
    $price = floatval($price) ?: 20.00;

    // Calculate nights
    $nights = 0;
    if ($check_in && $check_out) {
        $date1 = new DateTime($check_in);
        $date2 = new DateTime($check_out);
        $nights = $date1->diff($date2)->days;
    }

    // Format dates for initial display
    $check_in_formatted = $check_in ? (new DateTime($check_in))->format('D, M d') : 'Check In';
    $check_out_formatted = $check_out ? (new DateTime($check_out))->format('D, M d') : 'Check Out';
    ?>

    <div class="container my-5">
        <div class="row">
            <!-- Left Section: Lot Details -->
            <div class="col-lg-8">
                <!-- Park Name and Lot Title -->
                <div class="mb-3">
                    <a href="<?php echo home_url(); ?>" class="text-success text-decoration-none">Little Star RV Park</a>
                    <h1 class="h3 mt-1"><?php echo esc_html($lot->post_title); ?></h1>
                </div>

                <!-- Images -->
                <div class="mb-4">
                    <!-- Main Image -->
                    <?php if (has_post_thumbnail($post_id)) : ?>
                        <img src="<?php echo get_the_post_thumbnail_url($post_id, 'large'); ?>" class="img-fluid rounded mb-2" alt="<?php echo esc_attr($lot->post_title); ?>">
                    <?php else : ?>
                        <div class="bg-light text-center p-5 rounded mb-2">No Image Available</div>
                    <?php endif; ?>

                    <!-- Thumbnails -->
                    <div class="d-flex gap-2">
                        <?php
                        $additional_images = get_post_meta($post_id, '_rv_lot_images', true);
                        $thumbnail_count = 0;
                        if ($additional_images && is_array($additional_images)) :
                            foreach ($additional_images as $image_id) :
                                if ($thumbnail_count >= 3) break;
                                $thumbnail_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                if ($thumbnail_url) :
                        ?>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" class="img-fluid rounded" style="width: 100px; height: 75px; object-fit: cover;" alt="Thumbnail">
                        <?php
                                    $thumbnail_count++;
                                endif;
                            endforeach;
                        endif;
                        ?>
                        <?php if ($thumbnail_count > 0) : ?>
                            <button class="btn btn-outline-secondary btn-sm align-self-center">View <?php echo $thumbnail_count; ?> Photos</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Overview Section -->
                <div class="mb-4">
                    <h2 class="h5">Overview</h2>
                    <h3 class="h6">Site Amenities</h3>
                    <div class="d-flex flex-wrap gap-3">
                        <?php
                        if ($amenities && !is_wp_error($amenities)) :
                            foreach ($amenities as $amenity) :
                                $icon = '';
                                switch (strtolower($amenity->name)) {
                                    case '30-amp':
                                        $icon = '<i class="bi bi-plug"></i>';
                                        break;
                                    case '50-amp':
                                        $icon = '<i class="bi bi-plug-fill"></i>';
                                        break;
                                    case 'back-in':
                                        $icon = '<i class="bi bi-arrow-left-circle"></i>';
                                        break;
                                    case 'charcoal grill':
                                        $icon = '<i class="bi bi-fire"></i>';
                                        break;
                                    case 'electricity':
                                        $icon = '<i class="bi bi-lightning"></i>';
                                        break;
                                    case 'picnic table':
                                        $icon = '<i class="bi bi-table"></i>';
                                        break;
                                    case 'sewer hook-up':
                                        $icon = '<i class="bi bi-water"></i>';
                                        break;
                                    case 'water hook-up':
                                        $icon = '<i class="bi bi-droplet"></i>';
                                        break;
                                    case 'wi-fi':
                                        $icon = '<i class="bi bi-wifi"></i>';
                                        break;
                                    case '20-amp':
                                        $icon = '<i class="bi bi-plug"></i>';
                                        break;
                                    default:
                                        $icon = '<i class="bi bi-check-circle"></i>';
                                }
                        ?>
                                <div>
                                    <?php echo $icon; ?> <?php echo esc_html($amenity->name); ?>
                                </div>
                        <?php
                            endforeach;
                        else :
                            echo '<p>No amenities available.</p>';
                        endif;
                        ?>
                    </div>
                </div>
            </div>

            <!-- Right Section: Booking Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm p-4 sticky-top" style="top: 20px;">
                    <form id="booking-form">
                        <!-- Step 1: Trip Details -->
                        <div class="mb-4">
                            <h3 class="h6 text-muted">1. Trip Details</h3>
                            <input type="hidden" name="campsite" value="<?php echo $post_id; ?>">
                            <div class="mb-1 calendar-container">
                                <label class="form-label">Dates</label>
                                <div id="dateDisplay" class="date-display">
                                    <span id="checkInText"><?php echo $check_in_formatted ?: 'Check In'; ?></span>
                                    <span style='color: black;'>→</span>
                                    <span id="checkOutText"><?php echo $check_out_formatted ?: 'Check Out'; ?></span>
                                </div>
                                <p id="dateError" class="m-0" style="color: red;"></p>
                                <input type="text" id="dateRange" style="position: absolute; opacity: 0; height: 0; width: 0; padding: 0; border: none;">
                                <div class="hidden-inputs">
                                    <input type="hidden" id="check_in" name="check_in" value="<?php echo esc_attr($check_in ?: ''); ?>">
                                    <input type="hidden" id="check_out" name="check_out" value="<?php echo esc_attr($check_out ?: ''); ?>">
                                </div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label class="form-label">Guests</label>
                                <button id="guestDropdownBtn" type="button" class="form-select text-start">
                                    <span id="guestSummary">2 Adults, 2 Children, 2 Pets</span>
                                </button>
                                <div id="guestDropdown" class="card shadow-sm p-3 position-absolute bg-white d-none" style="z-index: 1000;">
                                    <h6>Number of Guests</h6>
                                    <div id="adultsWrapper" class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Adults</span>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('adults', -1)">-</button>
                                            <input id="adultsCount" type="text" class="form-control text-center" value="2" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('adults', 1)">+</button>
                                        </div>
                                    </div>
                                    <div id="childrenWrapper" class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Children</span>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('children', -1)">-</button>
                                            <input id="childrenCount" type="text" class="form-control text-center" value="2" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('children', 1)">+</button>
                                        </div>
                                    </div>
                                    <div id="petsWrapper" class="d-flex justify-content-between align-items-center">
                                        <span>Pets</span>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('pets', -1)">-</button>
                                            <input id="petsCount" type="text" class="form-control text-center" value="2" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('pets', 1)">+</button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="adultsInput" name="adults" value="2">
                                <input type="hidden" id="childrenInput" name="children" value="2">
                                <input type="hidden" id="petsInput" name="pets" value="2">
                            </div>

                            <script>
                                document.getElementById("guestDropdownBtn").addEventListener("click", function(event) {
                                    event.stopPropagation();
                                    document.getElementById("guestDropdown").classList.toggle("d-none");
                                });

                                document.addEventListener("click", function(event) {
                                    const dropdown = document.getElementById("guestDropdown");
                                    if (!dropdown.classList.contains("d-none") && !dropdown.contains(event.target) && event.target.id !== "guestDropdownBtn") {
                                        dropdown.classList.add("d-none");
                                    }
                                });

                                function updateGuests(type, change) {
                                    const input = document.getElementById(`${type}Count`);
                                    const hiddenInput = document.getElementById(`${type}Input`);
                                    const wrapper = document.getElementById(`${type}Wrapper`);
                                    
                                    let newValue = parseInt(input.value) + change;
                                    if (newValue < 0) newValue = 0;
                                    
                                    input.value = newValue;
                                    hiddenInput.value = newValue;
                                    
                                    wrapper.style.display = newValue > 0 ? 'flex' : 'none';
                                    updateGuestSummary();
                                }

                                function updateGuestSummary() {
                                    const adults = document.getElementById("adultsCount").value;
                                    const children = document.getElementById("childrenCount").value;
                                    const pets = document.getElementById("petsCount").value;
                                    
                                    let summary = [];
                                    if (adults > 0) summary.push(`${adults} Adults`);
                                    if (children > 0) summary.push(`${children} Children`);
                                    if (pets > 0) summary.push(`${pets} Pets`);
                                    
                                    document.getElementById("guestSummary").innerText = summary.length ? summary.join(", ") : "Select Guests";
                                }
                            </script>
                        </div>

                        <!-- Step 2: Equipment Details -->
                        <div class="mb-4">
                            <h3 class="h6 text-muted">2. Equipment Details</h3>
                            <div class="mb-3">
                                <label class="form-label">Equipment Type</label>
                                <select class="form-select" id="equipment_type" name="equipment_type">
                                    <option value="">Select Equipment Type</option>
                                    <option value="rv">RV</option>
                                    <option value="tent">Tent</option>
                                    <option value="trailer">Trailer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Length (ft)</label>
                                <input type="number" class="form-control" id="length_ft" name="length_ft" min="0" placeholder="e.g., 30" value="<?php echo esc_attr($length_ft ?: ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slide-Outs</label>
                                <select class="form-select" id="slide_outs" name="slide_outs">
                                    <option value="">Select Slide-Outs</option>
                                    <option value="0">Slide-Outs</option>
                                    <option value="1">1 Slide-Out</option>
                                    <option value="2">2 Slide-Outs</option>
                                    <option value="3">3 Slide-Outs</option>
                                </select>
                            </div>
                        </div>

                        <!-- Step 3: Choose Your Site -->
                        <div class="mb-4">
                            <h3 class="h6 text-muted">3. Choose Your Site</h3>
                            <div class="mb-3">
                                <label class="form-label">Site Location</label>
                                <input type="text" class="form-control" id="site_location" name="site_location" value="<?php echo esc_attr($lot->post_title ?: ''); ?>" placeholder="e.g., Lot #5">
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="mb-4">
                            <?php
                            $nightly_rate = $price ?: 0;
                            $subtotal = $nightly_rate * ($nights ?: 1);
                            $weekly_rate = 0; // Adjust if you have a weekly rate logic
                            $campground_fees = 5.00; // Example fee
                            $total = $subtotal + $campground_fees;
                            ?>
                            <div class="d-flex justify-content-between">
                                <span class="night-price">$<?php echo number_format($nightly_rate, 2); ?> x <?php echo ($nights ?: 1); ?> Nights</span>
                                <span class="night-subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <?php if ($weekly_rate > 0) : ?>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>Weekly Rate</span>
                                    <span>-$<?php echo number_format($weekly_rate, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between text-muted">
                                <span>Campground Fees</span>
                                <span>$<?php echo number_format($campground_fees, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Site Total</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <input type="hidden" name="room_title" value="<?php echo esc_attr($lot->post_title ?: ''); ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .calendar-container {
            background: white;
            padding: 0;
            border-radius: 8px;
            text-align: center;
            position: relative;
        }

        .date-display {
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            background: white;
            color: #999;
            font-size: 16px;
        }

        .date-display.active {
            color: black;
        }

        .date-display span {
            margin: 0 5px;
            color: black;
        }

        .hidden-inputs {
            display: none;
        }

        .flatpickr-calendar {
            top: 100% !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }
    </style>

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
            let isAvailable = false;

            // Pass PHP variables to JS
            const nightlyRate = <?php echo json_encode($price); ?>;
            const campgroundFees = <?php echo json_encode($campground_fees); ?>;
            const campsiteId = <?php echo json_encode($post_id); ?>;
        const initialAdults = <?php echo json_encode($adults); ?>;
        const initialChildren = <?php echo json_encode($children); ?>;

            // update the url date picker with the selected date checkout and checkin
           // Function to format date as YYYY-MM-DD
        function getISODate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
          
// Function to update URL
function updateURL(check_in, check_out) {
            const url = new URL(window.location.href);
            url.searchParams.set('check_in', getISODate(check_in));
            url.searchParams.set('check_out', getISODate(check_out));
            url.searchParams.set('campsite', campsiteId);
            url.searchParams.set('adults', initialAdults);
            url.searchParams.set('children', initialChildren);
            window.history.pushState({}, document.title, url.toString());
        }

            // Function to update price breakdown
            function updatePriceBreakdown(check_in, check_out) {
                const date1 = new Date(check_in);
                const date2 = new Date(check_out);
                const nights = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24)) || 1;
                
                const subtotal = nightlyRate * nights;
                const total = subtotal + campgroundFees;

                $('.d-flex.justify-content-between').eq(0).html(
                    `<span>$${nightlyRate.toFixed(2)} x ${nights} Nights</span><span>$${subtotal.toFixed(2)}</span>`
                );
                $('.d-flex.justify-content-between.fw-bold').html(
                    `<span>Site Total</span><span>$${total.toFixed(2)}</span>`
                );
                $('.night-price').text(`$${nightlyRate.toFixed(2)} x ${nights} Nights`);
                $('.night-subtotal').text(`$${subtotal.toFixed(2)}`);
            }

            // Initialize Flatpickr
            window.fpInstance = flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                defaultDate: [<?php echo $check_in ? "'$check_in'" : 'null'; ?>, <?php echo $check_out ? "'$check_out'" : 'null'; ?>],
                onChange: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        const formatDate = date => {
                            return date.toLocaleDateString('en-US', {
                                weekday: 'short',
                                month: 'short',
                                day: '2-digit'
                            });
                        };

                        const getISODate = date => {
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            return `${year}-${month}-${day}`;
                        };

                        const check_in = selectedDates[0];
                        const check_out = selectedDates[1];

                        $('#checkInText').text(formatDate(check_in));
                        $('#checkOutText').text(formatDate(check_out));
                        $('#check_in').val(getISODate(check_in));
                        $('#check_out').val(getISODate(check_out));

                        // Update price breakdown
                        updatePriceBreakdown(check_in, check_out);
// Update URL
updateURL(check_in, check_out);
                        // Availability check
                        $.ajax({
                            url: rvbs_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'rvbs_check_availability',
                                nonce: rvbs_ajax.nonce,
                                post_id: $('input[name="post_id"]').val(),
                                check_in: check_in,
                                check_out: check_out,
                                filter: 'booknowpage'
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#add-to-cart-btn').prop('disabled', false).text('Add to Cart');
                                    if (response.data.html == 'available') {
                                        $('#dateError').text('Available for this date');
                                        $('#dateError').css('color', 'green');
                                        isAvailable = true;
                                    } else {
                                        $('#dateError').text('Not available for this date');
                                        $('#dateError').css('color', 'red');
                                        isAvailable = false;
                                        $('#dateRange').focus();
                                        window.fpInstance.open();
                                    }
                                } else {
                                    $('#add-to-cart-btn').prop('disabled', true).text('Unavailable');
                                    $('#dateError').text('Not available for this date');
                                    $('#dateError').css('color', 'red');
                                    isAvailable = false;
                                    $('#dateRange').focus();
                                    window.fpInstance.open();
                                }
                            },
                            error: function() {
                                $('#dateError').text('Error checking availability');
                                $('#dateError').css('color', 'red');
                                isAvailable = false;
                                $('#dateRange').focus();
                                window.fpInstance.open();
                            }
                        });
                    }
                },
                onClose: function(selectedDates) {
                    // Keep your existing onClose logic if any
                },
                appendTo: document.querySelector('.calendar-container')
            });

            $('#dateDisplay').on('click', function() {
                window.openCalendar();
            });

            // Handle guests selection
            $('#guests').on('change', function() {
                const value = $(this).val();
                $('#adults').val(value);
                $('#children').val(0);
            });
        });
    </script>

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