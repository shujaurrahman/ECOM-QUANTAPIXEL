<?php
session_start();
header('Content-Type: application/json');

if(isset($_POST['submit'])) {
    try {
        require_once('./logics.class.php');
        $adminObj = new logics();

        if(empty($_POST['grandTotal_hidden'])){
            $grandTotal_hidden = $_POST['total'];
        }else{
            $grandTotal_hidden = $_POST['grandTotal_hidden'];
        }
    
        $verification = $adminObj->PlaceOrder(
            $_SESSION['user_id'],
            $_POST['name'],
            $_POST['email'],
            $_POST['mobile'],
            $_POST['address1'],
            $_POST['address2'],
            $_POST['city'],
            $_POST['state'],
            $_POST['pincode'],
            $_POST['shipping_name'],
            $_POST['shipping_email'],
            $_POST['shipping_mobile'],
            $_POST['shipping_address1'],
            $_POST['shipping_address2'],
            $_POST['shipping_city'],
            $_POST['shipping_state'],
            $_POST['shipping_pincode'],
            $_POST['total_products'],
            $_POST['subtotal'],
            $_POST['gst'],
            $_POST['total'],
            $grandTotal_hidden,
            $_POST['coupon_hidden'],
            $_POST['discount_hidden'],
            $_POST['couponType_hidden'],
            $_POST['payment_mode'],
            $_POST['payment_amount'],
            $_POST['payment_reference'],
            $payment_proofName
        );
        
        if(!empty($verification['status']) && $verification['status']==1) {
            echo json_encode([
                'status' => 1,
                'order_id' => $verification['order_id'],
                'amount' => $grandTotal_hidden,
                'customer_name' => $_POST['name'],
                'customer_email' => $_POST['email'],
                'customer_mobile' => $_POST['mobile'],
                'message' => 'Order saved successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to save order'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 0,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}