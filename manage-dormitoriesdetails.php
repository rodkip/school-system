<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
} else {
    $mess = "";
    $update = false;
    $dormid = '';
    $gender = '';
    $dormitoryname = '';
    $dormitorycapacity = '';
    $description = '';
    $id = 0;

    if(isset($_POST['submit'])) {
        try {
            $dormitoryname = $_POST['dormitoryname'];
            $dormitorycapacity = $_POST['dormitorycapacity'];
            $description = $_POST['description'];
            $gender = $_POST['gender'];
            $dormid = $_POST['dormid'];
         
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO dormitoriesdetails (dormid, dormitoryname, dormitorycapacity, description, gender) 
                    VALUES('$dormid', '$dormitoryname', '$dormitorycapacity', '$description', '$gender')";

            $dbh->exec($sql);
            
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "New record ADDED successful";
            $update = false;
            
            // Clear form fields
            $dormitoryname = '';
            $dormitorycapacity = '';
            $description = '';
            $gender = '';
            $dormid = '';
        } catch (PDOException $e) {
            echo $sql."<br>".$e->getMessage();
        }
    }

    // Deleting a record
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM dormitoriesdetails WHERE id=$id";
        $dbh->exec($sql);
     
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Record Deleted!!";
    }

    // Editing a record
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $update = true;
        $sql = "SELECT * FROM dormitoriesdetails WHERE id=$id";
        $query = $dbh->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);
        
        if($query->rowCount() > 0) {
            foreach($results as $row) {              
                $dormitoryname = $row->dormitoryname;
                $dormitorycapacity = $row->dormitorycapacity;
                $description = $row->description;
                $gender = $row->gender;
                $dormid = $row->dormid;
                $id = $row->id;

                $_SESSION['messagestate'] = 'added';
                $_SESSION['mess'] = "Record on EDIT mode!!";
            }
        }
    }

    // Updating a record
    if(isset($_POST['update'])) {
        $id = $_POST['id'];
        $dormitoryname = $_POST['dormitoryname'];
        $dormitorycapacity = $_POST['dormitorycapacity'];
        $description = $_POST['description'];     
        $gender = $_POST['gender'];
        $dormid = $_POST['dormid'];
        
        $dbh->query("UPDATE dormitoriesdetails SET 
                    dormitoryname = '$dormitoryname', 
                    dormitorycapacity = '$dormitorycapacity', 
                    description = '$description', 
                    gender = '$gender' 
                    WHERE id = $id");
    
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Record UPDATED successful!";
        
        $update = false;
        
        // Clear form fields
        $dormitoryname = '';
        $dormitorycapacity = '';
        $description = '';
        $gender = '';
        $dormid = '';
        $id = 0;
    }

    // Generate next dormid for new entries
    $nextdormid = "Dorm01";
    try {
        $sql = "SELECT dormid FROM dormitoriesdetails ORDER BY CAST(SUBSTRING(dormid, 5) AS UNSIGNED) DESC LIMIT 1";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && isset($row['dormid'])) {
            $lastNumeric = (int)substr($row['dormid'], 4);
            $nextNumeric = $lastNumeric + 1;
            $nextdormid = 'Dorm' . str_pad($nextNumeric, 2, '0', STR_PAD_LEFT);
        }
    } catch (PDOException $e) {
        echo "Error fetching dorm ID: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dorms</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>

<body onload="startTime()">
    <!--  wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php');?>
        <!-- end navbar top -->

        <!-- navbar side -->
        <?php include_once('includes/sidebar.php');?>
        <!-- end navbar side -->
        
        <!--  page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <!---messanger-->
                    <?php include_once('updatemessagepopup.php'); ?>
                    <!-- end messanger -->
                    <h2 class="page-header">Manage Dormitories:</h2>
                </div>
                <!--end page header -->
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                                        <div class="card card-body bg-light">
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="dormid">Dormitory ID</label>
                                                    <input type="text" class="form-control" name="dormid" id="dormid" 
                                                        required placeholder="Enter Dormitory ID" 
                                                        value="<?php echo ($update) ? $dormid : $nextdormid; ?>" 
                                                        <?php echo ($update) ? 'readonly' : ''; ?>>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="dormitoryname">Dormitory Name</label>
                                                    <input type="text" class="form-control" name="dormitoryname" 
                                                        id="dormitoryname" required placeholder="Enter Dormitory Name" 
                                                        value="<?php echo $dormitoryname; ?>">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="dormitorycapacity">Capacity</label>
                                                    <input type="number" class="form-control" name="dormitorycapacity" 
                                                        id="dormitorycapacity" placeholder="Enter Capacity" 
                                                        value="<?php echo $dormitorycapacity; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-4">
                                                    <label for="gender">Gender</label>
                                                    <select class="form-control" name="gender" id="gender" required>
                                                        <option value="">--Select--</option>
                                                        <option value="Male" <?php if($gender == 'Male') echo 'selected'; ?>>Male</option>
                                                        <option value="Female" <?php if($gender == 'Female') echo 'selected'; ?>>Female</option>
                                                        <option value="Mixed" <?php if($gender == 'Mixed') echo 'selected'; ?>>Mixed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="description">Description</label>
                                                    <textarea class="form-control" name="description" id="description" 
                                                        placeholder="Enter Description"><?php echo $description; ?></textarea>
                                                </div>
                                        
                                                <?php if ($update): ?>
                                                    <button type="submit" name="update" class="btn btn-primary">Update Dormitory</button>
                                                <?php else: ?>
                                                    <button type="submit" name="submit" class="btn btn-success">Add Dormitory</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    <!-- End Form Elements -->
                    <div class="panel panel-primary">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- Advanced Tables -->
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <form>
                                                <br>
                                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>DormId</th>
                                                            <th>Name</th>
                                                            <th>Capacity</th> 
                                                            <th>Description</th>  
                                                            <th>Gender</th>  
                                                            <th>Action</th>      
                                                        </tr>
                                                    </thead>
                                                    <tbody>                                   
                                                        <?php
                                                        $sql = "SELECT * FROM dormitoriesdetails";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                        $cnt = 1;
                                                        if($query->rowCount() > 0) {
                                                            foreach($results as $row) { 
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlentities($cnt);?></td>
                                                            <td><?php echo htmlentities($row->dormid);?></td>
                                                            <td><a href="manage-dormlist.php?dormid=<?php echo htmlentities($row->dormid);?>">
                                                                <?php echo htmlentities($row->dormitoryname);?></a></td>
                                                            <td><?php echo htmlentities($row->dormitorycapacity);?></td>
                                                            <td><?php echo htmlentities($row->description);?></td>  
                                                            <td><?php echo htmlentities($row->gender);?></td>             
                                                            <td>
                                                                <a href="manage-dormitoriesdetails.php?edit=<?php echo htmlentities($row->id);?>">Edit</a> || 
                                                                <a href="manage-dormitoriesdetails.php?delete=<?php echo htmlentities($row->id);?>" 
                                                                    onclick="return confirm('You want to delete the record?!!')" name="delete">Delete</a>
                                                            </td>
                                                        </tr>
                                                        <?php 
                                                            $cnt++;
                                                            }
                                                        } 
                                                        ?>  
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->

    <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <!-- Page-Level Plugin Scripts-->
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
    <?php
    if (isset($_SESSION['messagestate']) && ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted')) {
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
<?php } ?>