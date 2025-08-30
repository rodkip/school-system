<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $messagestate = '';
    $mess = "";

  // Add new otherpayitemname
      if (isset($_POST['submit'])) {
        $otherpayitemname = trim($_POST['otherpayitemname']);
        $description = trim($_POST['description']);

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for duplicate otherpayitemname name
        $check_sql = "SELECT COUNT(*) FROM otherpayitems WHERE otherpayitemname = :otherpayitemname";
        $stmt = $dbh->prepare($check_sql);
        $stmt->bindParam(':otherpayitemname', $otherpayitemname, PDO::PARAM_STR);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Item already exists!";
        } else {
            $insert_sql = "INSERT INTO otherpayitems (otherpayitemname, description) VALUES (:otherpayitemname, :description)";
            $stmt = $dbh->prepare($insert_sql);
            $stmt->bindParam(':otherpayitemname', $otherpayitemname, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Item record created successfully.";
            header("Location: manage-otherpayitems.php");
        }
      }

    // Update existing otherpayitemname
    elseif (isset($_POST['update_submit'])) {
        $id = $_POST['id'];
        $otherpayitemname = $_POST['otherpayitemname'];
        $description = $_POST['description'];

        $sql = "UPDATE otherpayitems SET otherpayitemname='$otherpayitemname', description='$description' WHERE id=$id";
        $dbh->query($sql);

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Votehead record UPDATED...";
        header("Location: manage-otherpayitems.php");
    }

    // Delete otherpayitemname
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "DELETE FROM otherpayitems WHERE id=$id";
            $dbh->exec($sql);

            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Votehead record deleted";
            header("Location: manage-otherpayitems.php");
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }
    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Other Payments Items</title>
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
                            <h2 class="page-header">Manage Other Payments Items <i class="fa fa-users-cog"></i></h2>

                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_otherpaymentitems')): ?>
                                    <?php include('newotherpayitempopup.php'); ?>
                                    <a href="#newotherpayitems" data-toggle="modal" class="btn btn-primary">
                                        <i class="fa fa-plus-circle"></i> New Pay Item
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
                <div class="panel-heading">Other Payments Items List</div>
                <div class="panel-body">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:25%;">Item</th>
                            <th>Description</th>
                            <th style="width:15%;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $sql = "SELECT * FROM otherpayitems ORDER BY id DESC";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0):
                                foreach ($results as $row):
                        ?>
                        <tr>
                            <td><?= $cnt; ?></td>
                            <td><?= htmlentities($row->otherpayitemname); ?></td>
                            <td><?= htmlentities($row->description); ?></td>
                            <td>
                            <?php if (has_permission($accounttype, 'edit_otherpaymentitems')): ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="#editModal<?= $row->id ?>" data-toggle="modal">
                                    <i class="fa fa-pencil"></i> Edit
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="manage-otherpayitems.php?delete=<?= htmlentities($row->id); ?>"
                                    onclick="return confirm('Are you sure you want to delete this Other payments item?');">
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

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $row->id ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $row->id ?>" aria-hidden="true">
                            <div class="modal-dialog">
                            <form method="post" action="manage-classdetails.php">
                                <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="editModalLabel<?= $row->id ?>">Edit Pay Item</h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= htmlentities($row->id) ?>">
                                    <div class="form-group">
                                    <label for="otherpayitemname<?= $row->id ?>">Item Name</label>
                                    <input type="text" class="form-control" name="otherpayitemname" id="otherpayitemname<?= $row->id ?>" value="<?= htmlentities($row->otherpayitemname) ?>" required>
                                    </div>
                                    <div class="form-group">
                                    <label for="description<?= $row->id ?>">Description</label>
                                    <textarea class="form-control" name="description" id="description<?= $row->id ?>" rows="3"><?= htmlentities($row->description) ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_otherpayitemname" class="btn btn-success">Update</button>
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
                            error_log("Vote Head Query Error: " . $e->getMessage());
                        }
                        ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                </div>

        </div> <!-- /#page-wrapper -->
    </div> <!-- /#wrapper -->

    <!-- Scripts -->
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

        <?php if ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted'): ?>
            function hideMsg() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            document.getElementById("popup").style.visibility = "visible";
            window.setTimeout(hideMsg, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
