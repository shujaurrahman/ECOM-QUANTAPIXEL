<?php
session_start();
require_once('./logics.class.php'); // Fix path to logics.class.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add error logging
    error_log("Payment Data Received: " . print_r($_POST, true));
    
    $adminObj = new logics();
    
    // Get payment details from POST and sanitize
    $data = [
        'order_id' => filter_var($_POST['order_id'], FILTER_SANITIZE_STRING),
        'payment_id' => filter_var($_POST['payment_id'], FILTER_SANITIZE_STRING),
        'payment_signature' => filter_var($_POST['signature'], FILTER_SANITIZE_STRING),
        'amount' => filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'status' => 'success',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Log sanitized data
    error_log("Sanitized Payment Data: " . print_r($data, true));
    
    $result = $adminObj->savePaymentDetails($data);
    
    // Log result
    error_log("Save Payment Result: " . print_r($result, true));
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}