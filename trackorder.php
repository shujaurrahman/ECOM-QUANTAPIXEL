<?php
session_start();
require_once('./header.php');
require_once('./logics.class.php');

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$logicsObj = new logics();
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
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://apiv2.shiprocket.in/v1/external/courier/track/shipment/" . $shipment['shipment_id'][0],
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
                        // Update progress information
                        $currentStatus = $trackingData['tracking_data']['shipment_track'][0]['current_status'] ?? 'Processing';
                        $expectedDate = isset($trackingData['tracking_data']['etd']) ? date('F j, Y', strtotime($trackingData['tracking_data']['etd'])) : 'Estimating...';
                        $courierName = !empty($shipment['courier_company'][0]) ? $shipment['courier_company'][0] : 'Assigned Courier';
                        
                        // Get tracking activities
                        if (isset($trackingData['tracking_data']['shipment_track_activities'])) {
                            $trackingActivities = $trackingData['tracking_data']['shipment_track_activities'];
                        }
                        
                        // Update steps based on tracking status
                        $statusMapping = [
                            'ORDER PLACED' => 0,
                            'PROCESSING' => 1,
                            'MANIFESTED' => 2,
                            'PICKED UP' => 3,
                            'IN TRANSIT' => 3,
                            'OUT FOR DELIVERY' => 4,
                            'DELIVERED' => 5
                        ];
                        
                        $statusKey = strtoupper($currentStatus);
                        $progressStep = 0;
                        
                        foreach ($statusMapping as $status => $step) {
                            if (strpos($statusKey, $status) !== false) {
                                $progressStep = $step;
                                break;
                            }
                        }
                        
                        // Mark completed steps
                        for ($i = 0; $i <= $progressStep; $i++) {
                            $steps[$i]['status'] = $i < $progressStep ? 'completed' : 'active';
                        }
                        
                        // Calculate progress percentage
                        $progressPercentage = ($progressStep / (count($steps) - 1)) * 100;
                    }
                }
            }
        }
    }
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
        
        .order-info {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .order-info-title {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .status-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            height: 100%;
            transition: all 0.3s;
        }
        
        .status-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        
        .status-card .status-label {
            color: #777;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .status-card .status-value {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Tracking Progress */
        .steps {
            border: 1px solid #e7e7e7;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
            background: #fff;
        }

        .steps-header {
            padding: .375rem;
            border-bottom: 1px solid #e7e7e7;
            background: #f8f9fa;
        }

        .steps-header .progress {
            height: .5rem;
            border-radius: 4px;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            background-color: #c4996c;
            transition: width 0.6s ease;
        }

        .steps-body {
            display: table;
            table-layout: fixed;
            width: 100%;
        }

        .step {
            display: table-cell;
            position: relative;
            padding: 1.5rem .75rem;
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
            width: 3rem;
            height: 3rem;
            margin: 0 auto;
            margin-bottom: .75rem;
            transition: all 0.25s ease-in-out;
            border-radius: 50%;
            background-color: #f8f9fa;
            color: #adb5bd;
            text-align: center;
            line-height: 3rem;
        }
        
        .step-icon svg {
            width: 1.5rem;
            height: 1.5rem;
            vertical-align: middle;
        }
        
        .step-title {
            font-size: 14px;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        
        .step-subtitle {
            font-size: 12px;
            font-weight: 400;
            color: #777;
            display: block;
        }

        .step-active .step-icon {
            background-color: rgba(196, 153, 108, 0.1);
            color: #c4996c;
        }
        
        .step-active .step-title {
            color: #c4996c;
        }

        .step-completed .step-icon {
            background-color: #c4996c;
            color: #fff;
        }
        
        /* Activity Timeline */
        .activity-timeline {
            position: relative;
            padding-left: 50px;
            margin-bottom: 50px;
        }
        
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-point {
            position: absolute;
            left: -50px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #c4996c;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #c4996c;
        }
        
        .timeline-date {
            color: #777;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .timeline-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .timeline-location {
            color: #777;
            font-size: 13px;
        }
        
        .no-shipment {
            text-align: center;
            padding: 50px 20px;
        }
        
        .no-shipment-icon {
            font-size: 60px;
            color: #c4996c;
            margin-bottom: 20px;
        }
        
        .no-shipment-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .no-shipment-text {
            font-size: 16px;
            color: #777;
            max-width: 500px;
            margin: 0 auto;
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
                padding: 1rem;
            }
            .step:last-child {
                border-bottom: 0;
            }
        }
    </style>
</head>
<body>

<div class="container order-tracking-container">
    <div class="tracking-header text-center">
        <h1 class="tracking-title">Track Your Order</h1>
        <p class="tracking-subtitle">Order #<?php echo $orderId; ?></p>
    </div>
    
    <?php if (empty($shipment) || $shipment['status'] != 1): ?>
    <!-- No Shipment Information -->
    <div class="card">
        <div class="card-body no-shipment">
            <div class="no-shipment-icon">
                <i data-feather="package"></i>
            </div>
            <h3 class="no-shipment-title">Shipment Not Created Yet</h3>
            <p class="no-shipment-text">
                Your order is being processed. The shipment will be created soon and tracking information will be available here.
                <br><br>
                Please check back later or contact customer support for more information.
            </p>
            <div class="mt-4">
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
                <a href="account.php" class="btn btn-outline-primary ml-2">Go to My Orders</a>
            </div>
        </div>
    </div>
    <?php else: ?>
    
    <!-- Shipment Details -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="status-card">
                <p class="status-label">Shipped via</p>
                <p class="status-value"><?php echo htmlspecialchars($courierName); ?></p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="status-card">
                <p class="status-label">Current Status</p>
                <p class="status-value"><?php echo htmlspecialchars($currentStatus); ?></p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="status-card">
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
        <h3 class="order-info-title">Tracking Activity</h3>
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
                    <i data-feather="map-pin" style="width: 14px; height: 14px; vertical-align: -2px;"></i> 
                    <?php echo htmlspecialchars($activity['location']); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($shipment['awb_code'][0])): ?>
    <div class="text-center mt-4">
        <a href="https://shiprocket.co/tracking/<?php echo htmlspecialchars($shipment['awb_code'][0]); ?>" 
           target="_blank" class="btn btn-primary">
            <i data-feather="external-link" style="width: 16px; height: 16px;"></i> 
            Track on Shiprocket
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
  });
</script>
</body>
</html>