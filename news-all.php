<?php
session_start();
require_once('header.php');
$Obj = new logics();
$news = $Obj->getNews();
?>

<!-- News Page Start -->
<div class="container-fluid py-5">
    <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4">
        <span class="bg-secondary pr-3">Latest News</span>
    </h2>
    
    <div class="row px-xl-5">
        <?php
        if (!empty($news['status']) && $news['status'] == 1) {
            for ($i = 0; $i < $news['count']; $i++) {
                if ($news['status_value'][$i] == 1) {
                    ?>
                    <div class="col-lg-3 col-md-4 col-6 pb-1">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100 mobile-small-image" src="./panels/admin/newsimages/<?php echo htmlspecialchars($news['featured_image'][$i]); ?>" 
                                     alt="<?php echo htmlspecialchars($news['newsheading'][$i]); ?>">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href="<?php echo htmlspecialchars($news['newslink'][$i]); ?>" target="_blank"><i class="fa fa-external-link-alt"></i></a>
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none mobile-small-font text-truncate product-name" style="max-width: 100%;" 
                                   href="<?php echo htmlspecialchars($news['newslink'][$i]); ?>" target="_blank">
                                   <?php echo htmlspecialchars($news['newsheading'][$i]); ?>
                                </a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <p class="text-muted small"><?php echo htmlspecialchars(substr($news['newsdesc'][$i], 0, 80)) . '...'; ?></p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 px-3">
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($news['created_at'][$i])); ?></small>
                                    <a href="<?php echo htmlspecialchars($news['newslink'][$i]); ?>" target="_blank" class="btn btn-sm btn-primary">Visit Link</a>
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
                    <h3>No news available at the moment.</h3>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<!-- News Page End -->

<?php
require_once('footer.php');
?>