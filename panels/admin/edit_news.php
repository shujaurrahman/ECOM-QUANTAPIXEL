<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if(!empty($_SESSION['role'])){
  $title="editnews";
  require_once('header.php');
  require_once('./logics.class.php');
  $adminObj = new logics();

  // Check if ID is provided
  if(empty($_GET['id'])) {
    header('Location: news.php');
    exit;
  }

  $news_id = intval($_GET['id']);
  $news_details = $adminObj->getNewsById($news_id);

  // Process form submission
  if(isset($_POST['Update']) && !empty($_POST['username']) && !empty($_POST['newsheading']) && !empty($_POST['newsdesc']) && !empty($_POST['meta_title']) && !empty($_POST['meta_keywords']) && !empty($_POST['meta_description']) && !empty($_POST['newslink'])) {
    
    $featured_imagePhoto = $news_details['featured_image']; // Changed from $news_details['featured_image'][0]
    
    // Handle image upload if a new one is provided
    if (!empty($_FILES['featured_image']['name'])) {
      $featured_imageName = $_FILES['featured_image']['name'];
      $featured_imageTempLocal = $_FILES['featured_image']['tmp_name'];
      
      // Append current date and time to make unique filename
      $timestamp = date("YmdHis");
      $extension = pathinfo($featured_imageName, PATHINFO_EXTENSION);
      $featured_imageName = pathinfo($featured_imageName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
      
      $featured_imageStore = "newsimages/" . $featured_imageName;
      
      // Move uploaded image to folder
      move_uploaded_file($featured_imageTempLocal, $featured_imageStore);
      
      // Store new image name
      $featured_imagePhoto = $featured_imageName;
    }

    // Create slug from news heading
    $slug = strtolower($_POST['newsheading']);
    $slug_url = str_replace(' ', '-', $slug);
    
    // Update news item
    $verification = $adminObj->updateNews(
      $news_id,
      $_POST['username'], 
      $_POST['newsheading'], 
      $_POST['newsdesc'], 
      $_POST['meta_title'], 
      $_POST['meta_keywords'], 
      $_POST['meta_description'], 
      $_POST['newslink'], 
      $featured_imagePhoto, 
      $slug_url,
      isset($_POST['status']) ? 1 : 0
    );
    
    if(!empty($verification['status']) && $verification['status']==1){
      echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';
      echo '<script>';
      echo 'Swal.fire({
              icon: "success",
              title: "News Successfully Updated!",
              showConfirmButton: true,
          }).then(function() {
              window.location.href = "news.php";
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
              window.location.href = "edit_news.php?id=' . $news_id . '";
          });';
      echo '</script>';
    }

    if(!empty($verification['error'])) {
        echo '<div class="alert alert-danger">';
        echo 'Error details: ' . $verification['error'];
        echo '</div>';
    }

    if(!empty($verification['debug'])) {
        echo '<div style="display:none;">';
        echo '<pre>';
        print_r($verification['debug']);
        echo '</pre>';
        echo '</div>';
    }
  }

  // If no news found with the given ID
  if(empty($news_details) || empty($news_details['status']) || $news_details['status'] != 1) {
    echo '<div class="container-xxl flex-grow-1 container-p-y">';
    echo '<div class="alert alert-danger">News not found or inactive.</div>';
    echo '</div>';
    require_once('footer.php');
    exit;
  }
?>

<!--  Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-lg-12 mb-4 order-0">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-sm-12">
            <div class="card-body d-flex justify-content-between">
              <h5 class="card-title text-primary">Edit News</h5>
              <a href="./news.php" class="btn btn-sm btn-primary">Back to News</a>
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
              <form action="./edit_news.php?id=<?php echo $news_id; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="username" value="<?php echo $_SESSION['role']; ?>">
                
                <div class="mb-3">
                  <label for="newsheading" class="form-label">News Title</label>
                  <input type="text" name="newsheading" id="newsheading" value="<?php echo htmlspecialchars($news_details['newsheading']); ?>" placeholder="Enter News Title* (Max:200)" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="newsdesc" class="form-label">News Description</label>
                  <textarea name="newsdesc" id="newsdesc" rows="4" placeholder="Enter News Description*" class="form-control" required><?php echo htmlspecialchars($news_details['newsdesc']); ?></textarea>
                </div>
                
                <div class="mb-3">
                  <label for="newslink" class="form-label">News Link</label>
                  <input type="url" name="newslink" id="newslink" value="<?php echo htmlspecialchars($news_details['newslink']); ?>" placeholder="Enter News Link*" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="featured_image" class="form-label">Featured Image</label>
                  <?php if(!empty($news_details['featured_image'])): ?>
                    <div class="mb-2">
                      <img src="./newsimages/<?php echo $news_details['featured_image']; ?>" alt="Current Image" style="max-width: 200px; max-height: 200px;">
                      <p class="text-muted">Current image</p>
                    </div>
                  <?php endif; ?>
                  <input type="file" name="featured_image" id="featured_image" class="form-control">
                  <small class="text-muted">Leave empty to keep current image</small>
                </div>
                
                <div class="mb-3">
                  <label for="meta_title" class="form-label">Meta Title</label>
                  <input type="text" name="meta_title" id="meta_title" value="<?php echo htmlspecialchars($news_details['meta_title']); ?>" placeholder="Enter Meta Title*" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="meta_keywords" class="form-label">Meta Keywords</label>
                  <input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo htmlspecialchars($news_details['meta_keywords']); ?>" placeholder="Enter Meta Keywords*" class="form-control" required>
                </div>
                
                <div class="mb-3">
                  <label for="meta_description" class="form-label">Meta Description</label>
                  <textarea name="meta_description" id="meta_description" rows="3" placeholder="Enter Meta Description*" class="form-control" required><?php echo htmlspecialchars($news_details['meta_description']); ?></textarea>
                </div>
                
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?php echo (isset($news_details['status_value']) && $news_details['status_value'] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="status">Active</label>
                </div>
                
                <button type="submit" name="Update" class="btn btn-primary">Update News</button>
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
} else {
  header('location:login.php');
}
?>