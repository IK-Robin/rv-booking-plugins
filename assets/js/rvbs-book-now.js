window.openCalendar = function() {
    if (window.fpInstance && typeof window.fpInstance.open === 'function') {
        window.fpInstance.open();
    } else {
        console.error('Flatpickr instance not initialized or open method not available.');
    }
};

function updateGuests(type, change) {
    const input = document.getElementById(`${type}Count`);
    const hiddenInput = document.getElementById(`${type}Input`);
    const wrapper = document.getElementById(`${type}Wrapper`);
    let newValue = parseInt(input.value) + change;
    if (newValue < 0) newValue = 0;
    input.value = newValue;
    hiddenInput.value = newValue;
    wrapper.style.display = newValue > 0 ? 'flex' : 'none';
    updateGuestSummary();
    checkAvailability(jQuery('#check_in').val(), jQuery('#check_out').val(), false); // Use jQuery instead of $
}

function updateGuestSummary() {
    const adults = document.getElementById("adultsCount").value;
    const children = document.getElementById("childrenCount").value;
    const pets = document.getElementById("petsCount").value;
    let summary = [];
    if (adults > 0) summary.push(`${adults} Adults`);
    if (children > 0) summary.push(`${children} Children`);
    if (pets > 0) summary.push(`${pets} Pets`);
    document.getElementById("guestSummary").innerText = summary.length ? summary.join(", ") : "Select Guests";
}

// Define checkAvailability globally
function checkAvailability(check_in, check_out, openCalendarOnFail = false) {
    jQuery.ajax({
        url: bookingData.ajaxUrl,
        type: 'POST',
        data: {
            action: bookingData.checkAvailabilityAction,
            nonce: bookingData.nonce,
            post_id: jQuery('input[name="post_id"]').val(),
            check_in: check_in,
            check_out: check_out,
            adults: jQuery('#adultsInput').val(),
            children: jQuery('#childrenInput').val(),
            pets: jQuery('#petsInput').val(),
            length_ft: jQuery('#length_ft').val() || 0,
            filter: 'booknowpage'
        },
        success: function(response) {
            if (response.success && response.data.html === 'available') {
                jQuery('#submit-btn').prop('disabled', false).text(bookingData.useSessionData ? 'Update' : 'Add to Cart');
                jQuery('#dateError').text('Available for this date').css('color', 'green');
                window.isAvailable = true; // Store in global scope
            } else {
                jQuery('#submit-btn').prop('disabled', true).text('Unavailable');
                const errorMsg = response.data.message || 'Not available for this date';
                jQuery('#dateError').text(errorMsg).css('color', 'red');
                window.isAvailable = false;
            }
            if (openCalendarOnFail) window.openCalendar();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            jQuery('#submit-btn').prop('disabled', true).text('Unavailable');
            jQuery('#dateError').text('Error checking availability').css('color', 'red');
            window.isAvailable = false;
            if (openCalendarOnFail) window.openCalendar();
            console.error('AJAX Error:', textStatus, errorThrown);
        }
    });
}

jQuery(document).ready(function($) {
    if (typeof flatpickr === 'undefined') {
        console.error('Flatpickr is not loaded.');
        return;
    }

    const {
        editMode,
        useSessionData,
        nightlyRate,
        campgroundFees,
        campsiteId,
        initialCheckIn,
        initialCheckOut,
        adults,
        children,
        pets,
        ajaxUrl,
        nonce,
        checkAvailabilityAction,
    } = bookingData;
    console.log('Booking Data:', bookingData);
    console.log('Check Availability Action:', checkAvailabilityAction);

    window.isAvailable = false; // Global scope for isAvailable

    document.getElementById("guestDropdownBtn").addEventListener("click", function(event) {
        event.stopPropagation();
        document.getElementById("guestDropdown").classList.toggle("d-none");
    });

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("guestDropdown");
        if (!dropdown.classList.contains("d-none") && !dropdown.contains(event.target) && event.target.id !== "guestDropdownBtn") {
            dropdown.classList.add("d-none");
        }
    });

    function getISODate(date) {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function updateURL(check_in, check_out) {
        const url = new URL(window.location.href);
        url.searchParams.set('check_in', check_in);
        url.searchParams.set('check_out', check_out);
        url.searchParams.set('campsite', campsiteId);
        url.searchParams.set('adults', $('#adultsInput').val());
        url.searchParams.set('children', $('#childrenInput').val());
        url.searchParams.set('pets', $('#petsInput').val());
        url.searchParams.set('length_ft', $('#length_ft').val());
        if (editMode) url.searchParams.set('edit', 'true');
        window.history.pushState({}, document.title, url.toString());
    }

    function updatePriceBreakdown(check_in, check_out) {
        const date1 = new Date(check_in);
        const date2 = new Date(check_out);
        const nights = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24)) || 1;
        const rate = parseFloat(nightlyRate) || 20.00;
        const subtotal = rate * nights;
        const total = subtotal + parseFloat(campgroundFees);
        $('.night-price').text(`$${rate.toFixed(2)} x ${nights} Nights`);
        $('.night-subtotal').text(`$${subtotal.toFixed(2)}`);
        $('.fw-bold span:last').text(`$${total.toFixed(2)}`);
    }

    window.fpInstance = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: [initialCheckIn, initialCheckOut],
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                const formatDate = date => date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: '2-digit' });
                const check_in = selectedDates[0];
                const check_out = selectedDates[1];

                $('#checkInText').text(formatDate(check_in));
                $('#checkOutText').text(formatDate(check_out));
                $('#check_in').val(getISODate(check_in));
                $('#check_out').val(getISODate(check_out));

                updatePriceBreakdown(check_in, check_out);
                updateURL(getISODate(check_in), getISODate(check_out));
                checkAvailability(getISODate(check_in), getISODate(check_out), true);
            }
        },
        appendTo: document.querySelector('.calendar-container')
    });

    $('#dateDisplay').on('click', function() {
        window.openCalendar();
    });

    $('#length_ft').on('change', function() {
        checkAvailability($('#check_in').val(), $('#check_out').val(), false);
    });
 
    // $('#booking-form').on('submit', function(e) {
    //     e.preventDefault();
    //     if (!window.isAvailable) {
    //         $('#dateError').text('Please select available dates and options').css('color', 'red');
    //         window.openCalendar();
    //         return;
    //     }

    //     const formData = new FormData(this);
    //     formData.append('action', 'add_to_cart');
    //     formData.append('_ajax_nonce', nonce);
    //     formData.append('edit_mode', useSessionData ? 'true' : 'false');

    //     $.ajax({
    //         type: 'POST',
    //         url: ajaxUrl,
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         beforeSend: function() {
    //             $('#submit-btn').text('Processing...').prop('disabled', true);
    //         },
    //         success: function(response) {
    //             if (response.success) {
    //                 $('#booking-form').prepend(`<p class="success-message" style="color: green;">${response.data.message}</p>`);
    //                 setTimeout(() => $('.success-message').remove(), 3000);
    //                 let formattedPrice = parseFloat(response.data.total_price).toFixed(2);
    //                 $('.cart-count').text(response.data.cart_count);
    //                 $('.custom-cart-link a').html(`
    //                     <span class="cart-total-price">$${formattedPrice}</span>
    //                     <i class="fa fa-shopping-cart"></i>
    //                     <span class="cart-count">${response.data.cart_count}</span>
    //                 `);
    //             } else {
    //                 $('#booking-form').prepend(`<p class="error-message" style="color: red;">Error: ${response.data.message || 'Failed to process'}</p>`);
    //             }
    //         },
    //         error: function(jqXHR, textStatus, errorThrown) {
    //             console.error('AJAX Error:', textStatus, errorThrown);
    //             $('#booking-form').prepend('<p class="error-message" style="color: red;">An error occurred. Please try again.</p>');
    //         },
    //         complete: function() {
    //             $('#submit-btn').text(useSessionData ? 'Update' : 'Add to Cart').prop('disabled', !window.isAvailable);
    //         }
    //     });
    // });

    if (initialCheckIn && initialCheckOut) {
        checkAvailability(initialCheckIn, initialCheckOut, false);
        updatePriceBreakdown(initialCheckIn, initialCheckOut);
    } else {
        $('#submit-btn').prop('disabled', true).text('Select Dates');
        $('#dateError').text('Please select dates').css('color', 'red');
        window.isAvailable = false;
    }

    if (useSessionData) {
        $('#adultsCount').val(adults);
        $('#childrenCount').val(children);
        $('#petsCount').val(pets);
        $('#adultsInput').val(adults);
        $('#childrenInput').val(children);
        $('#petsInput').val(pets);
        updateGuestSummary();
    }

    // Rest of your filterRVLots function remains unchanged
});