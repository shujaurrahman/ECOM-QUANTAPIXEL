<?php 
session_start();
if(!empty($_SESSION['role'])){
  $title="addnews";
require_once('header.php');

if(!empty($_POST['username']) && !empty($_POST['newsheading']) && !empty($_POST['newsdesc']) && !empty($_POST['newslink']) && !empty($_POST['meta_title']) && !empty($_POST['meta_keywords']) && !empty($_POST['meta_description'])){
    require_once('./logics.class.php');
    $adminObj = new logics();

    if (!empty($_FILES['featured_image']['name'])) {
        $featured_imageName = $_FILES['featured_image']['name'];
        $featured_imageTempLocal = $_FILES['featured_image']['tmp_name'];
        
        // Append current date and time to the featured_image name to make it unique
        $timestamp = date("YmdHis");
        $extension = pathinfo($featured_imageName, PATHINFO_EXTENSION);
        $featured_imageName = pathinfo($featured_imageName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
  
        $featured_imageStore = "newsimages/" . $featured_imageName;
  
        // Move uploaded featured_image photo to desired directory
        move_uploaded_file($featured_imageTempLocal, $featured_imageStore);
  
        // Store featured_image photo name
        $featured_imagePhoto = $featured_imageName;
      }

    // No slug needed as we'll use the newslink directly
    $verification = $adminObj->AddNews($_POST['username'], $_POST['newsheading'], $_POST['newsdesc'], $_POST['newslink'], $_POST['meta_title'], $_POST['meta_keywords'], $_POST['meta_description'], $featured_imagePhoto);
    if(!empty($verification['status']) && $verification['status']==1){
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';
        echo '<script>';
        echo 'Swal.fire({
                icon: "success",
                title: "News Successfully Submitted!",
                showConfirmButton: true,
            }).then(function() {
                window.location.href = "addnews";
            });';
        echo '</script>';
    }else{
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';
        echo '<script>';
        echo 'Swal.fire({
                icon: "error",
                title: "Data not Submitted!",
                text: "Please try again"
            }).then(function() {
                window.location.href = "addnews";
            });';
        echo '</script>';
    }
}
 ?>

 <style>
  /* Hide CKEditor notification */
.cke_notification_warning {
    display: none !important;
}

.cke_notification {
    display: none !important;
}
 </style>
<!--  Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-lg-12 mb-4 order-0">
        <div class="card">
          <div class="d-flex align-items-end row">
            <div class="col-sm-12">
              <div class="card-body d-flex justify-content-between">
                <h5 class="card-title text-primary">Add News</h5>
                <a href="./news.php" class="btn btn-sm btn-primary">View News</a>
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
                <form action="./addnews.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="username" value="<?php echo $_SESSION['role'] ?>" id="">
                    <input type="text" name="newsheading" placeholder="Enter News Title* (Max:200)" class="form-control mb-3" required>
                    <input type="text" name="newsdesc" placeholder="Enter News Description* (Max:200)" class="form-control mb-3" required>
                    <input type="url" name="newslink" placeholder="Enter News Link* (e.g., https://example.com)" class="form-control mb-3" required>
                    <input type="file" name="featured_image" class="form-control mb-3" required>
                    <input type="text" name="meta_title" placeholder="Enter Meta Title*" class="form-control mb-3" required>
                    <input type="text" name="meta_keywords" placeholder="Enter Meta Keywords*" class="form-control mb-3" required>
                    <input type="text" name="meta_description" placeholder="Enter Meta Description*" class="form-control mb-3" required>
                    <br>
                    <input type="submit" name="submit" value="Submit" class="btn btn-primary">
                </form>
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

<!-- Include CKEditor script -->
<script src="https://cdn.ckeditor.com/4.22.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('description', {
        height: 300
    });
</script>


