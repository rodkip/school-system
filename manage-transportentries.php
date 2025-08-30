<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
} else {
    // Handle new transport entry submission
    if (isset($_POST['submit'])) {
        try {
            // Sanitize & validate inputs
            $studentadmno = trim($_POST['studentadmno']);
            $stagefullname = trim($_POST['stagefullname']);
            $childtreatment = trim($_POST['childtreatment']);
            $transporttreatment = trim($_POST['transporttreatment']);
            $terms = isset($_POST['term']) ? $_POST['term'] : [];
    
            if (empty($studentadmno) || empty($stagefullname) || empty($childtreatment)) {
                throw new Exception("Missing required fields.");
            }
    
            // Fetch academicyear using stagefullname
            $academicYearQuery = $dbh->prepare("
                SELECT academicyear 
                FROM transportstructure
                WHERE stagefullname = :stagefullname
                LIMIT 1
            ");
            $academicYearQuery->execute([':stagefullname' => $stagefullname]);
            $academicYearData = $academicYearQuery->fetch(PDO::FETCH_ASSOC);
    
            if (!$academicYearData) {
                throw new Exception("No academic year found for stage: $stagefullname.");
            }
    
            $academicyear = $academicYearData['academicyear'];
    
            // Fetch gradefullname (classentryfullname)
            $classEntryQuery = $dbh->prepare("
                SELECT classentryfullname 
                FROM classentries
                WHERE studentadmno = :studentadmno
                AND LEFT(classentryfullname, 4) = :academicyear
                LIMIT 1
            ");
            $classEntryQuery->execute([
                ':studentadmno' => $studentadmno,
                ':academicyear' => $academicyear
            ]);
            $classEntryData = $classEntryQuery->fetch(PDO::FETCH_ASSOC);
    
            if (!$classEntryData || empty($classEntryData['classentryfullname'])) {
                throw new Exception("No grade (class) record found for student AdmNo: $studentadmno in academic year: $academicyear.");
            }
    
            $gradefullname = $classEntryData['classentryfullname'];
    
            // Fetch transport charges
            $smt = $dbh->prepare('SELECT * FROM transportstructure WHERE stagefullname = :stagefullname');
            $smt->execute([':stagefullname' => $stagefullname]);
            $transportData = $smt->fetch(PDO::FETCH_ASSOC);
    
            if (!$transportData) {
                throw new Exception("Stage not found in the transport structure.");
            }
    
            $firstTermCharge = $transportData['firsttermcharge'];
            $secondTermCharge = $transportData['secondtermcharge'];
            $thirdTermCharge = $transportData['thirdtermcharge'];    
         

            // Fetch transporttreatmentrate from DB
            $sql = "SELECT transporttreatmentrate FROM feetreatmentrates WHERE treatment = :transporttreatment LIMIT 1";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':transporttreatment', $transporttreatment, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['transporttreatmentrate'])) {
                $transporttreatmentrate = (float)$result['transporttreatmentrate'];
            }

            // Fetch childtreatmentrate from DB
            $sql = "SELECT transporttreatmentrate as childtreatmentrate FROM childtreatmentrates WHERE childtreatment = :childtreatment LIMIT 1";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['childtreatmentrate'])) {
                $childtreatmentrate = (float)$result['childtreatmentrate'];
            }
                        
    
            // Final charge per term (with default waivers set to 0)
            $firstTermAmountFinal = in_array('FirstTerm', $terms) ? $firstTermCharge * $childtreatmentrate * $transporttreatmentrate : 0;
            $secondTermAmountFinal = in_array('SecondTerm', $terms) ? $secondTermCharge * $childtreatmentrate * $transporttreatmentrate : 0;
            $thirdTermAmountFinal = in_array('ThirdTerm', $terms) ? $thirdTermCharge * $childtreatmentrate * $transporttreatmentrate: 0;
    
            // Check for duplicate registration
            $checkStmt = $dbh->prepare('
                SELECT COUNT(*) 
                FROM transportentries 
                WHERE studentadmno = :studentadmno 
                AND stagefullname = :stagefullname
            ');
            $checkStmt->execute([
                ':studentadmno' => $studentadmno,
                ':stagefullname' => $stagefullname
            ]);
            $existingRecordCount = $checkStmt->fetchColumn();
    
            if ($existingRecordCount > 0) {
                throw new Exception("AdmNo: $studentadmno is already registered to $stagefullname.");
            }
    
            // Insert record with waiver columns
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
          $sql = "INSERT INTO transportentries 
        (
            studentadmno, stagefullname, transporttreatment, transporttreatmentrate,
            childtreatment, childtreatmentrate, 
            firsttermtransport, secondtermtransport, thirdtermtransport, 
            classentryfullname, 
            firsttermtransportwaiver, secondtermtransportwaiver, thirdtermtransportwaiver, 
            transportwaiver
        )
        VALUES 
        (
            :studentadmno, :stagefullname, :transporttreatment, :transporttreatmentrate,
            :childtreatment, :childtreatmentrate,
            :firstTerm, :secondTerm, :thirdTerm,
            :gradefullname,
            0, 0, 0,
            'No'
        )";

            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':studentadmno' => $studentadmno,
                ':stagefullname' => $stagefullname,
                ':transporttreatment' => $transporttreatment,
                ':transporttreatmentrate' => $transporttreatmentrate,
                ':childtreatment' => $childtreatment,
                ':childtreatmentrate' => $childtreatmentrate,
                ':firstTerm' => $firstTermAmountFinal,
                ':secondTerm' => $secondTermAmountFinal,
                ':thirdTerm' => $thirdTermAmountFinal,
                ':gradefullname' => $gradefullname
            ]);

    
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Transport registration completed successfully.";
    
        } catch (PDOException $e) {
            error_log("PDO Error: " . $e->getMessage());
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Application Error: " . $e->getMessage());
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = $e->getMessage();
        }
    }
    
    // Handle edit transport entry
    if (isset($_GET['editid'])) {
        $editid = intval($_GET['editid']);
        $sql = "SELECT * FROM transportentries WHERE id=:editid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':editid', $editid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
    }
    
    // Handle update transport entry
    if (isset($_POST['update'])) {
        try {
            $id = $_POST['id'];
            $studentadmno = trim($_POST['studentadmno']);
            $stagefullname = trim($_POST['stagefullname']);
            $childtreatment = trim($_POST['childtreatment']);
            $transporttreatment = trim($_POST['transporttreatment']);
            $terms = isset($_POST['term']) ? $_POST['term'] : [];
            
            if (empty($studentadmno) || empty($stagefullname) || empty($childtreatment)) {
                throw new Exception("Missing required fields.");
            }
            
            // Fetch transport charges
            $smt = $dbh->prepare('SELECT * FROM transportstructure WHERE stagefullname = :stagefullname');
            $smt->execute([':stagefullname' => $stagefullname]);
            $transportData = $smt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transportData) {
                throw new Exception("Stage not found in the transport structure.");
            }
            
            $firstTermCharge = $transportData['firsttermcharge'];
            $secondTermCharge = $transportData['secondtermcharge'];
            $thirdTermCharge = $transportData['thirdtermcharge'];
            
            // Fetch transporttreatmentrate from DB
            $sql = "SELECT transporttreatmentrate FROM feetreatmentrates WHERE treatment = :transporttreatment LIMIT 1";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':transporttreatment', $transporttreatment, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $transporttreatmentrate = $result && isset($result['transporttreatmentrate']) ? (float)$result['transporttreatmentrate'] : 1;
            
            // Fetch childtreatmentrate from DB
            $sql = "SELECT transporttreatmentrate as childtreatmentrate FROM childtreatmentrates WHERE childtreatment = :childtreatment LIMIT 1";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $childtreatmentrate = $result && isset($result['childtreatmentrate']) ? (float)$result['childtreatmentrate'] : 1;
            
            // Calculate final amounts for selected terms
            $firstTermAmountFinal = in_array('FirstTerm', $terms) ? $firstTermCharge * $childtreatmentrate * $transporttreatmentrate : 0;
            $secondTermAmountFinal = in_array('SecondTerm', $terms) ? $secondTermCharge * $childtreatmentrate * $transporttreatmentrate : 0;
            $thirdTermAmountFinal = in_array('ThirdTerm', $terms) ? $thirdTermCharge * $childtreatmentrate * $transporttreatmentrate : 0;
            
            // Update the record
            $sql = "UPDATE transportentries SET 
                    studentadmno = :studentadmno,
                    stagefullname = :stagefullname,
                    transporttreatment = :transporttreatment,
                    transporttreatmentrate = :transporttreatmentrate,
                    childtreatment = :childtreatment,
                    childtreatmentrate = :childtreatmentrate,
                    firsttermtransport = :firstTerm,
                    secondtermtransport = :secondTerm,
                    thirdtermtransport = :thirdTerm
                    WHERE id = :id";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':studentadmno' => $studentadmno,
                ':stagefullname' => $stagefullname,
                ':transporttreatment' => $transporttreatment,
                ':transporttreatmentrate' => $transporttreatmentrate,
                ':childtreatment' => $childtreatment,
                ':childtreatmentrate' => $childtreatmentrate,
                ':firstTerm' => $firstTermAmountFinal,
                ':secondTerm' => $secondTermAmountFinal,
                ':thirdTerm' => $thirdTermAmountFinal,
                ':id' => $id
            ]);
            
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Transport entry updated successfully.";
            header('location:manage-transportentries.php');
            exit();
            
        } catch (PDOException $e) {
            error_log("PDO Error: " . $e->getMessage());
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("Application Error: " . $e->getMessage());
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = $e->getMessage();
        }
    }
    
    // Handle deletion of transport entries
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "DELETE FROM transportentries WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Records DELETED successfully.";
        } catch (PDOException $e) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Records Not DELETED successfully.";
        }
    }
    
    // Handle updating transport waivers
    if (isset($_POST['update_transport_waivers'])) {
        try {
            $id = $_POST['id'];
            $firsttermtransportwaiver = $_POST['firsttermtransportwaiver'];
            $secondtermtransportwaiver = $_POST['secondtermtransportwaiver'];
            $thirdtermtransportwaiver = $_POST['thirdtermtransportwaiver'];
            $transportwaiver = $_POST['transportwaiver'];
            
            // Validate numeric values for waivers
            if (!is_numeric($firsttermtransportwaiver) || !is_numeric($secondtermtransportwaiver) || !is_numeric($thirdtermtransportwaiver)) {
                throw new Exception("Waiver amounts must be numeric values.");
            }
            
            $sql = "UPDATE transportentries SET 
                    firsttermtransportwaiver = :firsttermtransportwaiver, 
                    secondtermtransportwaiver = :secondtermtransportwaiver, 
                    thirdtermtransportwaiver = :thirdtermtransportwaiver,
                    transportwaiver = :transportwaiver
                    WHERE id = :id";
                    
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':firsttermtransportwaiver', $firsttermtransportwaiver, PDO::PARAM_STR);
            $stmt->bindParam(':secondtermtransportwaiver', $secondtermtransportwaiver, PDO::PARAM_STR);
            $stmt->bindParam(':thirdtermtransportwaiver', $thirdtermtransportwaiver, PDO::PARAM_STR);
            $stmt->bindParam(':transportwaiver', $transportwaiver, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Transport waiver details updated successfully.";
        } catch (Exception $e) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS|Transport Entries</title>
    <!-- Core CSS - Include with every page -->
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
                            <td width="100%">
                                <h2 class="page-header">Transport Session Entries: <i class="fa fa-bus"></i></h2>
                            </td>
                            <td>
                                <?php if (has_permission($accounttype, 'new_transportstageassigning')): ?>
                                <?php include('newtransportentriespopup.php'); ?>
                                <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa  fa-plus-circle"></i>&nbsp;&nbsp;Add Learner to Route</a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><a href="manage-transportstages.php" class="btn btn-success"> Manage Transport Stages</a></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><button type="button" class="btn btn-success" onclick="downloadCSV()">Download CSV</button></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="alert alert-primary" style="background-color: #e3f2fd; border-left: 4px solid #2196F3; color: #0d47a1; padding: 12px; margin: 15px 0; border-radius: 4px;">
                                <i class="fas fa-info-circle" style="color: #2196F3; margin-right: 8px;"></i>
                                <strong>Transport Waiver Notice:</strong> For students with special transport arrangements, apply appropriate 
                                <span style="background-color: #ffeb3b; padding: 2px 6px; border-radius: 3px; font-weight: bold;">Transport Waivers</span> 
                                via the Action menu to account for adjusted transport charges.
                            </div>
                        </div>
                        
                        <div class="panel-body">
                            <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                <table class="table table-striped table-bordered table-hover" id="dataTable1">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>AdmNo</th>
                                            <th>Name</th>
                                            <th>Grade</th>
                                            <th>AcademicYear</th>
                                            <th>Route</th>
                                            <th>Child Treatment</th>
                                            <th>Transport Treatment</th>
                                            <th>1st-Term Transport</th>
                                            <th>2nd-Term Transport</th>
                                            <th>3rd-Term Transport</th>
                                            <th>Waiver Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT 
                                            transportentries.id,
                                            transportentries.studentadmno,
                                            transportentries.stagefullname,
                                            transportentries.childtreatment,
                                            transportentries.transporttreatment,
                                            transportentries.firsttermtransport,
                                            transportentries.secondtermtransport,
                                            transportentries.thirdtermtransport,
                                            transportentries.firsttermtransportwaiver,
                                            transportentries.secondtermtransportwaiver,
                                            transportentries.thirdtermtransportwaiver,
                                            transportentries.transportwaiver,
                                            studentdetails.studentname,
                                            transportstructure.academicyear,
                                            classentries.gradefullname
                                        FROM 
                                            transportentries 
                                        INNER JOIN 
                                            studentdetails 
                                            ON transportentries.studentadmno = studentdetails.studentadmno
                                        INNER JOIN 
                                            transportstructure
                                            ON transportentries.stagefullname = transportstructure.stagefullname
                                        LEFT JOIN 
                                            classentries 
                                            ON transportentries.classentryfullname = classentries.classentryfullname 
                                        ORDER BY 
                                            transportentries.id DESC";
                                            
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                                // Calculate net transport amounts after waivers
                                                $firstTermNet = $row->firsttermtransport - $row->firsttermtransportwaiver;
                                                $secondTermNet = $row->secondtermtransport - $row->secondtermtransportwaiver;
                                                $thirdTermNet = $row->thirdtermtransport - $row->thirdtermtransportwaiver;
                                        ?>
                                                <tr>
                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                    <td><?php echo htmlentities($row->studentadmno); ?></td>
                                                    <td><?php echo htmlentities($row->studentname); ?></td>
                                                    <td><?php echo htmlentities($row->gradefullname); ?></td>
                                                    <td><?php echo htmlentities($row->academicyear); ?></td>
                                                    <td><?php echo htmlentities($row->stagefullname); ?></td>
                                                    <td><?php echo htmlentities($row->childtreatment); ?></td>
                                                    <td><?php echo htmlentities($row->transporttreatment); ?></td>
                                                    <td>
                                                        <?php echo number_format($firstTermNet); ?>
                                                        <?php if ($row->firsttermtransportwaiver > 0): ?>
                                                            <br><small style="background-color: #ffeb3b; padding: 2px 6px; border-radius: 3px; font-weight: bold;">(Incl Waiver: -<?php echo number_format($row->firsttermtransportwaiver); ?>)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo number_format($secondTermNet); ?>
                                                        <?php if ($row->secondtermtransportwaiver > 0): ?>
                                                            <br><small style="background-color: #ffeb3b; padding: 2px 6px; border-radius: 3px; font-weight: bold;">(Incl Waiver: -<?php echo number_format($row->secondtermtransportwaiver); ?>)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo number_format($thirdTermNet); ?>
                                                        <?php if ($row->thirdtermtransportwaiver > 0): ?>
                                                            <br><small style="background-color: #ffeb3b; padding: 2px 6px; border-radius: 3px; font-weight: bold;">(Incl Waiver: -<?php echo number_format($row->thirdtermtransportwaiver); ?>)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php 
                                                        $transportwaiver = htmlentities($row->transportwaiver);
                                                        if ($transportwaiver == 'Yes') {
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
                                                            <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                                                <?php if (has_permission($accounttype, 'edit_transportstageassigning')): ?>
                                                                    <li>
                                                                        <a href="#" data-toggle="modal" data-target="#editTransportModal<?php echo htmlentities($row->id); ?>">
                                                                            <i class="fa fa-pencil"></i> Edit
                                                                        </a>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    <li>
                                                                        <a href="#" data-toggle="modal" data-target="#transportWaiverModal<?php echo htmlentities($row->id); ?>">
                                                                            <i class="fa fa-percentage"></i> Waivers Edit
                                                                        </a>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    <li>
                                                                        <a href="manage-transportentries.php?delete=<?php echo htmlentities($row->id); ?>" onclick="return confirm('You want to delete the record?!!')" name="delete">
                                                                            <i class="fa fa-trash-o"></i> Delete
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                        
                                                        <!-- Edit Transport Entry Modal -->
                                                        <div class="modal fade" id="editTransportModal<?php echo htmlentities($row->id); ?>" tabindex="-1" role="dialog" aria-labelledby="editTransportModalLabel">
                                                            <div class="modal-dialog" style="max-width: 700px; width: 90%;" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                        <h2 class="modal-title" id="editTransportModalLabel">Edit Transport Entry</h2>
                                                                        <p>Name: <b><?php echo htmlentities($row->studentname); ?></b> (<?php echo htmlentities($row->studentadmno); ?>)</p>
                                                                    </div>
                                                                    <form method="post" action="manage-transportentries.php">
                                                                        <input type="hidden" name="id" value="<?php echo htmlentities($row->id); ?>">

                                                                        <table class="table table-bordered table-sm">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <th>Learner's AdmNo</th>
                                                                                    <td>
                                                                                        <input type="text" class="form-control" name="studentadmno" value="<?php echo htmlentities($row->studentadmno); ?>" required readonly>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Route</th>
                                                                                    <td>
                                                                                        <select class="form-control" name="stagefullname" required>
                                                                                            <?php
                                                                                            $sql = "SELECT stagefullname FROM transportstructure ORDER BY stagefullname";
                                                                                            $query = $dbh->prepare($sql);
                                                                                            $query->execute();
                                                                                            $stages = $query->fetchAll(PDO::FETCH_OBJ);

                                                                                            if ($query->rowCount() > 0) {
                                                                                                foreach ($stages as $stage) {
                                                                                                    $selected = ($stage->stagefullname == $row->stagefullname) ? 'selected' : '';
                                                                                                    echo '<option value="'.htmlentities($stage->stagefullname).'" '.$selected.'>'.htmlentities($stage->stagefullname).'</option>';
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Child Treatment</th>
                                                                                    <td>
                                                                                        <select class="form-control" name="childtreatment" required>
                                                                                            <?php
                                                                                            $sql = "SELECT childtreatment FROM childtreatmentrates ORDER BY childtreatment";
                                                                                            $query = $dbh->prepare($sql);
                                                                                            $query->execute();
                                                                                            $treatments = $query->fetchAll(PDO::FETCH_OBJ);

                                                                                            if ($query->rowCount() > 0) {
                                                                                                foreach ($treatments as $treatment) {
                                                                                                    $selected = ($treatment->childtreatment == $row->childtreatment) ? 'selected' : '';
                                                                                                    echo '<option value="'.htmlentities($treatment->childtreatment).'" '.$selected.'>'.htmlentities($treatment->childtreatment).'</option>';
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Transport Treatment</th>
                                                                                    <td>
                                                                                        <select class="form-control" name="transporttreatment" required>
                                                                                            <?php
                                                                                            $sql = "SELECT treatment FROM feetreatmentrates ORDER BY treatment";
                                                                                            $query = $dbh->prepare($sql);
                                                                                            $query->execute();
                                                                                            $treatments = $query->fetchAll(PDO::FETCH_OBJ);

                                                                                            if ($query->rowCount() > 0) {
                                                                                                foreach ($treatments as $treatment) {
                                                                                                    $selected = ($treatment->treatment == $row->transporttreatment) ? 'selected' : '';
                                                                                                    echo '<option value="'.htmlentities($treatment->treatment).'" '.$selected.'>'.htmlentities($treatment->treatment).'</option>';
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th>Terms Applied</th>
                                                                                    <td>
                                                                                        <div class="form-check">
                                                                                            <input type="checkbox" name="term[]" value="FirstTerm" <?php echo ($row->firsttermtransport > 0) ? 'checked' : ''; ?> class="form-check-input" id="firstTerm">
                                                                                            <label class="form-check-label" for="firstTerm">First Term</label>
                                                                                        </div>
                                                                                        <div class="form-check">
                                                                                            <input type="checkbox" name="term[]" value="SecondTerm" <?php echo ($row->secondtermtransport > 0) ? 'checked' : ''; ?> class="form-check-input" id="secondTerm">
                                                                                            <label class="form-check-label" for="secondTerm">Second Term</label>
                                                                                        </div>
                                                                                        <div class="form-check">
                                                                                            <input type="checkbox" name="term[]" value="ThirdTerm" <?php echo ($row->thirdtermtransport > 0) ? 'checked' : ''; ?> class="form-check-input" id="thirdTerm">
                                                                                            <label class="form-check-label" for="thirdTerm">Third Term</label>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                             <tr>
                                                                                    <td colspan="2">
                                                                                        <div class="alert alert-info mb-0 p-2" style="max-width: 100%; white-space: normal; word-wrap: break-word; word-break: break-word; line-height: 1.5;">
                                                                                            <i class="fa fa-info-circle me-1 text-primary"></i>
                                                                                            <small>
                                                                                                Note: Changing the route or treatments requires you to reopen the Fee Payment details of the learner to recalculate the transport charges for the selected terms.
                                                                                            </small>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>

                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Transport Waiver Edit Modal -->
                                                        <div class="modal fade" id="transportWaiverModal<?php echo htmlentities($row->id); ?>" tabindex="-1" role="dialog" aria-labelledby="transportWaiverModalLabel">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                        <h2 class="modal-title" id="transportWaiverModalLabel">Edit Transport Waivers</h2>
                                                                        Name: <b><?php echo htmlentities($row->studentname); ?></b>(<?php echo htmlentities($row->studentadmno); ?>), 
                                                                        Route: <b><?php echo htmlentities($row->stagefullname); ?></b>
                                                                    </div>
                                                                    <form method="post" action="manage-transportentries.php">
                                                                        <div class="modal-body">
                                                                            <input type="hidden" name="id" value="<?php echo htmlentities($row->id); ?>">
                                                                            <div class="form-group">
                                                                                <label>Waiver Status:</label>
                                                                                <select name="transportwaiver" class="form-control">
                                                                                    <option value="No" <?php echo ($row->transportwaiver == 'No') ? 'selected' : ''; ?>>No Waiver</option>
                                                                                    <option value="Yes" <?php echo ($row->transportwaiver == 'Yes') ? 'selected' : ''; ?>>Has Waiver</option>
                                                                                </select>
                                                                            </div>
                                                                            <table class="table table-bordered table-sm">
                                                                                <thead class="thead-light">
                                                                                    <tr>
                                                                                        <th>Term</th>
                                                                                        <th>Original Amount</th>
                                                                                        <th>Waiver Amount (Ksh)</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td>First Term</td>
                                                                                        <td><?php echo number_format($row->firsttermtransport); ?></td>
                                                                                        <td>
                                                                                            <div class="input-group">
                                                                                                <span class="input-group-addon">Ksh</span>
                                                                                                <input type="number" step="0.01" class="form-control" name="firsttermtransportwaiver" 
                                                                                                    value="<?php echo htmlentities($row->firsttermtransportwaiver); ?>" placeholder="Enter waiver amount">
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>Second Term</td>
                                                                                        <td><?php echo number_format($row->secondtermtransport); ?></td>
                                                                                        <td>
                                                                                            <div class="input-group">
                                                                                                <span class="input-group-addon">Ksh</span>
                                                                                                <input type="number" step="0.01" class="form-control" name="secondtermtransportwaiver" 
                                                                                                    value="<?php echo htmlentities($row->secondtermtransportwaiver); ?>" placeholder="Enter waiver amount">
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>Third Term</td>
                                                                                        <td><?php echo number_format($row->thirdtermtransport); ?></td>
                                                                                        <td>
                                                                                            <div class="input-group">
                                                                                                <span class="input-group-addon">Ksh</span>
                                                                                                <input type="number" step="0.01" class="form-control" name="thirdtermtransportwaiver" 
                                                                                                    value="<?php echo htmlentities($row->thirdtermtransportwaiver); ?>" placeholder="Enter waiver amount">
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        <div class="alert alert-warning mt-3 mb-0 p-3 d-flex align-items-center">
                                                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                            <small>Note: Waivers will be deducted from the total transport charges. <br> 
                                                                            Enter amounts carefully and ensure they don't exceed the original amounts.</small>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            <button type="submit" name="update_transport_waivers" class="btn btn-primary">Save Changes</button>
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
        </div>
    </div>

    <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
    // DataTables initialization
    $(document).ready(function() {
        $('#dataTable1').DataTable({
            "columnDefs": [
                { "orderable": false, "targets": [11] } // Disable sorting on Actions column
            ]
        });
    });
    
    function downloadCSV() {
        var table = $('#dataTable1').DataTable();
        var headers = [];
        var data = [];
        
        // Get headers (skip the last column - Actions)
        $('#dataTable1 thead th').each(function(index) {
            if (index < 11) { // Only take first 11 columns
                headers.push($(this).text());
            }
        });
        
        // Get data rows (skip the last column - Actions)
        table.rows().every(function() {
            var rowData = this.data();
            var rowArray = [];
            for (var i = 0; i < 11; i++) { // Only take first 11 columns
                // Remove HTML tags and extra spaces
                var cellContent = $(rowData[i]).text().trim() || rowData[i];
                cellContent = cellContent.replace(/\s+/g, ' '); // Replace multiple spaces with single space
                rowArray.push(cellContent);
            }
            data.push(rowArray);
        });
        
        // Convert to CSV
        var csvContent = "data:text/csv;charset=utf-8,";
        
        // Add headers
        csvContent += headers.join(",") + "\r\n";
        
        // Add data rows
        data.forEach(function(rowArray) {
            var row = rowArray.map(function(item) {
                // Escape double quotes and wrap in quotes if contains comma
                if (item.includes(',') || item.includes('"')) {
                    return '"' + item.replace(/"/g, '""') + '"';
                }
                return item;
            }).join(",");
            csvContent += row + "\r\n";
        });
        
        // Download CSV file
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "transport_entries.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
    
    <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>

    <script>
    function admnoAvailability() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "checkadmno.php",
            data:'studentadmno='+$("#studentadmno").val(),
            type: "POST",
            success:function(data){
                $("#user-availability-status1").html(data);
                $("#loaderIcon").hide();
            },
            error:function (){}
        });
    }
    </script>
</body>
</html>