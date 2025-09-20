jQuery(document).ready(function($) {
    $('#rvb-pay-button').on('click', function(e) {
        e.preventDefault();

        let bookingId = $(this).data('booking-id');
        let amount    = $(this).data('amount');

       window.location.href = '<?php echo esc_url(home_url('/')); ?>?rvb_payment=yes&amount=' + amount + '&booking_id=' + bookingId;

    });
});
