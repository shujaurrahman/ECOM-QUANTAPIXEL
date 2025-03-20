<?php 
session_start();
if(!empty($_SESSION['role'])){
  $title="addBlogs";
require_once('header.php');

if(!empty($_POST['username']) && !empty($_POST['blog_heading']) && !empty($_POST['blog_desc']) && !empty($_POST['meta_title']) && !empty($_POST['meta_keywords']) && !empty($_POST['meta_description']) && !empty($_POST['description']) ){
    require_once('./logics.class.php');
    $adminObj = new logics();

    if (!empty($_FILES['featured_image']['name'])) {
        $featured_imageName = $_FILES['featured_image']['name'];
        $featured_imageTempLocal = $_FILES['featured_image']['tmp_name'];
        
        // Append current date and time to the featured_image name to make it unique
        $timestamp = date("YmdHis");
        $extension = pathinfo($featured_imageName, PATHINFO_EXTENSION);
        $featured_imageName = pathinfo($featured_imageName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
  
        $featured_imageStore = "Blogimages/" . $featured_imageName;
  
        // Move uploaded featured_image photo to desired directory
        move_uploaded_file($featured_imageTempLocal, $featured_imageStore);
  
        // Store featured_image photo name
        $featured_imagePhoto = $featured_imageName;
      }

    // Create a slug from the blog heading
    $slug = strtolower($_POST['blog_heading']);
    // Remove any special characters except alphanumeric and hyphens
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    // Replace multiple hyphens with a single hyphen
    $slug = preg_replace('/-+/', '-', $slug);
    // Trim hyphens from the beginning and end
    $slug = trim($slug, '-');
    // Add a unique identifier (timestamp and random string)
    $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5);
    $timestamp = date('YmdHis');
    $slug = $slug . '-' . $timestamp . '-' . $randomString;
    $slug_url = str_replace(' ','-',$slug);
    $verification = $adminObj->AddBlogs($_POST['username'], $_POST['blog_heading'], $_POST['blog_desc'], $_POST['meta_title'], $_POST['meta_keywords'], $_POST['meta_description'], $_POST['description'], $featured_imagePhoto, $slug_url);
    if(!empty($verification['status']) && $verification['status']==1){
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';
        echo '<script>';
        echo 'Swal.fire({
                icon: "success",
                title: "Blog Successfully Submitted!",
                showConfirmButton: true,
            }).then(function() {
                window.location.href = "addblogs";
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
                window.location.href = "addblogs";
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
                <h5 class="card-title text-primary">Add Blogs</h5>
                <a href="./blogs.php" class="btn btn-sm btn-primary">View Blogs</a>
                
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
                <!-- <h5 class="card-title text-primary">Instructions to Use the Portal</h5> -->
                 <form action="./addblogs.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="username" value="<?php echo $_SESSION['role'] ?>" id="">
                    <input type="text" name="blog_heading" placeholder="Enter Blog Title* (Max:200)" class="form-control mb-3" required>
                    <input type="text" name="blog_desc" placeholder="Enter Blog Description* (Max:200)" class="form-control mb-3" required>
                    <input type="file" name="featured_image" class="form-control mb-3" required>
                    <input type="text" name="meta_title" placeholder="Enter Meta Title*" class="form-control mb-3" required>
                    <input type="text" name="meta_keywords" placeholder="Enter Meta Keywords*" class="form-control mb-3" required>
                    <input type="text" name="meta_description" placeholder="Enter Meta Description*" class="form-control mb-3" required>
                    <textarea name="description" placeholder="Blog Description* " rows="8" class="form-control mb-3" id="description"></textarea>
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


