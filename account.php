<?php 
session_start();
if(!empty($_SESSION['username'])){
require_once('./header.php');
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
                    <table class="table table-bordered" id="example">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>Order Id</th>
                                <th>Products Count</th>
                                <th>Price</th>
                                <!-- <th>Order Approval</th> -->
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            
                            for($i=0;$i<$getOrders['count'];$i++){ 
                                if($getOrders['user_id'][$i]==$_SESSION['user_id']){
                                ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo $getOrders['id'][$i]; ?></td>
                                    <td><a href="" data-toggle="modal" data-target="#OrderProductsModal" 
                                    onclick="fetchOrderProducts(<?php echo $getOrders['id'][$i]; ?>)"
                                    
                                    ><?php echo $getOrders['total_products'][$i]; ?> Products</a></td>
                                    <td><?php echo $getOrders['grandtotal'][$i]; ?></td>
                                    <!-- <td><b style="color:<?php if($getOrders['approval'][$i]=='pending'){ echo "orange"; }elseif($getOrders['approval'][$i]=='approved'){ echo "green"; }elseif($getOrders['approval'][$i]=='rejected'){ echo "red"; } ?>"><?php echo $getOrders['approval'][$i]; ?></b></td> -->
                                    <td><b style="color:<?php if($getOrders['order_status'][$i]=='placed'){ echo "orange"; }elseif($getOrders['order_status'][$i]=='dispatched'){ echo "blue"; }elseif($getOrders['order_status'][$i]=='delivered'){ echo "green"; }elseif($getOrders['order_status'][$i]=='cancelled'){ echo "red"; } ?>"><?php echo $getOrders['order_status'][$i]; ?></b></td>
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
    // alert(orderId)
    $.ajax({
        url: 'open-logics.php', // Create a separate PHP file to handle the request
        type: 'POST',
        data: { order_id: orderId, getOrderProducts:'getOrderProducts' },
        success: function(response) {
            if (typeof response === "string") {
                    data = JSON.parse(response);
                }
            // const data = JSON.parse(response);

            if (data.status == '1') {
                console.log(response)
                let rows = '';
                for (let i = 0; i < data.count; i++) {
                    rows += `<tr>
                        <td>${i + 1}</td>
                        <td><img src="panels/admin/product/${data.product_image[i]}" style="border-radius: 50%;width:50px" alt=""></td>
                        <td><a href="./product-view?slug=${data.product_slug[i]}">${data.product_name[i]}</a></td>
                        <td>${data.product_type[i]}</td>
                        <td>${data.product_price[i]}</td>
                        <td>${data.quantity[i]}</td>
                        <td>${data.product_weight[i]}</td>
                        <td>${data.product_actual_price[i]}</td>
                    </tr>`;
                }
                $('#OrderProductsModal tbody').html(rows);
            } else {
                $('#OrderProductsModal tbody').html('<tr><td colspan="8">No products found...</td></tr>');
            }
        },
        error: function() {
            alert('Failed to fetch order products. Please try again.');
        }
    });
}
</script>
