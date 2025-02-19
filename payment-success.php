<?php
session_start();
require_once('./header.php');

// Get parameters from URL
$orderId = isset($_GET['oid']) ? $_GET['oid'] : '';
$paymentId = isset($_GET['rp_payment_id']) ? $_GET['rp_payment_id'] : '';
$signature = isset($_GET['rp_signature']) ? $_GET['rp_signature'] : '';
$productNames = isset($_GET['product_names']) ? $_GET['product_names'] : '';
$quantities = isset($_GET['quantities']) ? $_GET['quantities'] : '';
$totalAmount = isset($_GET['total_amount']) ? $_GET['total_amount'] : '';
?>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.btn {
    transition: all 0.3s;
}



.section-title span {
    position: relative;
}

.section-title span:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 2px;
    background-color: #007bff;
}
</style>

<div class="container-fluid py-5">
    <div class="row px-xl-5 justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="section-title position-relative text-uppercase mb-4">
                            <span class="bg-white px-3">Payment Successful</span>
                        </h2>
                        
                        <div class="payment-info mb-5">
                            <div class="alert alert-success" role="alert">
                                <h4 class="alert-heading mb-3">Thank You for Your Purchase!</h4>
                                <p>Your order has been placed successfully and will be processed soon.</p>
                            </div>
                            
                            <div class="order-details p-4 bg-light rounded">
                                <h4 class="mb-4 text-primary">Order Summary</h4>
                                <div class="row mb-3">
                                    <div class="col-sm-6 text-muted">Order ID:</div>
                                    <div class="col-sm-6"><?php echo htmlspecialchars($orderId); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-6 text-muted">Payment ID:</div>
                                    <div class="col-sm-6"><?php echo htmlspecialchars($paymentId); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-6 text-muted">Product(s):</div>
                                    <div class="col-sm-6"><?php echo htmlspecialchars($productNames); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-6 text-muted">Total Amount:</div>
                                    <div class="col-sm-6">₹<?php echo number_format($totalAmount, 2); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <a href="index.php" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-shopping-cart mr-2"></i>Continue Shopping
                            </a>
                            <a href="account.php" class="btn btn-outline-primary btn-lg px-5 ml-3">
                                <i class="fas fa-box mr-2"></i>View Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('./footer.php'); ?>

