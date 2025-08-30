<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check session validity
if (strlen($_SESSION['cpmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $mess = "";

    // Handle form submission
    if (isset($_POST['submit'])) {
        try {
            $payrollmonth = $_POST['payrollmonth'];        
            $payrollyear = $_POST['payrollyear'];
            $bank = $_POST['bank'];
            $chequeno = $_POST['chequeno'];
            $payrollserialno = $payrollmonth . "/" . $payrollyear . $bank;

            // Insert payroll details into the database
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO payrolldetails (payrollmonth, payrollyear, bank, chequeno, payrollserialno) 
                    VALUES ('$payrollmonth', '$payrollyear', '$bank', '$chequeno', '$payrollserialno')";
            $dbh->exec($sql);

            // Set success message
            $messagestate = 'added';
            $mess = "New record created...";
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Handle record deletion
    if (isset($_GET['delete'])) {
        try {
            $id = $_GET['delete'];

            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "DELETE FROM payrolldetails WHERE id = $id";
            $dbh->exec($sql);

            // Set deletion message
            $messagestate = 'deleted';
            $mess = "Record Deleted...";
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Handle record update
    if (isset($_POST['update_submit'])) {
        $id = $_POST['id'];
        $payrollmonth = $_POST['editpayrollmonth'];        
        $payrollyear = $_POST['editpayrollyear'];
        $bank = $_POST['editbank'];
        $chequeno = $_POST['editchequeno'];
        $payrollserialno = $payrollmonth . "/" . $payrollyear . $bank;

        $dbh->query("UPDATE payrolldetails SET payrollmonth = '$payrollmonth', 
                                                  payrollyear = '$payrollyear', 
                                                  bank = '$bank', 
                                                  chequeno = '$chequeno', 
                                                  payrollserialno = '$payrollserialno' 
                     WHERE id = $id") or die($dbh->error);

        // Set update message
        $messagestate = 'added';
        $mess = "Record Updated...";
    }

    $currentyear = date("Y");
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Kipmetz-SMS|Payroll List</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>

<body>
    <!-- Wrapper -->
    <div id="wrapper">
        <!-- Navbar Top -->
        <?php include_once('includes/header.php'); ?>
        <!-- End Navbar Top -->

        <!-- Navbar Side -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- End Navbar Side -->

        <!-- Page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- Page Header -->
                <div class="col-lg-12">
                    <!-- Messenger -->
                    <?php 
                    if ($messagestate == 'added') {
                        echo '<div class="popup" id="popup" style="background: green;"><i class="fa fa-check-circle"></i>&nbsp;&nbsp;' . $mess . '</div>';
                    } else {
                        echo '<div class="popup" id="popup" style="background: rgb(206, 69, 133);"><i class="fa fa-times"></i>&nbsp;&nbsp;' . $mess . '</div>';
                    }
                    ?>
                    <!-- End Messenger -->
                    <h2 class="page-header">Manage Payroll Entries Details</h2>
                </div>
                <!-- End Page Header -->
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- Advanced Tables -->
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <?php include('newpayrolldetailspopup.php'); echo $pay; ?>
                                        </form>
                                        <a href="#myModal" data-toggle="modal" class="btn btn-primary">
                                            <i class="fa fa-plus-circle"></i>&nbsp;&nbsp;New PayrollSerial
                                        </a>
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <br>
                                                <table class="table table-striped table-bordered table-hover" id="dataTables-example" style="font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>PayrollSerialNo</th>
                                                            <th>PayrollMonth</th>
                                                            <th>PayrollYear</th>
                                                            <th>Bank</th>  
                                                            <th>ChequeNo</th>
                                                            <th>Entries Count</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sql = "SELECT payrolldetails.payrollserialno, payrolldetails.payrollmonth, 
                                                               payrolldetails.payrollyear, payrolldetails.bank, 
                                                               payrolldetails.chequeno, payrolldetails.entrydate, 
                                                               payrolldetails.id, COUNT(payrollentriesdetails.staffidno) AS paycount 
                                                               FROM payrolldetails 
                                                               LEFT JOIN payrollentriesdetails 
                                                               ON payrolldetails.payrollserialno = payrollentriesdetails.payrollserialno 
                                                               GROUP BY payrolldetails.payrollserialno, payrolldetails.payrollmonth, 
                                                               payrolldetails.payrollyear, payrolldetails.bank, 
                                                               payrolldetails.chequeno, payrolldetails.entrydate, 
                                                               payrolldetails.id 
                                                               ORDER BY payrolldetails.id DESC";

                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                        $cnt = 1;
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $row) {
                                                        ?>
                                                                <tr>
                                                                    <td><?php echo htmlentities($cnt); ?></td>
                                                                    <td><?php echo htmlentities($row->payrollserialno); ?></td>
                                                                    <td><?php echo htmlentities($row->payrollmonth); ?></td>
                                                                    <td><?php echo htmlentities($row->payrollyear); ?></td>
                                                                    <td><?php echo htmlentities($row->bank); ?></td>
                                                                    <td><?php echo htmlentities($row->chequeno); ?></td> 
                                                                    <td><a href="manage-payrollentries.php?viewid=<?php echo htmlentities($row->payrollserialno); ?>"><?php echo htmlentities($row->paycount); ?></a></td>
                                                                    <td style="padding: 5px">
                                                                        <form method="POST" enctype="multipart/form-data" action="manage-payrolldetails.php">
                                                                            <?php include('editpayrolldetailspopup.php'); ?>
                                                                        </form>
                                                                        <div class="btn-group">
                                                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                                Action <span class="caret"></span>
                                                                            </button>                  
                                                                            <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                                                                <li><a href="#myModal<?php echo ($row->id); ?>" data-toggle="modal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Edit</a></li>
                                                                                <li class="divider"></li>
                                                                                <li><a href="manage-payrolldetails.php?delete=<?php echo htmlentities($row->id); ?>" onclick="return confirm('You want to delete the record?!!')" name="delete"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;Delete</a></li>
                                                                            </ul>
                                                                        </div>    
                                                                    </td>
                                                                </tr>
                                                        <?php 
                                                                $cnt++;
                                                            }
                                                        }
                                                        ?>
                                                        <h4> A total of <b><?php $count = $cnt - 1; echo "<span style='color:green'>$count</span>"; ?></b> payroll entries entered so far.</h4>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Page-wrapper -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Wrapper -->

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <!-- Page-Level Plugin Scripts -->
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
    <script>
        function payrollmonthAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "checkpayrollmonth.php",
                data: 'payrollmonth=' + $("#payrollmonth").val(),
                type: "POST",
                success: function(data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function () {}
            });
        }
    </script>  
    <?php
    if ($messagestate == 'added' || $messagestate == 'deleted') {
        echo '<script type="text/javascript">
            function hideMsg() {
                document.getElementById("popup").style.visibility="hidden";
            }
            document.getElementById("popup").style.visibility="visible";
            window.setTimeout("hideMsg()", 5000);
        </script>';
    }
    ?>

</body>

</html>
