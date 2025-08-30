<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Handle form submission for adding new class entries
if (isset($_POST['submit'])) {
    try {
        $gradefullname = $_POST['gradefullname'];
        $feetreatment = $_POST['feetreatment'];
        $childtreatment = $_POST['childtreatment'];
        $studentadmno = $_POST['studentadmno'];
        $entryterm = $_POST['entryterm'];
        $stream = $_POST['stream'];
        $boarding = $_POST['boarding'];
        $dorm = $_POST['dorm'];
        $childstatus = "Present";
        $feewaiver = $_POST['feewaiver'];
        $feegradename = $entryterm . $gradefullname . $boarding;
        $classentryfullname = $gradefullname . $studentadmno;

        // Fetch feetreatmentrate from DB
        $feetreatmentrate = 1; // default fallback
        $sql = "SELECT feetreatmentrate FROM feetreatmentrates WHERE treatment = :treatment LIMIT 1";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':treatment', $feetreatment, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['feetreatmentrate'])) {
            $feetreatmentrate = (float)$result['feetreatmentrate'];
        }

        // Fetch childtreatmentrate from DB
        $childtreatmentrate = 1; // default fallback
        $sql = "SELECT feetreatmentrate FROM childtreatmentrates WHERE childtreatment = :childtreatment LIMIT 1";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['feetreatmentrate'])) {
            $childtreatmentrate = (float)$result['feetreatmentrate'];
        }

        // Extract year from gradefullname
        $entryyear = substr($gradefullname, 0, 4);

        // Check if a class entry for the same year and student already exists
        $sql = "SELECT COUNT(*) FROM classentries 
                WHERE studentadmno = :studentadmno 
                AND LEFT(gradefullname, 4) = :entryyear";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $stmt->bindParam(':entryyear', $entryyear, PDO::PARAM_STR);
        $stmt->execute();
        $yearEntryCount = $stmt->fetchColumn();

        if ($yearEntryCount > 0) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Record NOT saved - Learner already has a grade entry for $entryyear.";
        }else {
            // Insert new class entry with default waiver values (0)
            $sql = "INSERT INTO classentries (
                        studentadmno, gradefullname, feetreatment, childtreatment, 
                        entryterm, boarding, feegradename, feetreatmentrate, 
                        childtreatmentrate, dorm, stream, classentryfullname, 
                        childstatus, feewaiver, firsttermfeewaiver, 
                        secondtermfeewaiver, thirdtermfeewaiver
                    ) VALUES (
                        :studentadmno, :gradefullname, :feetreatment, :childtreatment, 
                        :entryterm, :boarding, :feegradename, :feetreatmentrate, 
                        :childtreatmentrate, :dorm, :stream, :classentryfullname, 
                        :childstatus, :feewaiver, 0, 0, 0
                    )";
            $query = $dbh->prepare($sql);
            $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
            $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
            $query->bindParam(':feetreatment', $feetreatment, PDO::PARAM_STR);
            $query->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
            $query->bindParam(':entryterm', $entryterm, PDO::PARAM_STR);
            $query->bindParam(':boarding', $boarding, PDO::PARAM_STR);
            $query->bindParam(':feegradename', $feegradename, PDO::PARAM_STR);
            $query->bindParam(':feetreatmentrate', $feetreatmentrate, PDO::PARAM_STR);
            $query->bindParam(':childtreatmentrate', $childtreatmentrate, PDO::PARAM_STR);
            $query->bindParam(':dorm', $dorm, PDO::PARAM_STR);
            $query->bindParam(':stream', $stream, PDO::PARAM_STR);
            $query->bindParam(':classentryfullname', $classentryfullname, PDO::PARAM_STR);
            $query->bindParam(':childstatus', $childstatus, PDO::PARAM_STR);
            $query->bindParam(':feewaiver', $feewaiver, PDO::PARAM_STR);
            $query->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "New record created...";
        }
    } catch (PDOException $e) {
        $_SESSION['mess'] = "New Record NOT created - the admno doesn't exist in the reg book.";
    }
}


// Handle deletion of class entries
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];

        $sql = "DELETE FROM classentries WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Records DELETED successfully.";
    } catch (PDOException $e) {
        $_SESSION['mess'] = "Record Not Deleted!!";
    }
}

// Handle updating class entries
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $gradefullname = $_POST['gradefullname'];
    $feetreatment = $_POST['feetreatment'];
    $childtreatment = $_POST['childtreatment'];
    $studentadmno = $_POST['studentadmno'];
    $entryterm = $_POST['entryterm'];
    $stream = $_POST['stream'];
    $boarding = $_POST['boarding'];
    $dorm = $_POST['dorm'];
    $childstatus = "Present";
    $feewaiver = $_POST['feewaiver'];
    $feegradename = $entryterm . $gradefullname . $boarding;
    $classentryfullname = $gradefullname . $studentadmno;

    // Fetch feetreatmentrate from DB
    $feetreatmentrate = 1; // default fallback
    $sql = "SELECT feetreatmentrate FROM feetreatmentrates WHERE treatment = :treatment LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':treatment', $feetreatment, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && isset($result['feetreatmentrate'])) {
        $feetreatmentrate = (float)$result['feetreatmentrate'];
    }

    // Fetch childtreatmentrate from DB
    $childtreatmentrate = 1; // default fallback
    $sql = "SELECT feetreatmentrate FROM childtreatmentrates WHERE childtreatment = :childtreatment LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && isset($result['feetreatmentrate'])) {
        $childtreatmentrate = (float)$result['feetreatmentrate'];
    }

    $sql = "UPDATE classentries SET studentadmno = :studentadmno, gradefullname = :gradefullname, feetreatment = :feetreatment, childtreatment = :childtreatment, entryterm = :entryterm, boarding = :boarding, feegradename = :feegradename, feetreatmentrate = :feetreatmentrate, childtreatmentrate = :childtreatmentrate, dorm = :dorm, stream = :stream, classentryfullname = :classentryfullname, childstatus = :childstatus, feewaiver=:feewaiver WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
    $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
    $query->bindParam(':feetreatment', $feetreatment, PDO::PARAM_STR);
    $query->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
    $query->bindParam(':entryterm', $entryterm, PDO::PARAM_STR);
    $query->bindParam(':boarding', $boarding, PDO::PARAM_STR);
    $query->bindParam(':feegradename', $feegradename, PDO::PARAM_STR);
    $query->bindParam(':feetreatmentrate', $feetreatmentrate, PDO::PARAM_STR);
    $query->bindParam(':childtreatmentrate', $childtreatmentrate, PDO::PARAM_STR);
    $query->bindParam(':dorm', $dorm, PDO::PARAM_STR);
    $query->bindParam(':stream', $stream, PDO::PARAM_STR);
    $query->bindParam(':classentryfullname', $classentryfullname, PDO::PARAM_STR);
    $query->bindParam(':childstatus', $childstatus, PDO::PARAM_STR);
    $query->bindParam(':feewaiver', $feewaiver, PDO::PARAM_STR);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    $_SESSION['messagestate'] = 'added';
    $_SESSION['mess'] = "Records UPDATED successfully.";
}

// Handle updating waiver details
if (isset($_POST['update_waivers'])) {
    $id = $_POST['id'];
    $firsttermfeewaiver = $_POST['firsttermfeewaiver'];
    $secondtermfeewaiver = $_POST['secondtermfeewaiver'];
    $thirdtermfeewaiver = $_POST['thirdtermfeewaiver'];

    // Validate numeric values
    if (!is_numeric($firsttermfeewaiver) || !is_numeric($secondtermfeewaiver) || !is_numeric($thirdtermfeewaiver)) {
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "Waiver amounts must be numeric values.";
    } else {
        $sql = "UPDATE classentries SET firsttermfeewaiver = :firsttermfeewaiver, secondtermfeewaiver = :secondtermfeewaiver, thirdtermfeewaiver = :thirdtermfeewaiver WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':firsttermfeewaiver', $firsttermfeewaiver, PDO::PARAM_STR);
        $query->bindParam(':secondtermfeewaiver', $secondtermfeewaiver, PDO::PARAM_STR);
        $query->bindParam(':thirdtermfeewaiver', $thirdtermfeewaiver, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Waiver details updated successfully.";
    }
}

if (isset($_POST['newstatus'])) {
    $id = $_POST['id'];
    $newstatus = $_POST['newstatus'];
    $statusreason = isset($_POST['statusreason']) ? $_POST['statusreason'] : '';
    
    try {
        $sql = "UPDATE classentries SET childstatus = :newstatus, statusreason = :statusreason WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':newstatus', $newstatus, PDO::PARAM_STR);
        $query->bindParam(':statusreason', $statusreason, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Student status updated successfully to: $newstatus";
    } catch (PDOException $e) {
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "Error updating status: " . $e->getMessage();
    }
} 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Grade Entries</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for status modal */
        .status-modal .modal-header {
            background-color: #3498db;
            color: white;
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .status-modal .modal-title {
            font-weight: 600;
        }
        
        .status-modal .modal-body {
            padding: 20px;
        }
        
        .status-modal .form-group {
            margin-bottom: 20px;
        }
        
        .status-modal .alert-info {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            color: #495057;
            padding: 10px 15px;
            border-radius: 4px;
        }
        
        .status-modal .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .status-modal .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .status-badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .status-badge i {
            margin-right: 6px;
        }
        
        .badge-present {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-gone {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-suspended {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
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
                                <h2 class="page-header">Manage Classes Admissions Entries <i class="fa fa-file-alt" aria-hidden="true"></i></h2>
                            </td>
                            <td>
                            <?php if (has_permission($accounttype, 'new_classadmission')): ?>
                                <form method="post" enctype="multipart/form-data" action="manage-classentries.php">
                                    <?php include('newclassentriespopup.php'); ?>
                                    <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa fa-plus-circle"></i>&nbsp;&nbsp;New Class Admission</a>
                                </form>
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
                    <div class="alert alert-primary" style="background-color: #e3f2fd; border-left: 4px solid #2196F3; color: #0d47a1; padding: 12px; margin: 15px 0; border-radius: 4px;">
                        <i class="fas fa-info-circle" style="color: #2196F3; margin-right: 8px;"></i>
                        <strong>Important Notice:</strong> For students enrolling mid-term, please apply the appropriate 
                        <span style="background-color: #ffeb3b; padding: 2px 6px; border-radius: 3px; font-weight: bold;">Fee Waiver</span> 
                        via the Action menu to account for prorated fee adjustments.
                    </div>
                </div>

                <div class="panel-body">
                    <div class="table-responsive" style="overflow-x: auto; width: 100%">
                          <div id="table-wrapper">
                                    <!-- Table loading animation -->
                                <?php include('tableloadinganimation.php'); ?>  
                                <!-- Table loading animation end-->
                                <div id="table-container" style="display: none;">
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>AdmNo</th>
                                    <th>Name</th>
                                    <th>Grade Fullname</th>
                                    <th>Entry Term</th>
                                    <th>Fee Treatment</th>
                                    <th>Child Treatment</th>
                                    <th>Stream</th>
                                    <th>Boarding?</th>
                                    <th>Dorm</th>
                                    <th>Status</th>
                                    <th>FeeWaiver</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $sql = "SELECT classentries.id, classentries.dorm, classentries.studentadmno, classentries.gradefullname,
                                    classentries.feetreatment, classentries.feetreatmentrate, classentries.childtreatment,
                                    classentries.childtreatmentrate, classentries.entryterm, classentries.boarding,
                                    classentries.feegradename, studentdetails.studentname, classentries.stream, classentries.childstatus,classentries.statusreason, classentries.feewaiver,
                                    dormitoriesdetails.dormitoryname, classentries.firsttermfeewaiver, classentries.secondtermfeewaiver, 
                                    classentries.thirdtermfeewaiver
                                    FROM classentries
                                    INNER JOIN studentdetails ON classentries.studentadmno = studentdetails.studentadmno
                                    LEFT JOIN dormitoriesdetails ON classentries.dorm = dormitoriesdetails.dormid
                                    ORDER BY classentries.id DESC";
                
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                ?>
                                    <tr>
                                        <td><?php echo htmlentities($cnt); ?></td>
                                        <td><?php echo htmlentities($row->studentadmno); ?></td>
                                        <td><?php echo htmlentities($row->studentname); ?></td>
                                        <td><?php echo htmlentities($row->gradefullname); ?></td>
                                        <td><?php echo htmlentities($row->entryterm); ?></td>
                                        <td><?php echo htmlentities($row->feetreatment); ?></td>
                                        <td><?php echo htmlentities($row->childtreatment); ?></td>
                                        <td><?php echo htmlentities($row->stream); ?></td>
                                        <td><?php echo htmlentities($row->boarding); ?></td>
                                        <td><?php echo htmlentities($row->dormitoryname); ?></td>
                                        <td style="text-align: center;">
                                            <?php 
                                            $status = htmlentities($row->childstatus);
                                            switch($status) {
                                                case 'Present':
                                                    echo '<span class="status-badge badge-present">
                                                            <i class="fas fa-check-circle"></i> Present
                                                        </span>';
                                                    break;
                                                case 'Gone':
                                                    echo '<span class="status-badge badge-gone">
                                                            <i class="fas fa-user-slash"></i> Gone
                                                        </span>';
                                                    break;
                                                case 'Suspended':
                                                    echo '<span class="status-badge badge-suspended">
                                                            <i class="fas fa-exclamation-triangle"></i> Suspended
                                                        </span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge badge-pill badge-secondary" style="padding: 8px; font-size: 14px;">
                                                            <i class="fas fa-question-circle"></i> Unknown
                                                        </span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php 
                                            $feewaiver = htmlentities($row->feewaiver);
                                            if ($feewaiver == 'Yes') {
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
                                        <td style="padding: 5px">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                    Action <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu pull-right" role="menu">
                                                    <?php if (has_permission($accounttype, 'edit_classadmission')): ?>
                                                        <li>
                                                            <a href="edit-classentry.php?editid=<?php echo htmlentities($row->id); ?>">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="#" data-toggle="modal" data-target="#waiverModal<?php echo htmlentities($row->id); ?>">
                                                                <i class="fa fa-percentage"></i> Waivers Edit
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="#" data-toggle="modal" data-target="#statusModal<?php echo htmlentities($row->id); ?>">
                                                                <i class="fas fa-user-edit"></i> Update Status
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="manage-classentries.php?delete=<?php echo htmlentities($row->id); ?>"
                                                               onclick="return confirm('You want to delete the record?!!')">
                                                                <i class="fa fa-trash-o"></i> Delete
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                            
                                            <!-- Waiver Edit Modal -->
                                            <div class="modal fade" id="waiverModal<?php echo htmlentities($row->id); ?>" tabindex="-1" role="dialog" aria-labelledby="waiverModalLabel">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h2 class="modal-title" id="waiverModalLabel">Edit Fee Waivers </h2>
                                                            Name: <b><?php echo htmlentities($row->studentname); ?></b>(<?php echo htmlentities($row->studentadmno); ?>), 
                                                            Grade: <b><?php echo htmlentities($row->gradefullname); ?></b>
                                                        </div>
                                                        <form method="post" action="manage-classentries.php">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?php echo htmlentities($row->id); ?>">
                                                                <table class="table table-bordered table-sm">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Term</th>
                                                                            <th>Waivers Amount (Ksh)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td>First Term</td>
                                                                            <td>
                                                                                <div class="input-group">
                                                                                    <span class="input-group-addon">Ksh</span>
                                                                                    <input type="number" step="0.01" class="form-control" id="firsttermfeewaiver" name="firsttermfeewaiver" 
                                                                                        value="<?php echo htmlentities($row->firsttermfeewaiver); ?>" placeholder="Enter amount">
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Second Term</td>
                                                                            <td>
                                                                                <div class="input-group">
                                                                                    <span class="input-group-addon">Ksh</span>
                                                                                    <input type="number" step="0.01" class="form-control" id="secondtermfeewaiver" name="secondtermfeewaiver" 
                                                                                        value="<?php echo htmlentities($row->secondtermfeewaiver); ?>" placeholder="Enter amount">
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Third Term</td>
                                                                            <td>
                                                                                <div class="input-group">
                                                                                    <span class="input-group-addon">Ksh</span>
                                                                                    <input type="number" step="0.01" class="form-control" id="thirdtermfeewaiver" name="thirdtermfeewaiver" 
                                                                                        value="<?php echo htmlentities($row->thirdtermfeewaiver); ?>" placeholder="Enter amount">
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="alert alert-warning mt-3 mb-0 p-3 d-flex align-items-center">
                                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                <small>Note: Waivers will be deducted from the total term fees. <br> Enter amounts carefully.</small>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                <button type="submit" name="update_waivers" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Update Modal -->
                                            <div class="modal fade status-modal" id="statusModal<?php echo htmlentities($row->id); ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title" id="statusModalLabel">
                                                                <i class="fas fa-user-edit mr-2"></i> Update Student Status
                                                            </h4>
                                                        </div>
                                                        <form method="post" action="manage-classentries.php">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?php echo htmlentities($row->id); ?>">
                                                                
                                                                <div class="student-info mb-4 p-3" style="background-color: #f8f9fa; border-radius: 5px;">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlentities($row->studentname); ?></p>
                                                                            <p class="mb-1"><strong>Adm No:</strong> <?php echo htmlentities($row->studentadmno); ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p class="mb-1"><strong>Grade:</strong> <?php echo htmlentities($row->gradefullname); ?></p>
                                                                            <p class="mb-1"><strong>Stream:</strong> <?php echo htmlentities($row->stream); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label for="childstatus" class="font-weight-bold">Current Status:</label>
                                                                    <div class="alert alert-info p-3">
                                                                        <?php 
                                                                        $currentStatus = htmlentities($row->childstatus);
                                                                        echo "<div class='d-flex align-items-center'>
                                                                                <i class='fas fa-info-circle mr-2'></i>
                                                                                <span class='font-weight-bold'>$currentStatus</span>
                                                                              </div>";
                                                                        
                                                                        if (!empty($row->statusreason)) {
                                                                            echo "<div class='mt-2'>
                                                                                    <i class='fas fa-comment mr-2'></i>
                                                                                    <em>" . htmlentities($row->statusreason) . "</em>
                                                                                  </div>";
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label for="newstatus" class="font-weight-bold">Update Status To:</label>
                                                                    <select class="form-control select-status" id="newstatus" name="newstatus" required>
                                                                        <option value="">Select New Status</option>
                                                                        <option value="Present" data-color="#28a745">Present</option>
                                                                        <option value="Gone" data-color="#dc3545">Gone</option>
                                                                        <option value="Suspended" data-color="#ffc107">Suspended</option>
                                                                    </select>
                                                                </div>
                                                                <br>
                                                                <div class="form-group" id="reasonField" style="display:none;">
                                                                    <label for="statusreason" class="font-weight-bold">Reason (Optional):</label>
                                                                    <textarea class="form-control" id="statusreason" name="statusreason" rows="3"  colspan="300" placeholder="Enter reason for status change..."><?php echo htmlentities($row->statusreason); ?></textarea>
                                                                    <br><small class="text-muted">This will help track why the status was changed.</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                    <i class="fas fa-times mr-1"></i> Cancel
                                                                </button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save mr-1"></i> Update Status
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
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
            
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
            
            // Show reason field when status is not "Present"
            $('select[name="newstatus"]').change(function() {
                if ($(this).val() !== 'Present') {
                    $('#reasonField').show();
                } else {
                    $('#reasonField').hide();
                }
                
                // Update select styling based on selected status
                $(this).removeClass('status-present status-gone status-suspended');
                if ($(this).val() === 'Present') {
                    $(this).addClass('status-present');
                } else if ($(this).val() === 'Gone') {
                    $(this).addClass('status-gone');
                } else if ($(this).val() === 'Suspended') {
                    $(this).addClass('status-suspended');
                }
            });
            
            // Initialize select styling
            $('.select-status').each(function() {
                if ($(this).val() === 'Present') {
                    $(this).addClass('status-present');
                } else if ($(this).val() === 'Gone') {
                    $(this).addClass('status-gone');
                } else if ($(this).val() === 'Suspended') {
                    $(this).addClass('status-suspended');
                }
            });
        });
        
        function admnoAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "checkadmno.php",
                data: 'studentadmno=' + $("#studentadmno").val(),
                type: "POST",
                success: function (data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function () {}
            });
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
    <style>
        /* Additional styles for status select */
        .select-status {
            transition: all 0.3s ease;
        }
        
        .status-present {
            border-left: 4px solid #28a745;
        }
        
        .status-gone {
            border-left: 4px solid #dc3545;
        }
        
        .status-suspended {
            border-left: 4px solid #ffc107;
        }
        
        .select-status:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
    </style>

    <?php
    if ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted') {
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