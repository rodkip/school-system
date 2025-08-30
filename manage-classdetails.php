<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$currentacademicyear = date("Y");
$messagestate = '';
$mess = "";

// Handle form submission for adding class details
if (isset($_POST['submit'])) {
    $gradename = $_POST['gradename'];
    $academicyear = $_POST['academicyear'];
    $classcapacity = $_POST['classcapacity'];
    $gradefullname = $academicyear . $gradename;

    // Check if the record already exists
    $sql = "SELECT * FROM classdetails WHERE gradefullname = :gradefullname";
    $query = $dbh->prepare($sql);
    $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
    
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Record NOT saved - DUPLICATE Class";
    } else {
        // Insert new class details
        $sql = "INSERT INTO classdetails (gradefullname, gradename, academicyear, classcapacity) VALUES (:gradefullname, :gradename, :academicyear, :classcapacity)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
        $query->bindParam(':gradename', $gradename, PDO::PARAM_STR);
        $query->bindParam(':academicyear', $academicyear, PDO::PARAM_STR);
        $query->bindParam(':classcapacity', $classcapacity, PDO::PARAM_INT);
        $query->execute();

 
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Records CREATED successfully.";
    }
}

// Handle form submission for updating class details
if (isset($_POST['update_submit'])) {
    $id = $_POST['id'];
    $gradename = $_POST['editgradename'];
    $academicyear = $_POST['editacademicyear'];
    $classcapacity = $_POST['editclasscapacity'];
    $gradefullname = $academicyear . $gradename;

    // Update class details
    $sql = "UPDATE classdetails SET gradename = :gradename, academicyear = :academicyear, classcapacity = :classcapacity, gradefullname = :gradefullname WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':gradename', $gradename, PDO::PARAM_STR);
    $query->bindParam(':academicyear', $academicyear, PDO::PARAM_STR);
    $query->bindParam(':classcapacity', $classcapacity, PDO::PARAM_INT);
    $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();


    $_SESSION['messagestate'] = 'added';
    $_SESSION['mess'] = "Records UPDATED successfully.";
}

// Handle class deletion
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];

        // Delete class record
        $sql = "DELETE FROM classdetails WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();


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
    <title>Kipmetz-SMS | Grades</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

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
                            <td width="100%"><h1 class="page-header">Manage Classes<i class="fa fa-chalkboard-teacher" aria-hidden="true"></i></h1></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <form method="post" enctype="multipart/form-data" action="manage-classdetails.php">
                                <?php if (has_permission($accounttype, 'new_class')): ?>
                                    <?php include('newclassdetailspopup.php'); ?>
                                    <a href="#myModal" data-toggle="modal" class="btn btn-success"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;New Class</a>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="manage-classentries.php" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Manage Class Admissions Entries</a>
                            </td>
                            
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="panel panel-primary">
            <div class="panel-heading">
                <i class="fa fa-chalkboard-teacher" aria-hidden="true"></i> Class Details Summary
            </div>

    <div class="panel-body">
    <div class="table-responsive" style="overflow-x: auto; width: 100%">
 
            <span style='color:red'>Click on the Grade FullName to view the Class List</span>
            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                <thead>
                    <tr>
                        <th>S.NO</th>
                        <th>Grade Name</th>
                        <th>Academic Year</th>
                        <th>Grade FullName</th>
                        <th>Class Capacity</th>
                        <th>Class Pop-Entered</th>
                        <th>Comment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT classdetails.gradename, classdetails.id, classdetails.academicyear, classdetails.gradefullname,
                                   COUNT(classentries.studentAdmNo) AS CountOfstudentAdmNo, classdetails.classcapacity
                            FROM classentries
                            RIGHT JOIN classdetails ON classentries.gradefullname = classdetails.gradefullName
                            GROUP BY classdetails.gradename, classdetails.id, classdetails.academicyear,
                                     classdetails.gradefullName, classdetails.classcapacity
                            ORDER BY classdetails.id DESC";
                    $query = $dbh->prepare($sql);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                    $cnt = 1;
                    if ($query->rowCount() > 0) {
                        foreach ($results as $row) {
                    ?>
                        <tr>
                            <td><?php echo htmlentities($cnt); ?></td>
                            <td>
                                <?php
                                echo htmlentities($row->gradename);
                                $id = $row->id;
                                include('editclassdetailspopup.php');
                                ?>
                            </td>
                            <td><?php echo htmlentities($row->academicyear); ?></td>
                            <td>
                                <a href="classentrypergrade.php?viewgradefullname=<?php echo htmlentities($row->gradefullname); ?>">
                                    <?php echo htmlentities($row->gradefullname); ?>
                                </a>
                            </td>
                            <td><?php echo htmlentities($row->classcapacity); ?></td>
                            <td><?php echo htmlentities($row->CountOfstudentAdmNo); ?></td>
                            <td>
                                <?php
                                if ($row->classcapacity == "0") {
                                    echo "";
                                } elseif ($row->classcapacity > $row->CountOfstudentAdmNo) {
                                    echo "";
                                } else {
                                    echo "The class is full";
                                }
                                ?>
                            </td>
                            <td style="padding: 5px">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                        Action <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu pull-right" role="menu">
                                        <?php if (has_permission($accounttype, 'edit_class')): ?>
                                            <li>
                                                <a href="#myModal<?php echo $row->id; ?>" data-toggle="modal">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </li>
                                            <li class="divider"></li>
                                            <li>
                                                <a href="manage-classdetails.php?delete=<?php echo htmlentities($row->id); ?>"
                                                   onclick="return confirm('You want to delete the record?!! -- <?php echo htmlentities($row->gradefullname); ?>')">
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

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
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