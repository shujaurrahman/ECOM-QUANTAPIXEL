<?php
session_start();
require_once('header.php');
$Obj = new logics();

// Check if slug is provided
if(empty($_GET['slug'])) {
    header('Location: blogs.php');
    exit;
}

$slug = $_GET['slug'];
$blog = $Obj->getBlogBySlug($slug);

// If blog not found
if(empty($blog) || empty($blog['status']) || $blog['status'] != 1) {
    echo '<div class="container py-5">';
    echo '<div class="alert alert-danger">Blog not found.</div>';
    echo '</div>';
    require_once('footer.php');
    exit;
}
?>

<!-- Blog Detail Start -->
<div class="container-fluid py-5">
    <div class="row px-xl-5">
        <div class="col-lg-8 offset-lg-2">
            <div class="mb-4">
                <h1 class="mb-4"><?php echo htmlspecialchars($blog['blog_heading'][0]); ?></h1>
                <div class="mb-3">
                    <!-- <span class="mr-3"><i class="far fa-user text-primary"></i> <?php echo htmlspecialchars($blog['username'][0]); ?></span> -->
                    <span><i class="far fa-calendar-alt text-primary"></i> <?php echo date('d M Y', strtotime($blog['created_at'][0])); ?></span>
                </div>
                <img class="img-fluid w-100 rounded mb-4" src="./panels/admin/Blogimages/<?php echo $blog['featured_image'][0]; ?>" alt="<?php echo htmlspecialchars($blog['blog_heading'][0]); ?>">
                <p class="mb-4"><?php echo htmlspecialchars($blog['blog_desc'][0]); ?></p>
                <div class="blog-content">
                    <?php echo $blog['description'][0]; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Blog Detail End -->

<?php
require_once('footer.php');
?>