<?php
session_start();
if(!empty($_SESSION['role'])){
  $title="editblog";
  require_once('header.php');
  require_once('./logics.class.php');
  $adminObj = new logics();

  // Check if ID is provided
  if(empty($_GET['id'])) {
    header('Location: blogs.php');
    exit;
  }

  $blog_id = intval($_GET['id']);
  $blog_details = $adminObj->getBlogById($blog_id);

  // Process form submission
  if(!empty($_POST['username']) && !empty($_POST['blog_heading']) && !empty($_POST['blog_desc']) && !empty($_POST['meta_title']) && !empty($_POST['meta_keywords']) && !empty($_POST['meta_description']) && !empty($_POST['description'])) {
    
    $featured_imagePhoto = $blog_details['featured_image'][0]; // Default to existing image
    
    // Handle image upload if a new one is provided
    if (!empty($_FILES['featured_image']['name'])) {
      $featured_imageName = $_FILES['featured_image']['name'];
      $featured_imageTempLocal = $_FILES['featured_image']['tmp_name'];
      
      // Append current date and time to make unique filename
      $timestamp = date("YmdHis");
      $extension = pathinfo($featured_imageName, PATHINFO_EXTENSION);
      $featured_imageName = pathinfo($featured_imageName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
      
      $featured_imageStore = "Blogimages/" . $featured_imageName;
      
      // Move uploaded image to folder
      move_uploaded_file($featured_imageTempLocal, $featured_imageStore);
      
      // Store new image name
      $featured_imagePhoto = $featured_imageName;
    }

    // Create slug from blog heading
    $slug = strtolower($_POST['blog_heading']);
    $slug_url = str_replace(' ', '-', $slug);
    
    // Update blog post
    $verification = $adminObj->updateBlog(
      $blog_id,
      $_POST['username'], 
      $_POST['blog_heading'], 
      $_POST['blog_desc'], 
      $_POST['meta_title'], 
      $_POST['meta_keywords'], 
      $_POST['meta_description'], 
      $_POST['description'], 
      $featured_imagePhoto, 
      $slug_url,
      isset($_POST['status']) ? 1 : 0
    );
    
    if(!empty($verification['status']) && $verification['status'] == 1){
      echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';
      echo '<script>';
      echo 'Swal.fire({
              icon: "success",
              title: "Blog Successfully Updated!",
              showConfirmButton: true,
          }).then(function() {
              window.location.href = "blogs.php";
          });';
      echo '</script>';
    } else {
      echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';
      echo '<script>';
      echo 'Swal.fire({
              icon: "error",
              title: "Data not Updated!",
              text: "Please try again"
          }).then(function() {
              window.location.href = "edit_blog.php?id=' . $blog_id . '";
          });';
      echo '</script>';
    }
  }

  // If no blog found with the given ID
  if(empty($blog_details) || empty($blog_details['status']) || $blog_details['status'] != 1) {
    echo '<div class="container-xxl flex-grow-1 container-p-y">';
    echo '<div class="alert alert-danger">Blog not found.</div>';
    echo '</div>';
    require_once('footer.php');
    exit;
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
              <h5 class="card-title text-primary">Edit Blog</h5>
              <a href="./blogs.php" class="btn btn-sm btn-primary">Back to Blogs</a>
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
              <form action="./edit_blog.php?id=<?php echo $blog_id; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="username" value="<?php echo $_SESSION['role']; ?>">
                
                <div class="mb-3">
                  <label for="blog_heading" class="form-label">Blog Title</label>
                  <input type="text" name="blog_heading" id="blog_heading" value="<?php echo htmlspecialchars($blog_details['blog_heading'][0]); ?>" placeholder="Enter Blog Title* (Max:200)" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="blog_desc" class="form-label">Short Description</label>
                  <input type="text" name="blog_desc" id="blog_desc" value="<?php echo htmlspecialchars($blog_details['blog_desc'][0]); ?>" placeholder="Enter Blog Description* (Max:200)" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="featured_image" class="form-label">Featured Image</label>
                  <?php if(!empty($blog_details['featured_image'][0])): ?>
                    <div class="mb-2">
                      <img src="./Blogimages/<?php echo $blog_details['featured_image'][0]; ?>" alt="Current Image" style="max-width: 200px; max-height: 200px;">
                      <p class="text-muted">Current image</p>
                    </div>
                  <?php endif; ?>
                  <input type="file" name="featured_image" id="featured_image" class="form-control">
                  <small class="text-muted">Leave empty to keep current image</small>
                </div>
                
                <div class="mb-3">
                  <label for="meta_title" class="form-label">Meta Title</label>
                  <input type="text" name="meta_title" id="meta_title" value="<?php echo htmlspecialchars($blog_details['meta_title'][0]); ?>" placeholder="Enter Meta Title*" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="meta_keywords" class="form-label">Meta Keywords</label>
                  <input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo htmlspecialchars($blog_details['meta_keywords'][0]); ?>" placeholder="Enter Meta Keywords*" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="meta_description" class="form-label">Meta Description</label>
                  <input type="text" name="meta_description" id="meta_description" value="<?php echo htmlspecialchars($blog_details['meta_description'][0]); ?>" placeholder="Enter Meta Description*" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="description" class="form-label">Blog Content</label>
                  <textarea name="description" id="description" rows="8" class="form-control" required><?php echo htmlspecialchars($blog_details['description'][0]); ?></textarea>
                </div>
                
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?php echo ($blog_details['status_value'][0] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="status">Active</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Blog</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->

<!-- Include CKEditor script -->
<script src="https://cdn.ckeditor.com/4.22.0/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('description', {
    height: 300,
    removePlugins: 'notification'
  });
</script>

<?php 
  require_once('footer.php');
} else {
  header('location:login.php');
}
?>