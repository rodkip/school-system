<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
    || isset($_POST['update_all_balances'])) {

    header('Content-Type: text/plain');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');

    set_time_limit(0);
    ini_set('memory_limit', '512M');
    ini_set('output_buffering', 0);
    ini_set('zlib.output_compression', 0);

    function send_progress($message, $flush = true) {
        echo $message . "\n";
        if ($flush) {
            ob_flush();
            flush();
        }
    }

    try {
        $countSql = "SELECT COUNT(DISTINCT studentadmno) as total FROM studentdetails";
        $countQuery = $dbh->prepare($countSql);
        $countQuery->execute();
        $totalStudents = $countQuery->fetch(PDO::FETCH_OBJ)->total;

        send_progress("Found $totalStudents students to process...");

        $sql = "SELECT DISTINCT studentadmno FROM studentdetails ORDER BY studentadmno";
        $query = $dbh->prepare($sql);
        $query->execute();

        $updatedCount = 0;
        $processed = 0;

        while ($student = $query->fetch(PDO::FETCH_OBJ)) {
            $processed++;
            $searchadmno = $student->studentadmno;
            $percentage = round(($processed / $totalStudents) * 100);
            send_progress("PROGRESS: $processed / $totalStudents ({$percentage}%)");


            try {
                $yearlybal = 0;
                $arr = 0;

// Include the SQL query file
                require_once 'updatefeebalancesql.php';
                // Prepare the SQL query with votehead-based calculations
                $sql = getStudentFeeQuery();

                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_OBJ);

                foreach ($results as $row) {
                    $firstterm_total = ($row->firsttermfee + $row->firsttermtransport) - $row->firsttermtransportwaiver;
                    $secondterm_total = ($row->secondtermfee + $row->secondtermtransport) - $row->secondtermtransportwaiver;
                    $thirdterm_total = ($row->thirdtermfee + $row->thirdtermtransport) - $row->thirdtermtransportwaiver;

                    $adjusted = $yearlybal + $arr;
                    $remaining_payment = $row->totpayperyear;

                    $firstterm_net = max(0, $firstterm_total - $row->firsttermfeewaiver + $adjusted);
                    $firstterm_payment = min($remaining_payment, $firstterm_net);
                    $firstterm_balance = $firstterm_net - $firstterm_payment;
                    $remaining_payment -= $firstterm_payment;

                    $secondterm_net = max(0, $secondterm_total - $row->secondtermfeewaiver);
                    $secondterm_payment = min($remaining_payment, $secondterm_net);
                    $secondterm_balance = $secondterm_net - $secondterm_payment;
                    $remaining_payment -= $secondterm_payment;

                    $thirdterm_net = max(0, $thirdterm_total - $row->thirdtermfeewaiver);
                    $thirdterm_payment = min($remaining_payment, $thirdterm_net);
                    $thirdterm_balance = $thirdterm_net - $thirdterm_payment;

                    $firsttermbalcal = max(0, min($firstterm_balance, ($firstterm_total + $adjusted)));
                    $secondtermbalcal = max(0, min($secondterm_balance, $secondterm_total));
                    $thirdtermbalcal = max(0, min($thirdterm_balance, $thirdterm_total));

                    $yearlybal = $row->totcalfee - $row->totpayperyear +  $adjusted;

                    $feebalancecode = $row->gradefullname . $row->studentadmno;

                    $check_sql = "SELECT feebalancecode FROM feebalances WHERE feebalancecode = :feebalancecode";
                    $check_query = $dbh->prepare($check_sql);
                    $check_query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
                    $check_query->execute();

                    if ($check_query->rowCount() > 0) {
                        $update_sql = "UPDATE feebalances SET 
                            childstatus = :childstatus,
                            arrears = :arrears,
                            firsttermbal = :firsttermbalcal,
                            secondtermbal = :secondtermbalcal,
                            thirdtermbal = :thirdtermbalcal,
                            yearlybal = :balperyear,
                            feetreatment = :feetreatment,
                            childtreatment = :childtreatment,
                            studentname = :studentname,
                            gradefullname = :gradefullname,
                            totalfee = :totcalfee,
                            totalpaid = :totpayperyear,
                            firsttermfee = :firsttermfeecal,
                            secondtermfee = :secondtermfeecal,
                            thirdtermfee = :thirdtermfeecal,
                            othersfee = :othersfeecal,
                            boarding = :boarding
                            WHERE feebalancecode = :feebalancecode";

                        $update_query = $dbh->prepare($update_sql);
                        $update_query->execute([
                            ':feebalancecode' => $feebalancecode,
                            ':childstatus' => $row->childstatus,
                            ':arrears' => 0,
                            ':firsttermbalcal' => $firsttermbalcal,
                            ':secondtermbalcal' => $secondtermbalcal,
                            ':thirdtermbalcal' => $thirdtermbalcal,
                            ':balperyear' => $yearlybal,
                            ':feetreatment' => $row->feeTreatment,
                            ':childtreatment' => $row->childTreatment,
                            ':studentname' => $row->studentname,
                            ':gradefullname' => $row->gradefullname,
                            ':totcalfee' => $row->totcalfee,
                            ':totpayperyear' => $row->totpayperyear,
                            ':firsttermfeecal' => $row->firsttermfee,
                            ':secondtermfeecal' => $row->secondtermfee,
                            ':thirdtermfeecal' => $row->thirdtermfee,
                            ':othersfeecal' => $row->othersfee,
                            ':boarding' => $row->boarding
                        ]);
                    }
                    // INSERT logic would go here (if needed)
                }
            } catch (Exception $e) {
                send_progress("Error processing admission number {$searchadmno}: " . $e->getMessage());
            }
        }

        send_progress("All balances processed successfully.");

    } catch (Exception $e) {
        send_progress("Critical error: " . $e->getMessage());
    }

    exit();
}


// Handle delete actions for invalid records and orphaned balances
if (isset($_POST['delete_all_invalid_records'])) {            
    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Backup orphaned balances (those with no matching payment)
        $sql = "CREATE TABLE IF NOT EXISTS orphaned_balances_backup AS
                SELECT *
                FROM feebalances
                WHERE NOT EXISTS (
                    SELECT 1 
                    FROM feepayments 
                    WHERE feepayments.academicyear = LEFT(feebalances.feebalancecode, 4)
                      AND feepayments.studentadmno = feebalances.studentadmno
                )";
        $dbh->exec($sql);
        
        // Delete feebalances with invalid feebalancecode
        $sql1 = "DELETE FROM feebalances 
                 WHERE feebalancecode NOT IN (SELECT f.classentryfullname FROM Classentries f)";
        $dbh->exec($sql1);
        
        // Delete feebalances with missing studentadmno in studentdetails
        $sql2 = "DELETE fb
                 FROM feebalances fb
                 LEFT JOIN studentdetails sd ON fb.studentadmno = sd.studentadmno
                 WHERE sd.studentadmno IS NULL";
        $dbh->exec($sql2);
        
        // Delete orphaned balances (those with no matching payment)
        $sql3 = "DELETE FROM feebalances
                 WHERE NOT EXISTS (
                    SELECT 1 
                    FROM feepayments 
                    WHERE feepayments.academicyear = LEFT(feebalances.feebalancecode, 4)
                      AND feepayments.studentadmno = feebalances.studentadmno
                 )";
        $dbh->exec($sql3);

        // Backup and delete classentries with missing studentdetails
        $sql4_backup = "CREATE TABLE IF NOT EXISTS invalid_classentries_backup AS
                        SELECT *
                        FROM classentries ce
                        WHERE NOT EXISTS (
                            SELECT 1 FROM studentdetails sd
                            WHERE sd.studentadmno = ce.studentadmno
                        )";
        $dbh->exec($sql4_backup);

        $sql4_delete = "DELETE ce
                        FROM classentries ce
                        LEFT JOIN studentdetails sd ON ce.studentadmno = sd.studentadmno
                        WHERE sd.studentadmno IS NULL";
        $dbh->exec($sql4_delete);

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Data cleanup successfull!";
        
    } catch (PDOException $e) {
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "Error deleting records: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html>
   <head>
      <title>Kipmetz-SMS|Fee Balance Updating</title>
      <!-- Core CSS - Include with every page -->
      <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
      <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
      <link href="assets/css/style.css" rel="stylesheet" />
      <link href="assets/css/main-style.css" rel="stylesheet" />
      <!-- Page-Level CSS -->
      <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
      <style>
        /* Style the tab */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color:rgb(21, 149, 208);
            margin-bottom: 20px;
        }
        
        /* Style the buttons inside the tab */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 10px 16px;
            transition: 0.3s;
            font-size: 14px;
        }
        
        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #ddd;
        }
        
        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #4CAF50;
            color: white;
        }
        
        /* Style the tab content */
        .tabcontent {
            display: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-top: none;
            animation: fadeEffect 1s;
        }
        
        /* Go from zero to full opacity */
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
      </style>
      <script>
         function opentab(evt, tabName) {
         // Declare all variables
         var i, tabcontent, tablinks;
         
         // Get all elements with class="tabcontent" and hide them
         tabcontent = document.getElementsByClassName("tabcontent");
         for (i = 0; i < tabcontent.length; i++) {
         tabcontent[i].style.display = "none";
         }
         
         // Get all elements with class="tablinks" and remove the class "active"
         tablinks = document.getElementsByClassName("tablinks");
         for (i = 0; i < tablinks.length; i++) {
         tablinks[i].className = tablinks[i].className.replace(" active", "");
         }
         
         // Show the current tab, and add an "active" class to the button that opened the tab
         document.getElementById(tabName).style.display = "block";
         evt.currentTarget.className += " active";
         }
      </script>
      <script type="text/javascript">     
        function PrintDiv() {    
           var divToPrint = document.getElementById('divToPrint');
           var popupWin = window.open('', '_blank', 'width=500,height=500');
           popupWin.document.open();
           popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
            popupWin.document.close();
                }
      </script>
   </head>
   <body>
    <!-- wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php');?>
        <!-- end navbar top -->
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php');?>
        <!-- end navbar side -->
        <!-- page-wrapper -->
        <div id="page-wrapper">
            <div class="panel panel-primary">    
                <div id="page-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <br>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="page-header">
                                    Manage Fee Balances and Other Errors/Issues 
                                </h2>
                                <?php include_once('updatemessagepopup.php'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Fee Balance Update Panel -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                            
                            <form id="balanceForm">
                                <input type="hidden" name="update_all_balances" value="1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-refresh"></i> Update All Balances
                                </button>
                                This will update fee balances for all Learners in the system especially if there are changes on Fee/transports structures.
                            </form>

                          <!-- Tab links -->
                            <div class="tab" style="margin-top: 20px;">
                                <button class="tablinks btn btn-info" onclick="opentab(event, 'InvalidClassEntry')">
                                    <i class="fas fa-exclamation-circle me-2" style="color: #ffc107;"></i> Balances Missing ClassEntries
                                </button>
                                <button class="tablinks btn btn-info" onclick="opentab(event, 'MissingStudent')">
                                    <i class="fas fa-user-slash me-2" style="color: #17a2b8;"></i> Balances Missing StudentRecords
                                </button>
                                <button class="tablinks btn btn-info" onclick="opentab(event, 'MissingStudentclassentry')">
                                    <i class="fas fa-user-check me-2" style="color: #20c997;"></i> ClassEntry Missing Student Records
                                </button>
                                <button class="tablinks btn btn-info" onclick="opentab(event, 'OrphanedBalances')">
                                    <i class="fas fa-database me-2" style="color: #6f42c1;"></i> View Orphaned Balances
                                </button>
                                <button class="tablinks btn btn-danger" id="defaultOpen" onclick="opentab(event, 'DeleteActions')">
                                    <i class="fas fa-trash-alt me-2" style="color: #ffffff;"></i> Delete Actions
                                </button>
                            </div>


                            <!-- Tab content -->
                            <div id="InvalidClassEntry" class="tabcontent">
                                <h3>Fee Balances with Invalid ClassEntry</h3>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>FeeBalance Code</th>
                                                <th>Student AdmNo</th>
                                                <th>Student Name</th>
                                                <th>Grade Name</th>
                                                <th>Yearly Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT fb.* FROM feebalances fb 
                                                    WHERE fb.feebalancecode NOT IN (SELECT classentryfullname FROM classentries)";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt = 1;
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) { ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo htmlentities($row->feebalancecode ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->studentadmno ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->studentname ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->gradefullname ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->yearlybal ?? '-'); ?></td>
                                                    </tr>
                                                <?php $cnt++; }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="6" style="color:red; text-align:center;">No records found</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="MissingStudent" class="tabcontent">
                                <h3>Fee Balances with Missing Student Details</h3>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example2">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>FeeBalance Code</th>
                                                <th>Student AdmNo</th>
                                                <th>Student Name</th>
                                                <th>Grade Name</th>
                                                <th>Yearly Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT fb.* FROM feebalances fb 
                                                    LEFT JOIN studentdetails sd ON fb.studentadmno = sd.studentadmno
                                                    WHERE sd.studentadmno IS NULL";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt = 1;
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) { ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo htmlentities($row->feebalancecode ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->studentadmno ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->studentname ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->gradefullname ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->yearlybal ?? '-'); ?></td>
                                                    </tr>
                                                <?php $cnt++; }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="6" style="color:red; text-align:center;">No records found</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="MissingStudentclassentry" class="tabcontent">
                                <h3>ClassEntry with Missing Student Details</h3>
                                <div class="table-responsive">
                                 <table class="table table-striped table-bordered table-hover" id="dataTables-example2">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Gradefullname</th>
                                            <th>Student AdmNo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT ce.gradefullname, ce.studentadmno 
                                                FROM classentries ce
                                                LEFT JOIN studentdetails sd ON ce.studentadmno = sd.studentadmno
                                                WHERE sd.studentadmno IS NULL";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) { ?>
                                                <tr>
                                                    <td><?php echo $cnt; ?></td>
                                                    <td><?php echo htmlentities($row->gradefullname ?? '-'); ?></td>
                                                    <td><?php echo htmlentities($row->studentadmno ?? '-'); ?></td>
                                                </tr>
                                            <?php $cnt++; }
                                        } else { ?>
                                            <tr>
                                                <td colspan="3" style="color:red; text-align:center;">No records found</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                </div>
                            </div>

                            <div id="OrphanedBalances" class="tabcontent">
                                <h3>Orphaned Fee Balances (No Matching Payments)</h3>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example3">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>FeeBalance Code</th>
                                                <th>Student AdmNo</th>
                                                <th>Student Name</th>
                                                <th>Grade Name</th>
                                                <th>Yearly Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT fb.* FROM feebalances fb 
                                                    WHERE NOT EXISTS (
                                                        SELECT 1 
                                                        FROM feepayments 
                                                        WHERE feepayments.academicyear = LEFT(fb.feebalancecode, 4)
                                                        AND feepayments.studentadmno = fb.studentadmno
                                                    )";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt = 1;
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) { ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo htmlentities($row->feebalancecode ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->studentadmno ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->studentname ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->gradefullname ?? '-'); ?></td>
                                                        <td><?php echo htmlentities($row->yearlybal ?? '-'); ?></td>
                                                    </tr>
                                                <?php $cnt++; }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="6" style="color:red; text-align:center;">No records found</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="DeleteActions" class="tabcontent" style="display: block;">
                                <div class="card shadow-sm border border-danger p-4">
                                    <h3 class="text-danger mb-4">
                                        <i class="fas fa-broom me-2" style="color: #dc3545;"></i> Clean Data Actions
                                    </h3>
                                    <form id="deleteInvalidForm" action="manage-cleanup.php" method="POST">
                                        <div class="alert alert-warning mt-3">
                                            <h6><i class="fas fa-exclamation-triangle me-2 text-warning"></i> Data Integrity Warning</h6>
                                            <p>This operation will remove all records identified as invalid or orphaned:</p>
                                            <ul class="mb-2">
                                                <li><i class="fas fa-ban me-2 text-danger"></i> Fee balances without matching Class Entries</li>
                                                <li><i class="fas fa-user-slash me-2 text-danger"></i> Fee balances missing corresponding Student Details</li>
                                                <li><i class="fas fa-user-times me-2 text-danger"></i> Class Entries with no Student Details</li>
                                                <li><i class="fas fa-database me-2 text-danger"></i> Fee balances without matching payment transactions (orphaned)</li>
                                            </ul>
                                            <p class="mb-0">
                                                <strong><i class="fas fa-archive me-2 text-primary"></i> Note:</strong> Check on each tab the affected records before proceeding to delete.
                                            </p>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-danger mb-3" name="delete_all_invalid_records" value="1">
                                            <i class="fas fa-trash-alt me-2"></i> Delete All Invalid Records
                                        </button>
                                    </form>
                                </div>
                            </div>


                            <!-- Progress Display -->
                            <div id="update-progress" style="margin-top:15px; display:none;">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%">
                                        <span class="sr-only">0% Complete</span>
                                    </div>
                                </div>
                                <p id="status-message">Initializing update...</p>
                                <pre id="debug-output" style="height: 100px; overflow: auto;"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    $('#dataTables-example1').DataTable({
        "columnDefs": [
            { 
                "targets": "_all",
                "defaultContent": "-",
                "render": function (data, type, row) {
                    return data === null || data === undefined || data === '' ? '-' : data;
                }
            }
        ]
    });
    
    $('#dataTables-example2').DataTable({
        "columnDefs": [
            { 
                "targets": "_all",
                "defaultContent": "-",
                "render": function (data, type, row) {
                    return data === null || data === undefined || data === '' ? '-' : data;
                }
            }
        ]
    });
    
    $('#dataTables-example3').DataTable({
        "columnDefs": [
            { 
                "targets": "_all",
                "defaultContent": "-",
                "render": function (data, type, row) {
                    return data === null || data === undefined || data === '' ? '-' : data;
                }
            }
        ]
    });
    
    // Error handling for DataTables
    $.fn.dataTable.ext.errMode = 'none';
    
    $('#dataTables-example1').on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
    });
    
    $('#dataTables-example2').on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
    });
    
    $('#dataTables-example3').on('error.dt', function(e, settings, techNote, message) {
        console.log('DataTables error: ', message);
    });
});
      </script>
      <script>
         if (window.history.replaceState){
           window.history.replaceState(null,null,window.location.href);
         }
      </script>
      <script>
         document.getElementById("defaultOpen").click();
      </script>
      <script>
document.getElementById("balanceForm").addEventListener("submit", function (e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to update ALL student fee balances? This may take several minutes.')) return;

    const progressContainer = document.getElementById("update-progress");
    const progressBar = document.querySelector(".progress-bar");
    const statusMsg = document.getElementById("status-message");
    const debugOutput = document.getElementById("debug-output");

    progressContainer.style.display = "block";
    progressBar.style.width = "0%";
    statusMsg.innerText = "Initializing update...";
    debugOutput.textContent = "Starting update process...\n";

    // Show simulated progress
    let progress = 0;
    const interval = setInterval(() => {
        if (progress < 90) {
            progress += 5;
            progressBar.style.width = progress + "%";
            progressBar.querySelector("span").innerText = progress + "% Complete";
            statusMsg.innerText = `Updating... ${progress}%`;
        }
    }, 300);

    // Create form data
    const formData = new FormData(document.getElementById("balanceForm"));

    // Make proper AJAX request
    fetch(window.location.href, {
        method: "POST",
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(text || "Network response was not ok");
            });
        }
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        
        function readChunk() {
            return reader.read().then(({value, done}) => {
                if (done) {
                    clearInterval(interval);
                    progressBar.style.width = "100%";
                    progressBar.querySelector("span").innerText = "100% Complete";
                    statusMsg.innerText = "Update completed successfully.";
                    debugOutput.textContent += "\nUpdate completed successfully.";
                    return;
                }
                
                const text = decoder.decode(value);
                debugOutput.textContent += text + "\n";
                debugOutput.scrollTop = debugOutput.scrollHeight;
                
                if (text.startsWith("PROGRESS: ")) {
                    const newProgress = parseInt(text.replace("PROGRESS: ", ""));
                    if (!isNaN(newProgress)) {
                        progress = Math.max(progress, newProgress);
                        progressBar.style.width = progress + "%";
                        progressBar.querySelector("span").innerText = progress + "% Complete";
                        statusMsg.innerText = `Updating... ${progress}%`;
                    }
                } else {
                    statusMsg.innerText = text;
                }
                
                return readChunk();
            });
        }
        
        return readChunk();
    })
    .catch(error => {
        clearInterval(interval);
        statusMsg.innerText = "An error occurred during update: " + error.message;
        debugOutput.textContent += "\nERROR: " + error.message;
        console.error("Error:", error);
    });
});
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