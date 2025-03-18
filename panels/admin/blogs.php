<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!empty($_SESSION['role'])) {
    $title = "blogs";
    require_once('./header.php');
    require_once('./logics.class.php');

    $getUsers = new logics();
    $verification = $getUsers->getBlogs();

    if (!empty($verification['status']) && $verification['status'] == 1) {
        ?>

        <!--  Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-sm-12">
                                <div class="card-body" style="overflow-x: scroll;">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title text-primary">View All Blogs</h5>
                                        <a href="./addblogs.php" class="btn btn-sm btn-primary">Add Blogs</a>
                                    </div>
                                    <br>
                                    <table id="example" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Username</th>
                                                <th>Blog Heading</th>
                                                <th>Blog Description</th>
                                                <th>Meta Title</th>
                                                <th>Meta Keywords</th>
                                                <th>Meta Description</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < $verification['count']; $i++) {
                                            ?>
                                            <tr>
                                                <td><?php echo $verification['id'][$i]; ?></td>
                                                <td><img src="./Blogimages/<?php echo $verification['featured_image'][$i]; ?>" width="100px" height="100px" alt="Blog Image"></td>
                                                <td><?php echo $verification['username'][$i]; ?></td>
                                                <td>
                                                    <a href="../../blog-detail.php?slug=<?php echo $verification['slug_url'][$i]; ?>" 
                                                       target="_blank" 
                                                       class="text-primary text-decoration-underline"
                                                       title="View Blog">
                                                        <?php echo $verification['blog_heading'][$i]; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo substr($verification['blog_desc'][$i], 0, 50) . '...'; ?></td>
                                                <td><?php echo $verification['meta_title'][$i]; ?></td>
                                                <td><?php echo $verification['meta_keywords'][$i]; ?></td>
                                                <td><?php echo substr($verification['meta_description'][$i], 0, 50) . '...'; ?></td>
                                                <td><?php echo $verification['created_at'][$i]; ?></td>
                                                <td>
                                                    <div class="btn-group" role="group" aria-label="Blog Actions">
                                                        <a href="edit_blog.php?id=<?php echo $verification['id'][$i]; ?>" 
                                                           class="btn btn-sm btn-primary me-2" 
                                                           style="margin-right: 8px;">Edit</a>
                                                        <a href="manage-status?delete_record_id=<?php echo $verification['id'][$i]; ?>&delete_table_name=blogs&url=blogs" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Content -->

        <?php
    } else {
        echo '<div class="container-xxl flex-grow-1 container-p-y">';
        echo '<div class="alert alert-info">No blogs found. <a href="./addblogs.php" class="btn btn-sm btn-primary">Add a Blog</a></div>';
        echo '</div>';
    }

    require_once('footer.php');
} else {
    header('location:login.php');
}
?>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script>
    new DataTable('#example');
</script>

<style>
/* Add additional styling for proper button spacing */
.btn-group .btn {
    margin-right: 5px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}

/* Add styling for blog heading links */
.text-decoration-underline {
    text-decoration: underline !important;
}

.text-decoration-underline:hover {
    text-decoration: none !important;
    font-weight: bold;
}
</style>