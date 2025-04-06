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
    checkAvailability(jQuery('#check_in').val(), jQuery('#check_out').val(), false);
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
                window.isAvailable = true;
            } else {
                jQuery('#submit-btn').prop('disabled', true).text('Unavailable');
                const errorMsg = response.data.message || 'Not available for this date';
                jQuery('#dateError').text(errorMsg).css('color', 'red');
                window.isAvailable = false;
            }

            // Update Flatpickr with unavailable dates
            if (response.data.unavailable_dates) {
                window.fpInstance.set('disable', response.data.unavailable_dates.map(date => new Date(date)));
                window.fpInstance.redraw(); // Redraw the calendar to reflect changes
            }

            // if (openCalendarOnFail) window.openCalendar();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            jQuery('#submit-btn').prop('disabled', true).text('Unavailable');
            jQuery('#dateError').text('Error checking availability').css('color', 'red');
            window.isAvailable = false;
            if(response.data.html == "unavailable" && response.data.message =="Dates not available.")  {

                if (openCalendarOnFail) window.openCalendar();
            }
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

    window.isAvailable = false;

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

    // Initialize Flatpickr with disable functionality
    window.fpInstance = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: [initialCheckIn, initialCheckOut],
        disable: [], // Initially empty, will be updated by AJAX
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
});