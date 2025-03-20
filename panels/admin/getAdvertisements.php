<?php
session_start();
if (!empty($_SESSION['role'])) {
    $title = "added-advertisements";
    require_once('header.php');
    require_once('./logics.class.php');

    $getUsers = new logics();
    $verification = $getUsers->getAdvertisements();

    if (!empty($verification['status']) && $verification['status'] == 1) {
        ?>

        <!-- Add this CSS section at the top of your file -->
        <style>
            .ad-location-badge {
                padding: 6px 12px;
                border-radius: 15px;
                font-size: 0.85rem;
                font-weight: 500;
                display: inline-block;
                white-space: nowrap;
                min-width: 120px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .carousel-ad { 
                background-color: #FFF3E0; 
                color: #E65100;
                border: 1px solid #FFB74D;
            }
            .hero-top-1 { 
                background-color: #E8F5E9; 
                color: #2E7D32;
                border: 1px solid #81C784;
            }
            .hero-top-2 { 
                background-color: #E3F2FD; 
                color: #1565C0;
                border: 1px solid #64B5F6;
            }
            .bottom-1 { 
                background-color: #F3E5F5; 
                color: #6A1B9A;
                border: 1px solid #BA68C8;
            }
            .bottom-2 { 
                background-color: #FFEBEE; 
                color: #C62828;
                border: 1px solid #E57373;
            }

            .ad-img-preview {
                width: 100px;
                height: 60px;
                object-fit: cover;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                transition: transform 0.2s;
            }

            .ad-img-preview:hover {
                transform: scale(1.8);
                z-index: 1000;
            }

            .status-badge {
                min-width: 90px;
            }

            .action-icons a {
                padding: 5px;
                transition: transform 0.2s;
            }

            .action-icons a:hover {
                transform: scale(1.2);
            }

            .table thead tr {
                background-color: #f8f9fa;
            }

            .table td {
                vertical-align: middle;
                min-width: 50px; /* Minimum width for all cells */
            }
            
            /* Specific width for location column */
            .table td:nth-child(6) {
                min-width: 140px;
                white-space: nowrap;
            }
        </style>

        <!--  Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-12">
                        <div class="card-body d-flex justify-content-between">
                            <h5 class="card-title text-primary">View All Advertisements</h5>
                            <a href="./addbannerads" class="btn btn-sm btn-primary">Add Banner Advertisement</a>
                            
                        </div>
                        </div>
                    
                    </div>
                    </div>
                </div>
                </div>



            <div class="row">
                <div class="col-lg-12 mb-4 order-0">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-sm-12">
                                <div class="card-body" style="overflow-x: scroll;">
                                    
                                    <br>
                                    <!-- Replace the existing table with this enhanced version -->
                                    <table id="example" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Preview</th>
                                                <th>Theme</th>
                                                <th>URL</th>
                                                <th>Location</th>
                                                <th>Created</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php for ($i = 0; $i < $verification['count']; $i++): 
                                            // Define location badge class and text
                                            $locationClass = '';
                                            $locationText = '';
                                            switch($verification['location'][$i]) {
                                                case '0':
                                                    $locationClass = 'carousel-ad';
                                                    $locationText = 'Hero Carousel';
                                                    break;
                                                case '1':
                                                    $locationClass = 'hero-top-1';
                                                    $locationText = 'Hero Top 1';
                                                    break;
                                                case '2':
                                                    $locationClass = 'hero-top-2';
                                                    $locationText = 'Hero Top 2';
                                                    break;
                                                case '3':
                                                    $locationClass = 'bottom-1';
                                                    $locationText = 'Bottom 1';
                                                    break;
                                                case '4':
                                                    $locationClass = 'bottom-2';
                                                    $locationText = 'Bottom 2';
                                                    break;
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo $i + 1; ?></td>
                                                <td><?php echo $verification['name'][$i]; ?></td>
                                                <td><img src="./advertisements/<?php echo $verification['image'][$i]; ?>" class="ad-img-preview" alt=""></td>
                                                <td><?php echo $verification['description'][$i]; ?></td>
                                                <td><?php echo $verification['url'][$i]; ?></td>
                                                <td><span class="ad-location-badge <?php echo $locationClass; ?>"><?php echo $locationText; ?></span></td>
                                                <td><?php echo $verification['created_at'][$i]; ?></td>
                                                <td>
                                                    <a href="manage-status?update_record_id=<?php echo $verification['id'][$i]; ?>&update_table_name=advertisements&statusval=<?php if($verification['statusval'][$i]==1){ echo "2"; }else{ echo "1"; } ?>&url=getAdvertisements" onclick="return confirm('Are you sure to Update?')" class="btn btn-sm btn-outline-<?php if($verification['statusval'][$i]==1){ echo "success"; }else{ echo "danger"; } ?> rounded-pill status-badge"> <?php if($verification['statusval'][$i]==1){ echo "Active"; }else{ echo "InActive"; } ?></a>
                                                </td>
                                                <td class="action-icons">
                                                    <a href="editAdvertisement?id=<?php echo $verification['id'][$i]; ?>"><i class="menu-icon tf-icons mx-2 bx bx-edit"></i></a>
                                                    <a href="manage-status?delete_record_id=<?php echo $verification['id'][$i]; ?>&delete_table_name=advertisements&url=getAdvertisements" onclick="return confirm('Are you sure to Delete?')"><i class="menu-icon tf-icons bx bx-trash text-danger mx-2"></i></a>
                                                </td>
                                            </tr>
                                            <?php endfor; ?>
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
<script>
    new DataTable('#example');
</script>