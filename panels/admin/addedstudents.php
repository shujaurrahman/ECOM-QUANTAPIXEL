<?php
session_start();
if (!empty($_SESSION['role'])) {
    $title = "students";
    require_once('header.php');
    require_once('./logics.class.php');

    $getUsers = new logics();
    $verification = $getUsers->GetAddedStudents();

    if (!empty($verification['status']) && $verification['status'] == 1) {
        ?>
        <head>
            <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
        </head>

        <!--  Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-sm-12">
                                <div class="card-body" style="overflow-x: scroll;">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title text-primary">View All Students</h5>
                                        <a href="./addstudents.php" class="btn btn-sm btn-primary">Add Students</a>
                                    </div>
                                    <br>
                                    <table id="example" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td>ID</td>
                                                <td>Student ID</td>
                                                <td>Name</td>
                                                <td>Email</td>
                                                <td>Password</td>
                                                <td>Domain</td>
                                                <td>Created At</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < $verification['count']; $i++) {
                                            ?>
                                            <tr>
                                                <td><?php echo $i + 1; ?></td>
                                                
                                                
                                                <td><?php echo $verification['stud_id'][$i]; ?></td>
                                                <td><?php echo $verification['name'][$i]; ?></td>
                                                <td><?php echo $verification['email'][$i]; ?></td>
                                                <td><?php echo $verification['password'][$i]; ?></td>
                                                <td><?php echo $verification['domain'][$i]; ?></td>
                                                
                                                <td><?php echo $verification['created_at'][$i]; ?></td>
                                                
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
        echo "Data not fetched";
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