<?php
/*
Template Name: Booking Confirmation
*/

// Check if the theme is block-based (Full Site Editing)
$is_fse_theme = wp_is_block_theme();

// Start session
if (!session_id()) {
    session_start();
}

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Fetch booking details
global $wpdb;
$booking = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rvbs_bookings WHERE id = %d",
        $booking_id
    )
);

if (!$booking) {
    wp_redirect(home_url());
    exit;
}

// Get user details
$user = get_user_by('id', $booking->user_id);
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
</head>

<body <?php body_class(); ?>>
    <?php
    if (!$is_fse_theme) {
        get_header();
    } else {
        echo do_blocks('<!-- wp:template-part {"slug":"header"} /-->');
    }
    ?>

    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #000;
        }
        p {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }
    </style>

    <div class="confirmation-container">
        <h1>Booking Confirmation</h1>
        <p>Thank you, <?php echo esc_html($user->display_name); ?>, for your booking!</p>
        <p>Booking ID: <?php echo esc_html($booking->id); ?></p>
        <p>Lot: <?php echo esc_html(get_the_title($booking->post_id)); ?></p>
        <p>Check-In: <?php echo esc_html($booking->check_in); ?></p>
        <p>Check-Out: <?php echo esc_html($booking->check_out); ?></p>
        <p>Total Price: $<?php echo number_format($booking->total_price, 2); ?></p>
        <p>Status: <?php echo esc_html(ucfirst($booking->status)); ?></p>
        <p>We’ve sent a confirmation email to <?php echo esc_html($user->user_email); ?>.</p>
    </div>

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