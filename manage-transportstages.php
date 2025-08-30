<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$academicyear = date("Y");
$messagestate = '';
$mess = "";

if (isset($_POST['submit'])) {
    $stagename = $_POST['stagename'];
    $stagecomments = $_POST['stagecomments'];

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for duplicate stagename before insert
        $checkSql = "SELECT COUNT(*) FROM transportstages WHERE stagename = :stagename";
        $checkStmt = $dbh->prepare($checkSql);
        $checkStmt->bindParam(':stagename', $stagename, PDO::PARAM_STR);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Stage name already exists. Please choose a different name.";
        } else {
            $sql = "INSERT INTO transportstages (stagename, stagecomments) VALUES (:stagename, :stagecomments)";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':stagename', $stagename, PDO::PARAM_STR);
            $stmt->bindParam(':stagecomments', $stagecomments, PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Records ADDED successfully.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} elseif (isset($_POST['update_submit'])) {
    $id = $_POST['id'];
    $stagename = $_POST['stagename'];
    $stagecomments = $_POST['stagecomments'];

    try {
        // Check for duplicate stagename during update (excluding the current ID)
        $checkSql = "SELECT COUNT(*) FROM transportstages WHERE stagename = :stagename AND id != :id";
        $checkStmt = $dbh->prepare($checkSql);
        $checkStmt->bindParam(':stagename', $stagename, PDO::PARAM_STR);
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['messagestate'] = 'error';
            $_SESSION['mess'] = "Stage name already exists. Please choose a different name.";
        } else {
            $sql = "UPDATE transportstages SET stagename = :stagename, stagecomments = :stagecomments WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':stagename', $stagename, PDO::PARAM_STR);
            $stmt->bindParam(':stagecomments', $stagecomments, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Records UPDATED successfully.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM transportstages WHERE id = :id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Records DELETED successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kipmetz-SMS | Transport Stages</title>
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
                                <h1 class="page-header">
                                    Manage Transport Stages <i class="fa fa-route"></i>
                                </h1>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_transportstage')): ?>
                                    <?php include('newtransportstagepopup.php'); ?>
                                    <a href="#myModal" data-toggle="modal" class="btn btn-success">
                                        <i class="fa fa-map-marker-alt"></i> New Stage
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="manage-transportstructure.php" class="btn btn-primary">
                                    <i class="fa fa-coins"></i> Transport Charges
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
                    <div class="panel-heading"><i class="fa fa-map-signs fa-fw"></i> Transport Stages</div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <br>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Stage Name</th>
                                            <th>Comments</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM transportstages ORDER BY id DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;

                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                        ?>
                                                <tr>
                                                    <td><?php echo $cnt; ?></td>
                                                    <td><?php echo htmlentities($row->stagename); ?></td>
                                                    <td><?php echo htmlentities($row->stagecomments); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                Action <span class="caret"></span>
                                                            </button>
                                                            <?php if (has_permission($accounttype, 'edit_transportstage')): ?>
                                                            <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                                                <li>
                                                                    <a href="#myModal<?php echo $row->id; ?>" data-toggle="modal">
                                                                        <i class="fa fa-pencil"></i> Edit
                                                                    </a>
                                                                </li>
                                                                <li class="divider"></li>
                                                                <li>
                                                                    <a href="manage-transportstages.php?delete=<?php echo htmlentities($row->id); ?>" onclick="return confirm('You want to delete the record?!!')" name="delete">
                                                                        <i class="fa fa-trash-o"></i> Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                            <?php endif; ?>
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
        </div>

    </div>

    <!-- Core Scripts -->
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

        <?php if ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted') : ?>
            function hideMsg() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            document.getElementById("popup").style.visibility = "visible";
            window.setTimeout(hideMsg, 5000);
        <?php endif; ?>
    </script>
</body>

</html>
