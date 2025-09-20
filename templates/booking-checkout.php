<?php
/**
 * Booking Checkout Template
 */
get_header();
?>
<div class="rvb-checkout-wrapper">
    <h1>Booking Checkout</h1>
    <p>Booking ID: 111</p>
    <p>Total Amount: $1000</p>
    <button id="rvb-pay-button" data-booking-id="111" data-amount="1000">
        Pay with WooCommerce
    </button>
</div>
<?php
get_footer();
