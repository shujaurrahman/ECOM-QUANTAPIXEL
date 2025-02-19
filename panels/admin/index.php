<?php 
session_start();
if(!empty($_SESSION['role'])){
  $title="home";
require_once('header.php');
 ?>
 
<!--  Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-lg-12 mb-4 order-0">
        <div class="card">
          <div class="d-flex align-items-end row">
            <div class="col-sm-7">
              <div class="card-body">
                <h5 class="card-title text-primary">Welcome <?php echo $_SESSION['role'] ?>! 🎉</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-12 mb-4 order-0">
        <div class="card">
          <div class="d-flex align-items-end row">
            <div class="col-sm-12">
              <div class="card-body">
                <h5 class="card-title text-primary">Admin Portal Features</h5>
                <ul class="list-unstyled">
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Track and manage all customer orders, monitor their status, process updates, and ensure smooth fulfillment.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> View details of registered users and update their status to active or inactive as needed</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Add new products by uploading images, writing descriptions, setting prices, and including necessary details for better visibility.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Modify existing products by updating descriptions, prices, images, and stock details to maintain an accurate inventory.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Create and organize product categories to structure the platform and make browsing easier for users.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Introduce sub-categories to further classify products, making it easier for users to find relevant items.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Define and manage product attributes like size, color, material, and other variations to improve clarity.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Highlight important product features such as key specifications, brand details, and warranty information for informed decision-making.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Create and manage discount coupons, promotional offers, and limited-time deals to encourage sales and customer engagement.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Maintain and update contact information to ensure customers and stakeholders can easily reach support and business inquiries.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Manage subscriber lists, send newsletters, and engage with users through regular updates and promotional emails.</li>
                <li class="mb-3"><i class="menu-icon tf-icons bx bx-plus-circle"></i> Oversee and control advertisements, ensuring they are placed effectively to maximize visibility and engagement.</li>
              </ul>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
<!-- / Content -->

<?php 
require_once('footer.php');
}else{
  header('location:login.php');
}
?>

            