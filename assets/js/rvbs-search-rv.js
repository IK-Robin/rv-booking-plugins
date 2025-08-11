
        // connected to teh rvbs_all-script.php
        
        window.openCalendar = function() {
            if (window.fpInstance && typeof window.fpInstance.open === 'function') {
                window.fpInstance.open();
            } else {
                console.error('Flatpickr instance not initialized or open method not available.');
            }
        };

        jQuery(document).ready(function($) {
            // Ensure flatpickr is defined
            if (typeof flatpickr === 'undefined') {
                console.error('Flatpickr is not loaded.');
                return;
            }

            // Initialize Flatpickr
            window.fpInstance = flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                minDate: "today",
                onClose: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        const formatDate = date => date.toLocaleDateString('en-US', {
                            weekday: 'short',
                            month: 'short',
                            day: '2-digit'
                        });
                        document.querySelector("#checkInText").textContent = formatDate(selectedDates[0]);
                        document.querySelector("#checkOutText").textContent = formatDate(selectedDates[1]);
                        document.querySelector("#dateDisplay").classList.add("active");

                        const getISODate = date => {
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            return `${year}-${month}-${day}`;
                        };

                        $('#checkin_date').val(getISODate(selectedDates[0]));
                        $('#checkout_date').val(getISODate(selectedDates[1]));

                        // Trigger filter and update UI
                        filterRVLots();
                        check_room_aval();
                        updateResetButtonsVisibility();
                    }
                },
                appendTo: document.querySelector('.calendar-container')
            });

            // Attach click event to dateDisplay
            $('#dateDisplay').on('click', function() {
                window.openCalendar();
            });

            function filterRVLots() {
                var siteType = $('.filter-btn.active').data('filter') || 'all';
                var selectedFeatures = [];
                $('.feature-filter:checked').each(function() {
                    selectedFeatures.push($(this).val());
                });
                var selectedAmenities = [];
                $('.aminets-filter:checked').each(function() {
                    selectedAmenities.push($(this).val());
                });
                var checkInDate = $('#checkin_date').val();
                var checkOutDate = $('#checkout_date').val();
                var adults = $('#adult').val() || 1; // Default to 1 if empty
                var children = $('#children').val() || 0; // Default to 0 if empty
                var priceRange = $('input[name="price_range"]:checked').val() || ''; // Get selected price range

                $.ajax({
                    type: 'POST',
                    url: rvbs_serch_rv.ajax_url,
                    data: {
                        action: rvbs_serch_rv.filter_rv_lots,
                        site_type: siteType,
                        features: selectedFeatures,
                        aminets: selectedAmenities,
                        check_in: checkInDate,
                        check_out: checkOutDate,
                        adults: adults,
                        children: children,
                        price_range: priceRange
                    },
                    beforeSend: function() {
                        $('#show_aval_room').html('<p class="text-center">Loading...</p>');
                    },
                    success: function(response) {
                        $('#show_aval_room').html(response);
                        updateResetButtonsVisibility(); // Update visibility of all reset buttons
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            }

            // Check filter states and update reset button visibility
            function updateResetButtonsVisibility() {
                // Dates
                if ($('#checkin_date').val() || $('#checkout_date').val()) {
                    $('#reset_avail').show();
                } else {
                    $('#reset_avail').hide();
                }

                // Guests
                if (($('#adult').val() && $('#adult').val() !== '1') || ($('#children').val() && $('#children').val() !== '0')) {
                    $('#guest_reset').show();
                } else {
                    $('#guest_reset').hide();
                }

                // Price
                if ($('input[name="price_range"]:checked').length > 0) {
                    $('#reset_price').show();
                } else {
                    $('#reset_price').hide();
                }

                // Features
                if ($('.feature-filter:checked').length > 0) {
                    $('#reset_features').show();
                } else {
                    $('#reset_features').hide();
                }

                // Amenities
                if ($('.aminets-filter:checked').length > 0) {
                    $('#reset_amenities').show();
                } else {
                    $('#reset_amenities').hide();
                }
            }

            // Handle Site Type Filter Click
            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                filterRVLots();
            });

            // Handle all filter changes
            $('.feature-filter, .aminets-filter, .price-filter, #adult, #children').on('change', function() {
                filterRVLots();
            });

            // Guest filter function
            window.guest_filter = function() {
                filterRVLots();
            };

            // Clear guest function
            window.clear_guest = function() {
                $('#adult').val('1');
                $('#children').val('1');
                filterRVLots();
            };

            // Clear price function
            window.clear_price = function() {
                $('input[name="price_range"]').prop('checked', false);
                filterRVLots();
            };

            // Clear date function
            window.clear_date = function() {
                document.querySelector("#checkInText").textContent = 'Check In';
                document.querySelector("#checkOutText").textContent = 'Check Out';
                document.querySelector("#dateDisplay").classList.remove("active");
                $('#checkin_date').val('');
                $('#checkout_date').val('');
                $('#nights_display').remove();
                filterRVLots();
            };

            // Clear features function
            window.clear_features = function() {
                $('.feature-filter').prop('checked', false);
                filterRVLots();
            };

            // Clear amenities function
            window.clear_amenities = function() {
                $('.aminets-filter').prop('checked', false);
                filterRVLots();
            };

            // Check room availability
            window.check_room_aval = function() {
                var checkIn = $('#checkin_date').val();
                var checkOut = $('#checkout_date').val();
                if (checkIn && checkOut) {
                    var checkInDate = new Date(checkIn);
                    var checkOutDate = new Date(checkOut);
                    if (checkOutDate > checkInDate) {
                        var timeDiff = checkOutDate - checkInDate;
                        var nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
                        $('#nights_display').remove();
                        $('.rvbs-search-rv').prepend(
                            '<div id="nights_display" class="text-center mb-3">Booked for ' + nights + ' night' + (nights > 1 ? 's' : '') + '</div>'
                        );
                    }
                }
            };

            // Initial check for reset buttons visibility
            updateResetButtonsVisibility();



            // load more post when on click the load more button
            let currentPage = 1;
            let loading = false;
        
            $('#load_more').on('click', function(e) {
                e.preventDefault();
                
                if (loading) return;
                loading = true;
                
                $(this).text('Loading...'); // Show loading state
                
                $.ajax({
                    url: rvbs_serch_rv.ajax_url, // WordPress AJAX URL
                    type: 'POST',
                    data: {
                        action: rvbs_serch_rv.action,
                        page: currentPage + 1,
                        nonce: rvbs_serch_rv.nonce // You'll need to localize this
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#show_aval_room').append(response.data.html); // Append new posts
                            currentPage++;
                            
                            // Hide button if we've reached max pages
                            if (currentPage >= response.data.max_pages) {
                                $('#load_more').hide();
                            }
                        } else {
                            $('#load_more').text('No More Posts');
                        }
                    },
                    error: function() {
                        alert('Error loading posts');
                    },
                    complete: function() {
                        $('#load_more').text('Load More');
                        loading = false;
                    }
                });
            });
        });
