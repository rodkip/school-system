<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $messagestate = false;
    $mess = "";

    if (isset($_POST['submit'])) {
        try {
            $gradefullname = $_POST['gradefullname'];
            $entryterm = $_POST['entryterm'];
            $boarding = $_POST['boarding'];
            $firsttermfee = $_POST['firsttermfee'];
            $secondtermfee = $_POST['secondtermfee'];
            $thirdtermfee = $_POST['thirdtermfee'];
            $othersfee = $_POST['othersfee'];
            $totalfee = $firsttermfee + $secondtermfee + $thirdtermfee + $othersfee;
            $feestructurename = $entryterm . $gradefullname . $boarding;

            // Check if the record exists before saving
            $sql = "SELECT * FROM feestructure WHERE feestructurename = '$feestructurename'";
            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            if ($query->rowCount() > 0) {
                $_SESSION['messagestate'] = 'deleted';
                $_SESSION['mess'] = "NOT saved - DUPLICATE Record";
            } else {
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = "INSERT INTO feestructure (gradefullname, entryterm, boarding, firsttermfee, secondtermfee, thirdtermfee, othersfee, totalfee, feestructurename) 
                        VALUES ('$gradefullname', '$entryterm', '$boarding', '$firsttermfee', '$secondtermfee', '$thirdtermfee', '$othersfee', '$totalfee', '$feestructurename')";
                $dbh->exec($sql);

                $_SESSION['messagestate'] = 'added';
                $_SESSION['mess'] = "New Fee Structure record created...";
            }
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }

// Updating fee structure
if (isset($_POST['submit_update'])) {
    try {
        $id = $_POST['id'];
        $gradefullname = $_POST['gradefullname'];
        $entryterm = $_POST['entryterm'];
        $boarding = $_POST['boarding'];
        $firsttermfee = $_POST['firsttermfee'];
        $secondtermfee = $_POST['secondtermfee'];
        $thirdtermfee = $_POST['thirdtermfee'];
        $othersfee = $_POST['othersfee'];
        $totalfee = $firsttermfee + $secondtermfee + $thirdtermfee + $othersfee;
        $feestructurename = $entryterm . $gradefullname . $boarding;

        // Use prepared statement
        $sql = "UPDATE feestructure 
                SET gradefullname = :gradefullname, 
                    entryterm = :entryterm, 
                    boarding = :boarding, 
                    firsttermfee = :firsttermfee, 
                    secondtermfee = :secondtermfee, 
                    thirdtermfee = :thirdtermfee, 
                    othersfee = :othersfee, 
                    totalfee = :totalfee, 
                    feestructurename = :feestructurename 
                WHERE id = :id";

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':gradefullname', $gradefullname);
        $stmt->bindParam(':entryterm', $entryterm);
        $stmt->bindParam(':boarding', $boarding);
        $stmt->bindParam(':firsttermfee', $firsttermfee);
        $stmt->bindParam(':secondtermfee', $secondtermfee);
        $stmt->bindParam(':thirdtermfee', $thirdtermfee);
        $stmt->bindParam(':othersfee', $othersfee);
        $stmt->bindParam(':totalfee', $totalfee);
        $stmt->bindParam(':feestructurename', $feestructurename);
        $stmt->execute();

        // ✅ Trigger updater after successful update
        require_once('fee_balance_updater.php');

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Fee Structure record UPDATED...";
        $mess = 'Record Updated';

        // Reset variables
        $gradefullname = '';
        $entryterm = '';
        $boarding = '';
        $firsttermfee = '';
        $secondtermfee = '';
        $thirdtermfee = '';
        $othersfee = '';
        $totalfee = '';
        $eid = '';

    } catch (PDOException $e) {
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Error updating Fee Structure.";
    }
}

if (isset($_POST['update_feestructurepervotehead'])) {
    // Debug first
    echo "<pre>"; print_r($_POST); echo "</pre>"; 
    
    try {
        $feestructurename = $_POST['feestructurename'];
        $voteheadData = $_POST['votehead'] ?? []; // Null coalescing for safety

        if (empty($voteheadData)) {
            throw new Exception("No votehead data received");
        }

        $dbh->beginTransaction();

        // First, clear existing entries for this fee structure
        $deleteSql = "DELETE FROM feestructurevoteheadcharges WHERE feestructurename = ?";
        $dbh->prepare($deleteSql)->execute([$feestructurename]);

        // Then insert new ones
        $insertSql = "INSERT INTO feestructurevoteheadcharges 
                     (feestructurename, votehead_id, firstterm, secondterm, thirdterm, total) 
                     VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $dbh->prepare($insertSql);

        foreach ($voteheadData as $voteheadName => $terms) {
            // Get votehead ID
            $voteheadIdQuery = $dbh->prepare("SELECT id FROM voteheads WHERE votehead = ? LIMIT 1");
            $voteheadIdQuery->execute([$voteheadName]);
            $voteheadId = $voteheadIdQuery->fetchColumn();

            if (!$voteheadId) {
                throw new Exception("Invalid votehead: " . htmlspecialchars($voteheadName));
            }

            // Calculate total
            $total = ($terms['first'] ?? 0) + ($terms['second'] ?? 0) + ($terms['third'] ?? 0);

            // Insert record
            $stmt->execute([
                $feestructurename,
                $voteheadId,
                $terms['first'] ?? 0,
                $terms['second'] ?? 0,
                $terms['third'] ?? 0,
                $total
            ]);
        }

        $dbh->commit();
        $_SESSION['success'] = "Fee structure updated successfully";
    } catch (Exception $e) {
        $dbh->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: manage-feestructure.php");
    exit();
}
    // Deletion
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];

            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "DELETE FROM feestructure WHERE id=$id";
            $dbh->exec($sql);

            $messagestate = 'deleted';
            $mess = "Record Deleted....";
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }
  
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS|Add Student</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <!-- Add in <head> -->


</head>
<body>
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php'); ?>
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php'); ?>
      
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Manage Fee STRUCTURES <i class="fa fa-user-secret"></i></h2>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_feestructure')): ?>
                                <?php include('newfeestructurepopup.php'); ?>
                                <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa  fa-plus-circle"></i>&nbsp;&nbsp;New Fee Structure</a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <!-- Votehead Distribution Modal -->
            <div class="modal fade" id="voteheadModal" tabindex="-1" role="dialog" aria-labelledby="voteheadModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="voteheadModalLabel">Votehead Distribution</h5>
                                <h3 style="color: blue;">
                                    <strong><span id="structureNameDisplay"></span></strong>
                                </h3>
                            </div>
                            <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered votehead-modal-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Votehead</th>
                                            <th>1st Term</th>
                                            <th>2nd Term</th>
                                            <th>3rd Term</th>
                                            <th style="font-weight: bold;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="voteheadModalBody">
                                        <!-- AJAX-loaded rows go here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">Manage Fee Structures</div>
                <div class="panel-body">
                <div class="table-responsive" style="overflow-x: auto; width: 100%">
                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
    <thead>
    <tr>
        <th>#</th>
        <th>Grade</th>
        <th>Entry Term</th>
        <th>Boarding?</th>
        <th>1st Term Fee</th>
        <th>2nd Term Fee</th>
        <th>3rd Term Fee</th>
        <th>Other Fees</th>
        <th>Total Fee</th>
        <th>Structure Name</th>      
        <th>Voteheads Present?</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT * FROM feestructure ORDER BY gradefullname DESC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $cnt = 1;

    if ($query->rowCount() > 0):
        foreach ($results as $row):
            // Check if voteheads exist for the given feestructurename
            $checkSql = "SELECT COUNT(*) FROM feestructurevoteheadcharges WHERE feestructurename = :fsname";
            $checkStmt = $dbh->prepare($checkSql);
            $checkStmt->bindParam(':fsname', $row->feestructurename, PDO::PARAM_STR);
            $checkStmt->execute();
            $hasVoteheads = $checkStmt->fetchColumn() > 0;
    ?>
        <tr>
            <td><?= htmlentities($cnt); ?></td>
            <td><?= htmlentities($row->gradefullname); ?></td>
            <td><?= htmlentities($row->entryterm); ?></td>
            <td><?= htmlentities($row->boarding); ?></td>
            <td><?= number_format($row->firsttermfee); ?></td>
            <td><?= number_format($row->secondtermfee); ?></td>
            <td><?= number_format($row->thirdtermfee); ?></td>
            <td><?= number_format($row->othersfee); ?></td>
            <td><strong><?= number_format($row->totalfee); ?></strong></td>
            <td><?= htmlentities($row->feestructurename); ?></td>
            <td>
                <?php if ($hasVoteheads): ?>
                    <span class="label label-success">Yes</span>
                <?php else: ?>
                    <span class="label label-danger">No</span>
                <?php endif; ?>
                <a href="javascript:void(0);"
                   class="btn btn-info btn-sm view-votehead"
                   data-feestructurename="<?= htmlentities($row->feestructurename); ?>"
                   data-toggle="modal"
                   data-target="#voteheadModal">
                    <i class="fa fa-eye" aria-hidden="true"></i> View Voteheads
                </a>
            </td>
            
            <td>
                <div class="btn-group" style="background-color: azure;">
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cogs"></i> Action <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right animated fadeIn" role="menu">
                        <?php if (has_permission($accounttype, 'edit_feestructure')): ?>
                            <li>
                                <a href="edit-feestructure.php?editid=<?= htmlentities($row->id); ?>">
                                    <i class="fa fa-pencil text-primary"></i> Edit Structure
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (has_permission($accounttype, 'edit_feestructure')): ?>
                            <li>
                                <a href="edit-feestructurepervoteaheads.php?editid=<?= htmlentities($row->id); ?>">
                                    <i class="fa fa-list-alt text-info"></i> Edit PerVoteheads
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (has_permission($accounttype, 'delete_feestructure')): ?>
                            <li class="divider"></li>
                            <li>
                                <a href="manage-feestructure.php?delete=<?= htmlentities($row->id); ?>"
                                   onclick="return confirm('Are you sure you want to delete this fee structure?');">
                                    <i class="fa fa-trash-o text-danger"></i> Delete
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </td>
        </tr>
    <?php
        $cnt++;
        endforeach;
    endif;
    ?>
    </tbody>
</table>

                    </div>
                </div>
                </div>

        </div>
    </div>
    

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
    </script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
 <script>
    // Simulate table loading
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        document.getElementById("spinner").style.display = "none"; // Hide spinner
        document.getElementById("table-container").style.display = "block"; // Show table
      }, 3000); // Adjust delay as per actual loading time
    });
  </script>
<script>
$(document).on('click', '.view-votehead', function () {
    const feestructurename = $(this).data('feestructurename');

    $.ajax({
        url: 'get_votehead_details.php',
        type: 'POST',
        data: { feestructurename: feestructurename },
        success: function (data) {
            $('#voteheadModalBody').html(data);
        },
        error: function () {
            $('#voteheadModalBody').html('<tr><td colspan="5">Failed to load data.</td></tr>');
        }
    });
});
$('#voteheadModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var structureName = button.data('feestructurename'); // Extract info from data-* attributes
    $('#structureNameDisplay').text(structureName); // Update the modal header
});

</script>

    <?php
    if ($messagestate == 'added' || $messagestate == 'deleted') {
        echo '<script type="text/javascript">
            function hideMsg() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            document.getElementById("popup").style.visibility = "visible";
            window.setTimeout("hideMsg()", 5000);
        </script>';
    }
    ?>
    
</body>
</html>
