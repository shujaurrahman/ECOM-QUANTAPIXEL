<?php 
session_start();

if(!empty($_SESSION['role'])){
    $title="advertisement";
    require_once('header.php');

    if(!empty($_POST['category_name']) && isset($_FILES['featured_image']) && !empty($_POST['description']) && !empty($_POST['url'])){
        require_once('./logics.class.php');
        $adminObj = new logics();

        if(!empty($_FILES['featured_image']['name'])){
            $featured_imageName = $_FILES['featured_image']['name'];
            $featured_imageTempLocal = $_FILES['featured_image']['tmp_name'];
            
            // Append current date and time to the featured_image name to make it unique
            $timestamp = date("YmdHis");
            $extension = pathinfo($featured_imageName, PATHINFO_EXTENSION);
            $featured_imageName = pathinfo($featured_imageName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;

            $featured_imageStore = "advertisements/" . $featured_imageName;

            // Move uploaded featured_image photo to desired directory
            move_uploaded_file($featured_imageTempLocal, $featured_imageStore);

            // Store featured_image photo name
            $featured_imagePhoto = $featured_imageName;
        }

        // Add this after your existing form validation
        if(!empty($_POST['location']) && $_POST['location'] != "0") {
            // Check if there's an existing active ad in this location
            $existingAd = $adminObj->getActiveAdByLocation($_POST['location']);
            if(!empty($existingAd['status']) && $existingAd['status'] == 1) {
                $warningMessage = "There is an existing advertisement in this location. Adding a new one will replace it.";
                echo "<script>
                    Swal.fire({
                        title: 'Warning',
                        text: '$warningMessage',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Continue',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            window.location.href = 'getAdvertisements';
                        }
                    });
                </script>";
            }
        }

        $verification = $adminObj->AddAdvertisement(
            $_POST['category_name'], 
            $featured_imagePhoto, 
            $_POST['description'], 
            $_POST['url'],
            $_POST['location']  // Add this parameter
        );
        
        if(!empty($verification['status']) && $verification['status'] == 1){
            echo '<script src="../js/sweetalert.js"></script>';
            echo '<script>';
            echo 'Swal.fire({
                    icon: "success",
                    title: "Advertisement Successfully Added!",
                    showConfirmButton: true,
                }).then(function() {
                    window.location.href = "getAdvertisements";
                });';
            echo '</script>';
        } else {
            echo '<script src="../js/sweetalert.js"></script>';
            echo '<script>';
            echo 'Swal.fire({
                    icon: "error",
                    title: "Data not Submitted!",
                    text: "Please try again"
                }).then(function() {
                    window.location.href = "getAdvertisements";
                });';
            echo '</script>';
        }
    }

    require_once('./logics.class.php');

    $obj = new logics();
    $editCategory = $obj->getAdvertisements();

    if (!empty($editCategory['status']) && $editCategory['status'] == 1) {
    ?>
    <!--  Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-12">
                            <div class="card-body d-flex justify-content-between">
                                <h5 class="card-title text-primary">Add Advertisement</h5>
                                <a href="./getAdvertisements" class="btn btn-sm btn-primary">View Added Advertisements</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="./addbannerads.php" method="post" enctype="multipart/form-data" class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                    <div class="card-body">
                        <label for="category_name">Advertisement Name <sup class="text-danger">*</sup></label>
                        <input type="text" name="category_name" placeholder="Enter Advertisement Name" class="form-control mb-3" required id="category_name">

                        <label for="image">Advertisement Reference Image</label>
                        <input type="file" name="featured_image" placeholder="Enter Category Name" class="form-control mb-3" id="image">

                        <label for="description">Advertisement Short Description<sup class="text-danger">*</sup></label>
                        <input type="text" name="description" placeholder="Ex. Save upto 20%" class="form-control mb-3" id="description">

                        <label for="url">Advertisement URL<sup class="text-danger">*</sup></label>
                        <input type="text" name="url" placeholder="url" class="form-control mb-3" id="url">

                        <!-- Add Location Selector -->
                        <label for="location">Select Advertisement Location<sup class="text-danger">*</sup></label>
                        <select name="location" id="location" class="form-control mb-3" required>
                            <option value="">-Select Location-</option>
                            <option value="0">Hero Carousel</option>
                            <option value="1">Hero Top-1</option>
                            <option value="2">Hero Top-2</option>
                            <option value="3">Bottom-1</option>
                            <option value="4">Bottom-2</option>
                        </select>

                        <!-- Add helper text for Carousel option -->
                        <div id="locationHelp" class="form-text mb-3" style="display: none;">
                            Note: Hero Carousel allows multiple advertisements to be displayed in a rotating slideshow.
                        </div>

                        <br>
                        <input type="submit" name="submit" value="Add Advertisement" class="btn btn-primary" id="">
                        <a href="getAdvertisements" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- / Content -->

    <!-- Add this right after the form, before the JavaScript -->
    <script src="../js/sweetalert.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Replace the existing JavaScript section -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const locationSelect = document.getElementById('location');
        
        locationSelect.addEventListener('change', async function() {
            const selectedLocation = this.value;
            
            // Show/hide carousel helper text
            const helpText = document.getElementById('locationHelp');
            helpText.style.display = selectedLocation === '0' ? 'block' : 'none';
            
            // Check for existing ads in selected location (except carousel)
            if (selectedLocation !== '0' && selectedLocation !== '') {
                try {
                    const response = await fetch(`check_existing_ad.php?location=${selectedLocation}`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    const data = await response.json();
                    
                    if (data.exists) {
                        // Using Sweetalert2 instead of Swal
                        Sweetalert2.fire({
                            title: 'Warning',
                            text: 'There is an existing advertisement in this location. Adding a new one will replace it.',
                            icon: 'warning',
                            iconColor: '#c4996c',
                            confirmButtonColor: '#c4996c',
                            cancelButtonColor: '#dc3545',
                            showCancelButton: true,
                            confirmButtonText: 'Continue',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (!result.isConfirmed) {
                                this.value = ''; // Reset dropdown if user cancels
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error checking existing ad:', error);
                }
            }
        });
    });
    </script>

    <!-- Add this CSS for better styling -->
    <style>
    .form-text {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: -0.5rem;
    }

    select.form-control {
        padding: 0.375rem 0.75rem;
        background-position: right 0.75rem center;
    }

    select.form-control:focus {
        border-color: #c4996c;
        box-shadow: 0 0 0 0.2rem rgba(196, 153, 108, 0.25);
    }

    .form-control::placeholder {
        color: #939393;
        opacity: 0.75;
    }
    </style>

    <?php 
    } else {
        echo "Data Not Fetched";
    }

    require_once('footer.php');
} else {
    header('location:login.php');
}
?>