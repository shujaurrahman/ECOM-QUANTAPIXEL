<?php
session_start();
if (!empty($_SESSION['role'])) {
    $title = "orders";
    require_once('header.php');
    require_once('./logics.class.php');

    $getUsers = new logics();
    $verification = $getUsers->getOrders();
    $order_products = $getUsers->getOrderProducts($_GET['id']);

    if (!empty($verification['status']) && $verification['status'] == 1) {
        ?>

        <!--  Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-12">
                        <div class="card-body d-flex justify-content-between">
                            <h5 class="card-title text-primary">Order Details</h5>
                            <!-- <a href="./addCategory" class="btn btn-sm btn-primary">Add new Category</a> -->
                            
                        </div>
                        </div>
                    
                    </div>
                    </div>
                </div>
                </div>



            <?php
            for ($i = 0; $i < $verification['count']; $i++) {
                if($verification['id'][$i] == $_GET['id']){
            ?>
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-sm-12">
                                <div class="card-body" style="overflow-x: scroll;">
                                    <h4 class="text-center"><u>Invoice</u></h4>
                                    <br>
                                    <div class="row">
                                        <div class="col-6">
                                            <div>
                                                <span><?php echo $verification['user_name'][$i]; ?></span><br>
                                                <span><?php echo $verification['user_mobile'][$i]; ?></span><br>
                                                <span><?php echo $verification['user_email'][$i]; ?></span><br>
                                                <span><?php echo $verification['user_address'][$i]; ?></span><br>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-end">
                                                <span>Order Id : <b>#<?php echo $verification['id'][$i]; ?></b></span><br>
                                                <span>Date: <b><?php echo $verification['created_at'][$i]; ?></b></span><br>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td>Sno</td>
                                                <td>Image</td>
                                                <td>Name</td>
                                                <td>Type</td>
                                                <td>Price</td>
                                                <td>Quantity</td>
                                                <td>Weight</td>
                                                <td>Grand Total</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($j = 0; $j < $order_products['count']; $j++) {
                                            ?>
                                            <tr>
                                                <td><?php echo $j+1; ?></td>
                                                <td><img src="./product/<?php echo $order_products['product_image'][$j]; ?>" style="width: 100px;" alt=""></td>
                                                <td><a href="../../product-view?slug=<?php echo $order_products['product_slug'][$j]; ?>" target="_blank"><?php echo $order_products['product_name'][$j]; ?></a></td>
                                                <td><?php echo $order_products['product_type'][$j]; ?></td>
                                                <td><?php echo $order_products['product_actual_price'][$j]; ?></td>
                                                <td><?php echo $order_products['quantity'][$j]; ?></td>
                                                <td><?php echo $order_products['product_weight'][$j]; ?></td>
                                                
                                                <td><?php echo $order_products['product_price'][$j]; ?></td>
                                                
                                                
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <br><br>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <h4><u>Billing Address</u></h4>
                                            <span><?php echo $verification['billing_fullname'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_email'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_mobile'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_address1'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_address2'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_city'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_state'][$i]; ?></span><br>
                                            <span><?php echo $verification['billing_pincode'][$i]; ?></span><br>

                                            <?php
                                            if(!empty($verification['shipping_fullname'][$i])){
                                                ?>
                                                <h4><u>Shipping Address</u></h4>
                                            <span><?php echo $verification['shipping_fullname'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_email'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_mobile'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_address1'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_address2'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_city'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_state'][$i]; ?></span><br>
                                            <span><?php echo $verification['shipping_pincode'][$i]; ?></span><br>
                                                <?php
                                            }
                                            ?>

                                        </div>
                                        <div class="col-lg-4">
                                            <h4><u>Payment Details</u></h4>
                                            <span><b>Payment Mode: </b><?php echo $verification['payment_mode'][$i]; ?></span><br>
                                            <span><b>Payment Amount: </b><?php echo $verification['payment_amount'][$i]; ?></span><br>
                                            <span><b>Payment Reference Number: </b><?php echo $verification['payment_reference'][$i]; ?></span><br>
                                            <span><b>Payment Proof: </b><img src="./payments/<?php echo $verification['payment_proof'][$i]; ?>" style="width: 100px;" alt=""></span><br><br>
                                            <a href="./payments/<?php echo $verification['payment_proof'][$i]; ?>" target="_blank" class="btn btn-sm btn-primary">View Payment Proof</a>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="text-end">
                                                <h4><b>Sub Total: </b><?php echo $verification['subtotal'][$i]; ?></h4>
                                                <h4><b>GST(18%): </b><?php echo $verification['gst'][$i]; ?></h4>
                                                <br>
                                                <h2><b>Grand Total:</b><?php echo $verification['grandtotal'][$i]; ?></h2>
                                                <?php
                                                if(!empty($verification['coupon'][$i])){
                                                ?>
                                                <p>(Coupon Applied <b><?php echo $verification['coupon'][$i]; ?></b> with <?php echo $verification['discount'][$i]; ?> <?php echo $verification['coupon_type'][$i]; ?> Discount )</p>
                                                <?php
                                                }
                                                ?>
                                                <br>
                                                <form action="" method="post">
                                                    <label for="">Order Approval</label>
                                                    <select name="approval" id="" class="form-select">
                                                        <option value="pending">pending</option>
                                                        <option value="approved">Approve</option>
                                                        <option value="rejected">Reject</option>
                                                    </select>
                                                </form>
                                                <br>
                                                <form action="" method="post">
                                                    <label for="">Order Status</label>
                                                    <select name="status" id="" class="form-select">
                                                        <option value="placed">placed</option>
                                                        <option value="dispatched">Dispatched</option>
                                                        <option value="delivered">Delivered</option>
                                                        <option value="cancelled">Cancelled</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>
                                    </div>




                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>

        </div>
        <!-- / Content -->

        <?php
    } else {
        echo "Data not fetched";
    }

    require_once('footer.php');
} else {
    header('location:login.php');
}
?>
<script>
    new DataTable('#example');
</script>