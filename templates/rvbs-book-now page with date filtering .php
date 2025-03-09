<?php
/*
Template Name: Book Now
*/
get_header();
?>

<div id="booking-container">
    <h2>Book an RV Lot</h2>
    
    <form id="availability-form">
        <div class="form-group">
            <label for="check_in">Check-in Date:</label>
            <input value="2025/3/06" type="date" id="check_in" name="check_in" required>
        </div>
        <div class="form-group">
            <label for="check_out">Check-out Date:</label>
            <input value="2025/3/14" type="date" id="check_out" name="check_out" required>
        </div>
        <button type="submit" id="check-availability">Check Availability</button>
    </form>

    <div id="availability-results"></div>
</div>

<style>
    .form-group {
        margin: 15px 0;
    }
    #availability-results {
        margin-top: 20px;
    }
    .lot-item {
        border: 1px solid #ddd;
        padding: 15px;
        margin: 10px 0;
    }
    .book-btn {
        background-color: #4CAF50;
        color: white;
        padding: 8px 16px;
        border: none;
        cursor: pointer;
    }
    #availability-form{
        width: 20%;
        margin: 0 auto;
    }
</style>

<script>
    jQuery(document).ready(function($) {
    // Check availability
    $('#availability-form').on('submit', function(e) {
        e.preventDefault();
        
        const check_in = $('#check_in').val();
        const check_out = $('#check_out').val();
        
        if (!check_in || !check_out) {
            alert('Please select both check-in and check-out dates.');
            return;
        }
        
        $.ajax({
            url: rvbs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rvbs_check_availability',
                nonce: rvbs_ajax.nonce,
                check_in: check_in,
                check_out: check_out
            },
            beforeSend: function() {
                $('#availability-results').html('Checking availability...');
            },
            success: function(response) {
                if (response.success) {
                    $('#availability-results').html(response.data.html);
                } else {
                    $('#availability-results').html('Error checking availability.');
                }
            },
            error: function() {
                $('#availability-results').html('An error occurred.');
            }
        });
    });
    
    // Book lot
    $(document).on('click', '.book-btn', function() {
        const lot_id = $(this).data('lot-id');
        const post_id = $(this).data('post-id');
        const check_in = $('#check_in').val();
        const check_out = $('#check_out').val(); 
        
        $.ajax({
            url: rvbs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rvbs_book_lot',
                nonce: rvbs_ajax.nonce,
                lot_id: lot_id,
                post_id: post_id,
                check_in: check_in,
                check_out: check_out
            },
            beforeSend: function() {
                $('#availability-results').html('Processing booking...');
            },
            success: function(response) {
                if (response.success) {
                    $('#availability-results').html(response.data);
                    alert('Booking successful!');
                } else {
                    $('#availability-results').html(response.data);
                    alert('Booking failed: ' + response.data);
                }
            },
            error: function() {
                $('#availability-results').html('An error occurred while booking.');
            }
        });
    });
});
</script>

<?php get_footer(); ?>