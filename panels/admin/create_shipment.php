<?php
session_start();
require_once('logics.class.php');

// Check if user is logged in and has appropriate role
if (empty($_SESSION['role'])) {
    header('location:login.php');
    exit;
}

// Configuration
require_once('ship-rocket-cred.php');

/**
 * Get ShipRocket authentication token
 */
function getShipRocketToken($config) {
    // Check if we have a valid cached token
    if (file_exists($config['token_file'])) {
        $token_data = json_decode(file_get_contents($config['token_file']), true);
        
        // Check if token is still valid (less than 9 days old to be safe)
        if ($token_data['expiry'] > time()) {
            return $token_data['token'];
        }
    }
    
    // Get new token
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'email' => $config['email'],
            'password' => $config['password'],
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        throw new Exception("cURL Error: " . $err);
    }
    
    $result = json_decode($response, true);
    
    if (!isset($result['token'])) {
        throw new Exception("Failed to authenticate with ShipRocket: " . $response);
    }
    
    // Calculate expiry (10 days - 1 day safety margin)
    $expiry = time() + (9 * 24 * 60 * 60);
    
    // Save token to file
    file_put_contents($config['token_file'], json_encode([
        'token' => $result['token'],
        'expiry' => $expiry
    ]));
    
    return $result['token'];
}

/**
 * Create shipment in ShipRocket
 */
function createShipment($order_id, $config) {
    // Get order details
    $getUsers = new logics();
    $orderDetails = $getUsers->getOrderDetails($order_id);
    
    if (empty($orderDetails['status']) || $orderDetails['status'] != 1) {
        return [
            'success' => false,
            'message' => 'Order not found'
        ];
    }
    
    try {
        // Get authentication token
        $token = getShipRocketToken($config);
        
        // Format order items
        $order_items = [];
        foreach ($orderDetails['products'] as $product) {
            $order_items[] = [
                'name' => $product['product_name'],
                'sku' => $product['product_code'] ?? $product['id'],
                'units' => (int)$product['quantity'],
                'selling_price' => (float)$product['product_price'] * 1.18,
                'discount' => 0,
                'tax' => '18' ,// Assuming 18% GST as shown in the order details
                'hsn' => '',
            ];
            
        }
        
        // Determine if shipping is same as billing
        $shipping_is_billing = empty($orderDetails['order']['shipping_fullname']) ? true : false;
        // Map payment mode to ShipRocket's accepted values
$payment_mode = strtolower($orderDetails['order']['payment_mode']);
// If payment mode is razorpay, set as Prepaid, otherwise COD
$shiprocket_payment_method = ($payment_mode === 'razorpay') ? "Prepaid" : "COD";
        // Create order payload
        $payload = [
            // 'order_id' => 'ORD-' . strtoupper(substr($orderDetails['order']['billing_fullname'], 0, 3)) . '-' . time(),
            'order_id' => 'ORD-' . strtoupper(substr($orderDetails['order']['billing_fullname'], 0, 3)) . '-' . time(),
            // 'order_id' => $orderDetails['order']['id'],
            'order_date' => date('Y-m-d H:i', strtotime($orderDetails['order']['formatted_date'])),
            'pickup_location' => $config['pickup_location'],
            'channel_id' => '',
            'comment' => 'Order from ' . $_SERVER['HTTP_HOST'],
            'billing_customer_name' => $orderDetails['order']['billing_fullname'],
            'billing_last_name' => '',
            'billing_address' => $orderDetails['order']['billing_address1'],
            'billing_address_2' => $orderDetails['order']['billing_address2'] ?? '',
            'billing_city' => $orderDetails['order']['billing_city'],
            'billing_pincode' => $orderDetails['order']['billing_pincode'],
            'billing_state' => $orderDetails['order']['billing_state'],
            'billing_country' => 'India',
            'billing_email' => $orderDetails['order']['billing_email'],
            'billing_phone' => $orderDetails['order']['billing_mobile'],
            'shipping_is_billing' => $shipping_is_billing,
            'payment_method' => $shiprocket_payment_method, // Should be 'COD' or 'Prepaid'
            'sub_total' => (float)$orderDetails['order']['grandtotal'],
            'length' => 10, // Default dimensions - adjust as needed
            'breadth' => 10,
            'height' => 10,
            'weight' => 1, // Default weight in kg
            'order_items' => $order_items,
        ];
        
        // Add shipping details if different from billing
        if (!$shipping_is_billing) {
            $payload['shipping_customer_name'] = $orderDetails['order']['shipping_fullname'];
            $payload['shipping_last_name'] = '';
            $payload['shipping_address'] = $orderDetails['order']['shipping_address1'];
            $payload['shipping_address_2'] = $orderDetails['order']['shipping_address2'] ?? '';
            $payload['shipping_city'] = $orderDetails['order']['shipping_city'];
            $payload['shipping_pincode'] = $orderDetails['order']['shipping_pincode'];
            $payload['shipping_state'] = $orderDetails['order']['shipping_state'];
            $payload['shipping_country'] = 'India';
            $payload['shipping_email'] = $orderDetails['order']['shipping_email'];
            $payload['shipping_phone'] = $orderDetails['order']['shipping_mobile'];
        }
        
        // Create shipment via API
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/orders/create/adhoc",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $token
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $err
            ];
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['order_id'])) {
            // Save shipment details to database
            try {
                $db_conn = new logics(); // Assuming your logics class has database connection
                
                // Prepare shipment data to save
                $shipment_data = [
                    'order_id' => $orderDetails['order']['id'],
                    'shiprocket_order_id' => $result['order_id'],
                    'shipment_id' => $result['shipment_id'] ?? null,
                    'tracking_number' => $result['tracking_number'] ?? null,
                    'courier_company' => $result['courier_name'] ?? null,
                    'awb_code' => $result['awb_code'] ?? null,
                    'payment_method' => $shiprocket_payment_method,
                    'shipping_cost' => $result['shipping_cost'] ?? null,
                    'customer_name' => $orderDetails['order']['billing_fullname'],
                    'customer_phone' => $orderDetails['order']['billing_mobile'],
                    'shipping_address' => !$shipping_is_billing ? 
                        $orderDetails['order']['shipping_address1'] : $orderDetails['order']['billing_address1'],
                    'shipping_city' => !$shipping_is_billing ? 
                        $orderDetails['order']['shipping_city'] : $orderDetails['order']['billing_city'],
                    'shipping_pincode' => !$shipping_is_billing ? 
                        $orderDetails['order']['shipping_pincode'] : $orderDetails['order']['billing_pincode'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'status' => 'created',
                    'response_data' => json_encode($result)
                ];
                
                // Save to database - using a method from your logics class
                // Assuming the method is available or implement it
                $saved = $db_conn->saveShipment($shipment_data);
                
                return [
                    'success' => true,
                    'shiprocket_order_id' => $result['order_id'],
                    'shipment_id' => $result['shipment_id'] ?? null,
                    'tracking_number' => $result['tracking_number'] ?? null,
                    'saved_to_db' => $saved,
                    'response' => $result
                ];
            } catch (Exception $e) {
                // Continue even if saving to DB fails
                return [
                    'success' => true,
                    'shiprocket_order_id' => $result['order_id'],
                    'shipment_id' => $result['shipment_id'] ?? null,
                    'db_error' => $e->getMessage(),
                    'response' => $result
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create shipment',
                'error' => $result
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Process request
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $response = createShipment($order_id, $shiprocket_config);
    
    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If called directly, show form
$title = "Create Shipment";
require_once('header.php');
?>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Create ShipRocket Shipment</h5>
            <a href="order-list.php" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back"></i> Back to Orders
            </a>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['id'])): ?>
                <?php
                    $getUsers = new logics();
                    $orderDetails = $getUsers->getOrderDetails($_GET['id']);
                    if (!empty($orderDetails['status']) && $orderDetails['status'] == 1):
                ?>
                    <!-- Order Summary Card -->
                    <div class="card border shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Order Information</h6>
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <span class="badge bg-primary rounded-pill p-2">
                                                <i class="bx bx-package"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Order #<?php echo $orderDetails['order']['id']; ?></h6>
                                            <small class="text-muted"><?php echo $orderDetails['order']['formatted_date']; ?></small>
                                            <div class="text-dark mt-1">
                                                <small>
                                                    <strong>Payment:</strong> <?php echo ucfirst($orderDetails['order']['payment_mode']); ?>
                                                </small>
                                            </div>
                                            <small>
                                                <strong>Total:</strong> ₹<?php echo $orderDetails['order']['grandtotal']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Customer Information</h6>
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <span class="badge bg-info rounded-pill p-2">
                                                <i class="bx bx-user"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo $orderDetails['order']['billing_fullname']; ?></h6>
                                            <small class="text-muted"><?php echo $orderDetails['order']['billing_email']; ?></small>
                                            <div class="mt-1">
                                                <small><?php echo $orderDetails['order']['billing_mobile']; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Shipping To</h6>
                                    <address class="small mb-0">
                                        <?php if (!empty($orderDetails['order']['shipping_fullname'])): ?>
                                            <strong><?php echo $orderDetails['order']['shipping_fullname']; ?></strong><br>
                                            <?php echo $orderDetails['order']['shipping_address1']; ?><br>
                                            <?php if(!empty($orderDetails['order']['shipping_address2'])) echo $orderDetails['order']['shipping_address2'] . '<br>'; ?>
                                            <?php echo $orderDetails['order']['shipping_city']; ?>, 
                                            <?php echo $orderDetails['order']['shipping_state']; ?> - <?php echo $orderDetails['order']['shipping_pincode']; ?>
                                        <?php else: ?>
                                            <strong><?php echo $orderDetails['order']['billing_fullname']; ?></strong><br>
                                            <?php echo $orderDetails['order']['billing_address1']; ?><br>
                                            <?php if(!empty($orderDetails['order']['billing_address2'])) echo $orderDetails['order']['billing_address2'] . '<br>'; ?>
                                            <?php echo $orderDetails['order']['billing_city']; ?>, 
                                            <?php echo $orderDetails['order']['billing_state']; ?> - <?php echo $orderDetails['order']['billing_pincode']; ?>
                                        <?php endif; ?>
                                    </address>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Package Details</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="bg-light p-2 rounded">
                                                <small class="d-block text-muted">Items</small>
                                                <strong><?php echo count($orderDetails['products']); ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-light p-2 rounded">
                                                <small class="d-block text-muted">Weight</small>
                                                <strong>1.0 kg</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary px-4" id="createShipment" 
                            data-order-id="<?php echo $_GET['id']; ?>">
                            <i class="bx bx-package me-2"></i> Create Shipment
                        </button>
                    </div>
                    
                    <div id="shipmentResult" class="mt-4" style="display: none;"></div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i> Order not found
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="text-center mb-4">
                            <i class="bx bx-package text-primary" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Create New Shipment</h5>
                            <p class="text-muted">Enter the order ID to proceed with shipment creation</p>
                        </div>
                        
                        <form action="create_shipment.php" method="get">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-search"></i>
                                </span>
                                <input type="text" class="form-control" name="id" id="order_id" 
                                    placeholder="Enter Order ID" required>
                                <button type="submit" class="btn btn-primary">Continue</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createShipmentBtn = document.getElementById('createShipment');
    
    if (createShipmentBtn) {
        createShipmentBtn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const resultDiv = document.getElementById('shipmentResult');
            
            createShipmentBtn.disabled = true;
            createShipmentBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            resultDiv.style.display = 'none';
            
            // Create form data
            const formData = new FormData();
            formData.append('order_id', orderId);
            
            fetch('create_shipment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                createShipmentBtn.disabled = false;
                createShipmentBtn.innerHTML = '<i class="bx bx-package me-2"></i> Create Shipment';
                resultDiv.style.display = 'block';
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bx bx-check-circle" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Shipment Created Successfully</h5>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body p-3">
                                                    <small class="text-muted d-block mb-1">ShipRocket Order ID</small>
                                                    <h6>${data.shiprocket_order_id}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        ${data.shipment_id ? `
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0">
                                                <div class="card-body p-3">
                                                    <small class="text-muted d-block mb-1">Shipment ID</small>
                                                    <h6>${data.shipment_id}</h6>
                                                </div>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bx bx-error-circle" style="font-size: 2rem;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Failed to Create Shipment</h5>
                                    <p>${data.message}</p>
                                    ${data.error ? `<div class="mt-3 p-3 bg-light rounded"><small class="text-monospace">${JSON.stringify(data.error, null, 2)}</small></div>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                createShipmentBtn.disabled = false;
                createShipmentBtn.innerHTML = '<i class="bx bx-package me-2"></i> Create Shipment';
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bx bx-error-circle" style="font-size: 2rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Error</h5>
                                <p>An unexpected error occurred: ${error.message}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        });
    }
});
</script>

<?php
require_once('footer.php');
?>