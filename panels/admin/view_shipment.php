<?php
session_start();
if (!empty($_SESSION['role'])) {
    $title = "View Shipment";
    require_once('header.php');
    require_once('./logics.class.php');
    
    // Check if order ID is provided
    if (!isset($_GET['id'])) {
        echo '<div class="container-fluid py-4">';
        echo '<div class="alert alert-danger">No order ID specified</div>';
        echo '</div>';
        require_once('footer.php');
        exit;
    }
    
    $order_id = $_GET['id'];
    $getUsers = new logics();
    
    // Get shipment details
    $shipment = $getUsers->getShipmentByOrderId($order_id);
    
    // Get order details
    $orderDetails = $getUsers->getOrderDetails($order_id);
    
    if (empty($shipment) || empty($orderDetails['status']) || $orderDetails['status'] != 1) {
        echo '<div class="container-fluid py-4">';
        echo '<div class="alert alert-danger">No shipment found for this order</div>';
        echo '</div>';
        require_once('footer.php');
        exit;
    }
    
    // Decode JSON response data
    $response_data = json_decode($shipment['response_data'], true);
?>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Shipment Details</h5>
            <div>
                <a href="order-details?id=<?php echo $order_id; ?>" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back"></i> Back to Order
                </a>
                <?php if (!empty($shipment['tracking_number'])): ?>
                <a href="https://shiprocket.co/tracking/<?php echo $shipment['tracking_number']; ?>" 
                   target="_blank" class="btn btn-sm btn-primary">
                    <i class="bx bx-map"></i> Track Shipment
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <!-- Shipment Summary -->
            <div class="row">
                <div class="col-md-7">
                    <div class="card border shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md me-3 bg-primary-light rounded-circle">
                                    <i class="bx bx-package text-primary" style="font-size: 1.5rem"></i>
                                </div>
                                <div>
                                <h6 class="mb-0">Shipment id #<?php echo $shipment['shipment_id']; ?></h6>
                                    <h6 class="mb-0">Order id #<?php echo $shipment['shiprocket_order_id']; ?></h6>
                             
                                    <small class="text-muted">Created on <?php echo date('d M Y H:i', strtotime($shipment['created_at'])); ?></small>
                                </div>
                                <span class="badge bg-info ms-auto"><?php echo ucfirst($shipment['status']); ?></span>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Shipping cost</small>
                                        <?php if (!empty($shipment['shipping_cost'])): ?>
                                            <strong class="fs-6"><?php echo $shipment['shipping_cost']; ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Not available yet</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Courier Company</small>
                                        <?php if (!empty($shipment['courier_company'])): ?>
                                            <strong class="fs-6"><?php echo $shipment['courier_company']; ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned yet</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">AWB Code</small>
                                        <?php if (!empty($shipment['awb_code'])): ?>
                                            <strong class="fs-6"><?php echo $shipment['awb_code']; ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Not available yet</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Payment Method</small>
                                        <strong class="fs-6"><?php echo $shipment['payment_method']; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="card border shadow-sm mb-4">
                        <div class="card-body p-3">
                            <h6 class="text-muted mb-3">Shipping To</h6>
                            <div class="d-flex">
                                <div class="avatar avatar-md me-3 bg-info-light rounded-circle">
                                    <i class="bx bx-map text-info" style="font-size: 1.5rem"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?php echo $shipment['customer_name']; ?></h6>
                                    <p class="mb-0 small"><?php echo $shipment['shipping_address']; ?></p>
                                    <p class="mb-0 small">
                                        <?php echo $shipment['shipping_city']; ?> - <?php echo $shipment['shipping_pincode']; ?>
                                    </p>
                                    <p class="mb-0 small text-muted"><?php echo $shipment['customer_phone']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Order Items</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover border-bottom mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderDetails['products'] as $product): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <?php if (!empty($product['product_image'])): ?>
                                                <img src="./product/<?php echo $product['product_image']; ?>" 
                                                     alt="<?php echo $product['product_name']; ?>" class="img-fluid rounded">
                                            <?php else: ?>
                                                <div class="avatar bg-light rounded">
                                                    <i class="bx bx-box text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                        <a href="../../product-view?slug=<?php echo $product['product_slug']; ?>">
                                            <h6 class="mb-0"><?php echo $product['product_name']; ?></h6>
                                            </a>
                                            <?php if (!empty($product['product_code'])): ?>
                                                <small class="text-muted"><?php echo $product['product_code']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo $product['quantity']; ?></td>
                                <td class="text-end">
                                    ₹<?php echo $product['product_price']; ?><br>
                                    <small class="text-muted">+ ₹<?php echo number_format($product['product_price'] * 0.18, 2); ?> GST</small><br>
                                    <strong>Total: ₹<?php echo number_format($product['product_price'] * 1.18, 2); ?></strong>
                                </td>
                            </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Shipment API Response (for debugging) -->
            <?php if($_SESSION['role'] == 'admin' && !empty($shipment['response_data'])): ?>
            <div class="card border shadow-sm mb-4 collapse" id="apiResponseDetails">
                <div class="card-header bg-light">
                    <h6 class="mb-0">API Response Details</h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code><?php echo json_encode(json_decode($shipment['response_data'], true), JSON_PRETTY_PRINT); ?></code></pre>
                </div>
            </div>
            <p class="text-center">
                <button class="btn btn-sm btn-outline-secondary" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#apiResponseDetails">
                    <i class="bx bx-code"></i> Toggle API Response
                </button>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
    require_once('footer.php');
} else {
    header('location:login.php');
}
?>