<?php
session_start();
require_once('header.php');

$slug = $_GET['slug'];
$slug = str_replace('-', ' ', $slug);
$slug = ucwords($slug);
?>
<link rel="stylesheet" href="css/product.css">
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
        <div class="col-lg-5 mb-30 product-gallery-column">
            <div class="product-gallery">
                <!-- Vertical thumbnails on the left (desktop) -->
                <div class="product-thumbnails vertical">
    <div class="thumbnails-container">
        <!-- Featured image thumbnail -->
        <div class="thumbnail-item active" data-slide-index="0">
            <img src="./panels/admin/product/<?php echo $products['featured_image'][$i] ?>" alt="Thumbnail">
        </div>
        
        <!-- Additional images thumbnails -->
        <?php 
        $imageList = explode(',', $products['additional_images'][$i]);
        $totalImages = count(array_filter($imageList));
        foreach ($imageList as $key => $image): 
            $slideIndex = $key + 1;
            if(trim($image) != ""):
        ?>
            <div class="thumbnail-item" data-slide-index="<?php echo $slideIndex; ?>">
                <img src="./panels/admin/product/<?php echo trim($image); ?>" alt="Thumbnail">
            </div>
        <?php 
            endif;
        endforeach; 
        ?>

        <!-- Video thumbnail at the end if video exists -->
        <?php if (!empty($products['product_video'][$i])): ?>
        <div class="thumbnail-item video-thumb" data-type="video" data-slide-index="<?php echo $totalImages + 1; ?>">
            <div class="video-thumbnail-overlay">
                <i class="fas fa-play-circle"></i>
                <img src="./panels/admin/product/<?php echo $products['featured_image'][$i] ?>" alt="Video Thumbnail">
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

                
                <!-- Main carousel (remove video from carousel) -->
<div id="product-carousel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <!-- Featured image slide -->
        <div class="carousel-item active">
            <div class="zoom-container">
                <img class="w-100 zoom-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i] ?>" alt="Image">
                <div class="zoom-lens"></div>
                <div class="zoom-result"></div>
            </div>
        </div>

        <!-- Additional image slides -->
        <?php 
        $imageList = explode(',', $products['additional_images'][$i]);
        foreach ($imageList as $image): 
            if(trim($image) != ""):
        ?>
            <div class="carousel-item">
                <div class="zoom-container">
                    <img class="w-100 zoom-image" src="./panels/admin/product/<?php echo trim($image); ?>" alt="Image">
                    <div class="zoom-lens"></div>
                    <div class="zoom-result"></div>
                </div>
            </div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
</div>

<!-- Separate video section (after carousel) -->
<?php if (!empty($products['product_video'][$i])): ?>
<div class="product-video-wrapper" style="display: none;">
    <div class="product-video-container">
        <video 
            id="product-video"
            controls 
            controlsList="nodownload"
            poster="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>"
        >
            <source src="./panels/admin/product/videos/<?php echo $products['product_video'][$i]; ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>
<?php endif; ?>
                
                <!-- Horizontal thumbnails below (mobile) -->
                <div class="product-thumbnails horizontal">
                    <div class="thumbnails-container">
                        <!-- Featured image thumbnail -->
                        <div class="thumbnail-item active" data-slide-index="0">
                            <img src="./panels/admin/product/<?php echo $products['featured_image'][$i] ?>" alt="Thumbnail">
                        </div>
                        
                        <!-- Additional images thumbnails -->
                        <?php 
                        foreach ($imageList as $key => $image): 
                            $slideIndex = $key + 1;
                            if(trim($image) != ""):
                        ?>
                            <div class="thumbnail-item" data-slide-index="<?php echo $slideIndex; ?>">
                                <img src="./panels/admin/product/<?php echo trim($image); ?>" alt="Thumbnail">
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 h-auto mb-30 product-details-column">
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
                                    <del>₹<?php echo number_format(floor($products['product_price'][$i])); ?></del>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the carousel with auto-sliding
    var carousel = $('#product-carousel').carousel({
        interval: 5000, // Auto-slide every 5 seconds
        pause: 'hover'  // Pause on mouse hover
    });
    
    // Function to handle thumbnail clicks (for both vertical and horizontal layouts)
    function setupThumbnailClicks() {
        $('.thumbnail-item').on('click', function() {
            var slideIndex = $(this).data('slide-index');
            
            // Move carousel to selected slide
            carousel.carousel(slideIndex);
            
            // Update active thumbnail in both layouts
            $('.thumbnail-item').removeClass('active');
            $('.thumbnail-item[data-slide-index="' + slideIndex + '"]').addClass('active');
        });
    }
    
    // Update thumbnails when carousel slides
    carousel.on('slide.bs.carousel', function(e) {
        var slideIndex = e.to;
        
        // Update active thumbnail in both layouts
        $('.thumbnail-item').removeClass('active');
        $('.thumbnail-item[data-slide-index="' + slideIndex + '"]').addClass('active');
        
        // Scroll the active thumbnail into view (vertical)
        const activeThumbVertical = document.querySelector('.product-thumbnails.vertical .thumbnail-item[data-slide-index="' + slideIndex + '"]');
        if (activeThumbVertical) {
            activeThumbVertical.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'start'
            });
        }
        
        // Scroll the active thumbnail into view (horizontal)
        const activeThumbHorizontal = document.querySelector('.product-thumbnails.horizontal .thumbnail-item[data-slide-index="' + slideIndex + '"]');
        if (activeThumbHorizontal) {
            activeThumbHorizontal.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    });
    
    // Handle window resize to switch between vertical and horizontal layouts
    function handleResponsiveLayout() {
        if (window.innerWidth < 992) {
            // Mobile layout
            $('.product-gallery').css('flex-direction', 'column');
            $('.product-thumbnails.vertical').css('display', 'none');
            $('.product-thumbnails.horizontal').css('display', 'flex');
        } else {
            // Desktop layout
            $('.product-gallery').css('flex-direction', 'row');
            $('.product-thumbnails.vertical').css('display', 'flex');
            $('.product-thumbnails.horizontal').css('display', 'none');
        }
        
        // Ensure consistent heights between gallery and product details
        adjustGalleryHeight();
    }
    
    // Adjust gallery height
    function adjustGalleryHeight() {
        if (window.innerWidth >= 992) {
            const galleryHeight = $('.product-gallery').outerHeight();
            const detailsHeight = $('.h-100.bg-light.p-30').outerHeight();
            
            if (galleryHeight > detailsHeight) {
                $('.h-100.bg-light.p-30').css('min-height', galleryHeight);
            } else if (detailsHeight > galleryHeight) {
                $('.product-gallery').css('min-height', detailsHeight);
            }
        } else {
            // Reset heights on mobile
            $('.product-gallery').css('min-height', '');
            $('.h-100.bg-light.p-30').css('min-height', '');
        }
    }
    
    // Initialize everything
    setupThumbnailClicks();
    handleResponsiveLayout();
    
    // Handle window resize
    $(window).on('resize', function() {
        handleResponsiveLayout();
    });
    
    // Fix for iOS Safari and some other mobile browsers
    $('.carousel').bcSwipe({ threshold: 50 });
});
</script>

<script>
// Simple swipe gesture support for Bootstrap carousels
(function($) {
    $.fn.bcSwipe = function(settings) {
        var config = { threshold: 50 };
        if (settings) {
            $.extend(config, settings);
        }

        this.each(function() {
            var startX, startY;
            
            $(this).on('touchstart', function(e) {
                if (e.originalEvent.touches.length === 1) {
                    var touch = e.originalEvent.touches[0];
                    startX = touch.pageX;
                    startY = touch.pageY;
                }
            });

            $(this).on('touchmove', function(e) {
                if (startX && startY) {
                    var touch = e.originalEvent.touches[0];
                    var diffX = startX - touch.pageX;
                    var diffY = startY - touch.pageY;

                    if (Math.abs(diffX) > Math.abs(diffY)) {
                        // Horizontal swipe
                        e.preventDefault();
                    }
                }
            });

            $(this).on('touchend', function(e) {
                if (startX && startY) {
                    var touch = e.originalEvent.changedTouches[0];
                    var diffX = startX - touch.pageX;

                    if (Math.abs(diffX) > config.threshold) {
                        if (diffX > 0) {
                            // Swipe left, go to next slide
                            $(this).carousel('next');
                        } else {
                            // Swipe right, go to previous slide
                            $(this).carousel('prev');
                        }
                    }
                    
                    startX = null;
                    startY = null;
                }
            });
        });

        return this;
    };
})(jQuery);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image zoom functionality
    function imageZoom() {
        const images = document.querySelectorAll('.zoom-container');
        
        images.forEach(container => {
            const img = container.querySelector('.zoom-image');
            const lens = container.querySelector('.zoom-lens');
            const result = container.querySelector('.zoom-result');
            
            // Only initialize if elements exist
            if (!img || !lens || !result) return;
            
            // Wait for image to fully load before setting up zoom
            img.onload = function() {
                setupZoom();
            };
            
            // If image is already loaded, set up zoom immediately
            if (img.complete) {
                setupZoom();
            }
            
            function setupZoom() {
                // Set up the background image for the result div
                result.style.backgroundImage = `url('${img.src}')`;
                
                // Calculate the ratio between result div and lens
                const cx = result.offsetWidth / lens.offsetWidth;
                const cy = result.offsetHeight / lens.offsetHeight;
                
                // Functions to handle mouse events
                container.addEventListener('mouseenter', () => {
                    lens.style.display = 'block';
                    result.style.display = 'block';
                });
                
                container.addEventListener('mouseleave', () => {
                    lens.style.display = 'none';
                    result.style.display = 'none';
                });
                
                container.addEventListener('mousemove', (e) => {
                    e.preventDefault();
                    
                    // Get cursor position
                    const pos = getCursorPos(e, container);
                    
                    // Calculate position of lens
                    let x = pos.x - (lens.offsetWidth / 2);
                    let y = pos.y - (lens.offsetHeight / 2);
                    
                    // Get actual image dimensions (may differ from display size)
                    const imageWidth = img.offsetWidth;
                    const imageHeight = img.offsetHeight;
                    
                    // Prevent lens from going outside the image
                    if (x > imageWidth - lens.offsetWidth) x = imageWidth - lens.offsetWidth;
                    if (x < 0) x = 0;
                    if (y > imageHeight - lens.offsetHeight) y = imageHeight - lens.offsetHeight;
                    if (y < 0) y = 0;
                    
                    // Set lens position
                    lens.style.left = x + "px";
                    lens.style.top = y + "px";
                    
                    // Calculate the position ratio
                    const xRatio = x / imageWidth;
                    const yRatio = y / imageHeight;
                    
                    // Move the image in the result div - use ratios for smoother zoom
                    result.style.backgroundPosition = `-${xRatio * (imageWidth * cx - result.offsetWidth)}px -${yRatio * (imageHeight * cy - result.offsetHeight)}px`;
                    result.style.backgroundSize = `${imageWidth * cx}px ${imageHeight * cy}px`;
                });
            }
            
            // Helper function to get cursor position
            function getCursorPos(e, elem) {
                const rect = elem.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                return { x, y };
            }
        });
    }
    
    // Initialize zoom on page load
    imageZoom();
    
    // Re-initialize zoom when carousel slides
    $('#product-carousel').on('slid.bs.carousel', function() {
        setTimeout(imageZoom, 100); // Small delay to ensure new slide is fully loaded
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize zoom functionality for the first image
    initZoom();
    
    // Update zoom when carousel changes images
    $('#product-carousel').on('slid.bs.carousel', function() {
        initZoom();
    });
    
    function initZoom() {
        const container = document.querySelector('.zoom-container');
        const img = container.querySelector('.zoom-image');
        const lens = container.querySelector('.zoom-lens');
        const result = container.querySelector('.zoom-result');
        
        // Only apply zoom on desktop/larger screens
        if (window.innerWidth < 992) {
            return;
        }
        
        // Set result background to the same image
        if (img && img.complete) {
            result.style.backgroundImage = `url('${img.src}')`;
        } else if (img) {
            img.addEventListener('load', function() {
                result.style.backgroundImage = `url('${img.src}')`;
            });
        }
        
        // Mouse enter - show zoom elements
        container.addEventListener('mouseenter', function() {
            lens.style.display = 'block';
            result.style.display = 'block';
        });
        
        // Mouse leave - hide zoom elements
        container.addEventListener('mouseleave', function() {
            lens.style.display = 'none';
            result.style.display = 'none';
        });
        
        // Mouse move - update zoom position and content
        container.addEventListener('mousemove', moveLens);
        
        function moveLens(e) {
            // Prevent any default action
            e.preventDefault();
            
            // Get cursor position
            const pos = getCursorPos(e);
            
            // Calculate position of lens
            let x = pos.x - (lens.offsetWidth / 2);
            let y = pos.y - (lens.offsetHeight / 2);
            
            // Prevent lens from going outside the image
            if (x > img.width - lens.offsetWidth) {x = img.width - lens.offsetWidth;}
            if (x < 0) {x = 0;}
            if (y > img.height - lens.offsetHeight) {y = img.height - lens.offsetHeight;}
            if (y < 0) {y = 0;}
            
            // Set lens position
            lens.style.left = x + "px";
            lens.style.top = y + "px";
            
            // Calculate ratio between result div and lens
            const cx = result.offsetWidth / lens.offsetWidth;
            const cy = result.offsetHeight / lens.offsetHeight;
            
            // Set background position for the result div
            result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
            result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
        }
        
        function getCursorPos(e) {
            let bounds = img.getBoundingClientRect();
            let x = e.pageX - bounds.left - window.scrollX;
            let y = e.pageY - bounds.top - window.scrollY;
            return {x: x, y: y};
        }
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modify Bootstrap's carousel to use fade transition instead of slide
    $('#product-carousel').on('slide.bs.carousel', function(e) {
        $(this).find('.carousel-item.active').addClass('fading');
        $(this).find('.carousel-item.active').removeClass('active');
        $(this).find('.carousel-item').eq(e.to).addClass('active');
        
        // Update active thumbnail
        $('.thumbnail-item').removeClass('active');
        $('.thumbnail-item[data-slide-index="' + e.to + '"]').addClass('active');
        
        // Reset zoom when changing slides
        $('.zoom-lens, .zoom-result').hide();
        
        setTimeout(function() {
            $('.carousel-item.fading').removeClass('fading');
        }, 600);
    });
    
    // Initialize zoom for the active slide
    initZoom();
    
    // Click on thumbnails to change carousel slide
    $('.thumbnail-item').on('click', function() {
        const slideIndex = $(this).data('slide-index');
        $('#product-carousel').carousel(slideIndex);
    });
});

// Your existing zoom functionality
function initZoom() {
    const container = document.querySelector('.zoom-container');
    if (!container) return;
    
    const img = container.querySelector('.zoom-image');
    const lens = container.querySelector('.zoom-lens');
    const result = container.querySelector('.zoom-result');
    
    // Only apply zoom on desktop/larger screens
    if (window.innerWidth < 992) {
        return;
    }
    
    // Set result background to the same image
    if (img && img.complete) {
        result.style.backgroundImage = `url('${img.src}')`;
    } else if (img) {
        img.addEventListener('load', function() {
            result.style.backgroundImage = `url('${img.src}')`;
        });
    }
    
    // Mouse enter - show zoom elements
    container.addEventListener('mouseenter', function() {
        lens.style.display = 'block';
        result.style.display = 'block';
    });
    
    // Mouse leave - hide zoom elements
    container.addEventListener('mouseleave', function() {
        lens.style.display = 'none';
        result.style.display = 'none';
    });
    
    // Mouse move - update zoom position and content
    container.addEventListener('mousemove', moveLens);
    
    function moveLens(e) {
        // Prevent any default action
        e.preventDefault();
        
        // Get cursor position
        const pos = getCursorPos(e);
        
        // Calculate position of lens
        let x = pos.x - (lens.offsetWidth / 2);
        let y = pos.y - (lens.offsetHeight / 2);
        
        // Prevent lens from going outside the image
        if (x > img.width - lens.offsetWidth) {x = img.width - lens.offsetWidth;}
        if (x < 0) {x = 0;}
        if (y > img.height - lens.offsetHeight) {y = img.height - lens.offsetHeight;}
        if (y < 0) {y = 0;}
        
        // Set lens position
        lens.style.left = x + "px";
        lens.style.top = y + "px";
        
        // Calculate ratio between result div and lens
        const cx = result.offsetWidth / lens.offsetWidth;
        const cy = result.offsetHeight / lens.offsetHeight;
        
        // Set background position for the result div
        result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
        result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
    }
    
    function getCursorPos(e) {
        let bounds = img.getBoundingClientRect();
        let x = e.pageX - bounds.left - window.scrollX;
        let y = e.pageY - bounds.top - window.scrollY;
        return {x: x, y: y};
    }
}
document.addEventListener('DOMContentLoaded', function() {
    // Handle video thumbnail click
    const videoThumb = document.querySelector('.video-thumb');
    const carousel = document.getElementById('product-carousel');
    const videoSlide = document.querySelector('.video-slide');
    const allSlides = document.querySelectorAll('.carousel-item');
    
    if (videoThumb) {
        videoThumb.addEventListener('click', function() {
            // Hide all slides
            allSlides.forEach(slide => {
                slide.classList.remove('active');
                if (!slide.classList.contains('video-slide')) {
                    slide.style.display = 'none';
                }
            });

            // Show and activate video slide
            videoSlide.style.display = 'block';
            videoSlide.classList.add('active');
            
            // Update thumbnail active state
            document.querySelectorAll('.thumbnail-item').forEach(thumb => {
                thumb.classList.remove('active');
            });
            videoThumb.classList.add('active');

            // Play video if present
            const video = videoSlide.querySelector('video');
            if (video) {
                video.play();
            }
        });
    }

    // Handle regular thumbnail clicks
    document.querySelectorAll('.thumbnail-item:not(.video-thumb)').forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Stop video if playing
            const video = document.querySelector('.video-slide video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }

            // Show all image slides again
            allSlides.forEach(slide => {
                if (!slide.classList.contains('video-slide')) {
                    slide.style.display = '';
                }
            });

            // Hide video slide
            if (videoSlide) {
                videoSlide.style.display = 'none';
                videoSlide.classList.remove('active');
            }
        });
    });
});

</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoThumb = document.querySelector('.video-thumb');
    const videoWrapper = document.querySelector('.product-video-wrapper');
    const carousel = document.getElementById('product-carousel');
    const video = document.getElementById('product-video');
    
    if (videoThumb && videoWrapper) {
        videoThumb.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Stop carousel autoplay
            $(carousel).carousel('pause');
            
            // Hide carousel
            carousel.style.display = 'none';
            
            // Show video wrapper
            videoWrapper.style.display = 'block';
            
            // Update thumbnail active state
            document.querySelectorAll('.thumbnail-item').forEach(thumb => {
                thumb.classList.remove('active');
            });
            videoThumb.classList.add('active');
        });
    }

    // Handle regular thumbnail clicks
    document.querySelectorAll('.thumbnail-item:not(.video-thumb)').forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Stop and reset video if playing
            if (video) {
                video.pause();
                video.currentTime = 0;
            }

            // Hide video wrapper
            if (videoWrapper) {
                videoWrapper.style.display = 'none';
            }

            // Show carousel
            carousel.style.display = 'block';
            
            // Resume carousel functionality
            $(carousel).carousel('cycle');
        });
    });
});
</script>
