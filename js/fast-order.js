jQuery(document).ready(function ($) {
    $('.js-fast-order-btn').on('click', function () {
        var phoneNumber = $('input[name="telephone"]').val();
        var quantity = $('input[name="quantity"]').val() || 1;
        var productID = $('body').attr('class').match(/postid-(\d+)/);

        if (productID) {
            productID = productID[1];
        }

        if (!productID) {
            alert('Choose a product!');
            return;
        }

        console.log({
            action: 'create_fast_order',
            phone: phoneNumber,
            quantity: quantity,
            product_id: productID
        });
    
        $.post(fastOrder.ajaxurl, {
            action: 'create_fast_order',
            phone: phoneNumber,
            quantity: quantity,
            product_id: productID
        }, function (response) {
            alert(response.message);
        });
    });

    var $telInput = $('.js-telMask');
    if ($telInput.length) {
        $telInput.mask('380 (99) 999-99-99');
    } 
});