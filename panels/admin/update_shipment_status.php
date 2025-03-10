<?php
session_start();
require_once('./logics.class.php');

// Only allow authorized access
if (empty($_SESSION['role'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['order_id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

// Initialize logics class
$logics = new logics();

// Get token
$tokenFile = './shiprocket_token.json';
$token = '';

if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (isset($tokenData['token']) && isset($tokenData['expiry']) && $tokenData['expiry'] > time()) {
        $token = $tokenData['token'];
    }
}

if (empty($token)) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

// Determine which API endpoint to use
$apiUrl = !empty($input['awb_code']) 
    ? "https://apiv2.shiprocket.in/v1/external/courier/track/awb/{$input['awb_code']}"
    : "https://apiv2.shiprocket.in/v1/external/courier/track/shipment/{$input['shipment_id']}";

// Make API call
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $token
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    error_log("Shipment status update failed for order {$input['order_id']}: $err");
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$trackingData = json_decode($response, true);

if (isset($trackingData['tracking_data'])) {
    $data = $trackingData['tracking_data'];
    $shipmentTrack = $data['shipment_track'][0];
    
    // Prepare update data
    $updateData = [
        'current_status' => $shipmentTrack['current_status'],
        'shipment_status' => $data['shipment_status'],
        'etd' => $data['etd'] ?? null,
        'courier_name' => $shipmentTrack['courier_name'] ?? '',
        'origin' => $shipmentTrack['origin'] ?? '',
        'destination' => $shipmentTrack['destination'] ?? '',
        'pickup_date' => $shipmentTrack['pickup_date'] ?? null,
        'delivered_date' => $shipmentTrack['delivered_date'] ?? null,
        'tracking_url' => $data['track_url'] ?? '',
        'last_status_update' => date('Y-m-d H:i:s')
    ];
    
    // Update activities if available
    if (!empty($data['shipment_track_activities'])) {
        $updateData['activities'] = json_encode($data['shipment_track_activities']);
    }
    
    // Update database using prepared statement
    try {
        $setClause = [];
        $params = [];
        
        foreach ($updateData as $key => $value) {
            if ($value !== null) {
                $setClause[] = "`$key` = :$key";
                $params[":$key"] = $value;
            }
        }
        
        $params[':order_id'] = $input['order_id'];
        
        $sql = "UPDATE shipments SET " . implode(', ', $setClause) . " WHERE order_id = :order_id";
        
        $stmt = $logics->updateShipmentWithQuery($sql, $params);
        
        if ($stmt) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'status' => $updateData['current_status']
            ]);
        } else {
            throw new Exception("Update failed");
        }
    } catch (Exception $e) {
        error_log("Database update failed for order {$input['order_id']}: " . $e->getMessage());
        header("HTTP/1.1 500 Internal Server Error");
        exit;
    }
} else {
    header("HTTP/1.1 404 Not Found");
    exit;
}