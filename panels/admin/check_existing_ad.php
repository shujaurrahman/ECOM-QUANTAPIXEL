<?php
session_start();
header('Content-Type: application/json');

if(!empty($_SESSION['role'])) {
    require_once('./logics.class.php');
    $adminObj = new logics();

    if(isset($_GET['location'])) {
        $existingAd = $adminObj->getActiveAdByLocation($_GET['location']);
        
        // Debug logging
        error_log('Checking location: ' . $_GET['location']);
        error_log('Result: ' . json_encode($existingAd));
        
        echo json_encode([
            'exists' => (!empty($existingAd['status']) && $existingAd['status'] == 1),
            'location' => $_GET['location']
        ]);
    } else {
        echo json_encode(['error' => 'No location specified']);
    }
} else {
    echo json_encode(['error' => 'Unauthorized access']);
}