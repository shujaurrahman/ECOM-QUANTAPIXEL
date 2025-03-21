<?php
session_start();
require_once('header.php');

$slug = $_GET['name'];
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
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1.25rem;
}
.py-4 {
    padding-top: .2rem !important;
}

.alert {
    background-color: rgba(196, 153, 108, 0.1); /* Theme color with opacity */
    border: 1px solid #c4996c;
    border-radius: 0.25rem;
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.alert-info {
    color: #6b4f32; /* Darker shade of theme color */
    background-color: rgba(196, 153, 108, 0.15);
    border-color: #c4996c;
}

.alert .alert-heading {
    color: #c4996c;
    margin-bottom: 1rem;
}

.alert hr {
    border-top-color: #c4996c;
    opacity: 0.2;
}

.alert .btn-primary {
    margin-top: 1rem;
    background-color: #c4996c;
    border-color: #c4996c;
    color: #fff;
    transition: all 0.3s ease;
    padding: 0.5rem 2rem;
}

.alert .btn-primary:hover {
    background-color: #a37b50; /* Darker shade of theme color */
    border-color: #a37b50;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(196, 153, 108, 0.2);
}
</style>
<!-- Breadcrumb Start -->
<div class="container-fluid">
    <div class="row px-xl-5">
        <div class="col-12">
            <nav class="breadcrumb bg-light mb-30">
                <a class="breadcrumb-item text-dark" href="index">Home</a>
                <a class="breadcrumb-item text-dark" href="#">Sub-Category</a>
                <span class="breadcrumb-item active"><?php echo $slug ?></span>
            </nav>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->

<!-- Shop Start -->
<div class="container-fluid">
    <!-- Grid View Section (Default) -->
    <div id="grid-view" class="row">
        <?php
        $subcategoriesFound = false; // Flag to track if any subcategories are found
        
        for($i = 0; $i < $subcategories['count']; $i++) {
            if($subcategories['statusval'][$i] == 1 && $subcategories['category_id'][$i] == $_GET['id']){
                $subcategoriesFound = true; // Set flag to true when a subcategory is found
                
                $slug = $subcategories['name'][$i];
                $slug = strtolower($slug);
                $slug = str_replace(' ', '-', $slug);
                $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                $slug = trim($slug, '-');
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 pb-1">
                    <a href="./product-listing?id=<?php echo $subcategories['id'][$i]; ?>&name=<?php echo $slug; ?>" class="text-decoration-none">
                        <div class="product-item bg-light mb-4">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" src="./panels/admin/subcategory/<?php echo $subcategories['image'][$i]; ?>" alt="" style="width: 100%; height: 300px;">
                            </div>
                            <div class="text-center py-4">
                                <h3 class="text-truncate"><?php echo $subcategories['name'][$i]; ?></h3>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <b><?php echo $subcategories['product_count'][$i]; ?> </b>&nbsp; Products
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
            }
        }

        // Display message if no subcategories were found
        if (!$subcategoriesFound) {
            ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">No Subcategories Found!</h4>
                    <p>We couldn't find any subcategories in this collection at the moment.</p>
                    <hr>
                    <p class="mb-0">
                        Check out our latest additions in our recent products collection.
                    </p>
                    <a href="recent-products" class="btn btn-primary">View Recent Products</a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div> 
<!-- Shop End -->

<script>
    // Toggle Grid View and List View
    document.getElementById('grid-view-btn').addEventListener('click', function() {
        document.getElementById('grid-view').style.display = 'block';
        document.getElementById('list-view').style.display = 'none';
    });

    document.getElementById('list-view-btn').addEventListener('click', function() {
        document.getElementById('grid-view').style.display = 'none';
        document.getElementById('list-view').style.display = 'block';
    });
</script>

<?php
require_once('footer.php');
?>
