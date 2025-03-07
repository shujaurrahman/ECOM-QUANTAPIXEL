<?php
session_start();
require_once('header.php');
$Obj = new logics();
$blogs = $Obj->getBlogs();
?>

<!-- Blogs Page Start -->
<div class="container-fluid pt-5 pb-3">
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Our Blogs</span>
    </h2>
    
    <div class="row px-xl-5">
        <?php
        if (!empty($blogs['status']) && $blogs['status'] == 1) {
            for ($i = 0; $i < $blogs['count']; $i++) {
                if ($blogs['status_value'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/Blogimages/<?php echo htmlspecialchars($blogs['featured_image'][$i]); ?>" 
                                     alt="<?php echo htmlspecialchars($blogs['blog_heading'][$i]); ?>">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="blog-detail.php?slug=<?php echo $blogs['slug_url'][$i]; ?>"><i class="fa fa-eye"></i></a>
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none mobile-small-font text-truncate product-name" style="max-width: 100%;" 
                                   href="blog-detail.php?slug=<?php echo $blogs['slug_url'][$i]; ?>">
                                   <?php echo htmlspecialchars($blogs['blog_heading'][$i]); ?>
                                </a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <p class="text-muted small"><?php echo htmlspecialchars(substr($blogs['blog_desc'][$i], 0, 80)) . '...'; ?></p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 px-3">
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($blogs['created_at'][$i])); ?></small>
                                    <a href="blog-detail.php?slug=<?php echo $blogs['slug_url'][$i]; ?>" class="btn btn-sm btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            ?>
            <div class="col-12 text-center py-5">
                <div class="p-4 bg-light">
                    <h3>No blog posts available.</h3>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<!-- Blogs Page End -->

<?php
require_once('footer.php');
?>