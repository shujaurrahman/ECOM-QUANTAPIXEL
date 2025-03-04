<?php
session_start();
require_once('./panels/admin/logics.class.php');

// Check if user is logged in
if (empty($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if the required parameters are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shiprocket_order_id']) && isset($_POST['order_id'])) {
    $shiprocket_order_id = $_POST['shiprocket_order_id'];
    $order_id = $_POST['order_id'];
    
    // Verify the order belongs to this user
    $logics = new logics();
    $orderDetails = $logics->getOrderDetails($order_id);
    
    if (empty($orderDetails['status']) || $orderDetails['status'] != 1 || 
        $orderDetails['order']['user_id'] != $_SESSION['user_id']) {
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to cancel this shipment'
        ]);
        exit;
    }
    
    // Get ShipRocket configuration
    require_once('ship-rocket-cred.php');
    
    try {
        // Get authentication token
        $token = null;
        
        // Check if we have a valid cached token
        if (file_exists($shiprocket_config['token_file'])) {
            $token_data = json_decode(file_get_contents($shiprocket_config['token_file']), true);
            
            // Check if token is still valid (less than 9 days old to be safe)
            if ($token_data['expiry'] > time()) {
                $token = $token_data['token'];
            }
        }
        
        // If no valid token, get a new one
        if (!$token) {
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
                    'email' => $shiprocket_config['email'],
                    'password' => $shiprocket_config['password'],
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
                throw new Exception("Failed to authenticate with ShipRocket");
            }
            
            $token = $result['token'];
            
            // Save token for future use
            $expiry = time() + (9 * 24 * 60 * 60);
            file_put_contents($shiprocket_config['token_file'], json_encode([
                'token' => $token,
                'expiry' => $expiry
            ]));
        }
        
        // Now cancel the shipment
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/orders/cancel",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'ids' => [(int)$shiprocket_order_id]
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $token
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            throw new Exception("cURL Error: " . $err);
        }
        
        $result = json_decode($response, true);
        
        // Update the shipment status in our database
        if (isset($result['message']) && strpos(strtolower($result['message']), 'success') !== false) {
            // Update shipment status to cancelled
            $logics->updateShipmentStatus($order_id, 'cancelled');
            
            // Update order status to cancelled
            $logics->updateOrderStatus($order_id, 'cancelled');
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Shipment cancelled successfully'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to cancel shipment',
                'response' => $result
            ]);
        }
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
}
?>
