<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$eid = $_GET['editid'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Update Transport Structure Details</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />

    <!-- Optional CDN Bootstrap for Modal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Update Transport Structure Details:</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php 
                                    if (isset($_SESSION['message'])) {
                                        echo '<div style="color: green;">' . $_SESSION['message'] . '</div>';
                                        unset($_SESSION['message']);
                                    }

                                    $sql = "SELECT * FROM transportstructure WHERE id = :eid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                    ?>

                                    <form method="post" enctype="multipart/form-data" action="manage-transportstructure.php">
                                        <input type="hidden" name="id" value="<?php echo $row->id; ?>">
                                        
                                        <table class="table">
                                            <tr>
                                                <td><label for="academicyear">Academic Year:</label></td>
                                                <td><input type="text" class="form-control" name="academicyear" id="academicyear" value="<?php echo $row->academicyear; ?>" readonly></td>
                                            </tr>
                                            <tr>
                                                <td><label for="stagename">Stage:</label></td>
                                                <td><input type="text" class="form-control" name="stagename" id="stagename" value="<?php echo $row->stagename; ?>" readonly></td>
                                            </tr>
                                            <tr>
                                                <td><label for="firsttermcharge">First Term Charge:</label></td>
                                                <td><input type="number" class="form-control" name="firsttermcharge" id="firsttermcharge" value="<?php echo $row->firsttermcharge; ?>" step="0.01"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="secondtermcharge">Second Term Charge:</label></td>
                                                <td><input type="number" class="form-control" name="secondtermcharge" id="secondtermcharge" value="<?php echo $row->secondtermcharge; ?>" step="0.01"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="thirdtermcharge">Third Term Charge:</label></td>
                                                <td><input type="number" class="form-control" name="thirdtermcharge" id="thirdtermcharge" value="<?php echo $row->thirdtermcharge; ?>" step="0.01"></td>
                                            </tr>
                                        </table>

                                        <div style="text-align: center;">
                                            <button type="submit" name="submit_update" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>

                                    <?php 
                                        }
                                    }
                                    ?> 
                                </div>
                            </div>
                        </div>
                    </div> <!-- End Panel -->
                </div>
            </div>
        </div> <!-- End page-wrapper -->
    </div> <!-- End wrapper -->

    <!-- Saving Modal -->
    <div class="modal fade" id="savingModal" tabindex="-1" role="dialog" aria-labelledby="savingModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                    <h4 class="mt-3">Saving record...</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>

    <!-- External Bootstrap CDN Support -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>

  <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Fix: Allow form to submit after showing modal
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault(); // prevent default submission
        var form = this;
        $('#savingModal').modal('show');

        // Delay then submit form programmatically
        setTimeout(function () {
            form.submit(); // resumes actual submission
        }, 3000);
    });
</script>

</body>
</html>
