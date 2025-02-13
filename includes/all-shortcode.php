<?php


function booking_form_check_availability() {
    ob_start(); // Start output buffering

    ?>
    <!-- Check Availability Form -->
    <div class="container bg-white avabality_form"> 
        <div class="row">
            <div class="col-lg-12 bg-white shadow p-4 rounded">
                <h5 class="mb-4">Check Booking Availability</h5>
                <form action="rooms.php" method="GET">
                    <div class="row align-items-end">
                        <div class="col-lg-3 mb-3">
                            <label class="form-label">Check-in Date</label>
                            <input required type="date" class="form-control shadow-none" name="checkIn">
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label">Checkout Date</label>
                            <input required type="date" class="form-control shadow-none" name="check_out">
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label class="form-label">Adults</label>
                            <select name="adult" class="form-select shadow-none">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="col-lg-2 mb-3">
                            <label class="form-label">Children</label>
                            <select name="children" class="form-select shadow-none">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <input type="hidden" name="check_availability">
                        <div class="col-lg-1 mb-lg-3 ps-0 mt-3">
                            <button type="submit" class="btn btn-primary shadow-none">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean(); // Return the buffered output
}

// Register the shortcode
add_shortcode('booking_form_check_availability', 'booking_form_check_availability');


?>
