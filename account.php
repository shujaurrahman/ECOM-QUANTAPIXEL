<?php 
session_start();
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getFormattedStatus($status) {
    // Format status for display
    $status = strtolower($status);
    $status = str_replace('_', ' ', $status);
    return ucwords($status);
}

if(!empty($_SESSION['username'])){
    require_once('./header.php');
    $logicsObj = new logics();
    
    // Get shipment data for all orders of this user
    $shipmentData = array();
    if($getOrders['count'] > 0) {
        for($i=0; $i<$getOrders['count']; $i++) {
            if($getOrders['user_id'][$i] == $_SESSION['user_id']) {
                $shipment = $logicsObj->getShipmentByOrderId($getOrders['id'][$i]);
                $shipmentData[$getOrders['id'][$i]] = $shipment;
            }
        }
    }
?>

    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30 d-flex justify-content-between align-items-center">
                    <div class="d-flex">
                        <a class="breadcrumb-item text-dark" href="#">Home</a>
                        <!-- <a class="breadcrumb-item text-dark" href="#">Shop</a> -->
                        <span class="breadcrumb-item active">Account</span>
                    </div>
                    <a href="logout" class="text-dark"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Breadcrumb End -->


    <!-- Checkout Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-12">
                <h5 class="section-title position-relative text-uppercase mb-3"><span class="bg-secondary pr-3">My Orders</span></h5>
                <div style="overflow-x:scroll">
                    <table class="table table-bordered order-table" id="example">
                        <thead>
                            <tr>
                                <th width="5%">Sno</th>
                                <th width="15%">Order Id</th>
                                <th width="15%">Products</th>
                                <th width="15%">Price</th>
                                <th width="15%">Status</th>
                                <th width="35%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            
                            for($i=0; $i<$getOrders['count']; $i++){ 
                                if($getOrders['user_id'][$i]==$_SESSION['user_id']){
                                    // Get status from either shipment or order
                                    $orderStatus = 'Order Placed';

                                    // Check if shipment data exists
                                    if(!empty($shipmentData[$getOrders['id'][$i]]) && 
                                       !empty($shipmentData[$getOrders['id'][$i]]['status']) && 
                                       $shipmentData[$getOrders['id'][$i]]['status'] == 1) {
                                        
                                        $shipStatus = $shipmentData[$getOrders['id'][$i]]['status_value'][0];
                                        if(!empty($shipStatus)) {
                                            $orderStatus = getFormattedStatus($shipStatus);
                                        }
                                    } else if(!empty($getOrders['order_status'][$i])) {
                                        // If no shipment but order has status
                                        $orderStatus = getFormattedStatus($getOrders['order_status'][$i]);
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td>
                                        <span class="order-id">#<?php echo $getOrders['id'][$i]; ?></span>
                                    </td>
                                    <td style="color: #c4996c !important ;">
                                        <a href="javascript:void(0);" 
                                           onclick="fetchOrderProducts(<?php echo $getOrders['id'][$i]; ?>)"
                                           class="products-link">
                                            <i class="bi bi-box-seam me-1"></i>
                                            <?php echo $getOrders['total_products'][$i]; ?> Products
                                        </a>
                                    </td>
                                    <td class="price-column">
                             
₹<?php echo number_format(floatval(str_replace(',', '', $getOrders['grandtotal'][$i])), 2); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background-color: #c4996c; color: #fff;">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                                            <?php echo $orderStatus; // Use $orderStatus instead of $getOrders['order_status'][$i] ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <div class="d-flex gap-2">
                                            <a href="order-details.php?id=<?php echo $getOrders['id'][$i]; ?>" 
                                               class="btn btn-invoice" 
                                               title="View Invoice">
                                                <i class="bi bi-file-earmark-text me-1"></i> Invoice
                                            </a>
                                            &nbsp; &nbsp; &nbsp;
                                            <a href="./trackorder?id=<?php echo $getOrders['id'][$i]; ?>" 
                                               class="btn btn-track" 
                                               title="Track Order">
                                                <i class="bi bi-truck me-1"></i> Track Order
                                            </a>
                                            
                                            <?php 
                                            // Show cancel button only for orders that aren't already cancelled or delivered
                                            // Use $orderStatus (the displayed status) instead of checking the database field directly
                                            if (strtolower($orderStatus) != 'cancelled' && 
                                                strtolower($orderStatus) != 'delivered') { 
                                            ?>
                                                &nbsp; &nbsp; &nbsp;
                                                <a href="javascript:void(0);" 
                                                   onclick="cancelOrder(<?php echo $getOrders['id'][$i]; ?>)" 
                                                   class="btn btn-cancel" 
                                                   title="Cancel Order">
                                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>

                                <?php
                                }
                            }
                            ?>
                            
                        </tbody>

                    </table>
                </div>

                
            </div>
            
        </div>
    </div>
    <!-- Checkout End -->

    <?php 
require_once('./footer.php');
} else {
    // Set a flag to trigger the modal
    $_SESSION['show_signin_modal'] = true;

    // Redirect to home page
    header('Location: index');
    exit(); // Always exit after a redirect to prevent further code execution
}
?>


<script>
    new DataTable('#example');
</script>


<script>
function fetchOrderProducts(orderId) {
    // Show loading state
    $('#OrderProductsModal tbody').html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');
    $('#OrderProductsModal').modal('show');

    $.ajax({
        url: 'open-logics.php',
        type: 'POST',
        data: { 
            order_id: orderId, 
            getOrderProducts: 'getOrderProducts' 
        },
        success: function(response) {
            let data;
            try {
                if (typeof response === "string") {
                    data = JSON.parse(response);
                } else {
                    data = response;
                }

                if (data.status == '1' && data.count > 0) {
                    let rows = '';
                    for (let i = 0; i < data.count; i++) {
                        rows += `<tr>
                            <td>${i + 1}</td>
                            <td><img src="panels/admin/product/${data.product_image[i]}" class="img-fluid" style="max-width: 50px; border-radius: 50%;" alt="${data.product_name[i]}"></td>
                            <td><a href="./product-view?slug=${data.product_slug[i]}">${data.product_name[i]}</a></td>
                            <td>${data.product_type[i]}</td>
                            <td>₹${data.product_price[i]}</td>
                            <td>${data.quantity[i]}</td>
                            <td>₹${data.product_actual_price[i]}</td>
                        </tr>`;
                    }
                    $('#OrderProductsModal tbody').html(rows);
                } else {
                    $('#OrderProductsModal tbody').html('<tr><td colspan="8" class="text-center">No products found</td></tr>');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                $('#OrderProductsModal tbody').html('<tr><td colspan="8" class="text-center text-danger">Error loading products</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
            $('#OrderProductsModal tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load products. Please try again.</td></tr>');
        }
    });
}

function cancelOrder(orderId) {
    if (!confirm("Are you sure you want to cancel this order?")) {
        return;
    }
    
    const cancelBtn = event.currentTarget;
    const originalText = cancelBtn.innerHTML;
    cancelBtn.disabled = true;
    cancelBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
    
    $.ajax({
        url: 'cancel_order.php',
        type: 'POST',
        data: { order_id: orderId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update the status badge immediately
                const statusBadge = cancelBtn.closest('tr').querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.innerHTML = '<i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> Cancelled';
                }
                
                // Hide the cancel button
                cancelBtn.style.display = 'none';
                
                // Optional: Show a success message
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success p-2 mt-2';
                successDiv.style.fontSize = '12px';
                successDiv.textContent = 'Order cancelled successfully';
                cancelBtn.parentElement.appendChild(successDiv);
                
                // Remove the success message after 3 seconds
                setTimeout(() => {
                    successDiv.remove();
                    location.reload(); // Reload to update all statuses
                }, 2000);
            } else {
                alert(response.message || 'Failed to cancel order');
                cancelBtn.disabled = false;
                cancelBtn.innerHTML = originalText;
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
            alert('Failed to process your request. Please try again.');
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = originalText;
        }
    });
}

// Add this to ensure jQuery and Bootstrap are loaded
$(document).ready(function() {
    // Initialize DataTable
    new DataTable('#example');
    
    // Ensure modal is working
    $('#OrderProductsModal').on('hidden.bs.modal', function () {
        $(this).find('tbody').html('');
    });
});
</script>

<!-- Order Products Modal -->
<div class="modal fade" id="OrderProductsModal" tabindex="-1" role="dialog" aria-labelledby="OrderProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="OrderProductsModalLabel">Order Products</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Products will be dynamically added here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .order-table {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    .order-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 15px;
    }
    .order-table tbody td {
        padding: 15px;
        vertical-align: middle;
    }
    .order-id {
        font-weight: 600;
        color: #2c3e50;
    }
    .products-link {
        text-decoration: none;
        font-weight: 500;
    }
    .products-link:hover {
        color: #c4996c;
    }
    .price-column {
        font-weight: 600;
        color: #2c3e50;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
        background-color: #c4996c !important; /* Force the same color for all status badges */
        color: #fff !important;
    }
    .action-buttons .btn {
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    .action-buttons .btn:hover {
        transform: translateY(-1px);
    }
    .btn-invoice {
        background: #fff;
        border: 1px solid #c4996c;
        color: #2c3e50;
    }
    .btn-invoice:hover {
        background: #ffff;
        color: #c4996c;
    }
    .btn-track {
        background: #fff;
        border: 1px solid #c4996c;
        color: #3498db;
    }
    .btn-track:hover {
        background: #fff;
        color: #c4996c;
    }
    .btn-cancel {
        background: #fff;
        border: 1px solid #c4996c;
        color: #e74c3c;
    }
    .btn-cancel:hover {
        background: #fff;
        color: #c4996c;
    }
</style>
