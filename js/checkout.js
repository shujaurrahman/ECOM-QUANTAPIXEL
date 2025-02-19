jQuery(document).ready(function($) {
    $('#PayNow').click(function(e) {
        e.preventDefault();

        // Validate form
        if (!$('#checkoutForm')[0].checkValidity()) {
            $('#checkoutForm')[0].reportValidity();
            return;
        }

        // Get form data
        var formData = new FormData($('#checkoutForm')[0]);
        formData.append('submit', 'true');

        // First save order details
        $.ajax({
            type: 'POST',
            url: 'process-order.php',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#PayNow').prop('disabled', true).html('Processing...');
            },
            success: function(response) {
        
                
                if(response && response.status === 1) {
                    // Order saved successfully, now initiate payment
                    var paymentData = {
                        billing_name: $('#billing_name').val(),
                        billing_mobile: $('#billing_mobile').val(),
                        billing_email: $('#billing_email').val(),
                        shipping_name: $('#shipping_name').val() || $('#billing_name').val(),
                        shipping_mobile: $('#shipping_mobile').val() || $('#billing_mobile').val(),
                        shipping_email: $('#shipping_email').val() || $('#billing_email').val(),
                        paymentOption: "netbanking",
                        payAmount: response.amount || $('#grandTotal').text().replace(/[^0-9]/g, ''),
                        order_id: response.order_id,
                        action: 'payOrder'
                    };


                    // Initiate Razorpay payment
                    $.ajax({
                        type: 'POST',
                        url: 'submitpayment.php',
                        data: paymentData,
                        dataType: 'json',
                        success: function(data) {
                            console.log('Payment initialization response:', data);
                            
                            if(data && data.res === 'success' && data.userData) {
                                // Remove commas and convert to number
                                var amount = parseFloat(data.userData.amount.replace(/,/g, ''));
                                
                                var options = {
                                    "key": data.razorpay_key,
                                    "amount": amount * 100, // Convert to paise
                                    "currency": "INR",
                                    "name": "ZXQS",
                                    "description": data.userData.description,
                                    "image": "images/zxqs-logo.jpg",
                                    "order_id": data.userData.rpay_order_id,
                                    "handler": function (response) {
                                        console.log('Payment success response:', response);

                                        // Save payment details first
                                        $.ajax({
                                            type: 'POST',
                                            url: 'save-payment.php',
                                            data: {
                                                order_id: data.order_number,
                                                payment_id: response.razorpay_payment_id,
                                                signature: response.razorpay_signature,
                                                amount: parseFloat(data.userData.amount.replace(/,/g, '')) // Convert amount to number
                                            },
                                            dataType: 'json',
                                            success: function(result) {
                                                console.log('Payment details saved:', result);
                                                if (result.status === 1) {
                                                    // Get product details from the cart
                                                    var productNames = [];
                                                    var quantities = [];
                                                    $('.product-name').each(function() {
                                                        productNames.push($(this).text());
                                                    });
                                                    $('.product-quantity').each(function() {
                                                        quantities.push($(this).val());
                                                    });
                                                    
                                                    // Redirect to success page
                                                    window.location.href = "payment-success.php?oid=" + data.order_number + 
                                                        "&rp_payment_id=" + response.razorpay_payment_id + 
                                                        "&rp_signature=" + response.razorpay_signature +
                                                        "&product_names=" + encodeURIComponent(productNames.join(', ')) +
                                                        "&quantities=" + encodeURIComponent(quantities.join(', ')) +
                                                        "&total_amount=" + encodeURIComponent(data.userData.amount);
                                                } else {
                                                    console.error('Error saving payment:', result.error);
                                                    alert('Payment successful but error saving details. Please contact support.');
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                console.error('Error saving payment:', {
                                                    xhr: xhr,
                                                    status: status,
                                                    error: error,
                                                    response: xhr.responseText
                                                });
                                                alert('Payment successful but error saving details. Please contact support.');
                                            }
                                        });
                                    },
                                    "prefill": {
                                        "name": data.userData.name || '',
                                        "email": data.userData.email || '',
                                        "contact": data.userData.mobile || ''
                                    },
                                    "theme": {
                                        "color": "#3399cc"
                                    },
                                    "modal": {
                                        "ondismiss": function() {
                                            $('#PayNow').prop('disabled', false).html('Place Order');
                                        }
                                    }
                                };
                                
                                console.log('Amount in paise:', amount * 100);
                                console.log('Razorpay options:', options);
                                
                                try {
                                    var rzp1 = new Razorpay(options);
                                    rzp1.on('payment.failed', function (response) {
                                        console.error('Payment failed:', response);
                                        window.location.href = "payment-failed.php?oid=" + data.order_number + 
                                            "&reason=" + encodeURIComponent(response.error.description) + 
                                            "&paymentid=" + response.error.metadata.payment_id;
                                    });
                                    rzp1.open();
                                } catch(e) {
                                    console.error('Razorpay initialization error:', e);
                                    alert('Error initializing payment gateway. Please try again.');
                                }
                            } else {
                                console.error('Invalid payment initialization response:', data);
                                alert('Payment initialization failed. Please try again.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Payment Error:', {xhr: xhr, status: status, error: error});
                            alert('Error initializing payment. Please try again.');
                            $('#PayNow').prop('disabled', false).html('Place Order');
                        }
                    });
                } else {
                    console.error('Order error:', response);
                    alert('Error saving order details. Please try again.');
                    $('#PayNow').prop('disabled', false).html('Place Order');
                }
            },
            error: function(xhr, status, error) {
                console.error('Order Error:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Error processing order. Please try again.');
                $('#PayNow').prop('disabled', false).html('Place Order');
            }
        });
    });
});