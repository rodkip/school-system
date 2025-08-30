<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Redirect if user is not logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Initialize variables
$mess = "";
$update = false;
$id = $bankname = $accountno = $accountname = $accountdescription = '';

// Add new record
if (isset($_POST['submit'])) {
    try {
        $bankname = trim($_POST['bankname']);
        $accountno = trim($_POST['accountno']);
        $accountname = trim($_POST['accountname']);
        $accountdescription = trim($_POST['accountdescription']);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the bankname and account number combination already exists
        $check_sql = "SELECT COUNT(*) FROM bankdetails WHERE bankname = :bankname AND accountno = :accountno";
        $check_stmt = $dbh->prepare($check_sql);
        $check_stmt->bindParam(':bankname', $bankname, PDO::PARAM_STR);
        $check_stmt->bindParam(':accountno', $accountno, PDO::PARAM_STR);
        $check_stmt->execute();
        $exists = $check_stmt->fetchColumn();

        if ($exists > 0) {
            $_SESSION['messagestate'] = 'error';
            $_SESSION['mess'] = "This bank and account number combination already exists!";
        } else {
            // Insert the new record
            $sql = "INSERT INTO bankdetails (bankname, accountno, accountname, accountdescription) 
                    VALUES (:bankname, :accountno, :accountname, :accountdescription)";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':bankname' => $bankname,
                ':accountno' => $accountno,
                ':accountname' => $accountname,
                ':accountdescription' => $accountdescription
            ]);

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Record created successfully.";

            // Clear input variables
            $bankname = $accountno = $accountname = $accountdescription = '';
        }
        
        // Redirect to avoid re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "An error occurred while processing your request.";
        
        // Redirect to avoid re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Delete a record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM bankdetails WHERE id=:id";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([':id' => $id]);

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Record deleted successfully.";
        
        // Redirect to avoid re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "An error occurred while deleting the record.";
        
        // Redirect to avoid re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Update a record
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $bankname = trim($_POST['bankname']);
    $accountno = trim($_POST['accountno']);
    $accountname = trim($_POST['accountname']);
    $accountdescription = trim($_POST['accountdescription']);

    try {
        $sql = "UPDATE bankdetails SET bankname=:bankname, accountno=:accountno, 
                accountname=:accountname, accountdescription=:accountdescription WHERE id=:id";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':bankname' => $bankname,
            ':accountno' => $accountno,
            ':accountname' => $accountname,
            ':accountdescription' => $accountdescription,
            ':id' => $id
        ]);

        $_SESSION['messagestate'] = 'updated';
        $_SESSION['mess'] = "Record updated successfully.";
        
        // Clear input variables
        $bankname = $accountno = $accountname = $accountdescription = '';
        
        // Redirect to avoid re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "An error occurred while updating the record.";
        
        // Redirect to avoid re-submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | Payment Accounts</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>
<body onload="startTime()">
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
                            <h2 class="page-header">Manage Payment Accounts <i class="fa fa-bank"></i></h2>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_paymentaccount')): ?>
                                    <?php include('newpaymentaccountpopup.php'); ?>
                                    <a href="#newpaymentaccount" data-toggle="modal" class="btn btn-primary">
                                        <i class="fa fa-plus-circle"></i> New Payment Account
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="panel panel-primary">
                <div class="panel-heading">Manage Bank Accounts</div>
                <div class="panel-body">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead >
                        <tr>
                            <th>#</th>
                            <th>Bank</th>
                            <th>Account No</th>
                            <th>Account Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $sql = "SELECT * FROM bankdetails ORDER BY bankname ASC";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0):
                                foreach ($results as $row):
                        ?>
                        <tr>
                            <td><?= htmlentities($cnt); ?></td>
                            <td><?= htmlentities($row->bankname); ?></td>
                            <td><?= htmlentities($row->accountno); ?></td>
                            <td><?= htmlentities($row->accountname); ?></td>
                            <td><?= htmlentities($row->accountdescription); ?></td>
                            <td>
                            <?php if (has_permission($accounttype, 'edit_paymentaccount') || has_permission($accounttype, 'delete_paymentaccount')): ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                <?php if (has_permission($accounttype, 'edit_paymentaccount')): ?>
                                <li>
                                    <a href="manage-bankdetails.php?edit=<?= htmlentities($row->id); ?>">
                                    <i class="fa fa-pencil"></i> Edit
                                    </a>
                                </li>
                                <?php endif; ?>
                                <?php if (has_permission($accounttype, 'delete_paymentaccount')): ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="manage-bankdetails.php?delete=<?= htmlentities($row->id); ?>"
                                    onclick="return confirm('Are you sure you want to delete this bank account?');">
                                    <i class="fa fa-trash-o"></i> Delete
                                    </a>
                                </li>
                                <?php endif; ?>
                                </ul>
                            </div>
                            <?php else: ?>
                                <span class="text-muted">No Actions Available</span>
                            <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                                $cnt++;
                                endforeach;
                            endif;
                        } catch (PDOException $e) {
                            error_log("Database error in bankdetails query: " . $e->getMessage());
                        }
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

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Show and hide status messages
        <?php if (isset($_SESSION['messagestate']) && ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted')): ?>
            function hideMsg() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            document.getElementById("popup").style.visibility = "visible";
            window.setTimeout("hideMsg()", 5000);
        <?php endif; ?>
    </script>
</body>
</html>