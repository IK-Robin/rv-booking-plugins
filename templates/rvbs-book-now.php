<?php
/*
Template Name: Book Now
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

// Get the post ID and query parameters from the URL
$post_id = isset($_GET['campsite']) ? intval($_GET['campsite']) : 0;
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
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
                    // Assuming you have additional images stored in a meta field (e.g., '_rv_lot_images')
                    $additional_images = get_post_meta($post_id, '_rv_lot_images', true);
                    $thumbnail_count = 0;
                    if ($additional_images && is_array($additional_images)) :
                        foreach ($additional_images as $image_id) :
                            if ($thumbnail_count >= 3) break; // Limit to 3 thumbnails
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
                            // Map amenity names to icons (you'll need to add your own icons)
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
                        <div class="mb-3 calendar-container">
                            <label class="form-label">Dates</label>
                            <div id="dateDisplay" class="date-display">
                                <span id="checkInText"><?php echo $check_in_formatted; ?></span>
                                <span style='color: black;'>→</span>
                                <span id="checkOutText"><?php echo $check_out_formatted; ?></span>
                            </div>
                            <input type="text" id="dateRange" style="position: absolute; opacity: 0; height: 0; width: 0; padding: 0; border: none;">
                            <div class="hidden-inputs">
                                <input type="hidden" id="check_in" name="check_in" value="<?php echo esc_attr($check_in); ?>">
                                <input type="hidden" id="check_out" name="check_out" value="<?php echo esc_attr($check_out); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Guests</label>
                            <select class="form-select" id="guests" name="guests">
                                <option value="<?php echo $adults; ?>" selected><?php echo $adults; ?> Adults<?php echo $children ? ', ' . $children . ' Children' : ''; ?></option>
                                <option value="1">1 Adult</option>
                                <option value="2">2 Adults</option>
                                <option value="3">3 Adults</option>
                                <option value="4">4 Adults</option>
                            </select>
                            <input type="hidden" id="adults" name="adults" value="<?php echo esc_attr($adults); ?>">
                            <input type="hidden" id="children" name="children" value="<?php echo esc_attr($children); ?>">
                        </div>
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
                            <input type="number" class="form-control" id="length_ft" name="length_ft" min="0" placeholder="e.g., 30">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slide-Outs</label>
                            <select class="form-select" id="slide_outs" name="slide_outs">
                                <option value="">Select Slide-Outs</option>
                                <option value="0">0 Slide-Outs</option>
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
                            <input type="text" class="form-control" id="site_location" name="site_location" value="<?php echo esc_attr($lot->post_title); ?>" placeholder="e.g., Lot #5">
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="mb-4">
                        <?php
                        $nightly_rate = $price;
                        $subtotal = $nightly_rate * $nights;
                        $weekly_rate = 0; // Adjust if you have a weekly rate logic
                        $campground_fees = 5.00; // Example fee
                        $total = $subtotal + $campground_fees;
                        ?>
                        <div class="d-flex justify-content-between">
                            <span>$<?php echo number_format($nightly_rate, 2); ?> x <?php echo $nights; ?> Nights</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
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
                    <input type="hidden" name="room_title" value="<?php echo esc_attr($lot->post_title); ?>">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap CSS and Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<!-- jQuery and Bootstrap JS (already included in WordPress) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

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

<script>
window.openCalendar = function() {
    if (window.fpInstance) {
        window.fpInstance.open();
    }
};


window.openCalendar = function() {
    if (window.fpInstance && typeof window.fpInstance.open === 'function') {
        window.fpInstance.open();
    } else {
        console.error('Flatpickr instance not initialized or open method not available.');
    }
};

jQuery(document).ready(function($) {
    // Initialize Flatpickr once
    window.fpInstance = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: [<?php echo $check_in ? "'$check_in'" : 'null'; ?>, <?php echo $check_out ? "'$check_out'" : 'null'; ?>],
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                const getISODate = date => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                const check_in = getISODate(selectedDates[0]);
                const check_out = getISODate(selectedDates[1]);

                // Update hidden inputs
                $('#check_in').val(check_in);
                $('#check_out').val(check_out);

                // Check availability
                $.ajax({
                    url: rvbs_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'rvbs_check_availability',
                        nonce: rvbs_ajax.nonce,
                        post_id: $('input[name="post_id"]').val(),
                        check_in: check_in,
                        check_out: check_out
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#add-to-cart-btn').prop('disabled', false).text('Add to Cart');
                        } else {
                            $('#add-to-cart-btn').prop('disabled', true).text('Unavailable');
                            alert('This lot is not available for the selected dates.');
                        }
                    },
                    error: function() {
                        alert('Error checking availability.');
                    }
                });
            }
        },
        onClose: function(selectedDates) {
            if (selectedDates.length === 2) {
                const formatDate = date => date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit' });
                document.querySelector("#checkInText").textContent = formatDate(selectedDates[0]);
                document.querySelector("#checkOutText").textContent = formatDate(selectedDates[1]);
                document.querySelector("#dateDisplay").classList.add("active");
                
                const getISODate = date => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };
                
                $('#check_in').val(getISODate(selectedDates[0]));
                $('#check_out').val(getISODate(selectedDates[1]));
                
                // Recalculate nights and update price breakdown
                const date1 = new Date(getISODate(selectedDates[0]));
                const date2 = new Date(getISODate(selectedDates[1]));
                const nights = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24));
                const price = <?php echo $price; ?>;
                const subtotal = price * nights;
                const campground_fees = 5.00;
                const total = subtotal + campground_fees;

                $('.d-flex:contains("Nights")').html(`<span>$${price.toFixed(2)} x ${nights} Nights</span><span>$${subtotal.toFixed(2)}</span>`);
                $('.d-flex:contains("Site Total") span:nth-child(2)').text('$' + total.toFixed(2));
            }
        },
        appendTo: document.querySelector('.calendar-container')
    });

    // Attach click event to dateDisplay
    $('#dateDisplay').on('click', function() {
        window.openCalendar();
    });

    // Handle form submission
    $('#booking-form').on('submit', function(e) {
        e.preventDefault();

        const post_id = $('input[name="post_id"]').val();
        const room_title = $('input[name="room_title"]').val();
        const check_in = $('#check_in').val();
        const check_out = $('#check_out').val();
        const adults = $('#adults').val();
        const children = $('#children').val();
        const equipment_type = $('#equipment_type').val();
        const length_ft = $('#length_ft').val();
        const slide_outs = $('#slide_outs').val();
        const site_location = $('#site_location').val();

        // Basic validation
        if (!check_in || !check_out) {
            alert('Please select check-in and check-out dates.');
            return;
        }
        if (!equipment_type) {
            alert('Please select an equipment type.');
            return;
        }
        if (!length_ft) {
            alert('Please enter the equipment length.');
            return;
        }
        if (!slide_outs) {
            alert('Please select the number of slide-outs.');
            return;
        }
        if (!site_location) {
            alert('Please enter the site location.');
            return;
        }

        $.ajax({
            url: rvbs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rvbs_book_lot',
                nonce: rvbs_ajax.nonce,
                lot_id: post_id,
                post_id: post_id,
                room_title: room_title,
                check_in: check_in,
                check_out: check_out,
                adults: adults,
                children: children,
                equipment_type: equipment_type,
                length_ft: length_ft,
                slide_outs: slide_outs,
                site_location: site_location
            },
            beforeSend: function() {
                $('#booking-form button[type="submit"]').text('Processing...').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    alert('Booking added to cart successfully!\nRoom: ' + room_title);
                    // Optionally redirect to a cart page
                    // window.location.href = '<?php echo home_url('/cart'); ?>';
                } else {
                    alert('Booking failed: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while booking.');
            },
            complete: function() {
                $('#booking-form button[type="submit"]').text('Add to Cart').prop('disabled', false);
            }
        });
    });

    // Handle guests selection
    $('#guests').on('change', function() {
        const value = $(this).val();
        $('#adults').val(value);
        $('#children').val(0); // Update as needed if children are selected
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

<?php wp_footer(); // Ensures scripts & footer styles load ?>