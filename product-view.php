<?php
session_start();
require_once('header.php');

$slug = $_GET['slug'];
$slug = str_replace('-', ' ', $slug);
$slug = ucwords($slug);
?>
<style>
    .product-item {
  height: 400px; 
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
</style>
<?php
    for ($i = 0; $i < $products['count']; $i++) {
        if ($products['statusval'][$i] == 1 && $products['slug'][$i] == $_GET['slug']) {
            $averageRating = $Obj->getAverageRating($products['id'][$i]);
            $averageRatingValue = $averageRating['status'] == 1 ? $averageRating['average_rating'] : 0;
            ?>
    <!-- Breadcrumb Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="breadcrumb bg-light mb-30">
                    <a class="breadcrumb-item text-dark" href="#">Home</a>
                    <a class="breadcrumb-item text-dark" href="#">Products</a>
                    <span class="breadcrumb-item active"><?php echo $slug ?></span>
                </nav>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->


    <!-- Shop Detail Start -->
     <?php
     for($i=0;$i<$products['count'];$i++){
        if($products['slug'][$i] == $_GET['slug']){
    ?>
    <div class="container-fluid pb-5">
        <div class="row px-xl-5">
            <div class="col-lg-5 mb-30">
                <div id="product-carousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner bg-light">
                        <div class="carousel-item active">
                            <img class="w-100" style="height: 500px;" src="./panels/admin/product/<?php echo $products['featured_image'][$i] ?>" alt="Image">
                        </div>
                        <?php 
                        $imageList = explode(',', $products['additional_images'][$i]); // Split the comma-separated string into an array
                        foreach ($imageList as $key => $image): 
                        ?>
                            <div class="carousel-item">
                                <img class="w-100" style="height: 500px;" src="./panels/admin/product/<?php echo trim($image); ?>" alt="Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="carousel-control-prev" href="#product-carousel" data-slide="prev">
                        <i class="fa fa-2x fa-angle-left text-dark"></i>
                    </a>
                    <a class="carousel-control-next" href="#product-carousel" data-slide="next">
                        <i class="fa fa-2x fa-angle-right text-dark"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-7 h-auto mb-30">
                <div class="h-100 bg-light p-30">
                    <h3><?php echo $products['product_name'][$i] ?></h3>
                    <div class="d-flex mb-3">
                        <div class="text-primary mr-2">
                            <?php
                            // Display filled stars based on average rating
                            for ($j = 0; $j < floor($averageRatingValue); $j++) {
                                echo '<small class="fas fa-star"></small>';
                            }
                            // Display half star if average rating is not an integer
                            if ($averageRatingValue - floor($averageRatingValue) >= 0.5) {
                                echo '<small class="fas fa-star-half-alt"></small>';
                            }
                            // Display empty stars
                            for ($j = ceil($averageRatingValue); $j < 5; $j++) {
                                echo '<small class="far fa-star"></small>';
                            }
                            ?>
                        </div>
                        <small class="pt-1">(<?php echo $averageRatingValue; ?> Reviews)</small>
                    </div>
                    <h3 class="font-weight-semi-bold mb-1" id="product-price">₹<?php echo $products['discounted_price'][$i] ?> &emsp;<small><del>₹<?php echo $products['product_price'][$i] ?></del></small></h3>
                    <!-- <span class="mb-4">Ornament & Weight : <b><?php echo $products['ornament_type'][$i] ?> (<?php echo $products['ornament_weight'][$i] ?> Grams)</b></span> -->
                    <br><br>
    
                    <p class="mb-4"><?php echo $products['short_description'][$i] ?></p>

<?php
$productVariations = $Obj->getProductVariations($products['id'][$i]);
// $productVariations = $Obj->getProductVariations(1);

// Group variations by attribute type (e.g., size, color)
$groupedVariations = [];
for ($j = 0; $j < $productVariations['count']; $j++) {
    $attribute = $productVariations['attribute_name'][$j];
    $variation = $productVariations['variation_name'][$j];
    $isSamePrice = $productVariations['is_same_price'][$j];
    $price = $isSamePrice ? $products['discounted_price'][$i] : $productVariations['ornament_weight'][$j];

    $groupedVariations[$attribute][] = [
        'name' => $variation,
        'price' => $price,
    ];
}
?>

<?php foreach ($groupedVariations as $attribute => $variations): ?>
    <div class="d-flex mb-3">
        <strong class="text-dark mr-3"><?php echo ucfirst($attribute); ?>:</strong>
        <form>
            <?php foreach ($variations as $index => $variation): ?>
                <div class="custom-control custom-radio custom-control-inline">
                    <input 
                        type="radio" 
                        class="custom-control-input variation-option" 
                        id="<?php echo strtolower($attribute) . '-' . $index; ?>" 
                        name="<?php echo strtolower($attribute); ?>" 
                        data-price="<?php echo $variation['price']; ?>" 
                        data-attribute="<?php echo strtolower($attribute); ?>" 
                        data-variation="<?php echo $variation['name']; ?>">
                    <label class="custom-control-label" for="<?php echo strtolower($attribute) . '-' . $index; ?>">
                        <?php echo $variation['name']; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </form>
    </div>
<?php endforeach; ?>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const priceElement = document.getElementById('product-price1');
        const variationOptions = document.querySelectorAll('.variation-option');

        // Helper function to update the price
        function updatePrice(event) {
            const selectedPrice = event.target.getAttribute('data-price');
            if (selectedPrice) {
                priceElement.innerHTML = `₹${selectedPrice}`;
            }
        }

        // Add event listeners to all variation options
        variationOptions.forEach(option => {
            option.addEventListener('change', updatePrice);
        });
    });
</script>



<div class="d-flex align-items-center mb-4 pt-2">
    <div class="input-group quantity mr-3" style="width: 130px;">
        <div class="input-group-btn">
            <button class="btn btn-primary btn-minus">
                <i class="fa fa-minus"></i>
            </button>
        </div>
        <input type="text" id="product-quantity" class="form-control bg-secondary border-0 text-center" value="1">
        <div class="input-group-btn">
            <button class="btn btn-primary btn-plus">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    <a 
        id="add-to-cart-link" 
        class="btn btn-primary px-3" 
        href="./open-logics?type=addToCart&product_id=<?php echo $products['id'][$i]; ?>&quantity=1">
        <i class="fa fa-shopping-cart mr-1"></i> Add To Cart
    </a>
    <!-- <a 
        id="add-to-cart-link" 
        data-toggle="modal" data-target="#PartialPurchaseModal"
        class="btn btn-primary px-3 mx-3" 
        href="contact">
        <i class="fa fa-lock mr-1"></i> Partial Purchase
    </a> -->
    
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantityInput = document.getElementById('product-quantity');
        const addToCartLink = document.getElementById('add-to-cart-link');
        const productId = "<?php echo $products['id'][$i]; ?>"; // Dynamic product ID

        // Update the Add to Cart link whenever the quantity changes
        function updateAddToCartLink() {
            const currentQuantity = parseInt(quantityInput.value) || 1; // Default to 1 if input is invalid
            addToCartLink.href = `./open-logics?type=addToCart&product_id=${productId}&quantity=${currentQuantity}`;
        }

        // Event listeners for the +/- buttons and input field
        document.querySelector('.btn-plus').addEventListener('click', () => {
            quantityInput.value = parseInt(quantityInput.value || 1) + 0;
            updateAddToCartLink();
        });

        document.querySelector('.btn-minus').addEventListener('click', () => {
            quantityInput.value = Math.max(parseInt(quantityInput.value || 1) - 0, 1); // Minimum quantity is 1
            updateAddToCartLink();
        });

        quantityInput.addEventListener('input', () => {
            updateAddToCartLink();
        });
    });
</script>


                    <br>
                    <!-- <h4>For Customization, Upload Pic to get estimated price:</h4>
                    <form action="./open-logics.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?php echo !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : '' ?>" >
                        <input type="hidden" name="slug" value="<?php echo $_GET['slug'] ?>" >
                        <input type="file" name="customization_image" required >
                        <?php
                        if(!empty($_SESSION['user_id'])){
                            ?>
                            <input type="submit" name="Upload" class="btn btn-primary" value="Upload" id="">
                            <?php
                        }else{
                            ?>
                            <a href="" onclick="alert('Please Login to Upload')" class="btn btn-primary">Upload</a>
                            <?php
                        }
                        ?>
                    </form> -->
                    <br>

                    <div class="d-flex pt-2">
                    <strong class="text-dark mr-2 my-auto">Share This Product:</strong>
                    <span 
                        style="border: 1px solid grey; padding: 5px; cursor: pointer;" 
                        class="rounded copy-link"
                        onclick="copyCurrentURL()">Copy Link <i class="fa fa-link"></i></span>

                    <script>
                        function copyCurrentURL() {
                            // Get the current URL
                            const currentURL = window.location.href;

                            // Copy the URL to the clipboard
                            navigator.clipboard.writeText(currentURL)
                                .then(() => {
                                    // Show an alert when the link is copied
                                    alert('Link Copied!');
                                })
                                .catch(err => {
                                    console.error('Failed to copy: ', err);
                                });
                        }
                    </script>

                        
                        <!-- <div class="d-inline-flex">
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        <?php
        $reviews = $Obj->getReviewByProductId($products['id'][$i]);
        ?>
        <div class="row px-xl-5">
            <div class="col">
                <div class="bg-light p-30">
                    <div class="nav nav-tabs mb-4">
                        <!-- <a class="nav-item nav-link text-dark active" data-toggle="tab" href="#tab-pane-1">General Info</a> -->
                        <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-2">Description</a>
                        <a class="nav-item nav-link text-dark" data-toggle="tab" href="#tab-pane-3">Reviews (<?php echo $reviews['count']; ?>)</a>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-pane-1">
                            <div class="mb-3">
                                <?php echo $products['general_info'][$i] ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-pane-2">
                            <div class="mb-3">
                                <?php echo $products['description'][$i] ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab-pane-3">
                            <div class="row">
                                <div class="col-md-6" style="height:400px;overflow-y:scroll">
                                    <h4 class="mb-4"><?php echo $reviews['count']; ?> review(s) for "<?php echo $slug; ?>"</h4>
                                    <?php 
                                    for($k = 0; $k < $reviews['count']; $k++) {
                                        // Convert date format
                                        $formattedDate = date("d M, Y", strtotime($reviews['created_at'][$k]));

                                        // Get the rating value
                                        $rating = $reviews['rating'][$k];

                                        ?>
                                        <div class="media mb-4">
                                            <img src="./images/demo-profile.jpeg" alt="Image" class="img-fluid mr-3 mt-1 rounded-circle" style="width: 45px;">
                                            <div class="media-body">
                                                <h6><?php echo $reviews['username'][$k] ?><small> - <i><?php echo $formattedDate ?></i></small></h6>
                                                <div class="text-primary mb-2">
                                                    <?php
                                                    // Display stars based on the rating
                                                    for ($star = 1; $star <= 5; $star++) {
                                                        if ($star <= $rating) {
                                                            echo '<i class="fas fa-star"></i>'; // Filled star
                                                        } else {
                                                            echo '<i class="far fa-star"></i>'; // Unfilled star
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <p><?php echo $reviews['review'][$k] ?></p>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-4">Leave a review</h4>
                                    <small>Your email address will not be published. Required fields are marked *</small>
                                    
                                    <form action="./open-logics" method="post">
                                        <div class="d-flex my-3">
                                            <p class="mb-0 mr-2">Your Rating * :</p>
                                            <div class="text-primary" id="star-rating">
                                                <i class="far fa-star" data-index="1"></i>
                                                <i class="far fa-star" data-index="2"></i>
                                                <i class="far fa-star" data-index="3"></i>
                                                <i class="far fa-star" data-index="4"></i>
                                                <i class="far fa-star" data-index="5"></i>
                                            </div>
                                        </div>

                                        <input type="hidden" name="product_id" value="<?php echo $products['id'][$i] ?>" id="">

                                        <input type="hidden" name="rating" id="rating-value" value="1"> <!-- Hidden field to store the rating value -->

                                        <div class="form-group">
                                            <label for="message">Your Review *</label>
                                            <textarea id="message" name="review" required cols="30" rows="5" class="form-control"></textarea>
                                        </div>

                                        <div class="form-group mb-0">
                                            <?php
                                            if(!empty($_SESSION['user_id'])) {
                                                ?>
                                                <input type="submit" name="submit" class="btn btn-primary" value="Leave Your Review">
                                                <?php
                                            } else {
                                                ?>
                                                <a href="" onclick="alert('Please Login to give Review')" class="btn btn-primary">Leave Your Review</a>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </form>

                                    <script>
                                        // Select all stars
                                        const stars = document.querySelectorAll('#star-rating i');
                                        const ratingInput = document.getElementById('rating-value');

                                        // Add click event to each star
                                        stars.forEach(star => {
                                            star.addEventListener('click', function() {
                                                const rating = this.getAttribute('data-index');
                                                
                                                // Update rating value in the hidden input
                                                ratingInput.value = rating;

                                                // Fill stars up to the clicked one
                                                stars.forEach((s, index) => {
                                                    if (index < rating) {
                                                        s.classList.remove('far'); // Unfilled star
                                                        s.classList.add('fas'); // Filled star
                                                    } else {
                                                        s.classList.remove('fas'); // Filled star
                                                        s.classList.add('far'); // Unfilled star
                                                    }
                                                });
                                            });
                                        });
                                    </script>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        } 
    } //End of For Loop
    ?>
    <!-- Shop Detail End -->
    <?php
        }
    }
    ?>


    <!-- Products Start -->
    <div class="container-fluid py-5 d-none">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">You May Also Like</span></h2>
        <div class="row px-xl-5">
            <div class="col">
                <div class="owl-carousel related-carousel">
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="images/Screenshot 2024-09-25 152514.png" style="height: 300px;" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="">Product Name Goes Here</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>$123.00</h5><h6 class="text-muted ml-2"><del>$123.00</del></h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small>(99)</small>
                            </div>
                        </div>
                    </div>
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="images/Screenshot 2024-09-25 152525.png" style="height: 300px;" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="">Product Name Goes Here</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>$123.00</h5><h6 class="text-muted ml-2"><del>$123.00</del></h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small>(99)</small>
                            </div>
                        </div>
                    </div>
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="images/Screenshot 2024-09-25 152605.png" style="height: 300px;" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="">Product Name Goes Here</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>$123.00</h5><h6 class="text-muted ml-2"><del>$123.00</del></h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small>(99)</small>
                            </div>
                        </div>
                    </div>
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="images/Screenshot 2024-09-25 152514.png" style="height: 300px;" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="">Product Name Goes Here</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>$123.00</h5><h6 class="text-muted ml-2"><del>$123.00</del></h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small>(99)</small>
                            </div>
                        </div>
                    </div>
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="images/Screenshot 2024-09-25 152525.png" style="height: 300px;" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="">Product Name Goes Here</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>$123.00</h5><h6 class="text-muted ml-2"><del>$123.00</del></h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small>(99)</small>
                            </div>
                        </div>
                    </div>
                    <div class="product-item bg-light">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100" src="images/Screenshot 2024-09-25 152605.png" style="height: 300px;" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-sync-alt"></i></a>
                                <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none text-truncate" href="">Product Name Goes Here</a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5>$123.00</h5><h6 class="text-muted ml-2"><del>$123.00</del></h6>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small>(99)</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Products End -->

    
     <!-- Featured Start -->
<div class="container-fluid pt-5">
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Recommended Products</span></h2>
    <div class="row px-xl-5 pb-3">
        <div class="owl-carousel related-carousel">
        <?php
        for ($i = 0; $i < min($products['count'], 8); $i++) {
            if ($products['statusval'][$i] == 1 && $products['is_recommended'][$i] == 1) {
                $averageRating = $Obj->getAverageRating($products['id'][$i]);
                $averageRatingValue = $averageRating['status'] == 1 ? $averageRating['average_rating'] : 0;
                ?>
                <div class="item pb-1">
                    <div class="product-item bg-light mb-4">
                        <div class="product-img position-relative overflow-hidden">
                            <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>" alt="">
                            <div class="product-action">
                                <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToCart&product_id=<?php echo $products['id'][$i] ?>"><i class="fa fa-shopping-cart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToWishlist&product_id=<?php echo $products['id'][$i] ?>"><i class="far fa-heart"></i></a>
                                <a class="btn btn-outline-dark btn-square" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><i class="fa fa-eye"></i></a>
                            </div>
                        </div>
                        <div class="text-center py-4">
                            <a class="h6 text-decoration-none mobile-small-font" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><?php echo $products['product_name'][$i]; ?></a>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <h5 class="font-weight-bold mr-2">
                                    ₹<?php echo number_format(floor($products['discounted_price'][$i])); ?>
                                </h5>
                                <small class="text-muted ml-2">
                                    <del>₹<?php echo number_format(floor($products['actual_price'][$i])); ?></del>
                                </small>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <?php
                                // Display filled stars based on average rating
                                for ($j = 0; $j < floor($averageRatingValue); $j++) {
                                    echo '<small class="fa fa-star text-primary mr-1"></small>';
                                }
                                // Display half star if average rating is not an integer
                                if ($averageRatingValue - floor($averageRatingValue) >= 0.5) {
                                    echo '<small class="fa fa-star-half-alt text-primary mr-1"></small>';
                                }
                                // Display empty stars
                                for ($j = ceil($averageRatingValue); $j < 5; $j++) {
                                    echo '<small class="far fa-star text-primary mr-1"></small>';
                                }
                                ?>
                                <small>(<?php echo $averageRatingValue; ?>)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        </div>
    </div>
</div>
<!-- Featured End -->

<?php
require_once('footer.php');
?>