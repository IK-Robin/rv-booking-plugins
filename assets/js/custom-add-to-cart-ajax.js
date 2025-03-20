jQuery(document).ready(function ($) {
    // Handle increase quantity
//     $(document).on('click', '.increase-quantity', function (e) {
//         e.preventDefault();
//         var product_id = $(this).data('product-id');
//         var quantityElement = $(this).closest('li').find('.quantity');
//         var currentQuantity = parseInt(quantityElement.text());

//         // Update UI instantly
//         quantityElement.text(currentQuantity + 1);

//         // Send AJAX request to update session
//         updateQuantity(product_id, 'increase', quantityElement, currentQuantity);
//     });

//     // Handle decrease quantity
//     $(document).on('click', '.decrease-quantity', function (e) {
//         e.preventDefault();
//         var product_id = $(this).data('product-id');
//         var quantityElement = $(this).closest('li').find('.quantity');
//         var currentQuantity = parseInt(quantityElement.text());

//         // Update UI instantly
//         if (currentQuantity > 1) {
//             quantityElement.text(currentQuantity - 1);
//             // Send AJAX request to update session
//             updateQuantity(product_id, 'decrease', quantityElement, currentQuantity);
//         } else {
//             // Remove the product from the UI if quantity is 1
//             $(this).closest('li').remove();
//             // Send AJAX request to remove the product from the session
//             updateQuantity(product_id, 'remove', quantityElement, currentQuantity);
//         }
//     });
   
// $(document).on('click', '.remove', function (e) {
//     e.preventDefault();
//     var product_id = $(this).data('product-id');
//     var itemElement = $(this).closest('li'); // Get the entire list item
//     console.log(product_id)
//     // Send AJAX request to remove the item
//     $.ajax({
//         url: ajax_object_create.ajax_url,
//         type: 'POST',
//         data: {
//             action: 'update_cart_quantity',
//             product_id: product_id,
//             action_type: 'remove',
//             ajax_nonce: ajax_object_create.nonce,
//         },
//         success: function (response) {
//             console.log("Remove Response:", response);
//             if (response.success) {
//                 // Remove item from UI
//                 itemElement.remove();

//                 // Update cart count and unique product count
//                 $('#total_cart_count').text(response.data.unique_count);
//                 $('.cart-count').text(response.data.cart_count);

//                 // If cart is empty, display message
//                 if (response.data.cart_count === 0) {
//                     $('.cart-items-container').html('<p>Your cart is empty.</p>');
//                 }
//             } else {
//                 console.log("Error: ", response.data);
//             }
//         },
//         error: function () {
//             console.log('AJAX request failed.');
//         },
//     });
// });



//     // Function to update quantity via AJAX
 
//     function updateQuantity(product_id, action, quantityElement, currentQuantity) {
//         $.ajax({
//             url: ajax_object_create.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'update_cart_quantity',
//                 product_id: product_id,
//                 action_type: action,
//                 ajax_nonce: ajax_object_create.nonce,
//             },
//             success: function (response) {
//                 if (!response.success) {
//                     console.log(response)
//                     // Revert UI change if AJAX fails
//                     quantityElement.text(currentQuantity);
//                     // alert('Error: ' + response.data);
                   
//                 }else{
//                     $('#total_cart_count').text(response.data.unique_count);

//                 }
//             },
//             error: function () {
//                 // Revert UI change if AJAX fails
//                 quantityElement.text(currentQuantity);
//                 // alert('AJAX request failed.');
//             },
//         });
//     }

    // $('#booking-form').on('submit', function (e) {
    //     e.preventDefault();
    

    //     console.log('Form Submitted');
    //     // get all the inputs value from the input fields directly send the formdata 
    //     var formdata = new FormData(this);
    //     $.ajax({
    //         type: 'POST',
    //         url: rvbs_add_to_cart.ajax_url,
    //         data:{
    //             action: 'add_to_cart',
    //             formdata: formdata,
    //             _ajax_nonce: rvbs_add_to_cart.nonce,
    //         }

    //     }).done(function (response) {
    //         console.log(response);
    //     });
   




    
    //     // $.ajax({
    //     //     url: rvbs_add_to_cart.ajax_url,
    //     //     type: 'POST',
    //     //     data: {
    //     //         action: 'add_to_cart',
    //     //         product_id: product_id,
    //     //         quantity: quantity,
    //     //         _ajax_nonce: rvbs_add_to_cart.nonce,
    //     //     },
    //     //     success: function (response) {
    //     //         console.log("AJAX Response: ", response);
    //     //         if (response.success) {
    //     //             $('.cart-count').text(response.data.cart_count);
    //     //             $('#uniq_product').text(response.data.unique_count);     
    //     //             $('#total_cart_count').text(response.data.unique_count);     
    //     //         } else {
    //     //             console.log("Error: ", response.data);
    //     //         }
    //     //     },
    //     //     error: function () {
    //     //         console.log('AJAX request failed.');
    //     //     },
    //     // });
   
   
   
    // });
    
 // on load get the session data and update the cart count
    // $.ajax({
    //     url: rvbs_add_to_cart.ajax_url,
    //     type: 'POST',
    //     data: {
    //         action: 'rvbs_get_cart_count',
    //         _ajax_nonce: rvbs_add_to_cart.nonce,
    //     },
    //     success: function (response) {
    //         console.log("AJAX Response: ", response);
    //         if (response.success) {
    //             $('.cart-count').text(response.data.cart_count);
    //             $('#uniq_product').text(response.data.unique_count);
    //             $('#total_cart_count').text(response.data.unique_count);
    //         } else {
    //             console.log("Error: ", response.data); 
    //         }
    //     },
    //     error: function () {
    //         console.log('AJAX request failed.'); 
    //     },
    // });



// Form submission with validation
$('#booking-form').on('submit', function(e) {
    e.preventDefault();
    console.log('hell')

    // Remove previous error messages
    $('.error-message').remove();

    // Get form field values
    const adults = $('#adultsInput').val().trim();
    const equipment_type = $('#equipment_type').val().trim();
    const length_ft = $('#length_ft').val().trim();
    const slide_outs = $('#slide_outs').val().trim();
    const site_location = $('#site_location').val().trim();

    // Validation flag
    let isValid = true;

    // Function to show error and focus field
    function showError(fieldId, message) {
        $(`#${fieldId}`).after(`<p class="error-message" style="color: red; font-size: 0.9em;">${message}</p>`);
        $(`#${fieldId}`).focus();
        isValid = false;
    }

    // Validate fields
    if (!adults || parseInt(adults) < 1) {
        showError('guestDropdownBtn', 'Please select at least one adult');
    }

    if (!equipment_type) {
        showError('equipment_type', 'Please select an equipment type');
    }

    if (!length_ft || parseInt(length_ft) <= 0) {
        showError('length_ft', 'Please enter a valid equipment length');
    }

    if (!slide_outs && slide_outs !== '0') { // '0' is a valid option
        showError('slide_outs', 'Please select the number of slide-outs');
    }

    if (!site_location) {
        showError('site_location', 'Please enter the site location');
    }

    // Stop if validation fails
    if (!isValid) {
        return;
    }

    // Proceed with AJAX submission
    const formData = new FormData(this);
    formData.append('action', 'add_to_cart');
    formData.append('_ajax_nonce', rvbs_add_to_cart.nonce);

    $.ajax({
        type: 'POST',
        url: rvbs_add_to_cart.ajax_url,
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#booking-form button[type="submit"]').text('Processing...').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                $('#booking-form').prepend('<p class="success-message" style="color: green;">Added to cart successfully!</p>');
                setTimeout(() => $('.success-message').remove(), 3000);
        
                // Ensure the total price is formatted to two decimal places
                let formattedPrice = parseFloat(response.data.total_price).toFixed(2);
        
                // Update cart count
                $('.cart-count').text(response.data.cart_count);
        
                // Append updated HTML to .custom-cart-link
                $('.custom-cart-link').html(`
                    <span class="cart-total-price">$${formattedPrice}</span>
                    <i class="fa fa-shopping-cart"></i>
                    <span class="cart-count">${response.data.cart_count}</span>
                `);
            } else {
                $('#booking-form').prepend(`<p class="error-message" style="color: red;">Error: ${response.data.message || 'Failed to add to cart'}</p>`);
            }
        },
        
        
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            $('#booking-form').prepend('<p class="error-message" style="color: red;">An error occurred. Please try again.</p>');
        },
        complete: function() {
            $('#booking-form button[type="submit"]').text('Add to Cart').prop('disabled', false);
        }
    });
});



// send the form data to the server and then process the data and book the lot 

            // // Handle form submission
            // $('#booking-form').on('submit', function(e) {
            //     e.preventDefault();

            //     // // Check availability status
            //     // if (!isAvailable) {
            //     //     $('#dateError').text('Please select available dates');
            //     //     $('#dateError').css('color', 'red');
            //     //     $('#dateRange').focus();
            //     //     window.fpInstance.open();
            //     //     return;
            //     // }

            //     const post_id = $('input[name="post_id"]').val();
            //     const room_title = $('input[name="room_title"]').val();
            //     const check_in = $('#check_in').val();
            //     const check_out = $('#check_out').val();
            //     const adults = $('#adults').val();
            //     const children = $('#children').val();
            //     const equipment_type = $('#equipment_type').val();
            //     const length_ft = $('#length_ft').val();
            //     const slide_outs = $('#slide_outs').val();
            //     const site_location = $('#site_location').val();

            //     // Validation with focus on fields
                
                


            //     $.ajax({
            //         url: rvbs_ajax.ajax_url,
            //         type: 'POST',
            //         data: {
            //             action: 'rvbs_book_lot',
            //             nonce: rvbs_ajax.nonce,
            //             lot_id: post_id,
            //             post_id: post_id,
            //             room_title: room_title,
            //             check_in: check_in,
            //             check_out: check_out,
            //             adults: adults,
            //             children: children,
            //             equipment_type: equipment_type,
            //             length_ft: length_ft,
            //             slide_outs: slide_outs,
            //             site_location: site_location
            //         },
            //         beforeSend: function() {
            //             $('#booking-form button[type="submit"]').text('Processing...').prop('disabled', true);
            //         },
            //         success: function(response) {
            //             if (response.success) {
            //                 // Success message (you might want to replace this with a UI update)
            //                 $('#formMessage').text('Booking added to cart successfully! Room: ' + room_title); // Add this element if not present
            //                 $('#formMessage').css('color', 'green');
            //             } else {
            //                 $('#formMessage').text('Booking failed: ' + response.data); // Add this element if not present
            //                 $('#formMessage').css('color', 'red');
            //                 // Focus on the first relevant field based on the error (if specific)
            //                 $('#dateRange').focus(); // Default focus, adjust based on error type if needed
            //             }
            //         },
            //         error: function() {
            //             $('#formMessage').text('An error occurred while booking'); // Add this element if not present
            //             $('#formMessage').css('color', 'red');
            //             $('#dateRange').focus(); // Default focus on error
            //         },
            //         complete: function() {
            //             $('#booking-form button[type="submit"]').text('Add to Cart').prop('disabled', false);
            //         }
            //     });
            // });





            // this code block work in rvbs-custom-menu-item.php file  add the doller sign to the cart total

            $('.block-cart .cart-total').each(function () {
                var cartTotal = $(this);
                if ($.trim(cartTotal.text()) !== "") {
                    cartTotal.text("$" + cartTotal.text());
                }
            });
            });





