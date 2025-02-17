<?php
/**
 * Template Name: Book Now
 */
session_start();
get_header();
?>

<div class="container-fluid bg-white">
    <div class="container">
        <?php get_template_part('components/header'); ?>
    </div>
</div>

<?php
// Get the post ID from URL
if (!isset($_GET['post_id'])) {
    wp_redirect(home_url('/'));
    exit;
}

$post_id = intval($_GET['post_id']);
$date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');

// Get post details
$post = get_post($post_id);

if (!$post) {
    echo '<div class="container mt-5"><h2 class="text-center text-danger">Post not found.</h2></div>';
    get_footer();
    exit;
}

// Store post data in session
$_SESSION['booking'] = [
    "id" => $post_id,
    "title" => get_the_title($post_id),
    "price" => get_post_meta($post_id, 'price', true),
    "date" => $date
];
?>

<div class="container">
    <div class="row">
        <div class="col-lg-12 px-4">
            <h2 class="pt-3 font-bold merinda"><?php echo esc_html($post->post_title); ?></h2>
            <a href="<?php echo home_url(); ?>" class="text-decoration-none text-secondary">Home</a>
            <span class="text-secondary"> > </span>
            <a href="#" class="text-decoration-none text-secondary">Book Now</a>
        </div>

        <div class="col-lg-7 col-md-12 mt-5">
            <div class="swiper rooms_slider">
                <div class="swiper-wrapper">
                    <?php
                    if (has_post_thumbnail($post_id)) {
                        echo "<div class='swiper-slide'><img width='100%' src='" . esc_url(get_the_post_thumbnail_url($post_id, 'large')) . "' alt=''></div>";
                    } else {
                        echo "<div class='swiper-slide'><img width='100%' src='" . esc_url(get_template_directory_uri() . '/assets/img/default.jpg') . "' alt='No Image'></div>";
                    }
                    ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <div class="col-lg-5 col-md-12 mt-5">
            <div class="card mb-3 border-0 shadow">
                <div class="card-body">
                    <form action="" id="book_now">
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="name_book" class="form-label">Name</label>
                                    <input required value="Robin" type="text" name="name_book" class="form-control shadow-none" id="name_book">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="phone_book" class="form-label">Phone</label>
                                    <input required value="8888" type="text" name="phone_book" class="form-control shadow-none" id="phone_book">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input required value="Kancherkol" type="text" name="address" class="form-control shadow-none" id="address">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Check-in Date</label>
                                    <input type="date" value="<?php echo esc_attr($date); ?>" name="checkin" class="form-control shadow-none" />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Check-out Date</label>
                                    <input type="date" name="checkout" class="form-control shadow-none" />
                                </div>
                            </div>
                            <div class="book-now col-lg-12">
                                <div class="spinner-border text-primary d-none mb-3" role="status" id="pre_loader">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h6 class="mb-3 text-danger" id="pay_info">Provide Check-in and Check-out Date!</h6>
                                <button disabled type="submit" id="book_submit" class="btn btn-primary w-100">Pay Now</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?php echo esc_url(get_template_directory_uri()); ?>/font_end_js/bookings.js"></script>
