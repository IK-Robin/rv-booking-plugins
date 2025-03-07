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