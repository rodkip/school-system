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

    // Add new votehead
    if (isset($_POST['submit'])) {
        $votehead = trim($_POST['votehead']);
        $description = trim($_POST['description']);
        $isfeepayment = $_POST['isfeepayment'];
        $isfeetreatmentcalculations = $_POST['isfeetreatmentcalculations'];

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for duplicate votehead name
        $check_sql = "SELECT COUNT(*) FROM voteheads WHERE votehead = :votehead";
        $stmt = $dbh->prepare($check_sql);
        $stmt->bindParam(':votehead', $votehead, PDO::PARAM_STR);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Votehead already exists!";
        } else {
            $insert_sql = "INSERT INTO voteheads (votehead, description, isfeepayment, isfeetreatmentcalculations) VALUES (:votehead, :description, :isfeepayment, :isfeetreatmentcalculations)";
            $stmt = $dbh->prepare($insert_sql);
            $stmt->bindParam(':votehead', $votehead, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':isfeepayment', $isfeepayment, PDO::PARAM_STR);
            $stmt->bindParam(':isfeetreatmentcalculations', $isfeetreatmentcalculations, PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Votehead record created successfully.";
        }
    }

    // Update existing votehead
    elseif (isset($_POST['update_submit'])) {
        $id = $_POST['id'];
        $votehead = $_POST['votehead'];
        $description = $_POST['description'];
        $isfeepayment = isset($_POST['isfeepayment']) ? 'yes' : 'no';
        $isfeetreatmentcalculations = isset($_POST['isfeetreatmentcalculations']) ? 'yes' : 'no';

        $sql = "UPDATE voteheads SET votehead='$votehead', description='$description', isfeepayment='$isfeepayment', isfeetreatmentcalculations='$isfeetreatmentcalculations' WHERE id=$id";
        $dbh->query($sql);

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Votehead record UPDATED...";
    }

    // Update existing votehead
    if (isset($_POST['update_votehead'])) {
        $id = intval($_POST['id']);
        $votehead = trim($_POST['votehead']);
        $description = trim($_POST['description']);
        $isfeepayment = trim($_POST['isfeepayment']);
        $isfeetreatmentcalculations = trim($_POST['isfeetreatmentcalculations']);

        try {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check for duplicate name excluding current record
            $check_sql = "SELECT COUNT(*) FROM voteheads WHERE votehead = :votehead AND id != :id";
            $stmt = $dbh->prepare($check_sql);
            $stmt->bindParam(':votehead', $votehead, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $duplicate = $stmt->fetchColumn();

            if ($duplicate > 0) {
                $_SESSION['messagestate'] = 'deleted';
                $_SESSION['mess'] = "Another record with the same votehead name already exists!";
            } else {
                $update_sql = "UPDATE voteheads SET votehead = :votehead, description = :description, isfeepayment = :isfeepayment, isfeetreatmentcalculations = :isfeetreatmentcalculations WHERE id = :id";
                $stmt = $dbh->prepare($update_sql);
                $stmt->bindParam(':votehead', $votehead, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':isfeepayment', $isfeepayment, PDO::PARAM_STR);
                $stmt->bindParam(':isfeetreatmentcalculations', $isfeetreatmentcalculations, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $_SESSION['messagestate'] = 'added';
                $_SESSION['mess'] = "Votehead updated successfully.";
            }
        } catch (PDOException $e) {
            error_log("Update Votehead Error: " . $e->getMessage());
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Failed to update votehead.";
        }
    }

    // Delete votehead
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "DELETE FROM voteheads WHERE id=$id";
            $dbh->exec($sql);

            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Votehead record deleted";
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Manage Voteheads</title>
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
                            <h2 class="page-header">Manage Voteheads <i class="fa fa-users-cog"></i></h2>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_votehead')): ?>
                                    <?php include('newvoteheadpopup.php'); ?>
                                    <a href="#newvotehead" data-toggle="modal" class="btn btn-primary">
                                        <i class="fa fa-plus-circle"></i> New Votehead
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="manage-expenditures.php" class="btn btn-primary">
                                    <i class="fa fa-plus-circle"></i> Expenditures
                                </a>
                            </td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">Expense Vote Heads</div>
                <div class="panel-body">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:20%;">VoteHead</th>
                            <th>Description</th>
                            <th>Is Fee Payment</th>
                            <th>For Treatment Calcs</th>
                            <th style="width:15%;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        try {
                            $sql = "SELECT * FROM voteheads ORDER BY id DESC";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0):
                                foreach ($results as $row):
                        ?>
                        <tr>
                            <td><?= $cnt; ?></td>
                            <td><?= htmlentities($row->votehead); ?></td>
                            <td><?= htmlentities($row->description); ?></td>
                            <td style="text-align: center;">
                                <?php 
                                    $isfeepayment = htmlentities($row->isfeepayment);
                                    if ($isfeepayment == 'Yes') {
                                        echo '<span class="badge badge-pill badge-success" style="padding: 8px; font-size: 14px;">
                                                <i class="fas fa-check-circle"></i> Yes
                                            </span>';
                                            } else {
                                                echo '<span class="badge badge-pill badge-secondary" style="padding: 8px; font-size: 14px;">
                                                        <i class="fas fa-times-circle"></i> No
                                                    </span>';
                                            }
                                ?>
                            </td>
                             <td style="text-align: center;">
                                <?php 
                                    $isfeetreatmentcalculations = htmlentities($row->isfeetreatmentcalculations);
                                    if ($isfeetreatmentcalculations == 'Yes') {
                                        echo '<span class="badge badge-pill badge-success" style="padding: 8px; font-size: 14px;">
                                                <i class="fas fa-check-circle"></i> Yes
                                            </span>';
                                            } else {
                                                echo '<span class="badge badge-pill badge-secondary" style="padding: 8px; font-size: 14px;">
                                                        <i class="fas fa-times-circle"></i> No
                                                    </span>';
                                            }
                                ?>
                            </td>
                            <td>
                            <?php if (has_permission($accounttype, 'edit_votehead')): ?>
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
                                    <a href="manage-voteheads.php?delete=<?= htmlentities($row->id); ?>"
                                    onclick="return confirm('Are you sure you want to delete this vote head?');">
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
                                <form method="post" action="manage-voteheads.php">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="editModalLabel<?= $row->id ?>">Edit Vote Head</h4>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= htmlentities($row->id) ?>">
                                            
                                            <!-- Vote Head Name -->
                                            <div class="form-group">
                                                <label for="votehead<?= $row->id ?>">Vote Head Name</label>
                                                <input type="text" class="form-control" name="votehead" id="votehead<?= $row->id ?>" value="<?= htmlentities($row->votehead) ?>" required>
                                            </div>
                                            
                                            <!-- Description -->
                                            <div class="form-group">
                                                <label for="description<?= $row->id ?>">Description</label>
                                                <textarea class="form-control" name="description" id="description<?= $row->id ?>" rows="3"><?= htmlentities($row->description) ?></textarea>
                                            </div>

                                            <!-- Is Fee Payment -->
                                            <div class="form-group">
                                                <label for="isfeepayment<?= $row->id ?>">Is Fee Payment?</label>
                                                <select name="isfeepayment" id="isfeepayment<?= $row->id ?>" class="form-control" required>
                                                    <option value="<?= htmlentities($row->isfeepayment) ?>"><?= htmlentities($row->isfeepayment) ?></option>
                                                    <option value="Yes">Yes</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>

                                            <!-- For Treatment Calculations -->
                                            <div class="form-group">
                                                <label for="isfeetreatmentcalculations<?= $row->id ?>">For Treatment Calculations?</label>
                                                <select name="isfeetreatmentcalculations" id="isfeetreatmentcalculations<?= $row->id ?>" class="form-control" required>
                                                    <option value="<?= htmlentities($row->isfeetreatmentcalculations) ?>"><?= htmlentities($row->isfeetreatmentcalculations) ?></option>
                                                    <option value="Yes">Yes</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update_votehead" class="btn btn-success">Update</button>
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