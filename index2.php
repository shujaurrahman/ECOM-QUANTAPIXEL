<?php
session_start();
require_once('header.php');

if (!empty($_SESSION['show_signin_modal'])):
?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            $('#SigninModal').modal('show');
        });
    </script>
    <?php
    unset($_SESSION['show_signin_modal']); // Remove the flag after use
endif;

?>


    <!-- Carousel Start -->
    <div class="container-fluid mb-3">
        <div class="row px-xl-5">
            <div class="col-lg-12">
            <div class="d-flex" style="overflow-x: scroll;">
                <?php for ($i = 0; $i < $categories['count']; $i++): 
                    if($categories['statusval'][$i]==1){
                    
                    // Get category details
                    $categoryName = htmlspecialchars($categories['name'][$i]);
                    $categoryImage = htmlspecialchars($categories['image'][$i]); // Assuming image URLs are in the 'image' field
                    $categoryDescription = htmlspecialchars($categories['description'][$i]);

                    $slug = $categories['name'][$i];
                    $slug = strtolower($slug);
                    $slug = str_replace(' ', '-', $slug);
                    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                    $slug = trim($slug, '-');
                    ?>
                    <div class="cat_div p-3">
                        <a href="sub-categories?id=<?php echo $categories['id'][$i]; ?>&name=<?php echo $categories['name'][$i]; ?>">
                            <img src="./panels/admin/category/<?php echo $categories['image'][$i]; ?>" style="width: 200px;height:200px;" class="rounded box-shadow-container" alt="">
                            <h5 class="text-center mt-2"><?php echo $categories['name'][$i]; ?></h5>
                        </a>
                    </div>

                    <?php 
                    }
                    endfor; ?>

                

               
            </div>
            </div>
        </div>
    </div>


    <!-- Carousel Start -->
    <div class="container-fluid mb-3">
        <div class="row px-xl-5">
            <div class="col-lg-8">
                <div id="header-carousel" class="carousel slide carousel-fade mb-30 mb-lg-0" data-ride="carousel">

                    <ol class="carousel-indicators">
                        <?php for ($i = 0; $i < $categories['count']; $i++):
                            if($categories['statusval'][$i]==1){
                            ?>
                            <li data-target="#header-carousel" data-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active"' : ''; ?>></li>

                            <?php 
                            }
                        endfor; ?>
                    </ol>

                    <div class="carousel-inner">
                        <?php for ($i = 0; $i < $categories['count']; $i++): 
                            if($categories['statusval'][$i]==1){
                            
                            // Get category details
                            $categoryName = htmlspecialchars($categories['name'][$i]);
                            $categoryImage = htmlspecialchars($categories['image'][$i]); // Assuming image URLs are in the 'image' field
                            $categoryDescription = htmlspecialchars($categories['description'][$i]);

                            $slug = $categories['name'][$i];
                            $slug = strtolower($slug);
                            $slug = str_replace(' ', '-', $slug);
                            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                            $slug = trim($slug, '-');
                            
                            ?>

                            <div class="carousel-item position-relative <?php echo $i === 0 ? 'active' : ''; ?>" style="height: 430px;">
                                <img class="position-absolute w-100 h-100" src="./panels/admin/category/<?php echo $categories['image'][$i]; ?>" style="width:100%">
                                <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                                    <div class="p-3" style="max-width: 700px;">
                                        <h1 class="display-4 text-white mb-3 animate__animated animate__fadeInDown"><?php echo $categories['name'][$i]; ?></h1>
                                        <p class="mx-md-5 px-5 animate__animated animate__bounceIn"><?php echo $categories['description'][$i]; ?></p>
                                        <a class="btn btn-outline-light py-2 px-4 mt-3 animate__animated animate__fadeInUp" href="sub-categories?id=<?php echo $categories['id'][$i]; ?>&name=<?php echo $slug; ?>">Shop Now</a>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            }
                            endfor; ?>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">

                    <?php 
                        for ($i = 0; $i < $advertisements['count']; $i++){
                            if($advertisements['statusval'][$i]==1){
                                if($advertisements['location'][$i]==1 || $advertisements['location'][$i]==2){
                                    ?>
                                    <div class="product-offer mb-30" style="height: 200px;">
                                        <img class="img-fluid" src="./panels/admin/advertisements/<?php echo $advertisements['image'][$i] ?>" alt="">
                                        <div class="offer-text">
                                            <h6 class="text-white text-uppercase"><?php echo $advertisements['description'][$i] ?></h6>
                                            <h3 class="text-white mb-3"><?php echo $advertisements['name'][$i] ?></h3>
                                            <a href="<?php echo $advertisements['url'][$i] ?>" class="btn btn-primary text-light">Shop Now</a>
                                        </div>
                                    </div>

                                    <?php

                                }
                            }
                        }
                            
                            ?>
            </div>
        </div>
    </div>
    <!-- Carousel End -->


    <!-- Featured Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5 pb-3">
            <div class="col-lg-3 col-md-6 col-6 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-check text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Quality Product</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-shipping-fast text-primary m-0 mr-2"></h1>
                    <h5 class="font-weight-semi-bold m-0">Free Shipping</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fas fa-exchange-alt text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">Easy Returns</h5>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6 pb-1">
                <div class="d-flex align-items-center bg-light mb-4" style="padding: 30px;">
                    <h1 class="fa fa-phone-volume text-primary m-0 mr-3"></h1>
                    <h5 class="font-weight-semi-bold m-0">24/7 Support</h5>
                </div>
            </div>
        </div>
    </div>
    <!-- Featured End -->

        <!-- Categories Start -->
        <div class="container-fluid pt-5">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">All Categories</span></h2>
        <div class="row px-xl-5 pb-3">
            <?php
            for($i=0;$i<$categories['count'];$i++){
                if($categories['statusval'][$i]==1){
                    
                    $slug = $categories['name'][$i];
                    $slug = strtolower($slug);
                    $slug = str_replace(' ', '-', $slug);
                    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                    $slug = trim($slug, '-');

                    
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <a class="text-decoration-none" href="./sub-categories?id=<?php echo $categories['id'][$i]; ?>&name=<?php echo $slug; ?>">
                            <div class="cat-item d-flex align-items-center mb-4">
                                <div class="overflow-hidden categories-image-mobile-small" >
                                    <img class="img-fluid" src="./panels/admin/category/<?php echo $categories['image'][$i]; ?>" alt="" style="width: 100%;height:100%">
                                </div>
                                <div class="flex-fill pl-3">
                                    <h6><?php echo $categories['name'][$i]; ?></h6>
                                    <small class="text-body"><?php echo $categories['product_count'][$i]; ?> Products</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                }
            }

            ?>
        </div>
        
    </div>
    <!-- Categories End -->


        <!-- Featured Start -->
    <div class="container-fluid pt-5">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Recommended Products</span></h2>
        <div class="row px-xl-5 pb-3">
            <div class="owl-carousel owl-theme">
            <?php
            for ($i = 0; $i < min($products['count'], 8); $i++) {
                if ($products['statusval'][$i] == 1 && $products['is_recommended'][$i] == 1) {
                    ?>
                    <div class="item  pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>"  alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToCart&product_id=<?php echo $products['id'][$i] ?>"><i class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToWishlist&product_id=<?php echo $products['id'][$i] ?>"><i class="far fa-heart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><i class="fa fa-eye"></i></a>
                                
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none  mobile-small-font" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><?php echo $products['product_name'][$i]; ?></a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5 class="font-weight-bold mr-2">
                                        ₹<?php echo number_format(floor($products['discounted_price'][$i])); ?>
                                    </h5>
                                    <small class="text-muted ml-2">
                                        <del>₹<?php echo number_format(floor($products['actual_price'][$i])); ?></del>
                                    </small>
                                </div>
                                <!-- <span class="font-weight-bold"><small class="text-danger">Save <?php echo $products['discount_percentage'][$i]; ?>% on </small> <?php echo $products['ornament_weight'][$i]; ?>g</span> -->

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
                    <?php
                }
            }
        ?>
                
                
            </div>
        </div>
    </div>





    <!-- Products Start -->
    <div class="container-fluid pt-5 pb-3">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Featured Products</span></h2>
        <div class="row px-xl-5">
        <?php
            for ($i = 0; $i < min($products['count'], 8); $i++) {
                if ($products['statusval'][$i] == 1 && $products['is_recommended'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>"  alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToCart&product_id=<?php echo $products['id'][$i] ?>"><i class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToWishlist&product_id=<?php echo $products['id'][$i] ?>"><i class="far fa-heart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><i class="fa fa-eye"></i></a>
                                    <!-- <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a> -->
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none  mobile-small-font" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><?php echo $products['product_name'][$i]; ?></a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5 class="font-weight-bold mr-2">
                                        ₹<?php echo number_format(floor($products['discounted_price'][$i])); ?>
                                    </h5>
                                    <small class="text-muted ml-2">
                                        <del>₹<?php echo number_format(floor($products['actual_price'][$i])); ?></del>
                                    </small>
                                </div>
                                <span class="font-weight-bold"><small class="text-danger">Save <?php echo $products['discount_percentage'][$i]; ?>% </small> </span>

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
                    <?php
                }
            }
        ?>

        </div>
        <div class="text-center">
            <a href="" class="btn btn-primary">View All <i class="fas fa-angle-double-right"></i></a>
        </div>
    </div>
    <!-- Products End -->


    <!-- Offer Start -->
    <div class="container-fluid pt-5 pb-3">
        <div class="row px-xl-5">
        <?php 
            for ($i = 0; $i < $advertisements['count']; $i++){
                if($advertisements['statusval'][$i]==1){
                    if($advertisements['location'][$i]==3 || $advertisements['location'][$i]==4){
                        ?>
                        <div class="col-md-6">
                            <div class="product-offer mb-30" style="height: 300px;">
                                <img class="img-fluid" src="./panels/admin/advertisements/<?php echo $advertisements['image'][$i] ?>" alt="">
                                <div class="offer-text">
                                    <h6 class="text-white text-uppercase"><?php echo  $advertisements['description'][$i] ?></h6>
                                    <h3 class="text-white mb-3"><?php echo  $advertisements['name'][$i] ?></h3>
                                    <a href="<?php echo $advertisements['url'][$i] ?>" class="btn btn-primary">Shop Now</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
        ?>
            
        </div>
    </div>
    <!-- Offer End -->

    <!-- Products Start -->
    <div class="container-fluid pt-5 pb-3 d-none">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Recent Products</span></h2>
        <div class="row px-xl-5">
        <?php
            for ($i = 0; $i < min($products['count'], 8); $i++) {
                if ($products['statusval'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>"  alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./product-view?id=<?php echo $products['id'][$i] ?>"><i class="fa fa-eye"></i></a>
                                    <!-- <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a> -->
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none mobile-small-font" href=""><?php echo $products['product_name'][$i]; ?></a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5 class="font-weight-bold mr-2">
                                        ₹<?php echo number_format(floor($products['discounted_price'][$i])); ?>
                                    </h5>
                                    <small class="text-muted ml-2">
                                        <del>₹<?php echo number_format(floor($products['actual_price'][$i])); ?></del>
                                    </small>
                                </div>
                                <span class="font-weight-bold"><small class="text-danger">Save <?php echo $products['discount_percentage'][$i]; ?>% </small> </span>

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
                    <?php
                }
            }
        ?>

        </div>
        <div class="text-center">
            <a href="" class="btn btn-primary">View All <i class="fas fa-angle-double-right"></i></a>
        </div>
    </div>
    <!-- Products End -->


    <!-- Recent Products -->
    <div class="container-fluid pt-5 pb-3">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Recent Products</span></h2>
        <div class="row px-xl-5">
        <?php
            for ($i = 0; $i < min($products['count'], 8); $i++) {
                if ($products['statusval'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>"  alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToCart&product_id=<?php echo $products['id'][$i] ?>"><i class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToWishlist&product_id=<?php echo $products['id'][$i] ?>"><i class="far fa-heart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><i class="fa fa-eye"></i></a>
                                    <!-- <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a> -->
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
                                <span class="font-weight-bold"><small class="text-danger">Save <?php echo $products['discount_percentage'][$i]; ?>% </small> </span>

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
                    <?php
                }
            }
        ?>

        </div>
        <div class="text-center">
            <a href="" class="btn btn-primary">View All <i class="fas fa-angle-double-right"></i></a>
        </div>
    </div>
    <!-- Products End -->

    <!-- Products Start -->
    <div class="container-fluid pt-5 pb-3">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Products Under Discounts</span></h2>
        <div class="row px-xl-5">
        <?php
            for ($i = 0; $i < min($products['count'], 8); $i++) {
                if ($products['statusval'][$i] == 1 && $products['is_lakshmi_kubera'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>"  alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToCart&product_id=<?php echo $products['id'][$i] ?>"><i class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./open-logics.php?type=addToWishlist&product_id=<?php echo $products['id'][$i] ?>"><i class="far fa-heart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href="./product-view?slug=<?php echo $products['slug'][$i] ?>"><i class="fa fa-eye"></i></a>
                                    <!-- <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a> -->
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
                                <span class="font-weight-bold"><small class="text-danger">Save <?php echo $products['discount_percentage'][$i]; ?>% </small> </span>

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
                    <?php
                }
            }
        ?>

        </div>
        <div class="text-center">
            <a href="" class="btn btn-primary">View All <i class="fas fa-angle-double-right"></i></a>
        </div>
    </div>
    <!-- Products End -->


    <!-- Products Start -->
    <div class="container-fluid pt-5 pb-3 d-none">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">Products Under Ready To Ship</span></h2>
        <div class="row px-xl-5">
        <?php
            for ($i = 0; $i < min($products['count'], 8); $i++) {
                if ($products['statusval'][$i] == 1 && $products['is_lakshmi_kubera'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" src="./panels/admin/product/<?php echo $products['featured_image'][$i]; ?>" style="height: 300px;" alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href=""><i class="far fa-heart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-eye"></i></a>
                                    <!-- <a class="btn btn-outline-dark btn-square" href=""><i class="fa fa-search"></i></a> -->
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none " href=""><?php echo $products['product_name'][$i]; ?></a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5 class="font-weight-bold mr-2">
                                        ₹<?php echo number_format(floor($products['discounted_price'][$i])); ?>
                                    </h5>
                                    <small class="text-muted ml-2">
                                        <del>₹<?php echo number_format(floor($products['actual_price'][$i])); ?></del>
                                    </small>
                                </div>
                                <span class="font-weight-bold"><small class="text-danger">Save <?php echo $products['discount_percentage'][$i]; ?>% </small> </span>

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
                    <?php
                }
            }
        ?>

        </div>
        <div class="text-center">
            <a href="" class="btn btn-primary">View All <i class="fas fa-angle-double-right"></i></a>
        </div>
    </div>
    <!-- Products End -->



        <!-- Vendor Start -->
        <div class="container-fluid py-5">
        <h1 class="text-center">People Talks ...!</h1>
        <br>
        <div class="row px-xl-5">
            <div class="col">
                <div class="owl-carousel testimonial-carousel">
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" alt="profile" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    <div class="bg-light p-4">
                        <div class="text-center">
                            <img src="./images/demo.avif" style="width: 100px;border-radius:50%" alt="" class="mx-auto">
                            <h5>In Love ...!</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid debitis mollitia ab ducimus non sequi, nulla accusantium officia ea consequatur.</p>
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                                <small class="fa fa-star text-primary mr-1"></small>
                            </div>
                            <p>-Krishna</p>

                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <!-- Vendor End -->



    <!-- Vendor Start -->
    <div class="container-fluid py-5">
        <h1 class="text-center">Our Partners</h1>
        <div class="row px-xl-5">
            <div class="col">
                <div class="owl-carousel vendor-carousel">
                    <div class="bg-light p-4">
                        <img src="img/vendor-1.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-2.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-3.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-4.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-5.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-6.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-7.jpg" alt="">
                    </div>
                    <div class="bg-light p-4">
                        <img src="img/vendor-8.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Vendor End -->


   <?php
    require_once('footer.php');
   ?>


<script>
$(function() {
    // Owl Carousel
    var owl = $(".owl-carousel");
    owl.owlCarousel({
        items: 3,
        margin: 10,
        loop: true,
        dots: false,
        navText: ["<i class='fa fa-angle-left icon-styling owl-prev'></i>", "<i class='fa fa-angle-right icon-styling owl-next'></i>"], // Custom navigation text/icons
        responsive: {
            0: { items: 2 }, // 2 items for mobile
            600: { items: 3 }, // 3 items for tablets
            1000: { items: 5 } // 5 items for desktops
        },
        autoplay: true,          // Enables auto-scroll
        autoplayTimeout: 1500,   // Time in milliseconds between auto-scrolls (3 seconds)
        autoplayHoverPause: true // Pauses auto-scroll on hover
    });
});

</script>