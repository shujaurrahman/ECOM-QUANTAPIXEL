<?php

function getMockTrackingScenarios($awb_code, $scenario = 'processing') {
    // Current date/time values for realistic tracking timeline
    $now = time();
    $orderDate = date('Y-m-d H:i:s', strtotime('-3 days', $now));
    
    // Base scenario that all other scenarios will extend
    $baseScenario = [
        'tracking_data' => [
            'track_status' => 1,
            'shipment_track' => [[
                'awb_code' => $awb_code,
                'courier_name' => 'ExpressShip',
                'origin' => 'Delhi Warehouse',
                'destination' => 'Mumbai',
                'weight' => '0.5',
                'pickup_date' => date('Y-m-d H:i:s', strtotime('-2 days', $now)),
                'delivered_date' => null,
                'edd' => date('Y-m-d H:i:s', strtotime('+2 days', $now))
            ]],
            'etd' => date('Y-m-d H:i:s', strtotime('+2 days', $now))
        ]
    ];

    // Define the scenarios with their specific data
    $scenarios = [
        'processing' => [
            'shipment_status' => 19,
            'current_status' => 'Processing',
            'activities' => [
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day 2 hours', $now)),
                    'status' => 'ORDER_RECEIVED',
                    'activity' => 'Order Received and Processing Started',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '19'
                ],
                [
                    'date' => $orderDate,
                    'status' => 'ORDER_PLACED',
                    'activity' => 'Order Placed',
                    'location' => 'Online',
                    'sr-status' => '1'
                ]
            ]
        ],
        'picked_up' => [
            'shipment_status' => 42,
            'current_status' => 'Ready to Ship',
            'activities' => [
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-12 hours', $now)),
                    'status' => 'PICKED',
                    'activity' => 'Package Picked Up by Courier',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '42'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-18 hours', $now)),
                    'status' => 'PACKED',
                    'activity' => 'Package Packed and Ready for Pickup',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '27'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day 2 hours', $now)),
                    'status' => 'ORDER_RECEIVED',
                    'activity' => 'Order Received and Processing Started',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '19'
                ],
                [
                    'date' => $orderDate,
                    'status' => 'ORDER_PLACED',
                    'activity' => 'Order Placed',
                    'location' => 'Online',
                    'sr-status' => '1'
                ]
            ]
        ],
        'in_transit' => [
            'shipment_status' => 18,
            'current_status' => 'In Transit',
            'activities' => [
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-6 hours', $now)),
                    'status' => 'TRANSIT',
                    'activity' => 'Package in Transit to Your City',
                    'location' => 'Regional Hub, Pune',
                    'sr-status' => '18'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day', $now)),
                    'status' => 'PICKED',
                    'activity' => 'Package Picked Up by Courier',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '42'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day 6 hours', $now)),
                    'status' => 'PACKED',
                    'activity' => 'Package Packed and Ready for Pickup',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '27'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-2 days', $now)),
                    'status' => 'ORDER_RECEIVED',
                    'activity' => 'Order Received and Processing Started',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '19'
                ],
                [
                    'date' => $orderDate,
                    'status' => 'ORDER_PLACED',
                    'activity' => 'Order Placed',
                    'location' => 'Online',
                    'sr-status' => '1'
                ]
            ]
        ],
        'out_for_delivery' => [
            'shipment_status' => 17,
            'current_status' => 'Out for Delivery',
            'activities' => [
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-2 hours', $now)),
                    'status' => 'OFD',
                    'activity' => 'Out for Delivery Today',
                    'location' => 'Mumbai Local Hub',
                    'sr-status' => '17'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-8 hours', $now)),
                    'status' => 'REACHED_DESTINATION',
                    'activity' => 'Package Reached Destination City',
                    'location' => 'Mumbai Local Hub',
                    'sr-status' => '38'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day', $now)),
                    'status' => 'TRANSIT',
                    'activity' => 'Package in Transit to Your City',
                    'location' => 'Regional Hub, Pune',
                    'sr-status' => '18'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-2 days', $now)),
                    'status' => 'PICKED',
                    'activity' => 'Package Picked Up by Courier',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '42'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-2 days 6 hours', $now)),
                    'status' => 'PACKED',
                    'activity' => 'Package Packed and Ready for Pickup',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '27'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-3 days', $now)),
                    'status' => 'ORDER_RECEIVED',
                    'activity' => 'Order Received and Processing Started',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '19'
                ],
                [
                    'date' => $orderDate,
                    'status' => 'ORDER_PLACED',
                    'activity' => 'Order Placed',
                    'location' => 'Online',
                    'sr-status' => '1'
                ]
            ]
        ],
        'delivered' => [
            'shipment_status' => 7,
            'current_status' => 'Delivered',
            'activities' => [
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 hour', $now)),
                    'status' => 'DLVD',
                    'activity' => 'Package Delivered Successfully',
                    'location' => 'Mumbai',
                    'sr-status' => '7'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-6 hours', $now)),
                    'status' => 'OFD',
                    'activity' => 'Out for Delivery Today',
                    'location' => 'Mumbai Local Hub',
                    'sr-status' => '17'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-8 hours', $now)),
                    'status' => 'REACHED_DESTINATION',
                    'activity' => 'Package Reached Destination City',
                    'location' => 'Mumbai Local Hub',
                    'sr-status' => '38'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-1 day', $now)),
                    'status' => 'TRANSIT',
                    'activity' => 'Package in Transit to Your City',
                    'location' => 'Regional Hub, Pune',
                    'sr-status' => '18'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-2 days', $now)),
                    'status' => 'PICKED',
                    'activity' => 'Package Picked Up by Courier',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '42'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-2 days 6 hours', $now)),
                    'status' => 'PACKED',
                    'activity' => 'Package Packed and Ready for Pickup',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '27'
                ],
                [
                    'date' => date('Y-m-d H:i:s', strtotime('-3 days', $now)),
                    'status' => 'ORDER_RECEIVED',
                    'activity' => 'Order Received and Processing Started',
                    'location' => 'Delhi Fulfillment Center',
                    'sr-status' => '19'
                ],
                [
                    'date' => $orderDate,
                    'status' => 'ORDER_PLACED',
                    'activity' => 'Order Placed',
                    'location' => 'Online',
                    'sr-status' => '1'
                ]
            ]
        ]
    ];

    if (!isset($scenarios[$scenario])) {
        $scenario = 'processing';
    }

    $selectedScenario = $scenarios[$scenario];
    $baseScenario['tracking_data']['shipment_status'] = $selectedScenario['shipment_status'];
    $baseScenario['tracking_data']['shipment_track'][0]['current_status'] = $selectedScenario['current_status'];
    $baseScenario['tracking_data']['shipment_track_activities'] = $selectedScenario['activities'];

    return $baseScenario;
}