<?php
session_start();
require_once('./header.php');
require_once('./logics.class.php');

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$logicsObj = new logics();
$orderDetails = null;
$shipment = null;
$trackingData = null;
$progressPercentage = 0;
$currentStatus = 'Order Placed';
$expectedDate = '';
$courierName = 'Not Assigned';
$steps = [
    [
        'name' => 'Order Placed',
        'status' => 'completed',
        'icon' => 'shopping-bag',
        'description' => 'Your order has been placed successfully.'
    ],
    [
        'name' => 'Processing',
        'status' => 'pending',
        'icon' => 'settings',
        'description' => 'Your order is being processed.'
    ],
    [
        'name' => 'Ready to Ship',
        'status' => 'pending',
        'icon' => 'package',
        'description' => 'Your order is packed and ready for pickup.'
    ],
    [
        'name' => 'Shipped',
        'status' => 'pending',
        'icon' => 'truck',
        'description' => 'Your order has been shipped.'
    ],
    [
        'name' => 'Out for Delivery',
        'status' => 'pending',
        'icon' => 'map-pin',
        'description' => 'Your order is out for delivery.'
    ],
    [
        'name' => 'Delivered',
        'status' => 'pending',
        'icon' => 'home',
        'description' => 'Your order has been delivered.'
    ]
];

$trackingActivities = [];

if ($orderId > 0) {
    // Get order details
    $orderDetails = $logicsObj->getOrderDetails($orderId);
    $shipment = $logicsObj->getShipmentByOrderId($orderId);
    
    if ($shipment['status'] == 1) {
        // Shipment exists, try to get tracking information
        if (!empty($shipment['shipment_id'][0])) {
            // Get token from file or regenerate if needed
            $tokenFile = './panels/admin/shiprocket_token.json';
            $token = '';
            
            if (file_exists($tokenFile)) {
                $tokenData = json_decode(file_get_contents($tokenFile), true);
                if (isset($tokenData['token']) && isset($tokenData['expiry']) && $tokenData['expiry'] > time()) {
                    $token = $tokenData['token'];
                }
            }
            
            if (empty($token)) {
                // Token doesn't exist or has expired, we'll show a simplified view
                $currentStatus = !empty($shipment['status_value'][0]) ? 
                    ucwords(str_replace('_', ' ', strtolower($shipment['status_value'][0]))) : 'Processing';
            } else {
                // Fetch tracking data from ShipRocket API
                if (!empty($shipment['awb_code'][0])) {
                    // Fetch tracking data using AWB number instead of shipment_id
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/courier/track/awb/" . $shipment['awb_code'][0],
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
                    
                    if (!$err) {
                        $trackingData = json_decode($response, true);
                        
                        if (isset($trackingData['tracking_data']) && isset($trackingData['tracking_data']['track_status']) && $trackingData['tracking_data']['track_status'] == 1) {
                            // Map ShipRocket status codes to our step system
                            $shipmentStatus = $trackingData['tracking_data']['shipment_status'];
                            $statusMapping = [
                                '27' => 1, // Pickup Booked -> Processing
                                '19' => 1, // Out For Pickup -> Processing
                                '42' => 2, // Picked Up -> Ready to Ship
                                '6' => 3,  // Shipped -> Shipped
                                '18' => 3, // In Transit -> Shipped
                                '38' => 3, // Reached at Destination -> Shipped
                                '17' => 4, // Out for Delivery -> Out for Delivery
                                '7' => 5,  // Delivered -> Delivered
                                '8' => 'cancelled', // Canceled
                                '9' => 'rto', // RTO Initiated
                                '10' => 'rto-delivered', // RTO Delivered
                                '12' => 'lost', // Lost
                                '21' => 'undelivered', // Undelivered
                                '22' => 'delayed', // Delayed
                            ];

                            // Get current shipment data
                            $shipmentTrack = $trackingData['tracking_data']['shipment_track'][0];
                            $currentStatus = $shipmentTrack['current_status'];
                            $courierName = $shipmentTrack['courier_name'] ?? 'Not Available';
                            $expectedDate = !empty($trackingData['tracking_data']['etd']) ? 
                                date('F j, Y', strtotime($trackingData['tracking_data']['etd'])) : 'Estimating...';
                            
                            // Get detailed tracking activities
                            if (isset($trackingData['tracking_data']['shipment_track_activities'])) {
                                $trackingActivities = $trackingData['tracking_data']['shipment_track_activities'];
                            }
                            
                            // Calculate progress step
                            $progressStep = 0;
                            if (isset($statusMapping[$shipmentStatus])) {
                                if (is_numeric($statusMapping[$shipmentStatus])) {
                                    $progressStep = $statusMapping[$shipmentStatus];
                                    
                                    // Mark completed steps
                                    for ($i = 0; $i <= $progressStep; $i++) {
                                        $steps[$i]['status'] = $i < $progressStep ? 'completed' : 'active';
                                    }
                                    
                                    // Calculate progress percentage
                                    $progressPercentage = ($progressStep / (count($steps) - 1)) * 100;
                                } else {
                                    // Handle special statuses (RTO, cancelled, etc.)
                                    switch ($statusMapping[$shipmentStatus]) {
                                        case 'cancelled':
                                            $isCancelled = true;
                                            break;
                                        case 'rto':
                                        case 'rto-delivered':
                                            // Add RTO status handling if needed
                                            $currentStatus = 'Returned to Origin';
                                            break;
                                        case 'lost':
                                        case 'undelivered':
                                        case 'delayed':
                                            // Add special status handling
                                            break;
                                    }
                                }
                            }

                            // Add additional delivery information
                            $deliveryDetails = [
                                'origin' => $shipmentTrack['origin'] ?? '',
                                'destination' => $shipmentTrack['destination'] ?? '',
                                'weight' => $shipmentTrack['weight'] ?? '',
                                'pickup_date' => $shipmentTrack['pickup_date'] ? 
                                    date('F j, Y', strtotime($shipmentTrack['pickup_date'])) : '',
                                'delivered_date' => $shipmentTrack['delivered_date'] ? 
                                    date('F j, Y', strtotime($shipmentTrack['delivered_date'])) : '',
                                'pod_url' => $shipmentTrack['pod_status'] ?? ''
                            ];
                        }
                    }
                }
            }
        }
    }
}

// If order status is cancelled, show cancelled message
$isCancelled = false;
if ($orderDetails && isset($orderDetails['order_status']) && strtolower($orderDetails['order_status']) == 'cancelled') {
    $isCancelled = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo $orderId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css" rel="stylesheet">
    <style>
        body{margin-top:20px;}
        
        .order-tracking-container {
            padding: 40px 0;
        }
        
        .tracking-header {
            margin-bottom: 30px;
            position: relative;
        }
        
        .tracking-title {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .tracking-subtitle {
            color: #777;
            font-size: 16px;
        }
        
        /* Enhanced Order Info Box */
        .order-summary {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 40px;
            overflow: hidden;
            border: 1px solid #eaeaea;
        }
        
        .order-summary-header {
            background-color: #f8f9fa;
            padding: 20px 25px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .order-summary-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .order-summary-title i {
            margin-right: 10px;
            color: #c4996c;
        }
        
        .order-summary-body {
            padding: 25px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            width: 140px;
            color: #777;
            font-size: 14px;
        }
        
        .detail-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        
        .badge-outline {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid;
        }
        
        .badge-primary-soft {
            color: #c4996c;
            background-color: rgba(196, 153, 108, 0.1);
            border-color: rgba(196, 153, 108, 0.2);
        }
        
        .badge-danger-soft {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
        }
        
        .order-info {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #eaeaea;
        }
        
        .order-info-title {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .order-info-title i {
            margin-right: 10px;
            color: #c4996c;
        }
        
        .status-cards-container {
            margin-bottom: 40px;
        }
        
        .status-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            height: 100%;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            border: 1px solid #eaeaea;
        }
        
        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: #c4996c;
        }
        
        .status-card:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .status-card .status-label {
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-card .status-value {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            word-break: break-word;
        }
        
        .status-card .status-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            opacity: 0.1;
            font-size: 40px;
            color: #c4996c;
        }
        
        /* Enhanced Tracking Progress */
        .steps {
            border: 1px solid #e7e7e7;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 40px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .steps-header {
            padding: 15px;
            border-bottom: 1px solid #e7e7e7;
            background: #f8f9fa;
        }

        .steps-header .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .progress-bar {
            background-color: #c4996c;
            transition: width 1s ease;
        }

        .steps-body {
            display: table;
            table-layout: fixed;
            width: 100%;
        }

        .step {
            display: table-cell;
            position: relative;
            padding: 25px 15px;
            transition: all 0.25s ease-in-out;
            border-right: 1px dashed #dfdfdf;
            color: rgba(0, 0, 0, 0.65);
            font-weight: 600;
            text-align: center;
            text-decoration: none;
        }

        .step:last-child {
            border-right: 0;
        }

        .step-icon {
            display: block;
            width: 60px;
            height: 60px;
            margin: 0 auto;
            margin-bottom: 15px;
            transition: all 0.25s ease-in-out;
            border-radius: 50%;
            background-color: #f8f9fa;
            color: #adb5bd;
            text-align: center;
            line-height: 60px;
        }
        
        .step-icon svg {
            width: 30px;
            height: 30px;
            vertical-align: middle;
        }
        
        .step-title {
            font-size: 16px;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }
        
        .step-subtitle {
            font-size: 13px;
            font-weight: 400;
            color: #777;
            display: block;
        }

        .step-active .step-icon {
            background-color: rgba(196, 153, 108, 0.1);
            color: #c4996c;
            box-shadow: 0 0 0 5px rgba(196, 153, 108, 0.1);
        }
        
        .step-active .step-title {
            color: #c4996c;
        }

        .step-completed .step-icon {
            background-color: #c4996c;
            color: #fff;
            box-shadow: 0 0 0 5px rgba(196, 153, 108, 0.2);
        }
        
        /* Enhanced Activity Timeline */
        .activity-timeline {
            position: relative;
            padding-left: 60px;
        }
        
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 25px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-point {
            position: absolute;
            left: -60px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #c4996c;
            border: 4px solid #fff;
            box-shadow: 0 0 0 3px rgba(196, 153, 108, 0.3);
            z-index: 1;
        }
        
        .timeline-date {
            color: #777;
            font-size: 13px;
            margin-bottom: 8px;
            background-color: rgba(196, 153, 108, 0.1);
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .timeline-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .timeline-location {
            color: #777;
            font-size: 14px;
            background-color: #f8f9fa;
            padding: 5px 12px;
            border-radius: 6px;
            display: inline-block;
        }
        
        .timeline-location i {
            margin-right: 5px;
        }
        
        /* No Shipment & Cancelled Order */
        .status-container {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            border: 1px solid #eaeaea;
        }
        
        .status-icon {
            font-size: 70px;
            color: #c4996c;
            margin-bottom: 25px;
            position: relative;
        }
        
        .status-icon.cancelled {
            color: #dc3545;
        }
        
        .status-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .status-text {
            font-size: 16px;
            color: #777;
            max-width: 500px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }
        
        .status-actions {
            margin-top: 25px;
        }
        
        .status-actions .btn {
            padding: 10px 24px;
            font-weight: 500;
            margin: 0 8px;
        }
        
        .awb-highlight {
            color: #c4996c;
            font-weight: 700;
            background-color: rgba(196, 153, 108, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        @media (max-width: 767.98px) {
            .steps-header {
                display: none;
            }
            .steps-body,
            .step {
                display: block;
            }
            .step {
                border-right: 0;
                border-bottom: 1px dashed #e7e7e7;
                padding: 20px 15px;
            }
            .step:last-child {
                border-bottom: 0;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
            .activity-timeline {
                padding-left: 40px;
            }
            .timeline-point {
                left: -40px;
            }
        }

        /* Add to your existing CSS */
        .timeline-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }

        .status-delayed {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-rto {
            background-color: #f8d7da;
            color: #721c24;
        }

        .pod-image {
            transition: transform 0.3s ease;
        }

        .pod-image:hover {
            transform: scale(1.02);
        }

        .delivery-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .delivery-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .delivery-info-item:last-child {
            margin-bottom: 0;
        }

        .delivery-info-label {
            font-size: 13px;
            color: #6c757d;
            width: 120px;
        }

        .delivery-info-value {
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container order-tracking-container">
    <div class="tracking-header text-center">
        <h1 class="tracking-title">Track Your Order</h1>
        <p class="tracking-subtitle">Order #<?php echo $orderId; ?></p>
    </div>
    
    <?php if ($isCancelled): ?>
    
    <!-- Cancelled Order Display -->
    <div class="status-container">
        <div class="status-icon cancelled">
            <i data-feather="x-circle"></i>
        </div>
        <h2 class="status-title">Order Cancelled</h2>
        <p class="status-text">
            This order has been cancelled and will not be processed or shipped.
            If you have any questions or wish to place a new order, please contact customer support.
        </p>
        <div class="status-actions">
            <a href="contact.php" class="btn btn-outline-primary">Contact Support</a>
            <a href="account.php" class="btn btn-primary">Return to My Orders</a>
        </div>
    </div>
    
    <?php elseif (empty($shipment) || $shipment['status'] != 1): ?>
    
    <!-- No Shipment Information -->
    <div class="status-container">
        <div class="status-icon">
            <i data-feather="package"></i>
        </div>
        <h2 class="status-title">Processing Your Order</h2>
        <p class="status-text">
            Your order is currently being processed. The shipment will be created soon and tracking 
            information will be available here. We'll notify you once your order ships.
        </p>
        <div class="status-actions">
            <a href="contact.php" class="btn btn-outline-primary">Contact Support</a>
            <a href="account.php" class="btn btn-primary">Return to My Orders</a>
        </div>
    </div>
    
    <?php else: ?>
    
    <!-- Order & Shipment Info Summary -->
    <div class="order-summary">
        <div class="order-summary-header">
            <h3 class="order-summary-title">
                <i data-feather="info"></i> Shipment Information
            </h3>
        </div>
        <div class="order-summary-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">AWB Number</div>
                        <div class="detail-value">
                            <?php if (!empty($shipment['awb_code'][0])): ?>
                                <span class="awb-highlight"><?php echo htmlspecialchars($shipment['awb_code'][0]); ?></span>
                            <?php else: ?>
                                Not Available
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Courier</div>
                        <div class="detail-value">
                            <?php echo !empty($shipment['courier_company'][0]) ? 
                                  htmlspecialchars($shipment['courier_company'][0]) : 'Not Assigned'; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="badge-outline badge-primary-soft">
                                <?php echo htmlspecialchars($currentStatus); ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Order Date</div>
                        <div class="detail-value">
                            <?php 
                            if ($orderDetails && !empty($orderDetails['order_date'])) {
                                echo date('F j, Y', strtotime($orderDetails['order_date']));
                            } else {
                                echo 'Not Available';
                            } 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row status-cards-container">
        <div class="col-md-4 mb-4">
            <div class="status-card">
                <div class="status-icon">
                    <i data-feather="package"></i>
                </div>
                <p class="status-label">Shipped via</p>
                <p class="status-value"><?php echo htmlspecialchars($courierName); ?></p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="status-card">
                <div class="status-icon">
                    <i data-feather="activity"></i>
                </div>
                <p class="status-label">Current Status</p>
                <p class="status-value"><?php echo htmlspecialchars($currentStatus); ?></p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="status-card">
                <div class="status-icon">
                    <i data-feather="calendar"></i>
                </div>
                <p class="status-label">Expected Delivery</p>
                <p class="status-value"><?php echo htmlspecialchars($expectedDate); ?></p>
            </div>
        </div>
    </div>

    <!-- Progress Tracking -->
    <div class="steps">
        <div class="steps-header">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?php echo $progressPercentage; ?>%" 
                     aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <div class="steps-body">
            <?php foreach ($steps as $step): ?>
            <div class="step step-<?php echo $step['status']; ?>">
                <span class="step-icon">
                    <i data-feather="<?php echo $step['icon']; ?>"></i>
                </span>
                <span class="step-title"><?php echo $step['name']; ?></span>
                <span class="step-subtitle"><?php echo $step['description']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Detailed Tracking Information -->
    <?php if (!empty($trackingActivities)): ?>
    <div class="order-info">
        <h3 class="order-info-title">
            <i data-feather="clock"></i> Tracking Activity
        </h3>
        <div class="activity-timeline">
            <?php foreach ($trackingActivities as $activity): ?>
            <div class="timeline-item">
                <div class="timeline-point"></div>
                <div class="timeline-date">
                    <?php echo date('M d, Y - h:i A', strtotime($activity['date'])); ?>
                </div>
                <div class="timeline-title"><?php echo htmlspecialchars($activity['activity']); ?></div>
                <?php if (!empty($activity['location'])): ?>
                <div class="timeline-location">
                    <i data-feather="map-pin"></i> 
                    <?php echo htmlspecialchars($activity['location']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add this after the tracking timeline section -->
    <?php if (!empty($deliveryDetails['pod_url']) && $shipmentStatus == 7): ?>
    <div class="order-info">
        <h3 class="order-info-title">
            <i data-feather="check-circle"></i> Proof of Delivery
        </h3>
        <div class="text-center">
            <img src="<?php echo htmlspecialchars($deliveryDetails['pod_url']); ?>" 
                 alt="Proof of Delivery" 
                 class="img-fluid pod-image"
                 style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($shipment['awb_code'][0])): ?>
    <div class="text-center mt-4 mb-4">
        <a href="https://shiprocket.co/tracking/<?php echo htmlspecialchars($shipment['awb_code'][0]); ?>" 
           target="_blank" class="btn btn-primary">
            <i data-feather="external-link" style="width: 16px; height: 16px; vertical-align: -3px;"></i> 
            Track on Shiprocket
        </a>
        <a href="account.php" class="btn btn-outline-secondary ms-2">
            <i data-feather="arrow-left" style="width: 16px; height: 16px; vertical-align: -3px;"></i>
            Return to My Orders
        </a>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<?php require_once('./footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
    
    // Animate progress bar
    setTimeout(function() {
      const progressBar = document.querySelector('.progress-bar');
      if (progressBar) {
        progressBar.style.transition = 'width 1.5s ease';
        progressBar.style.width = '<?php echo $progressPercentage; ?>%';
      }
    }, 300);
  });
</script>
</body>
</html>