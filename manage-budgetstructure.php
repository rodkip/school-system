<?php
session_start();
include('includes/dbconnection.php');

// Redirect unauthorized users
if (empty($_SESSION['cpmsaid'])) {
    header('location:logout.php');
    exit();
}
$financialyear = date("Y");
$mess = "";
$messagestate = '';

// Post a budget entry
if (isset($_POST['submit_budget'])) {
    try {
        $financialyear = $_POST['financialyear'];
        $votehead = $_POST['votehead'];
        $allocated_amount = $_POST['allocated_amount'];
        $username = $_POST['username'];

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for duplicate entry
        $sql = "SELECT id FROM budget WHERE financialyear = :financialyear AND votehead = :votehead";
        $query = $dbh->prepare($sql);
        $query->bindParam(':financialyear', $financialyear, PDO::PARAM_INT);
        $query->bindParam(':votehead', $votehead, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Duplicate record detected. Entry not saved.";
        } else {
            $sql = "INSERT INTO budget 
                    (financialyear, votehead, allocated_amount, username) 
                    VALUES 
                    (:financialyear, :votehead, :allocated_amount, :username)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':financialyear', $financialyear, PDO::PARAM_INT);
            $query->bindParam(':votehead', $votehead, PDO::PARAM_STR);
            $query->bindParam(':allocated_amount', $allocated_amount, PDO::PARAM_STR);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Budget entry CREATED successfully.";
        }

    } catch (PDOException $e) {
        error_log($e->getMessage()); // System-level logging
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Database error occurred. Please try again.";
    }

    // Redirect back to management interface
    header("Location: manage-budgetstructure.php");
    exit();
}


// Delete a budget record
if (isset($_GET['delete'])) {
    try {
        $id = intval($_GET['delete']);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM budget WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Budget record DELETED successfully.";

    } catch (PDOException $e) {
        error_log("DELETE ERROR: " . $e->getMessage());
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Failed to delete record. Please try again.";
    }

    header("Location: manage-budgetstructure.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Manage Budget</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>
<body>
<div id="wrapper">
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <div id="page-wrapper">
        <div class="row">
                <div class="col-lg-12">
                    <table>
                        <tr>
                            <td width="100%">
                                <h1 class="page-header">Manage Budget   <i class="fa fa-credit-card" aria-hidden="true"></i></h1>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_budgetentry')): ?>
                                    <?php include('newbudgetpopup.php'); ?>
                                <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;New Budget Entry</a>
                                
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

        <div class="panel panel-primary">
        <div class="panel-heading">
            <i class="fa fa-credit-card" aria-hidden="true"></i> Budget Records
        </div>

            <div class="panel-body">
            <div class="table-responsive" style="overflow-x: auto; width: 100%">
     
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Financial Year</th>
                            <th>Votehead</th>
                            <th>Allocated Amount</th>
                            <th>Entry Date</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "SELECT * FROM budget ORDER BY financialyear ASC, entrydate DESC";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $cnt = 1;

                        if ($query->rowCount() > 0) {
                            foreach ($results as $row) {
                        ?>
                            <tr>
                                <td><?php echo htmlentities($cnt); ?></td>
                                <td><?php echo htmlentities($row->financialyear); ?></td>
                                <td><?php echo htmlentities($row->votehead); ?></td>
                                <td><?php echo number_format($row->allocated_amount, 2); ?></td>
                                <td><?php echo htmlentities($row->entrydate); ?></td>
                                <td><?php echo htmlentities($row->username); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                            Action <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right">
                                            <?php if (has_permission($accounttype, 'edit_budgetentry')): ?>
                                                <li>
                                                    <a href="edit-budgetentry.php?editid=<?php echo htmlentities($row->id); ?>">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </a>
                                                </li>
                                           
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="?delete=<?php echo htmlentities($row->id); ?>"
                                                       onclick="return confirm('Are you sure you want to delete this record?');">
                                                        <i class="fa fa-trash-o"></i> Delete
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php
                                $cnt++;
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Scripts -->
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
</body>
</html>
