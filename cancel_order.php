<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

try {
    if (empty($_SESSION['user_id'])) {
        throw new Exception('Authentication required');
    }

    if (empty($_POST['order_id'])) {
        throw new Exception('Order ID is required');
    }

    require_once('./logics.class.php');
    require_once('./panels/admin/ship-rocket-cred.php');
    $logicsObj = new logics();

    $order_id = intval($_POST['order_id']);

    // Verify order ownership
    $orderCheck = $logicsObj->verifyOrderOwnership($order_id, $_SESSION['user_id']);
    if (!$orderCheck) {
        throw new Exception('Invalid order or not authorized');
    }

    // Get shipment details first
    $shipment = $logicsObj->getShipmentByOrderId($order_id);
    $shiprocketCancelled = false;
    
    // 1. First update the orders table status
    $orderUpdated = $logicsObj->updateOrderStatus($order_id, 'cancelled');
    
    if (!$orderUpdated) {
        throw new Exception('Failed to update order status');
    }
    
    // 2. If shipment exists, handle ShipRocket API and update shipment status
    if (!empty($shipment) && !empty($shipment['status']) && $shipment['status'] == 1) {
        // Try to cancel on ShipRocket API
        try {
            $token = getShipRocketToken($shiprocket_config);
            
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
                    "ids" => [$shipment['shiprocket_order_id'][0]]
                ]),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $token
                ],
            ]);
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if (!$err && $response) {
                $shiprocketCancelled = true;
            }
        } catch (Exception $e) {
            error_log("ShipRocket API Error: " . $e->getMessage());
            // Continue with local cancellation even if API fails
        }
        
        // Always update shipment status in local database
        $shipmentUpdated = $logicsObj->updateShipmentStatus($order_id, 'cancelled');
        if (!$shipmentUpdated) {
            error_log("Failed to update shipment status for order ID: " . $order_id);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    error_log("Cancel Order Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to cancel order: ' . $e->getMessage()
    ]);
}

// Helper function to get ShipRocket token
function getShipRocketToken($config) {
    if (file_exists($config['token_file'])) {
        $token_data = json_decode(file_get_contents($config['token_file']), true);
        if ($token_data['expiry'] > time()) {
            return $token_data['token'];
        }
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'email' => $config['email'],
            'password' => $config['password']
        ]),
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        throw new Exception("Authentication Error: " . $err);
    }
    
    $result = json_decode($response, true);
    if (!isset($result['token'])) {
        throw new Exception("Failed to get authentication token");
    }
    
    file_put_contents($config['token_file'], json_encode([
        'token' => $result['token'],
        'expiry' => time() + (9 * 24 * 60 * 60)
    ]));
    
    return $result['token'];
}
?>