<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!empty($_SESSION['role'])) {
    $title = "Payments";
    require_once('header.php');
    require_once('./logics.class.php');

    

    $getPayments = new logics();
    $verification = $getPayments->getPayments();

    // Change this line from checking 'status' to checking 'result'
    if (!empty($verification['result']) && $verification['result'] == 1) {
        ?>

        <!--  Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-12">
                        <div class="card-body d-flex justify-content-between">
                            <h5 class="card-title text-primary">View All Payments</h5>
                        </div>
                        </div>
                    
                    </div>
                    </div>
                </div>
                </div>



            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-sm-12">
                                <div class="card-body" style="overflow-x: scroll;">
                                    
                                    <br>
                                    <table id="example" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Payment ID</th>
                                                <th>Order ID</th>
                                                <th>Payment ID</th>
                                                <th>Amount</th>
                                                <th>Payment Status</th>
                                                <th>Payment Signature</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($verification['result']) && $verification['result'] == 1) { ?>
                                            <?php if ($verification['count'] > 0) { ?>
                                                <?php for ($i = 0; $i < $verification['count']; $i++) { ?>
                                                    <tr>
                                                        <td><?php echo $verification['id'][$i]; ?></td>
                                                        <td><?php echo $verification['order_id'][$i]; ?></td>
                                                        <td><?php echo $verification['payment_id'][$i]; ?></td>
                                                        <td>₹<?php echo number_format($verification['amount'][$i], 2); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo ($verification['status'][$i] == 'success') ? 'success' : 'danger'; ?>">
                                                                <?php echo ucfirst($verification['status'][$i]); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $verification['payment_signature'][$i]; ?></td>
                                                        <td><?php echo date('d M Y h:i A', strtotime($verification['created_at'][$i])); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No payments found</td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Error loading payments data</td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Content -->

        <?php
    } else {
        echo "No payment data found";
    }

    require_once('footer.php');
} else {
    header('location:login.php');
}
?>
<script>
    new DataTable('#example');
</script>