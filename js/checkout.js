jQuery(document).ready(function($) {
    $('#PayNow').click(function(e) {
        e.preventDefault();

        if (!$('#checkoutForm')[0].checkValidity()) {
            $('#checkoutForm')[0].reportValidity();
            return;
        }

        // Use existing submitpayment.php
        var paymentData = {
            action: 'payOrder',
            payAmount: $('#grandTotal').text().replace(/[^0-9]/g, ''),
            billing_name: $('#billing_name').val(),
            billing_email: $('#billing_email').val(),
            billing_mobile: $('#billing_mobile').val(),
            shipping_name: $('#shipping_name').val() || $('#billing_name').val(),
            shipping_email: $('#shipping_email').val() || $('#billing_email').val(),
            shipping_mobile: $('#shipping_mobile').val() || $('#billing_mobile').val(),
            paymentOption: 'netbanking'
        };

        $.ajax({
            type: 'POST',
            url: 'submitpayment.php',
            data: paymentData,
            dataType: 'json',
            beforeSend: function() {
                $('#PayNow').prop('disabled', true).html('Processing...');
            },
            success: function(data) {
                if(data.res === 'success') {
                    var options = {
                        "key": data.razorpay_key,
                        "amount": parseFloat(data.userData.amount) * 100,
                        "currency": "INR",
                        "name": "ZXQS",
                        "description": data.userData.description,
                        "order_id": data.userData.rpay_order_id,
                        "handler": function (response) {
                            console.log('Razorpay Response:', response);
                            var formData = new FormData($('#checkoutForm')[0]);
                            
                            // Log form data for debugging
                            for (var pair of formData.entries()) {
                                console.log(pair[0] + ': ' + pair[1]);
                            }
                            
                            // Add payment details
                            formData.append('submit', true);
                            formData.append('razorpay_payment_id', response.razorpay_payment_id);
                            formData.append('razorpay_order_id', data.userData.rpay_order_id);
                            formData.append('razorpay_signature', response.razorpay_signature);
                            formData.append('payment_mode', 'razorpay');
                            formData.append('payment_amount', data.userData.amount);
                            formData.append('payment_reference', response.razorpay_payment_id);
                            formData.append('payment_id', response.razorpay_payment_id);
                            formData.append('payment_date', new Date().toISOString().slice(0, 19).replace('T', ' '));
                            formData.append('order_status', 'confirmed');
                            formData.append('payment_status', 'paid');

                            $.ajax({
                                type: 'POST',
                                url: 'process-order.php',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(orderResponse) {
                                    console.log('Order Response:', orderResponse);
                                    if(orderResponse.status === 1) {
                                        // Get product details from the form
                                        let productDetails = [];
                                        $('.cart-item').each(function() {
                                            let $item = $(this);
                                            productDetails.push({
                                                name: $item.find('input[name="product_name[]"]').val(),
                                                quantity: $item.find('input[name="quantity[]"]').val(),
                                                price: $item.find('input[name="price[]"]').val()
                                            });
                                        });

                                        window.location.href = "payment-success.php?" + 
                                            "order_id=" + orderResponse.order_id +
                                            "&payment_id=" + response.razorpay_payment_id +
                                            "&razorpay_order_id=" + data.userData.rpay_order_id +
                                            "&amount=" + data.userData.amount +
                                            "&total_amount=" + $('#grandTotal').text().replace(/[^0-9]/g, '') +
                                            "&product_details=" + encodeURIComponent(JSON.stringify(productDetails)) +
                                            "&customer_name=" + encodeURIComponent($('#name').val()) +
                                            "&customer_email=" + encodeURIComponent($('#email').val()) +
                                            "&customer_mobile=" + encodeURIComponent($('#mobile').val());
                                    } else {
                                        window.location.href = "payment-failed.php?" +
                                            "oid=" + data.userData.rpay_order_id +
                                            "&paymentid=" + response.razorpay_payment_id +
                                            "&reason=" + encodeURIComponent(orderResponse.message || 'Payment processing failed');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Ajax Error:', {
                                        status: status,
                                        error: error,
                                        response: xhr.responseText
                                    });
                                    window.location.href = "payment-failed.php?" +
                                        "oid=" + data.userData.rpay_order_id +
                                        "&paymentid=NA" +
                                        "&reason=" + encodeURIComponent(error || 'Payment processing failed');
                                }
                            });
                        },
                        "prefill": {
                            "name": data.userData.name,
                            "email": data.userData.email,
                            "contact": data.userData.mobile
                        },
                        "theme": {
                            "color": "#3399cc"
                        },
                        "modal": {
                            "ondismiss": function() {
                                window.location.href = "payment-failed.php?" +
                                    "oid=" + data.userData.rpay_order_id +
                                    "&paymentid=NA" +
                                    "&reason=" + encodeURIComponent('Payment cancelled by user');
                            }
                        }
                    };

                    var rzp1 = new Razorpay(options);
                    rzp1.on('payment.failed', function (response) {
                        window.location.href = "payment-failed.php?" +
                            "oid=" + data.userData.rpay_order_id +
                            "&paymentid=" + response.error.metadata.payment_id +
                            "&reason=" + encodeURIComponent(response.error.description);
                    });
                    rzp1.open();
                } else {
                    alert('Error initializing payment: ' + (data.message || 'Unknown error'));
                    $('#PayNow').prop('disabled', false).html('Place Order');
                }
            },
            error: function() {
                alert('Error connecting to payment service');
                $('#PayNow').prop('disabled', false).html('Place Order');
            }
        });
    });
});