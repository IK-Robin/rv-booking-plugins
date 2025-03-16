jQuery(document).ready(function ($) {
    // Handle increase quantity
    $(document).on('click', '.increase-quantity', function (e) {
        e.preventDefault();
        var product_id = $(this).data('product-id');
        var quantityElement = $(this).closest('li').find('.quantity');
        var currentQuantity = parseInt(quantityElement.text());

        // Update UI instantly
        quantityElement.text(currentQuantity + 1);

        // Send AJAX request to update session
        updateQuantity(product_id, 'increase', quantityElement, currentQuantity);
    });

    // Handle decrease quantity
    $(document).on('click', '.decrease-quantity', function (e) {
        e.preventDefault();
        var product_id = $(this).data('product-id');
        var quantityElement = $(this).closest('li').find('.quantity');
        var currentQuantity = parseInt(quantityElement.text());

        // Update UI instantly
        if (currentQuantity > 1) {
            quantityElement.text(currentQuantity - 1);
            // Send AJAX request to update session
            updateQuantity(product_id, 'decrease', quantityElement, currentQuantity);
        } else {
            // Remove the product from the UI if quantity is 1
            $(this).closest('li').remove();
            // Send AJAX request to remove the product from the session
            updateQuantity(product_id, 'remove', quantityElement, currentQuantity);
        }
    });
   
$(document).on('click', '.remove', function (e) {
    e.preventDefault();
    var product_id = $(this).data('product-id');
    var itemElement = $(this).closest('li'); // Get the entire list item
    console.log(product_id)
    // Send AJAX request to remove the item
    $.ajax({
        url: ajax_object_create.ajax_url,
        type: 'POST',
        data: {
            action: 'update_cart_quantity',
            product_id: product_id,
            action_type: 'remove',
            ajax_nonce: ajax_object_create.nonce,
        },
        success: function (response) {
            console.log("Remove Response:", response);
            if (response.success) {
                // Remove item from UI
                itemElement.remove();

                // Update cart count and unique product count
                $('#total_cart_count').text(response.data.unique_count);
                $('.cart-count').text(response.data.cart_count);

                // If cart is empty, display message
                if (response.data.cart_count === 0) {
                    $('.cart-items-container').html('<p>Your cart is empty.</p>');
                }
            } else {
                console.log("Error: ", response.data);
            }
        },
        error: function () {
            console.log('AJAX request failed.');
        },
    });
});



    // Function to update quantity via AJAX
 
    function updateQuantity(product_id, action, quantityElement, currentQuantity) {
        $.ajax({
            url: ajax_object_create.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_quantity',
                product_id: product_id,
                action_type: action,
                ajax_nonce: ajax_object_create.nonce,
            },
            success: function (response) {
                if (!response.success) {
                    console.log(response)
                    // Revert UI change if AJAX fails
                    quantityElement.text(currentQuantity);
                    // alert('Error: ' + response.data);
                   
                }else{
                    $('#total_cart_count').text(response.data.unique_count);

                }
            },
            error: function () {
                // Revert UI change if AJAX fails
                quantityElement.text(currentQuantity);
                // alert('AJAX request failed.');
            },
        });
    }

    $('.custom-add-to-cart form').on('submit', function (e) {
        e.preventDefault();
    
        var product_id = $('#product_id').val();
        var quantity = $('#quantity').val();
    
        $.ajax({
            url: ajax_object_create.ajax_url,
            type: 'POST',
            data: {
                action: 'add_to_cart',
                product_id: product_id,
                quantity: quantity,
                _ajax_nonce: ajax_object_create.nonce,
            },
            success: function (response) {
                console.log("AJAX Response: ", response);
                if (response.success) {
                    $('.cart-count').text(response.data.cart_count);
                    $('#uniq_product').text(response.data.unique_count);     
                    $('#total_cart_count').text(response.data.unique_count);     
                } else {
                    console.log("Error: ", response.data);
                }
            },
            error: function () {
                console.log('AJAX request failed.');
            },
        });
    });
    
    


});