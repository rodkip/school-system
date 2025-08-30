<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}
if (isset($_GET['action']) && $_GET['action'] == 'download_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=staff_details.csv');
    $output = fopen('php://output', 'w');

    // Updated column headers to match the specified order
    fputcsv($output, array(
        'Name',               // staffname
        'IDNo',        // staffidno
        'Bank',               // bank
        'BankAccountNo',    // bankaccno
        'Title',              // stafftitle
        'Gender',             // gender
        'Contact',            // staffcontact
        'NSSF No',            // nssfaccno
        'NHIF No',            // nhifaccno
        'Employment Date',    // employmentdate
        'Staff ID',           // staffid        
        'Marital Status',     // maritalstatus
        'Health Issue',       // healthissue
        'Experience',         // experience
        'Education Level'     // educationlevel
    ));

    $sql = "SELECT staffname, staffidno, bank, bankaccno, stafftitle, gender, staffcontact, nssfaccno, nhifaccno, employmentdate, staffid, maritalstatus, healthissue, experience, educationlevel FROM staffdetails order by staffname ASC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        fputcsv($output, array(
            $row['staffname'],
            $row['staffidno'],
            $row['bank'],
            $row['bankaccno'],
            $row['stafftitle'],
            $row['gender'],
            $row['staffcontact'],
            $row['nssfaccno'],
            $row['nhifaccno'],
            $row['employmentdate'],
            $row['staffid'],
            $row['maritalstatus'],
            $row['healthissue'],
            $row['experience'],
            $row['educationlevel']
        ));
    }

    fclose($output);
    exit();
}


$mess = "";
$messagestate = "";

// Handle form submission for adding/updating staff details
if (isset($_POST['submit'])) {
    try {
        $id = $_POST['id'];
        $staffidno = $_POST['staffidno'];
        $staffname = $_POST['staffname'];
        $gender = $_POST['gender'];
        $bank = $_POST['bank'];
        $bankaccno = $_POST['bankaccno'];
        $stafftitle = $_POST['stafftitle'];
        $staffcontact = $_POST['staffcontact'];
        $nssfaccno = $_POST['nssfaccno'];
        $nhifaccno = $_POST['nhifaccno'];
        $maritalstatus = $_POST['maritalstatus'];
        $healthissue = $_POST['healthissue'];
        $experience = $_POST['experience'];
        $educationlevel = $_POST['educationlevel'];

        // Check if staff already exists
        $sql = "SELECT id FROM staffdetails WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            // Update existing staff details
            $sql = "UPDATE staffdetails SET 
                    staffidno = :staffidno, 
                    staffname = :staffname, 
                    gender = :gender, 
                    bank = :bank, 
                    bankaccno = :bankaccno, 
                    stafftitle = :stafftitle, 
                    staffcontact = :staffcontact, 
                    nssfaccno = :nssfaccno, 
                    nhifaccno = :nhifaccno, 
                    maritalstatus = :maritalstatus, 
                    healthissue = :healthissue, 
                    experience = :experience, 
                    educationlevel = :educationlevel 
                    WHERE id = :id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':staffidno', $staffidno, PDO::PARAM_STR);
            $query->bindParam(':staffname', $staffname, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':bank', $bank, PDO::PARAM_STR);
            $query->bindParam(':bankaccno', $bankaccno, PDO::PARAM_STR);
            $query->bindParam(':stafftitle', $stafftitle, PDO::PARAM_STR);
            $query->bindParam(':staffcontact', $staffcontact, PDO::PARAM_STR);
            $query->bindParam(':nssfaccno', $nssfaccno, PDO::PARAM_STR);
            $query->bindParam(':nhifaccno', $nhifaccno, PDO::PARAM_STR);
            $query->bindParam(':maritalstatus', $maritalstatus, PDO::PARAM_STR);
            $query->bindParam(':healthissue', $healthissue, PDO::PARAM_STR);
            $query->bindParam(':experience', $experience, PDO::PARAM_STR);
            $query->bindParam(':educationlevel', $educationlevel, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Staff Records UPDATED successfully.";
        } else {
            // Insert new staff details
            $sql = "INSERT INTO staffdetails 
                    (staffidno, staffname, gender, bank, bankaccno, staffcontact, nssfaccno, nhifaccno, stafftitle, maritalstatus, healthissue, experience, educationlevel) 
                    VALUES 
                    (:staffidno, :staffname, :gender, :bank, :bankaccno, :staffcontact, :nssfaccno, :nhifaccno, :stafftitle, :maritalstatus, :healthissue, :experience, :educationlevel)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':staffidno', $staffidno, PDO::PARAM_STR);
            $query->bindParam(':staffname', $staffname, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':bank', $bank, PDO::PARAM_STR);
            $query->bindParam(':bankaccno', $bankaccno, PDO::PARAM_STR);
            $query->bindParam(':staffcontact', $staffcontact, PDO::PARAM_STR);
            $query->bindParam(':nssfaccno', $nssfaccno, PDO::PARAM_STR);
            $query->bindParam(':nhifaccno', $nhifaccno, PDO::PARAM_STR);
            $query->bindParam(':stafftitle', $stafftitle, PDO::PARAM_STR);
            $query->bindParam(':maritalstatus', $maritalstatus, PDO::PARAM_STR);
            $query->bindParam(':healthissue', $healthissue, PDO::PARAM_STR);
            $query->bindParam(':experience', $experience, PDO::PARAM_STR);
            $query->bindParam(':educationlevel', $educationlevel, PDO::PARAM_STR);
            $query->execute();
         
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Staff Records ADDED successfully.";
        }
        $messagestate = 'added';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle form submission for updating staff details
if (isset($_POST['update_submit'])) {
    $id = $_POST['id'];
    $staffidno = $_POST['editstaffidno'];
    $staffname = $_POST['editstaffname'];
    $gender = $_POST['editgender'];
    $bank = $_POST['editbank'];
    $bankaccno = $_POST['editbankaccno'];
    $stafftitle = $_POST['editstafftitle'];
    $staffcontact = $_POST['editstaffcontact'];
    $nssfaccno = $_POST['editnssfaccno'];
    $nhifaccno = $_POST['editnhifaccno'];
    $maritalstatus = $_POST['editmaritalstatus'];
    $healthissue = $_POST['edithealthissue'];
    $experience = $_POST['editexperience'];
    $educationlevel = $_POST['editeducationlevel'];

    $sql = "UPDATE staffdetails SET 
            staffidno = :staffidno, 
            staffname = :staffname, 
            gender = :gender, 
            bank = :bank, 
            bankaccno = :bankaccno, 
            staffcontact = :staffcontact, 
            nssfaccno = :nssfaccno, 
            nhifaccno = :nhifaccno, 
            stafftitle = :stafftitle, 
            maritalstatus = :maritalstatus, 
            healthissue = :healthissue, 
            experience = :experience, 
            educationlevel = :educationlevel 
            WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':staffidno', $staffidno, PDO::PARAM_STR);
    $query->bindParam(':staffname', $staffname, PDO::PARAM_STR);
    $query->bindParam(':gender', $gender, PDO::PARAM_STR);
    $query->bindParam(':bank', $bank, PDO::PARAM_STR);
    $query->bindParam(':bankaccno', $bankaccno, PDO::PARAM_STR);
    $query->bindParam(':staffcontact', $staffcontact, PDO::PARAM_STR);
    $query->bindParam(':nssfaccno', $nssfaccno, PDO::PARAM_STR);
    $query->bindParam(':nhifaccno', $nhifaccno, PDO::PARAM_STR);
    $query->bindParam(':stafftitle', $stafftitle, PDO::PARAM_STR);
    $query->bindParam(':maritalstatus', $maritalstatus, PDO::PARAM_STR);
    $query->bindParam(':healthissue', $healthissue, PDO::PARAM_STR);
    $query->bindParam(':experience', $experience, PDO::PARAM_STR);
    $query->bindParam(':educationlevel', $educationlevel, PDO::PARAM_STR);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    $_SESSION['messagestate'] = 'added';
    $_SESSION['mess'] = "Staff Records UPDATED successfully.";
}

// Handle staff deletion
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $sql = "DELETE FROM staffdetails WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
    
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Staff Records DELETED successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

$currentdate = date("Y");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Staff Details</title>
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
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Manage Staff Details <i class="fa fa-users"></i></h2>
                            </td>
                            <td>
                                <form method="post" enctype="multipart/form-data" action="manage-staffdetails.php">
                                    <?php include('newstaffpopup.php'); ?>
                                </form>
                                <?php if (has_permission($accounttype, 'new_staff')): ?>
                                <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Register New Staff</a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="manage-staffdetails.php?action=download_csv" class="btn btn-success">
                                    <i class="fa fa-download"></i> Download CSV
                                </a>
                            </td>

                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><i class="fa fa-users"></i> Registered Staff Members</div>
            <div class="panel-body">
            <div class="table-responsive" style="overflow-x: auto; width: 100%">
            
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ID No</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Staff Title</th>
                                <th>Bank</th>
                                <th>Bank A/C No</th>
                                <th>Contact</th>
                                <th>Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM staffdetails ORDER BY id DESC";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                            ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td><?php echo htmlentities($row->staffidno); ?></td>
                                    <td><?php echo htmlentities($row->staffname); ?></td>
                                    <td><?php echo htmlentities($row->gender); ?></td>
                                    <td><?php echo htmlentities($row->stafftitle); ?></td>
                                    <td><?php echo htmlentities($row->bank); ?></td>
                                    <td><?php echo htmlentities($row->bankaccno); ?></td>
                                    <td><?php echo htmlentities($row->staffcontact); ?></td>
                                    <td>
                                        <?php 
                                            $popup_staff_data = $row;
                                            include('viewstaffdetailspopup.php'); 
                                        ?>
                                        <a href="#otherstaffdetails<?php echo $cnt; ?>" data-toggle="modal">
                                            <i class="fa fa-bars" aria-hidden="true"></i> All RegDetails
                                        </a>
                                    </td>
                                    <td style="padding: 5px">
                                        <div class="btn-group dropup">
                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                Action <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu pull-right" role="menu">
                                                <?php if (has_permission($accounttype, 'edit_staff')): ?>
                                                    <li>
                                                        <a href="#myModal<?php echo $row->id; ?>" data-toggle="modal">
                                                            <i class="fa fa-pencil"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    <li>
                                                        <a href="manage-staffdetails.php?delete=<?php echo htmlentities($row->id); ?>" 
                                                           onclick="return confirm('You want to delete the record?!!')">
                                                            <i class="fa fa-trash-o"></i> Delete
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <?php include('editstaffdetailspopup.php'); ?>
                                    </td>
                                </tr>
                            <?php
                                    $cnt++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <h4>There are <b><span style="color:green"><?php echo $cnt - 1; ?></span></b> staff members registered in the system.</h4>
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