<?php
session_start();
if (!empty($_SESSION['role'])) {
    $title = "orders";
    require_once('header.php');
    require_once('./logics.class.php');
    $getUsers = new logics();
    $verification = $getUsers->getOrders();

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
                            <h5 class="card-title text-primary">View All Orders</h5>
                            <!-- <a href="./addCategory" class="btn btn-sm btn-primary">Add new Category</a> -->
                            
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
                                                <td>Order ID</td>
                                                <td>User Details</td>
                                                <td>Total Products</td>
                                                <td>Total Price</td>
                                                <td>Created At</td>
                                                <td>Details</td>
                                                <td>Ship</td>
                                                <td>Status</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < $verification['count']; $i++) {
                                            ?>
                                            <tr>
                                                <td><?php echo $verification['id'][$i]; ?></td>
                                                <td>
                                                    <span><?php echo $verification['user_name'][$i]; ?></span><br>
                                                    <span><?php echo $verification['user_mobile'][$i]; ?></span><br>
                                                    <span><?php echo $verification['user_email'][$i]; ?></span><br>
                                                </td>
                                                <td><?php echo $verification['total_products'][$i]; ?> Products</td>
                                                
                                                <td><?php echo $verification['grandtotal'][$i]; ?></td>
                                                <td><?php echo $verification['created_at'][$i]; ?></td>
                                                <td>
                                                    <a href="order-details?id=<?php echo $verification['id'][$i]; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bx bx-show-alt"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php 
                                                    // Check if shipment exists for this order
                                                    $shipment = $getUsers->getShipmentByOrderId($verification['id'][$i]);
                                                    if (!empty($shipment)) {
                                                        // Shipment exists - show view shipment button
                                                        echo '<a href="view_shipment.php?id=' . $verification['id'][$i] . '" class="btn btn-sm btn-outline-success">';
                                                        echo '<i class="bx bx-check-circle"></i>';
                                                        echo '</a>';
                                                    } else {
                                                        // No shipment - show create shipment button
                                                        echo '<a href="create_shipment.php?id=' . $verification['id'][$i] . '" class="btn btn-sm btn-outline-info">';
                                                        echo '<i class="bx bx-package"></i>';
                                                        echo '</a>';
                                                    }
                                                    ?>
                                                </td>

                                                
<td>
    <?php 
    // Check if shipment exists for this order
    $shipment = $getUsers->getShipmentByOrderId($verification['id'][$i]);
    
    if (!empty($shipment)) {
        // Get status directly from shipment table status
        $status = !empty($shipment['status']) ? $shipment['status'] : 'New Order';
        
        // Map status to badge color
        $badgeClass = 'bg-label-info';
        switch(strtolower($status)) {
            case 'awb generated':
                $badgeClass = 'bg-label-primary';
                break;
            case 'delivered':
                $badgeClass = 'bg-label-success';
                break;
            case 'out for delivery':
                $badgeClass = 'bg-label-warning';
                break;
            case 'cancelled':
            case 'returned':
                $badgeClass = 'bg-label-danger';
                break;
        }
        
        // Display status badge
        echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($status) . '</span>';
    } else {
        // No shipment record exists
        echo '<span class="badge bg-label-secondary">New Order</span>';
    }
    ?>
</td>

                                                
<?php
// Inside your main loop where you display orders
// After fetching shipment details
if (!empty($shipment)) {
    $lastUpdate = strtotime($shipment['last_status_update'] ?? '');
    $fourHoursAgo = strtotime('-4 hours');
    
    if ($lastUpdate < $fourHoursAgo) {
        echo "<script>
            shipments.push({
                order_id: '{$verification['id'][$i]}',
                awb_code: '{$shipment['awb_code'][0]}',
                shipment_id: '{$shipment['shipment_id']}'
            });
        </script>";
    }
}
?>

                                            </tr>
                                            <?php
                                        }
                                        ?>
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

<script>
function updateShipmentStatuses(shipments) {
    shipments.forEach(shipment => {
        if (shipment.awb_code || shipment.shipment_id) {
            fetch('update_shipment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(shipment)
            }).catch(error => console.log('Status update error:', error));
        }
    });
}

// Collect all shipments that need updating
let shipments = [];
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (shipments.length > 0) {
        updateShipmentStatuses(shipments);
    }
});
</script>