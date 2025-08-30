<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check authentication
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Initialize message variables
$messagestate = '';
$mess = "";

// Handle Fee Treatment operations
if (isset($_POST['submitfeetreatment'])) {
    handleFeeTreatmentSubmission($dbh);
} elseif (isset($_POST['submitchildtreatment'])) {
    handleChildTreatmentSubmission($dbh);
} elseif (isset($_POST['update_feetreatmentrate'])) {
    updateFeeTreatment($dbh);
} elseif (isset($_POST['update_childtreatmentrate'])) {
    updateChildTreatment($dbh);
} elseif (isset($_GET['delete'])) {
    deleteTreatment($dbh);
}

// Function to handle fee treatment submission
function handleFeeTreatmentSubmission($dbh) {
    $treatment = trim($_POST['treatment']);
    $feetreatmentrate = trim($_POST['feetreatmentrate']);
    $transporttreatmentrate = trim($_POST['transporttreatmentrate']);
    
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check for duplicate
        $check_sql = "SELECT COUNT(*) FROM feetreatmentrates WHERE treatment = :treatment";
        $stmt = $dbh->prepare($check_sql);
        $stmt->bindParam(':treatment', $treatment, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            setMessage('deleted', "Treatment type already exists!");
        } else {
            $insert_sql = "INSERT INTO feetreatmentrates (treatment, feetreatmentrate, transporttreatmentrate) 
                          VALUES (:treatment, :feetreatmentrate, :transporttreatmentrate)";
            $stmt = $dbh->prepare($insert_sql);
            $stmt->execute([
                ':treatment' => $treatment,
                ':feetreatmentrate' => $feetreatmentrate,
                ':transporttreatmentrate' => $transporttreatmentrate
            ]);
            
            setMessage('added', "Fee/Transport Treatment record created successfully.");
        }
    } catch (PDOException $e) {
        error_log("Fee Treatment Submission Error: " . $e->getMessage());
        setMessage('deleted', "Error creating treatment record.");
    }
}

// Function to handle child treatment submission
function handleChildTreatmentSubmission($dbh) {
    $childtreatment = trim($_POST['childtreatment']);
    $feetreatmentrate = trim($_POST['feetreatmentrate']);
    $transporttreatmentrate = trim($_POST['transporttreatmentrate']);
    
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check for duplicate
        $check_sql = "SELECT COUNT(*) FROM childtreatmentrates WHERE childtreatment = :childtreatment";
        $stmt = $dbh->prepare($check_sql);
        $stmt->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            setMessage('deleted', "Treatment type already exists!");
        } else {
            $insert_sql = "INSERT INTO childtreatmentrates (childtreatment, feetreatmentrate, transporttreatmentrate) 
                          VALUES (:childtreatment, :feetreatmentrate, :transporttreatmentrate)";
            $stmt = $dbh->prepare($insert_sql);
            $stmt->execute([
                ':childtreatment' => $childtreatment,
                ':feetreatmentrate' => $feetreatmentrate,
                ':transporttreatmentrate' => $transporttreatmentrate
            ]);
            
            setMessage('added', "Child-Treatment record created successfully.");
        }
    } catch (PDOException $e) {
        error_log("Child Treatment Submission Error: " . $e->getMessage());
        setMessage('deleted', "Error creating child treatment record.");
    }
}

// Function to update fee treatment
function updateFeeTreatment($dbh) {
    $id = intval($_POST['id']);
    $treatment = trim($_POST['treatment']);
    $feetreatmentrate = trim($_POST['feetreatmentrate']);
    $transporttreatmentrate = trim($_POST['transporttreatmentrate']);
    
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check for duplicate excluding current record
        $check_sql = "SELECT COUNT(*) FROM feetreatmentrates WHERE treatment = :treatment AND id != :id";
        $stmt = $dbh->prepare($check_sql);
        $stmt->execute([':treatment' => $treatment, ':id' => $id]);
        
        if ($stmt->fetchColumn() > 0) {
            setMessage('deleted', "Another record with the same treatment name already exists!");
        } else {
            $update_sql = "UPDATE feetreatmentrates SET treatment = :treatment, 
                          feetreatmentrate = :feetreatmentrate, 
                          transporttreatmentrate = :transporttreatmentrate 
                          WHERE id = :id";
            $stmt = $dbh->prepare($update_sql);
            $stmt->execute([
                ':treatment' => $treatment,
                ':feetreatmentrate' => $feetreatmentrate,
                ':transporttreatmentrate' => $transporttreatmentrate,
                ':id' => $id
            ]);
            
            setMessage('added', "Treatment rate updated successfully.");
        }
    } catch (PDOException $e) {
        error_log("Update Treatment Rate Error: " . $e->getMessage());
        setMessage('deleted', "Failed to update treatment rate.");
    }
}

// Function to update child treatment
function updateChildTreatment($dbh) {
    $id = intval($_POST['id']);
    $childtreatment = trim($_POST['childtreatment']);
    $feetreatmentrate = trim($_POST['feetreatmentrate']);
    $transporttreatmentrate = trim($_POST['transporttreatmentrate']);
    
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check for duplicate excluding current record
        $check_sql = "SELECT COUNT(*) FROM childtreatmentrates WHERE childtreatment = :childtreatment AND id != :id";
        $stmt = $dbh->prepare($check_sql);
        $stmt->execute([':childtreatment' => $childtreatment, ':id' => $id]);
        
        if ($stmt->fetchColumn() > 0) {
            setMessage('deleted', "Another record with the same treatment name already exists!");
        } else {
            $update_sql = "UPDATE childtreatmentrates SET childtreatment = :childtreatment, 
                          feetreatmentrate = :feetreatmentrate, 
                          transporttreatmentrate = :transporttreatmentrate 
                          WHERE id = :id";
            $stmt = $dbh->prepare($update_sql);
            $stmt->execute([
                ':childtreatment' => $childtreatment,
                ':feetreatmentrate' => $feetreatmentrate,
                ':transporttreatmentrate' => $transporttreatmentrate,
                ':id' => $id
            ]);
            
            setMessage('added', "Child Treatment Record updated successfully.");
        }
    } catch (PDOException $e) {
        error_log("Update Child-Treatment Rate Error: " . $e->getMessage());
        setMessage('deleted', "Failed to update treatment rate.");
    }
}

// Function to delete treatment
function deleteTreatment($dbh) {
    try {
        $id = intval($_GET['delete']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $delete_sql = "DELETE FROM feetreatmentrates WHERE id = :id";
        $stmt = $dbh->prepare($delete_sql);
        $stmt->execute([':id' => $id]);
        
        setMessage('deleted', "Treatment rate record deleted.");
    } catch (PDOException $e) {
        error_log("Delete Treatment Error: " . $e->getMessage());
        setMessage('deleted', "Failed to delete treatment record.");
    }
}

// Helper function to set session messages
function setMessage($state, $message) {
    $_SESSION['messagestate'] = $state;
    $_SESSION['mess'] = $message;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | Manage Treatment Rates</title>
    
    <!-- CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    
    <style>
        .panel-heading {
            font-weight: bold;
            font-size: 16px;
        }
        .table th {
            background-color: #f5f5f5;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .modal-header {
            background-color: #337ab7;
            color: white;
        }
        .treatment-card {
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .treatment-card .panel-heading {
            border-radius: 5px 5px 0 0;
        }
    </style>
</head>

<body onload="startTime()">
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper">           
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-header clearfix">
                        <h2 class="pull-left"><i class="fa fa-percentage"></i> Manage Treatment Rates</h2>
                        <div class="pull-right action-buttons">
                            <?php include('newfeetreatmentpopup.php'); ?>
                            <a href="#newfeetreatmenthead" data-toggle="modal" class="btn btn-primary">
                                <i class="fa fa-plus-circle"></i> New Fee Treatment
                            </a>
                            
                            <?php include('newchildtreatmentpopup.php'); ?>
                            <a href="#newchildtreatment" data-toggle="modal" class="btn btn-success">
                                <i class="fa fa-plus-circle"></i> New Child Treatment
                            </a>
                            
                            <?php include_once('updatemessagepopup.php'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Fee/Transport Treatment Rates -->
                <div class="col-md-6">
                    <div class="panel panel-primary treatment-card">
                        <div class="panel-heading">
                            <i class="fa fa-money"></i> Fee/Transport Treatment Rates
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="feetreatmentTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>                           
                                            <th>Treatment</th>
                                            <th>Fee Rate</th>
                                            <th>Transport Rate</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            $sql = "SELECT * FROM feetreatmentrates ORDER BY id DESC";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt = 1;

                                            if ($query->rowCount() > 0):
                                                foreach ($results as $row):
                                        ?>
                                        <tr>
                                            <td><?= $cnt; ?></td>
                                            <td><?= htmlentities($row->treatment); ?></td>
                                            <td><?= htmlentities($row->feetreatmentrate); ?></td>
                                            <td><?= htmlentities($row->transporttreatmentrate); ?></td>
                                            <td>
                                                <?php if (has_permission($accounttype, 'edit_votehead')): ?>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                        Action <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu pull-right" role="menu">
                                                        <li>
                                                            <a href="#editFeeModal<?= $row->id ?>" data-toggle="modal">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="manage-treatmentrates.php?delete=<?= $row->id ?>"
                                                               onclick="return confirm('Are you sure you want to delete this Fee Treatment Item?');">
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

                                        <!-- Edit Fee Treatment Modal -->
                                        <div class="modal fade" id="editFeeModal<?= $row->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="post" action="manage-treatmentrates.php">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                            <h4 class="modal-title">Edit Fee Treatment</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                                            
                                                            <div class="form-group">
                                                                <label>Treatment Name</label>
                                                                <input type="text" class="form-control" name="treatment" value="<?= htmlentities($row->treatment) ?>" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Fee Treatment Rate</label>
                                                                <input type="number" class="form-control" name="feetreatmentrate" value="<?= htmlentities($row->feetreatmentrate) ?>" step="0.1" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Transport Treatment Rate</label>
                                                                <input type="number" class="form-control" name="transporttreatmentrate" value="<?= htmlentities($row->transporttreatmentrate) ?>" step="0.1" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="update_feetreatmentrate" class="btn btn-success">Update</button>
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <?php
                                                $cnt++;
                                                endforeach;
                                            endif;
                                        } catch (PDOException $e) {
                                            error_log("Fee Treatment Rates Query Error: " . $e->getMessage());
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Child Treatment Rates -->
                <div class="col-md-6">
                    <div class="panel panel-primary treatment-card">
                        <div class="panel-heading">
                            <i class="fa fa-child"></i> Child Treatment Rates
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="childtreatmentTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>                           
                                            <th>Child Treatment</th>
                                            <th>Fee Rate</th>
                                            <th>Transport Rate</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            $sql = "SELECT * FROM childtreatmentrates ORDER BY id asc";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt = 1;

                                            if ($query->rowCount() > 0):
                                                foreach ($results as $row):
                                        ?>
                                        <tr>
                                            <td><?= $cnt; ?></td>
                                            <td><?= htmlentities($row->childtreatment); ?></td>
                                            <td><?= htmlentities($row->feetreatmentrate); ?></td>
                                            <td><?= htmlentities($row->transporttreatmentrate); ?></td>
                                            <td>
                                                <?php if (has_permission($accounttype, 'edit_votehead')): ?>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                        Action <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu pull-right" role="menu">
                                                        <li>
                                                            <a href="#editChildModal<?= $row->id ?>" data-toggle="modal">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="manage-treatmentrates.php?deletechildtreatment=<?= $row->id ?>"
                                                               onclick="return confirm('Are you sure you want to delete this Child Treatment Item?');">
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

                                        <!-- Edit Child Treatment Modal -->
                                        <div class="modal fade" id="editChildModal<?= $row->id ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="post" action="manage-treatmentrates.php">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                            <h4 class="modal-title">Edit Child Treatment</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row->id ?>">
                                                            
                                                            <div class="form-group">
                                                                <label>Child Treatment Name</label>
                                                                <input type="text" class="form-control" name="childtreatment" value="<?= htmlentities($row->childtreatment) ?>" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Fee Treatment Rate</label>
                                                                <input type="number" class="form-control" name="feetreatmentrate" value="<?= htmlentities($row->feetreatmentrate) ?>" step="0.1" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Transport Treatment Rate</label>
                                                                <input type="number" class="form-control" name="transporttreatmentrate" value="<?= htmlentities($row->transporttreatmentrate) ?>" step="0.1" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="update_childtreatmentrate" class="btn btn-success">Update</button>
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <?php
                                                $cnt++;
                                                endforeach;
                                            endif;
                                        } catch (PDOException $e) {
                                            error_log("Child Treatment Rates Query Error: " . $e->getMessage());
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- /#page-wrapper -->
    </div> <!-- /#wrapper -->

    <!-- JavaScript -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#feetreatmentTable, #childtreatmentTable').DataTable({
                responsive: true,
                "pageLength": 10
            });

            // Handle message popup
            <?php if ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted'): ?>
                function hideMsg() {
                    $("#popup").fadeOut();
                }
                $("#popup").fadeIn();
                setTimeout(hideMsg, 5000);
            <?php endif; ?>

            // Prevent form resubmission on page refresh
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
</body>
</html>