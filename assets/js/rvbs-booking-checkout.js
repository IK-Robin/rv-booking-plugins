jQuery(document).ready(function($) {
    $('#rvb-pay-button').on('click', function(e) {
        e.preventDefault();

        let bookingId = $(this).data('booking-id');
        let amount    = $(this).data('amount');

        window.location.href = '/?rvb_payment=yes&amount=' + amount + '&booking_id=' + bookingId;
    });
});
