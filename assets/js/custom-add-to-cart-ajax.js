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
    
    

    $('#booking-form').on('submit', function (e) {
        e.preventDefault();
    
        console.log('Form Submitted');
    
        // Create FormData object from the form
        var formdata = new FormData(this);
    
        // Append additional AJAX action and nonce to the FormData
        formdata.append('action', 'add_to_cart');
        formdata.append('_ajax_nonce', rvbs_add_to_cart.nonce);
    
        $.ajax({
            type: 'POST',
            url: rvbs_add_to_cart.ajax_url,
            data: formdata,
            processData: false, // Prevent jQuery from automatically processing data
            contentType: false, // Prevent jQuery from setting content-type
        }).done(function (response) {
            console.log(response);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
        });
    });
    

});