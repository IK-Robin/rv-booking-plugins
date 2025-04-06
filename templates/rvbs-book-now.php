<?php
/*
Template Name: Book Now
*/

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Check URL parameters
$post_id = isset($_GET['campsite']) ? intval($_GET['campsite']) : 0;
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Redirect if required parameters missing
if (!$post_id || !$check_in || !$check_out) {
    wp_redirect(home_url('/search-rv/'));
    exit;
}




// Validate and sanitize dates
function sanitize_and_validate_date($date_str, $is_check_out = false, $check_in_str = '') {
    try {
        $date = new DateTime($date_str);
        // Ensure date is not before today
        $today = new DateTime('today');
        if ($date < $today) {
            return $today;
        }
        return $date;
    } catch (Exception $e) {
        // If invalid, fallback to a valid date
        $today = new DateTime('today');
        if ($is_check_out && $check_in_str) {
            // For check_out, fallback to check_in + 1 day if check_in is valid
            try {
                $check_in_date = new DateTime($check_in_str);
                $check_in_date->modify('+1 day');
                return $check_in_date;
            } catch (Exception $e) {
                return $today;
            }
        }
        return $today;
    }
}

// Process dates
$check_in_date = sanitize_and_validate_date($check_in, false);
$check_out_date = sanitize_and_validate_date($check_out, true, $check_in);

// Ensure check_out is after check_in
if ($check_out_date <= $check_in_date) {
    $check_out_date = clone $check_in_date;
    $check_out_date->modify('+1 day');
}

// Convert back to strings for use in the template
$check_in = $check_in_date->format('Y-m-d');
$check_out = $check_out_date->format('Y-m-d');

// Fetch RV lot post and redirect if invalid
$lot = get_post($post_id);
if (!$lot || $lot->post_type !== 'rv-lots') {
    wp_redirect(home_url('/search-rv/'));
    exit;
}

// Determine if we should load session data
$session_data = [];
$use_session_data = false;

// Check if URL has campsite or edit, and if session has matching campsite
if ((isset($_GET['campsite']) || $edit_mode) && 
    isset($_SESSION['cart']) && 
    is_array($_SESSION['cart']) && 
    isset($_SESSION['cart'][$post_id])) {
    $session_data = $_SESSION['cart'][$post_id];
    $use_session_data = true;
}

// Set values from session if conditions met, otherwise from URL or defaults
$adults = $use_session_data && isset($session_data['adults']) ? intval($session_data['adults']) : (isset($_GET['adults']) ? intval($_GET['adults']) : 1);
$children = $use_session_data && isset($session_data['children']) ? intval($session_data['children']) : (isset($_GET['children']) ? intval($_GET['children']) : 0);
$pets = $use_session_data && isset($session_data['pets']) ? intval($session_data['pets']) : (isset($_GET['pets']) ? intval($_GET['pets']) : 0);
$equipment_type = $use_session_data && isset($session_data['equipment_type']) ? sanitize_text_field($session_data['equipment_type']) : (isset($_GET['equipment_type']) ? sanitize_text_field($_GET['equipment_type']) : '');
$length_ft = $use_session_data && isset($session_data['length_ft']) ? intval($session_data['length_ft']) : (isset($_GET['length_ft']) ? intval($_GET['length_ft']) : '');
$slide_outs = $use_session_data && isset($session_data['slide_outs']) ? sanitize_text_field($session_data['slide_outs']) : (isset($_GET['slide_outs']) ? sanitize_text_field($_GET['slide_outs']) : '');
$site_location = $use_session_data && isset($session_data['site_location']) ? sanitize_text_field($session_data['site_location']) : '';
$check_in = $use_session_data && isset($session_data['check_in']) ? sanitize_text_field($session_data['check_in']) : $check_in;
$check_out = $use_session_data && isset($session_data['check_out']) ? sanitize_text_field($session_data['check_out']) : $check_out;

// Check if theme is block-based
$is_fse_theme = wp_is_block_theme();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    wp_head();
    if ($is_fse_theme) {
        $global_styles = file_get_contents(get_template_directory() . '/style.css');
        echo '<style>' . $global_styles . '</style>';
    }
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
</head>

<body <?php body_class(); ?>>
    <?php
    if (!$is_fse_theme) {
        get_header();
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
    }

    // Fetch RV lot post
    $lot = get_post($post_id);
    if (!$lot || $lot->post_type !== 'rv-lots') {
        wp_die('Invalid RV lot.');
    }

    // Get amenities, features, and price
    $amenities = get_the_terms($post_id, 'site_amenity');
    $features = get_the_terms($post_id, 'park_feature');
    $price = floatval(get_post_meta($post_id, '_rv_lots_price', true)) ?: 20.00;

    // Calculate nights
    $nights = $check_in && $check_out ? (new DateTime($check_in))->diff(new DateTime($check_out))->days : 0;
    $check_in_formatted = $check_in ? (new DateTime($check_in))->format('D, M d') : 'Check In';
    $check_out_formatted = $check_out ? (new DateTime($check_out))->format('D, M d') : 'Check Out';

    // Determine button text based on whether campsite exists in cart
    $button_text = $use_session_data ? 'Update' : 'Add to Cart';
    ?>

    <div class="container my-5">
        <div class="row">
            <!-- Left Section: Lot Details -->
            <div class="col-lg-8">
                <div class="mb-3">
                    <a href="<?php echo home_url(); ?>" class="text-success text-decoration-none">Little Star RV Park</a>
                    <h1 class="h3 mt-1"><?php echo esc_html($lot->post_title); ?></h1>
                </div>
                <div class="mb-4">
                    <?php if (has_post_thumbnail($post_id)) : ?>
                        <img src="<?php echo get_the_post_thumbnail_url($post_id, 'large'); ?>" class="img-fluid rounded mb-2" alt="<?php echo esc_attr($lot->post_title); ?>">
                    <?php else : ?>
                        <div class="bg-light text-center p-5 rounded mb-2">No Image Available</div>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                        <?php
                        $additional_images = get_post_meta($post_id, '_rv_lot_images', true);
                        $thumbnail_count = 0;
                        if ($additional_images && is_array($additional_images)) :
                            foreach ($additional_images as $image_id) :
                                if ($thumbnail_count >= 3) break;
                                $thumbnail_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                if ($thumbnail_url) :
                                    $thumbnail_count++;
                        ?>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>" class="img-fluid rounded" style="width: 100px; height: 75px; object-fit: cover;" alt="Thumbnail">
                        <?php endif; endforeach; endif; ?>
                        <?php if ($thumbnail_count > 0) : ?>
                            <button class="btn btn-outline-secondary btn-sm align-self-center">View <?php echo $thumbnail_count; ?> Photos</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-4">
                    <h2 class="h5">Overview</h2>
                    <h3 class="h6">Site Amenities</h3>
                    <div class="d-flex flex-wrap gap-3">
                        <?php
                        if ($amenities && !is_wp_error($amenities)) :
                            foreach ($amenities as $amenity) :
                                $icon = '';
                                switch (strtolower($amenity->name)) {
                                    case '30-amp': $icon = '<i class="bi bi-plug"></i>'; break;
                                    case '50-amp': $icon = '<i class="bi bi-plug-fill"></i>'; break;
                                    case 'back-in': $icon = '<i class="bi bi-arrow-left-circle"></i>'; break;
                                    case 'charcoal grill': $icon = '<i class="bi bi-fire"></i>'; break;
                                    case 'electricity': $icon = '<i class="bi bi-lightning"></i>'; break;
                                    case 'picnic table': $icon = '<i class="bi bi-table"></i>'; break;
                                    case 'sewer hook-up': $icon = '<i class="bi bi-water"></i>'; break;
                                    case 'water hook-up': $icon = '<i class="bi bi-droplet"></i>'; break;
                                    case 'wi-fi': $icon = '<i class="bi bi-wifi"></i>'; break;
                                    case '20-amp': $icon = '<i class="bi bi-plug"></i>'; break;
                                    default: $icon = '<i class="bi bi-check-circle"></i>';
                                }
                        ?>
                                <div><?php echo $icon; ?> <?php echo esc_html($amenity->name); ?></div>
                        <?php endforeach; else : ?>
                            <p>No amenities available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Section: Booking Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm p-4 sticky-top" style="top: 20px;">
                    <form id="booking-form">
                        <div class="mb-4">
                            <h3 class="h6 text-muted">1. Trip Details</h3>
                            <input type="hidden" name="campsite" value="<?php echo $post_id; ?>">
                            <div class="mb-1 calendar-container">
                                <label class="form-label">Dates</label>
                                <div id="dateDisplay" class="date-display">
                                    <span id="checkInText"><?php echo $check_in_formatted; ?></span>
                                    <span style="color: black;">→</span>
                                    <span id="checkOutText"><?php echo $check_out_formatted; ?></span>
                                </div>
                                <p id="dateError" class="m-0" style="color: red;"></p>
                                <input type="text" id="dateRange" style="position: absolute; opacity: 0; height: 0; width: 0; padding: 0; border: none;">
                                <div class="hidden-inputs">
                                    <input type="hidden" id="check_in" name="check_in" value="<?php echo esc_attr($check_in); ?>">
                                    <input type="hidden" id="check_out" name="check_out" value="<?php echo esc_attr($check_out); ?>">
                                </div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label class="form-label">Guests</label>
                                <button id="guestDropdownBtn" type="button" class="form-select text-start">
                                    <span id="guestSummary"><?php echo "$adults Adults, $children Children, $pets Pets"; ?></span>
                                </button>
                                <div id="guestDropdown" class="card shadow-sm p-3 position-absolute bg-white d-none" style="z-index: 1000;">
                                    <h6>Number of Guests</h6>
                                    <div id="adultsWrapper" class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Adults</span>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('adults', -1)">-</button>
                                            <input id="adultsCount" type="text" class="form-control text-center" value="<?php echo $adults; ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('adults', 1)">+</button>
                                        </div>
                                    </div>
                                    <div id="childrenWrapper" class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Children</span>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('children', -1)">-</button>
                                            <input id="childrenCount" type="text" class="form-control text-center" value="<?php echo $children; ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('children', 1)">+</button>
                                        </div>
                                    </div>
                                    <div id="petsWrapper" class="d-flex justify-content-between align-items-center">
                                        <span>Pets</span>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('pets', -1)">-</button>
                                            <input id="petsCount" type="text" class="form-control text-center" value="<?php echo $pets; ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateGuests('pets', 1)">+</button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="adultsInput" name="adults" value="<?php echo $adults; ?>">
                                <input type="hidden" id="childrenInput" name="children" value="<?php echo $children; ?>">
                                <input type="hidden" id="petsInput" name="pets" value="<?php echo $pets; ?>">
                            </div>
                        </div>

                        <!-- Step 2: Equipment Details -->
                        <div class="mb-4">
                            <h3 class="h6 text-muted">2. Equipment Details</h3>
                            <div class="mb-3">
                                <label class="form-label">Equipment Type</label>
                                <select class="form-select" id="equipment_type" name="equipment_type">
                                    <option value="">Select Equipment Type</option>
                                    <option value="rv" <?php echo $equipment_type === 'rv' ? 'selected' : ''; ?>>RV</option>
                                    <option value="tent" <?php echo $equipment_type === 'tent' ? 'selected' : ''; ?>>Tent</option>
                                    <option value="trailer" <?php echo $equipment_type === 'trailer' ? 'selected' : ''; ?>>Trailer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Length (ft)</label>
                                <input type="number" class="form-control" id="length_ft" name="length_ft" min="0" placeholder="e.g., 30" value="<?php echo esc_attr($length_ft); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slide-Outs</label>
                                <select class="form-select" id="slide_outs" name="slide_outs">
                                    <option value="">Select Slide-Outs</option>
                                    <option value="0" <?php echo $slide_outs === '0' ? 'selected' : ''; ?>>0 Slide-Outs</option>
                                    <option value="1" <?php echo $slide_outs === '1' ? 'selected' : ''; ?>>1 Slide-Out</option>
                                    <option value="2" <?php echo $slide_outs === '2' ? 'selected' : ''; ?>>2 Slide-Outs</option>
                                    <option value="3" <?php echo $slide_outs === '3' ? 'selected' : ''; ?>>3 Slide-Outs</option>
                                </select>
                            </div>
                        </div>

                        <!-- Step 3: Choose Your Site -->
                        <div class="mb-4">
                            <h3 class="h6 text-muted">3. Choose Your Site</h3>
                            <div class="mb-3">
                                <label class="form-label">Site Location</label>
                                <input type="text" class="form-control" id="site_location" name="site_location" value="<?php echo esc_attr($site_location ?: $lot->post_title); ?>" placeholder="e.g., Lot #5">
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="mb-4">
                            <?php
                            $nightly_rate = $price;
                            $subtotal = $nightly_rate * ($nights ?: 1);
                            $campground_fees = 5.00;
                            $total = $subtotal + $campground_fees;
                            ?>
                            <div class="d-flex justify-content-between">
                                <span class="night-price">$<?php echo number_format($nightly_rate, 2); ?> x <?php echo ($nights ?: 1); ?> Nights</span>
                                <span class="night-subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
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

                        <!-- Add to Cart / Update Button -->
                        <button type="submit" class="btn btn-success w-100" id="submit-btn"><?php echo $button_text; ?></button>
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <input type="hidden" name="room_title" value="<?php echo esc_attr($lot->post_title); ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .calendar-container { background: white; padding: 0; border-radius: 8px; text-align: center; position: relative; }
        .date-display { display: flex; justify-content: center; align-items: center; border: 1px solid #ccc; border-radius: 5px; padding: 10px; width: 100%; cursor: pointer; background: white; color: #999; font-size: 16px; }
        .date-display.active { color: black; }
        .date-display span { margin: 0 5px; color: black; }
        .hidden-inputs { display: none; }
        .flatpickr-calendar { top: 100% !important; left: 50% !important; transform: translateX(-50%) !important; }
        .flatpickr-day.disabled {
    background-color: #ffcccc !important; /* Light red background */
    color: #333 !important; /* Dark text for contrast */
    cursor: not-allowed;

}

.flatpickr-day.disabled:hover {
    background-color: #ff9999 !important; /* Slightly darker red on hover */
}
.flatpickr-day.nextMonthDay{
    color: #000 !important;
}
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

    <?php
    

    if (!$is_fse_theme) {
        get_footer();
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"footer"} /-->');
    }
    wp_footer();
    ?>
</body>
</html>