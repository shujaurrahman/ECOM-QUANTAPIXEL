<?php 
session_start();
if(!empty($_SESSION['role'])){
  $title = "changePwd";
  require_once('header.php');
  require_once('./logics.class.php');
  $changepwdObj = new logics();

  $err = ""; // Initialize error variable
  $succ = ""; // Initialize success variable

  if(isset($_POST['Update']) && !empty($_POST['current_pass']) && !empty($_POST['new_password']) && !empty($_POST['reenter_password'])){
    // Get the current password from database for verification
    $user_id = $_SESSION['id']; // Make sure session has user id
    $currentUser = $changepwdObj->getUserById($user_id);
    
    // Verify current password (using exact comparison)
    if($currentUser && $currentUser['password'] == $_POST['current_pass']){
        // Check if new passwords match
        if($_POST['new_password'] == $_POST['reenter_password']){
            // Change password
            $verification = $changepwdObj->changepwd($user_id, $_POST['new_password']);
            
            if(!empty($verification['status']) && $verification['status']==1){
                $succ = "Password successfully changed";
            } else {
                $err = "Password could not be changed. Please try again.";
            }
        } else {
            $err = "New password and re-entered password do not match";
        }
    } else {
        $err = "Current password is incorrect";
    }
  }
?>

<!--  Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-lg-12 mb-4 order-0">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-sm-12">
            <div class="card-body">
              <h5 class="card-title text-primary">Change Password</h5>
              
              <?php if(!empty($err)): ?>
              <div id="errorAlert" class="alert alert-danger alert-dismissible fade show">
                <?php echo $err; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              <?php endif; ?>
              
              <?php if(!empty($succ)): ?>
              <div id="successAlert" class="alert alert-success alert-dismissible fade show">
                <?php echo $succ; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              <?php endif; ?>
              
              <form action="changePwd.php" method="post">
                <div class="mb-3">
                  <label for="current_pass" class="form-label">Current Password</label>
                  <input type="password" class="form-control" id="current_pass" name="current_pass" required>
                </div>
                
                <div class="mb-3">
                  <label for="new_password" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                
                <div class="mb-3">
                  <label for="reenter_password" class="form-label">Re-enter New Password</label>
                  <input type="password" class="form-control" id="reenter_password" name="reenter_password" required>
                </div>
                
                <div class="text-center">
                  <button type="submit" name="Update" class="btn btn-primary">Change Password</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- / Content -->

<!-- Auto-dismiss alerts script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Auto-dismiss alerts after 3 seconds
  setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 3000);
});
</script>

<?php 
require_once('footer.php');
} else {
  header('location:login.php');
}
?>