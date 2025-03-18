<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!empty($_SESSION['role'])) {
    $title = "news";
    require_once('./header.php');
    require_once('./logics.class.php');

    $getUsers = new logics();
    $verification = $getUsers->getNews();

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
                                        <h5 class="card-title text-primary">View All News</h5>
                                        <a href="./addnews.php" class="btn btn-sm btn-primary">Add News</a>
                                    </div>
                                    <br>
                                    <table id="example" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                  
                                                <th>News Heading</th>
                                                <th>News Description</th>
                                                <th>News Link</th>
                                                <th>Meta Title</th>
                                                <th>Meta Keywords</th>
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
                                                <td><img src="./newsimages/<?php echo $verification['featured_image'][$i]; ?>" width="100px" height="100px" alt="News Image"></td>
                    
                                                <td><a href='<?php echo $verification['newslink'][$i]; ?>'target='_blank' ><?php echo $verification['newsheading'][$i]; ?></a></td>
                                                <td><?php echo substr($verification['newsdesc'][$i], 0, 50) . '...'; ?></td>
                                                <td><?php echo $verification['newslink'][$i]; ?></td>
                                                <td><?php echo $verification['meta_title'][$i]; ?></td>
                                                <td><?php echo $verification['meta_keywords'][$i]; ?></td>
                                                <td><?php echo $verification['created_at'][$i]; ?></td>
                                                <td>
                                                    <div class="btn-group" role="group" aria-label="News Actions">
                                                        <a href="edit_news.php?id=<?php echo $verification['id'][$i]; ?>" 
                                                           class="btn btn-sm btn-primary me-2" 
                                                           style="margin-right: 8px;">Edit</a>
                                                        <a href="manage-status?delete_record_id=<?php echo $verification['id'][$i]; ?>&delete_table_name=news&url=news" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this news item?')">Delete</a>
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
        echo '<div class="alert alert-info">No news found. <a href="./addnews.php" class="btn btn-sm btn-primary">Add News</a></div>';
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
</style>