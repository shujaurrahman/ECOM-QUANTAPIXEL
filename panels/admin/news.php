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
                                                <th>Username</th>
                                                <th>News Heading</th>
                                                <th>News Description</th>
                                                <th>News Link</th>
                                                <th>Meta Title</th>
                                                <th>Meta Keywords</th>
                                                <th>Created At</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < $verification['count']; $i++) {
                                            ?>
                                            <tr>
                                                <td><?php echo $verification['id'][$i]; ?></td>
                                                <td><img src="./newsimages/<?php echo $verification['featured_image'][$i]; ?>" width="100px" height="100px" alt="News Image"></td>
                                                <td><?php echo $verification['username'][$i]; ?></td>
                                                <td><?php echo $verification['newsheading'][$i]; ?></td>
                                                <td><?php echo substr($verification['newsdesc'][$i], 0, 50) . '...'; ?></td>
                                                <td><a href="<?php echo $verification['newslink'][$i]; ?>" target="_blank">View Link</a></td>
                                                <td><?php echo $verification['meta_title'][$i]; ?></td>
                                                <td><?php echo $verification['meta_keywords'][$i]; ?></td>
                                                <td><?php echo $verification['created_at'][$i]; ?></td>
                                                <td>
                                                    <?php if($verification['status_value'][$i] == 1): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
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