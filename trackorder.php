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
$awbCode = 'Not Available';
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
                        if (isset($trackingData['tracking_data']['shipment_track_activities']) && 
                            is_array($trackingData['tracking_data']['shipment_track_activities']) &&
                            !empty($trackingData['tracking_data']['shipment_track_activities'])) {
                            $trackingActivities = $trackingData['tracking_data']['shipment_track_activities'];
                        } else {
                            // Try alternate data path that some carriers might use
                            if (isset($trackingData['tracking_data']['shipment_track'][0]['track_activities']) && 
                                is_array($trackingData['tracking_data']['shipment_track'][0]['track_activities']) &&
                                !empty($trackingData['tracking_data']['shipment_track'][0]['track_activities'])) {
                                $trackingActivities = $trackingData['tracking_data']['shipment_track'][0]['track_activities'];
                            } else {
                                $trackingActivities = [];
                            }
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
            if (isset($trackingData['tracking_data']['etd']) && !empty($trackingData['tracking_data']['etd'])) {
                echo date('F j, Y', strtotime($trackingData['tracking_data']['etd']));
            } 
            else if (isset($trackingData['tracking_data']['shipment_track'][0]['edd']) && 
                     !empty($trackingData['tracking_data']['shipment_track'][0]['edd'])) {
                echo date('F j, Y', strtotime($trackingData['tracking_data']['shipment_track'][0]['edd']));
            }
            else if (!empty($expectedDate) && $expectedDate != 'Not Available' && $expectedDate != 'Estimating...') {
                echo htmlspecialchars($expectedDate);
            }
            else {
                echo "Estimating...";
            }
            ?>
        </p>
    </div>
</div>
    </div>


    <!-- Detailed Tracking Information with Enhanced UI -->
<div class="order-info tracking-timeline-container">
    <h3 class="order-info-title">
        <i data-feather="clock"></i> Tracking Activity
    </h3>
    
    <?php if (!empty($trackingActivities) && is_array($trackingActivities)): ?>
    <div class="activity-timeline">
        <?php foreach ($trackingActivities as $key => $activity): ?>
            <?php if (is_array($activity) && isset($activity['date']) && isset($activity['activity'])): 
                $isFirst = ($key === 0);
                $isLast = ($key === count($trackingActivities) - 1);
                $statusClass = $isFirst ? 'current' : '';
            ?>
            <div class="timeline-item <?php echo $statusClass; ?>">
                <div class="timeline-point <?php echo $isFirst ? 'active' : ($isLast ? 'start' : ''); ?>"></div>
                <div class="timeline-content">
                    <div class="timeline-date">
                        <?php 
                        try {
                            $date = new DateTime($activity['date']);
                            echo '<span class="date">' . $date->format('M d, Y') . '</span>';
                            echo '<span class="time">' . $date->format('h:i A') . '</span>';
                        } catch (Exception $e) {
                            echo '<span class="date">' . htmlspecialchars($activity['date']) . '</span>';
                        }
                        ?>
                    </div>
                    <div class="timeline-details">
                        <div class="timeline-title"><?php echo htmlspecialchars($activity['activity']); ?></div>
                        <?php if (isset($activity['location']) && !empty($activity['location'])): ?>
                        <div class="timeline-location">
                            <i data-feather="map-pin"></i> 
                            <?php echo htmlspecialchars($activity['location']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($activity['status']) && !empty($activity['status'])): ?>
                        <div class="timeline-status">
                            <span class="status-code"><?php echo htmlspecialchars($activity['status']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-tracking-state">
        <div class="empty-state-icon">
            <i data-feather="truck"></i>
        </div>
        <h4>Shipment In Progress</h4>
        <p>Your order is being processed. Detailed tracking information will appear here once your package starts its journey.</p>
        <div class="order-status-badge">
            <span class="badge-pulse"></span>
            Current Status: <?php echo htmlspecialchars($currentStatus); ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add styles for the enhanced tracking UI -->
<style>
/* Timeline Container */
.tracking-timeline-container {
    margin-top: 30px;
    margin-bottom: 30px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.order-info-title {
    padding: 20px;
    margin: 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 18px;
    color: #333;
    font-weight: 600;
}

.order-info-title i {
    margin-right: 8px;
    vertical-align: -2px;
    color: #4361ee;
}

/* Timeline Activity */
.activity-timeline {
    padding: 20px 0;
    position: relative;
}

.activity-timeline:before {
    content: '';
    position: absolute;
    left: 35px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
    z-index: 1;
}

.timeline-item {
    position: relative;
    padding: 15px 20px 15px 70px;
    margin-bottom: 0;
    transition: all 0.3s ease;
}

.timeline-item:hover {
    background-color: #f9fbff;
}

.timeline-item.current {
    background-color: #f5f8ff;
}

.timeline-point {
    position: absolute;
    left: 31px;
    top: 28px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e0e0e0;
    border: 2px solid #fff;
    z-index: 2;
}

.timeline-point.active {
    background: #4361ee;
    border-color: #eef2ff;
    box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.2);
    animation: pulse 2s infinite;
}

.timeline-point.start {
    background: #22c55e;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.7);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(67, 97, 238, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
    }
}

.timeline-content {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
}

.timeline-date {
    min-width: 110px;
    margin-right: 15px;
    display: flex;
    flex-direction: column;
}

.timeline-date .date {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.timeline-date .time {
    color: #666;
    font-size: 13px;
    margin-top: 2px;
}

.timeline-details {
    flex: 1;
}

.timeline-title {
    font-size: 15px;
    color: #333;
    font-weight: 500;
    margin-bottom: 5px;
}

.timeline-location {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #666;
    margin-top: 5px;
}

.timeline-location i {
    width: 14px;
    height: 14px;
    margin-right: 5px;
    color: #666;
}

.timeline-status {
    margin-top: 8px;
}

.status-code {
    display: inline-block;
    padding: 3px 10px;
    background-color: #f0f4ff;
    border-radius: 12px;
    color: #4361ee;
    font-size: 12px;
    font-weight: 500;
}

/* Empty state */
.empty-tracking-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f0f4ff;
    margin-bottom: 20px;
}

.empty-state-icon i {
    color: #4361ee;
    width: 40px;
    height: 40px;
}

.empty-tracking-state h4 {
    margin: 0 0 10px;
    font-weight: 600;
    color: #333;
}

.empty-tracking-state p {
    color: #666;
    max-width: 400px;
    margin: 0 0 20px;
}

.order-status-badge {
    display: inline-flex;
    align-items: center;
    background-color: #f0f9ff;
    padding: 8px 16px;
    border-radius: 20px;
    color: #0369a1;
    font-size: 14px;
    font-weight: 500;
}

.badge-pulse {
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: #0ea5e9;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
}

.badge-pulse:before {
    content: '';
    position: absolute;
    border: 1px solid #0ea5e9;
    left: -3px;
    top: -3px;
    right: -3px;
    bottom: -3px;
    border-radius: 50%;
    animation: pulse 2s linear infinite;
}

/* Mobile responsiveness */
@media (max-width: 576px) {
    .timeline-content {
        flex-direction: column;
    }
    
    .timeline-date {
        margin-right: 0;
        margin-bottom: 8px;
        flex-direction: row;
        align-items: center;
    }
    
    .timeline-date .time {
        margin-top: 0;
        margin-left: 8px;
    }
}
</style>

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
    
    <?php if (!empty($awbCode) && $awbCode != 'Not Available'): ?>
    <div class="text-center mt-4 mb-4">
        <a href="<?php echo isset($trackingData['tracking_data']['track_url']) ? 
            $trackingData['tracking_data']['track_url'] : 
            'https://shiprocket.co/tracking/'.$awbCode; ?>" 
           target="_blank" class="btn btn-primary">
            <i data-feather="external-link" style="width: 16px; height: 16px; vertical-align: -3px;"></i> 
            Track on Shiprocket
        </a>
        <a href="account.php" class="btn btn-outline-secondary btn-primary">
            <i data-feather="arrow-left" style="width: 16px; height: 16px; vertical-align: -3px;"></i>
            Return to My Orders
        </a>
    </div>
<?php else: ?>
    <div class="text-center mt-4 mb-4">
        <a href="account.php" class="btn btn-primary">
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
<style>
.activity-message {
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    color: #666;
}
</style>
</body>
</html>