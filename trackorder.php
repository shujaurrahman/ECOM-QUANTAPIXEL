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
$expectedDate = 'Not Available';
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
        'status' => 'pickup Generated',
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
$deliveryDetails = [];

if ($orderId > 0) {
    // Get order details
    $orderDetails = $logicsObj->getOrderDetails($orderId);
    $shipment = $logicsObj->getShipmentByOrderId($orderId);
    
    if ($shipment['status'] == 1) {
        // Shipment exists, try to get tracking information
        if (!empty($shipment['shipment_id'][0])) {
            // Get token from file or regenerate if needed
            $tokenFile = 'shiprocket_token.json';
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
                // Fetch tracking data from ShipRocket API using shipment_id
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
                        // Map ShipRocket status codes to our step system
                        $shipmentStatus = $trackingData['tracking_data']['shipment_status'];
                        
                        // Get current shipment data
                        if (isset($trackingData['tracking_data']['shipment_track']) && !empty($trackingData['tracking_data']['shipment_track'])) {
                            $shipmentTrack = $trackingData['tracking_data']['shipment_track'][0];
                            $currentStatus = isset($shipmentTrack['current_status']) && !empty($shipmentTrack['current_status']) ? 
                                $shipmentTrack['current_status'] : getHumanReadableStatus($shipmentStatus);
                            $courierName = $shipmentTrack['courier_name'] ?? 'Not Available';
                            $awbCode = $shipmentTrack['awb_code'] ?? 'Not Available';
                            
                            // Add additional delivery information
                            $deliveryDetails = [
                                'origin' => $shipmentTrack['origin'] ?? '',
                                'destination' => $shipmentTrack['destination'] ?? '',
                                'weight' => $shipmentTrack['weight'] ?? '',
                                'pickup_date' => !empty($shipmentTrack['pickup_date']) ? 
                                    date('F j, Y', strtotime($shipmentTrack['pickup_date'])) : '',
                                'delivered_date' => !empty($shipmentTrack['delivered_date']) ? 
                                    date('F j, Y', strtotime($shipmentTrack['delivered_date'])) : '',
                                'pod_url' => $shipmentTrack['pod_status'] ?? ''
                            ];
                        }
                        
                        // Set expected delivery date with better debugging
                        if (isset($trackingData['tracking_data']['etd']) && !empty($trackingData['tracking_data']['etd'])) {
                            try {
                                $expectedDate = date('F j, Y', strtotime($trackingData['tracking_data']['etd']));
                            } catch (Exception $e) {
                                // Log the error if date parsing fails
                                error_log("Failed to parse ETD date: " . $e->getMessage());
                                $expectedDate = 'Date format error';
                            }
                        } else if (isset($trackingData['tracking_data']['shipment_track'][0]['edd']) && 
                                   !empty($trackingData['tracking_data']['shipment_track'][0]['edd'])) {
                            try {
                                $expectedDate = date('F j, Y', strtotime($trackingData['tracking_data']['shipment_track'][0]['edd']));
                            } catch (Exception $e) {
                                error_log("Failed to parse EDD date: " . $e->getMessage());
                                $expectedDate = 'Date format error';
                            }
                        } else {
                            $expectedDate = 'Estimating...';
                        }
                        
                        // Force explicit parsing of date from API response
                        if (isset($trackingData) && is_array($trackingData)) {
                            // Try accessing ETD directly as string
                            if (isset($trackingData['tracking_data']['etd']) && !empty($trackingData['tracking_data']['etd'])) {
                                $raw_date = $trackingData['tracking_data']['etd'];
                                // Manually format date (handle potential date format issues)
                                $timestamp = strtotime($raw_date);
                                if ($timestamp) {
                                    $expectedDate = date('F j, Y', $timestamp);
                                } else {
                                    // If strtotime fails, use the date as is with a note
                                    $expectedDate = $raw_date . " (format issue)";
                                }
                            } 
                            // Try accessing EDD from shipment_track array
                            else if (isset($trackingData['tracking_data']['shipment_track'][0]['edd']) && 
                                     !empty($trackingData['tracking_data']['shipment_track'][0]['edd'])) {
                                $raw_date = $trackingData['tracking_data']['shipment_track'][0]['edd'];
                                $timestamp = strtotime($raw_date);
                                if ($timestamp) {
                                    $expectedDate = date('F j, Y', $timestamp);
                                } else {
                                    // If strtotime fails, use the date as is with a note
                                    $expectedDate = $raw_date . " (format issue)";
                                }
                            }
                            // Fallback for if we can't find date in the expected locations
                            else {
                                $expectedDate = 'Estimating...';
                                error_log("Expected delivery date not found in tracking data: " . json_encode($trackingData));
                            }
                        }
                        
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
                                for ($i = 0; $i < count($steps); $i++) {
                                    if ($i < $progressStep) {
                                        $steps[$i]['status'] = 'completed';
                                    } else if ($i == $progressStep) {
                                        $steps[$i]['status'] = 'active';
                                    } else {
                                        $steps[$i]['status'] = 'pending';
                                    }
                                }
                                
                                // Calculate progress percentage (from 0% to 100%)
                                $progressPercentage = ($progressStep / (count($steps) - 1)) * 100;
                            } else {
                                // Handle special statuses (RTO, cancelled, etc.)
                                switch ($statusMapping[$shipmentStatus]) {
                                    case 'cancelled':
                                        $isCancelled = true;
                                        break;
                                    case 'rto':
                                    case 'rto-delivered':
                                        $currentStatus = 'Returned to Origin';
                                        break;
                                    case 'lost':
                                        $currentStatus = 'Package Lost';
                                        break;
                                    case 'undelivered':
                                        $currentStatus = 'Delivery Attempted';
                                        break;
                                    case 'delayed':
                                        $currentStatus = 'Delivery Delayed';
                                        break;
                                    case 'untraceable':
                                        $currentStatus = 'Package Untraceable';
                                        break;
                                }
                            }
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

function getHumanReadableStatus($shipmentStatus) {
    $statusNames = [
        '6' => 'Shipped',
        '7' => 'Delivered',
        '8' => 'Cancelled',
        '9' => 'Return Initiated',
        '10' => 'Return Delivered',
        '12' => 'Lost',
        '13' => 'Pickup Error',
        '17' => 'Out For Delivery',
        '18' => 'In Transit',
        '19' => 'Out For Pickup',
        '21' => 'Undelivered',
        '22' => 'Delayed',
        '27' => 'Pickup Booked',
        '38' => 'Reached Destination Hub',
        '42' => 'Picked Up',
        '48' => 'Reached Warehouse',
        '52' => 'Shipment Booked',
        '59' => 'Box Packing'
    ];
    
    return isset($statusNames[$shipmentStatus]) ? $statusNames[$shipmentStatus] : 'Processing';
}

$statusMapping = [
    // Processing (Step 1)
    '27' => 1, // Pickup Booked -> Processing
    '19' => 1, // Out For Pickup -> Processing
    '52' => 1, // Shipment Booked -> Processing
    
    // Ready to Ship (Step 2)
    '42' => 2, // Picked Up -> Ready to Ship
    '48' => 2, // Reached Warehouse -> Ready to Ship
    '59' => 2, // Box Packing -> Ready to Ship
    '63' => 2, // Packed -> Ready to Ship
    '68' => 2, // PROCESSED AT WAREHOUSE -> Ready to Ship
    
    // Shipped (Step 3)
    '6' => 3,  // Shipped -> Shipped
    '18' => 3, // In Transit -> Shipped
    '38' => 3, // Reached at Destination -> Shipped
    '50' => 3, // In Flight -> Shipped
    '51' => 3, // Handover to Courier -> Shipped
    '54' => 3, // In Transit Overseas -> Shipped
    
    // Out for Delivery (Step 4)
    '17' => 4, // Out for Delivery -> Out for Delivery
    
    // Delivered (Step 5)
    '7' => 5,  // Delivered -> Delivered
    '23' => 5, // Partial_Delivered -> Delivered
    '26' => 5, // FULFILLED -> Delivered
    
    // Special statuses
    '8' => 'cancelled', // Canceled
    '9' => 'rto', // RTO Initiated
    '10' => 'rto-delivered', // RTO Delivered
    '12' => 'lost', // Lost
    '21' => 'undelivered', // Undelivered
    '22' => 'delayed', // Delayed
    '76' => 'untraceable', // UNTRACEABLE
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo $orderId; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/track.css">
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
                            <?php if (!empty($awbCode)): ?>
                                <span class="awb-highlight"><?php echo htmlspecialchars($awbCode); ?></span>
                            <?php else: ?>
                                Not Available
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Courier</div>
                        <div class="detail-value">
                            <?php echo !empty($courierName) ? 
                                  htmlspecialchars($courierName) : 'Not Assigned'; ?>
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
                            if ($shipment && !empty($shipment['created_at'][0])) {
                                echo date('F j, Y', strtotime($shipment['created_at'][0]));
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
        <p class="status-value">
            <?php 
            // Debug output as HTML comment
            echo "<!-- Debug tracking data: " . json_encode($trackingData) . " -->\n";
            
            if (isset($trackingData['tracking_data']['etd']) && !empty($trackingData['tracking_data']['etd'])) {
                $etdDate = $trackingData['tracking_data']['etd'];
                echo "<!-- Using ETD: $etdDate -->\n";
                echo date('F j, Y', strtotime($etdDate));
            } 
            else if (isset($trackingData['tracking_data']['shipment_track'][0]['edd']) && 
                     !empty($trackingData['tracking_data']['shipment_track'][0]['edd'])) {
                $eddDate = $trackingData['tracking_data']['shipment_track'][0]['edd'];
                echo "<!-- Using EDD: $eddDate -->\n";
                echo date('F j, Y', strtotime($eddDate));
            }
            else if (!empty($expectedDate) && $expectedDate != 'Not Available' && $expectedDate != 'Estimating...') {
                echo "<!-- Using expectedDate: $expectedDate -->\n";
                echo htmlspecialchars($expectedDate);
            }
            else {
                echo "Estimating...";
                echo "<!-- No date found in tracking data -->\n";
            }
            ?>
        </p>
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
        <a href="<?php echo isset($trackingData['tracking_data']['track_url']) ? 
            $trackingData['tracking_data']['track_url'] : 
            'https://shiprocket.co/tracking/'.$shipment['awb_code'][0]; ?>" 
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