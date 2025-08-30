<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Redirect if user is not logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    // Initialize variables for message handling
    $messagestate = false;
    $message = "";
    $currentacademicyear = date("Y");

    // Handle form submission
    if (isset($_POST['submit'])) {
        try {
            // Collect form data
            $stageName = $_POST['stagename'];
            $firstTermCharge = $_POST['firsttermcharge'];
            $secondTermCharge = $_POST['secondtermcharge'];
            $thirdTermCharge = $_POST['thirdtermcharge'];
            $academicYear = $_POST['academicyear'];

            // Generate stageFullName
            $stageFullName = $academicYear . $stageName;

            // Set PDO error handling mode
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepare SQL query using prepared statements
            $sql = "INSERT INTO transportstructure (stagename, firsttermcharge, secondtermcharge, thirdtermcharge, academicyear, stagefullname) 
                    VALUES (:stagename, :firsttermcharge, :secondtermcharge, :thirdtermcharge, :academicyear, :stagefullname)";

            $stmt = $dbh->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':stagename', $stageName);
            $stmt->bindParam(':firsttermcharge', $firstTermCharge);
            $stmt->bindParam(':secondtermcharge', $secondTermCharge);
            $stmt->bindParam(':thirdtermcharge', $thirdTermCharge);
            $stmt->bindParam(':academicyear', $academicYear);
            $stmt->bindParam(':stagefullname', $stageFullName);

            // Execute the statement
            $stmt->execute();

            // Set success message in session
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Transport Structure CREATED successfully.";
            

        } catch (PDOException $e) {
            // Error handling
            echo "Error: " . $e->getMessage();
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Error creating Transport Structure Records.";
        }
    }

    // Handle form submission for update
    if (isset($_POST['submit_update'])) {
        try {
            // Collect form data
            $id = $_POST['id'];
            $stageName = $_POST['stagename'];
            $firstTermCharge = $_POST['firsttermcharge'];
            $secondTermCharge = $_POST['secondtermcharge'];
            $thirdTermCharge = $_POST['thirdtermcharge'];
            $academicYear = $_POST['academicyear'];
            $stageFullName = $academicYear . $stageName;
    
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Prepare SQL
            $sql = "UPDATE transportstructure 
                    SET stagename = :stagename, 
                        firsttermcharge = :firsttermcharge, 
                        secondtermcharge = :secondtermcharge, 
                        thirdtermcharge = :thirdtermcharge, 
                        academicyear = :academicyear, 
                        stagefullname = :stagefullname 
                    WHERE id = :id";
    
            $stmt = $dbh->prepare($sql);
    
            // Bind parameters
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':stagename', $stageName);
            $stmt->bindParam(':firsttermcharge', $firstTermCharge);
            $stmt->bindParam(':secondtermcharge', $secondTermCharge);
            $stmt->bindParam(':thirdtermcharge', $thirdTermCharge);
            $stmt->bindParam(':academicyear', $academicYear);
            $stmt->bindParam(':stagefullname', $stageFullName);
    
            // Execute update
            $stmt->execute();
    
            // ✅ Trigger the fee balance updater
            require_once('fee_balance_updater.php');
    
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Transport Structure updated successfully.";
    
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Error updating Transport Structure Records.";
        }
    }
    

    // Handle deletion of records
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];

            // Use prepared statements to prevent SQL injection
            $sql = "DELETE FROM transportstructure WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Records DELETED successfully.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kipmetz-SMS | Transport Structure</title>
    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>

<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h1 class="page-header">
                                    Manage Transport Charges <i class="fa fa-network-wired"></i>
                                </h1>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_transportstructure')): ?>
                                    <?php include('newtransportstructurepopup.php'); ?>
                                    <a href="#myModal" data-toggle="modal" class="btn btn-primary">
                                        <i class="fa fa-plus-circle"></i> New Transport Charge
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="manage-transportstages.php" class="btn btn-success">
                                    <i class="fa fa-map-signs"></i> Manage Transport Stages
                                </a>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <?php include_once('updatemessagepopup.php'); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                    <div class="panel-heading">Transport Fee Structures</div>
                    <div class="panel-body">
                    <div class="table-responsive" style="overflow-x: auto; width: 100%">
                    <div id="table-wrapper">
                   
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead >
                            <tr>
                                <th>#</th>
                                <th>Stage</th>
                                <th>Academic Year</th>
                                <th>Stage Full Name</th>
                                <th>1st Term</th>
                                <th>2nd Term</th>
                                <th>3rd Term</th>
                                <th>Total Charge</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            try {
                                $sql = "SELECT * FROM transportstructure ORDER BY id DESC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                $cnt = 1;
                                if ($query->rowCount() > 0) {
                                foreach ($results as $row): ?>
                                    <tr>
                                    <td><?= $cnt; ?></td>
                                    <td><?= htmlentities($row->stagename); ?></td>
                                    <td><?= htmlentities($row->academicyear); ?></td>
                                    <td><?= htmlentities($row->stagefullname); ?></td>
                                    <td><?= number_format($row->firsttermcharge); ?></td>
                                    <td><?= number_format($row->secondtermcharge); ?></td>
                                    <td><?= number_format($row->thirdtermcharge); ?></td>
                                    <td><strong><?= number_format($row->firsttermcharge + $row->secondtermcharge + $row->thirdtermcharge); ?></strong></td>
                                    <td>
                                        <?php if (has_permission($accounttype, 'edit_transportstructure')): ?>
                                        <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                            Action <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right" role="menu">
                                            <li>
                                            <a href="edit-transportstructure.php?editid=<?= htmlentities($row->id); ?>">
                                                <i class="fa fa-pencil"></i> Edit
                                            </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                            <a href="manage-transportstructure.php?delete=<?= htmlentities($row->id); ?>" onclick="return confirm('Are you sure you want to delete this record?');">
                                                <i class="fa fa-trash-o"></i> Delete
                                            </a>
                                            </li>
                                        </ul>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">No Access</span>
                                        <?php endif; ?>
                                    </td>
                                    </tr>
                                <?php
                                $cnt++;
                                endforeach;
                                }
                            } catch (PDOException $e) {
                                error_log("Transport Structure Query Error: " . $e->getMessage());
                            }
                            ?>
                            </tbody>
                        </table>
                        </div> <!-- /.table-responsive -->
                    </div> <!-- /.panel-body -->
                    </div> <!-- /.panel -->
                </div> <!-- /.col -->
            </div> <!-- /.row -->

        </div>
    </div>
    <script>
    // Simulate table loading
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        document.getElementById("spinner").style.display = "none"; // Hide spinner
        document.getElementById("table-container").style.display = "block"; // Show table
      }, 3000); // Adjust delay as per actual loading time
    });
  </script>
    <!-- Core JavaScript - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        function admnoAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "checkadmnoforreg.php",
                data: 'studentadmno=' + $("#studentadmno").val(),
                type: "POST",
                success: function (data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function () {}
            });
        }

        <?php
        if ($messagestate == 'added' || $messagestate == 'deleted') {
            echo 'document.getElementById("popup").style.visibility = "visible";
                  window.setTimeout(function() {
                      document.getElementById("popup").style.visibility = "hidden";
                  }, 5000);';
        }
        ?>
    </script>

   
</body>

</html>
