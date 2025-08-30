<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check session authentication
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// CSV Export Logic
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
   header('Content-Type: text/csv; charset=utf-8');
   header('Content-Disposition: attachment; filename=vehicle_hire_data.csv');
   
   $output = fopen('php://output', 'w');
   fputcsv($output, [
       '#', 'Bus', 'Driver', 'Date', 'Days', 'Description', 'From', 'To',
       'ChargeAgreed', 'ContactPerson', 'PhoneNo', 'FinancialYear',
       'DriverAllowance', 'FuelCost', 'OtherCost','Profit', 'ReferenceNo'
   ]);

   $sql = "SELECT * FROM vehicleshiredetails ORDER BY id DESC";
   $query = $dbh->prepare($sql);
   $query->execute();
   $results = $query->fetchAll(PDO::FETCH_OBJ);
   $cnt = 1;

   foreach ($results as $row) {
       fputcsv($output, [
           $cnt++,
           $row->vehiclenoplate,
           $row->driver,
           $row->tripdate,
           $row->tripdays,
           $row->tripdescription,
           $row->placefrom,
           $row->placeto,
           $row->chargeagreed,
           $row->contactpersonname,
           $row->contactpersonphoneno,
           $row->financialyear,
           $row->driverallowance,
           $row->fuelcost,
           $row->othercost,
           $row->vehiclehireprofit,
           $row->referenceno
       ]);
   }

   fclose($output);
   exit();
}

// Existing PHP logic continues...
$currentyear = date("Y");
$messagestate = '';
$mess = '';

   // Adding new record
   if (isset($_POST['submit'])) {
       try {
           // Sanitize and prepare data
           $fields = [
               'vehiclenoplate', 'driver', 'tripdate', 'tripdays', 'tripdescription',
               'placefrom', 'placeto', 'chargeagreed', 'contactpersonname',
               'contactpersonphoneno', 'financialyear', 'driverallowance',
               'fuelcost', 'othercost', 'referenceno'
           ];
           
           $data = [];
           foreach ($fields as $field) {
               $data[$field] = $_POST[$field] ?? '';
           }

           // Compute vehiclehireprofit
           $chargeAgreed = floatval($data['chargeagreed']);
           $driverAllowance = floatval($data['driverallowance']);
           $fuelCost = floatval($data['fuelcost']);
           $otherCost = floatval($data['othercost']);

           $vehicleHireProfit = $chargeAgreed - ($driverAllowance + $fuelCost + $otherCost);

           // Add vehiclehireprofit to fields and data
           $fields[] = 'vehiclehireprofit';
           $data['vehiclehireprofit'] = $vehicleHireProfit;

           // Prepare and execute SQL
           $sql = "INSERT INTO vehicleshiredetails (" . implode(',', $fields) . ") 
                   VALUES (:" . implode(',:', $fields) . ")";
           
           $stmt = $dbh->prepare($sql);
           foreach ($fields as $field) {
               $stmt->bindParam(':' . $field, $data[$field]);
           }

           $stmt->execute();

           $_SESSION['messagestate'] = 'added';
           $_SESSION['mess'] = "Records ADDED successfully.";

       } catch (PDOException $e) {
    
           $_SESSION['messagestate'] = 'deleted';
           $_SESSION['mess'] = "Error.";
       }
   }


    // Updating existing record
    elseif (isset($_POST['update_submit'])) {
        try {
            $id = $_POST['id'];
            
            $fields = [
                'vehiclenoplate', 'driver', 'tripdate', 'tripdays', 'tripdescription',
                'placefrom', 'placeto', 'chargeagreed', 'contactpersonname',
                'contactpersonphoneno', 'financialyear', 'driverallowance',
                'fuelcost', 'othercost', 'referenceno'
            ];
            
            $data = [];
            foreach ($fields as $field) {
                $data[$field] = $_POST['edit' . $field] ?? '';
            }

            // Build update query
            $updateParts = [];
            foreach ($fields as $field) {
                $updateParts[] = "$field = :$field";
            }
            
            $sql = "UPDATE vehicleshiredetails SET " . implode(', ', $updateParts) . " WHERE id = :id";
            
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            foreach ($fields as $field) {
                $stmt->bindParam(':' . $field, $data[$field]);
            }
            
            $stmt->execute();
            
            $messagestate = 'added';
            $mess = "Record Updated";
            
        } catch (PDOException $e) {
            $messagestate = 'error';
            $mess = "Error updating record: " . $e->getMessage();
        }
    }


// Handle record deletion
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        
        $sql = "DELETE FROM vehicleshiredetails WHERE id = :id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $messagestate = 'deleted';
        $mess = "Record Deleted";
        
    } catch (PDOException $e) {
        $messagestate = 'error';
        $mess = "Error deleting record: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Vehicles Hire</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <!-- Page-Level CSS -->
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    
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
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                            <h1 class="page-header">Vehicle Hire</h1>
                            </td>
                            <td>
                            <td>
                            <?php if (has_permission($accounttype, 'new_vehiclehire')): ?>
                            <form action="manage-vehicleshiredetails.php" method="POST">
                                            <?php include('newvehiclehireentrypopup.php'); ?>
                                            <a href="#myModal" data-toggle="modal" class="btn btn-primary">
                                                <i class="fa fa-plus-circle"></i> New Vehicle Hire Entry
                                            </a>
                                        </form>
                                        <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><a href="?export=csv" class="btn btn-warning" style="margin-left: 10px;">
                              <i class="fa fa-download"></i> Download CSV
                           </a>
                           </td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">School Bus Hire Details</div>
                           <div class="panel-body">
                           <div class="table-responsive" style="overflow-x: auto; width: 100%">
                <div id="table-wrapper">
                    <!-- Table loading animation -->
                <?php include('tableloadinganimation.php'); ?>
                  <!-- Table loading animation end-->
                  <div id="table-container" style="display: none;">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example1" style="font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;font-size:12px;">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Bus</th>
                                            <th>Driver</th>
                                            <th>Date</th>
                                            <th>Days</th>
                                            <th>Description</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>ChargeAgreed</th>
                                            <th>ContactPerson</th>
                                            <th>PhoneNo</th>  
                                            <th>FinancialYear</th>
                                            <th>DriverAllo</th>   
                                            <th>FuelCost</th>
                                            <th>OtherCost</th> 
                                            <th>Profit</th> 
                                            <th>ReferenceNo</th>   
                                            <th>Action</th>                              
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM vehicleshiredetails ORDER BY id DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if ($query->rowCount() > 0) {
                                            $cnt = 1;
                                            foreach ($results as $row) {
                                        ?>
                                        <tr>
                                            <td><?= htmlentities($cnt) ?></td>
                                            <td><?= htmlentities($row->vehiclenoplate) ?></td>
                                            <td><?= htmlentities($row->driver) ?></td>
                                            <td><?= htmlentities($row->tripdate) ?></td>
                                            <td><?= htmlentities($row->tripdays) ?></td>
                                            <td><?= htmlentities($row->tripdescription) ?></td>
                                            <td><?= htmlentities($row->placefrom) ?></td>
                                            <td><?= htmlentities($row->placeto) ?></td>
                                            <td><?= number_format($row->chargeagreed) ?></td>
                                            <td><?= htmlentities($row->contactpersonname) ?></td>
                                            <td><?= htmlentities($row->contactpersonphoneno) ?></td>
                                            <td><?= htmlentities($row->financialyear) ?></td>
                                            <td><?= number_format($row->driverallowance) ?></td>
                                            <td><?= number_format($row->fuelcost) ?></td>
                                            <td><?= number_format($row->othercost) ?></td>
                                            <td><?= number_format($row->vehiclehireprofit) ?></td>
                                            <td><?= htmlentities($row->referenceno) ?></td>
                                            <td style="padding: 5px">
                                                <form method="POST" enctype="multipart/form-data" action="manage-payrollentries.php">
                                                    <?php include('editpayrollentrypopup.php'); ?>
                                                </form>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                        Action <span class="caret"></span>
                                                    </button>                  
                                                    <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                                        <li>
                                                            <a href="#myModal<?= $row->id ?>" data-toggle="modal">
                                                                <i class="fa fa-pencil"></i>&nbsp;&nbsp;Edit
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>                  
                                                            <a href="manage-vehicleshiredetails.php?delete=<?= htmlentities($row->id) ?>" onclick="return confirm('You want to delete the record?!!')" name="delete">
                                                                <i class="fa fa-trash-o"></i>&nbsp;&nbsp;Delete
                                                            </a>
                                                        </li>
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
                    <!--End Advanced Tables -->
                </div>
            </div>
            <!-- end page-wrapper -->
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
            $('#dataTables-example1').dataTable();
            $('#dataTables-example2').dataTable();
            $('#dataTables-example3').dataTable();
        });
        
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        document.getElementById("defaultOpen").click();
        
        <?php if ($messagestate): ?>
            function hideMsg() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            document.getElementById("popup").style.visibility = "visible";
            window.setTimeout("hideMsg()", 5000);
        <?php endif; ?>
    </script>
</body>
</html>