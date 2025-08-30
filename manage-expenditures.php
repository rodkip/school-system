<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$mess = "";
$messagestate = '';
$financialyear = date("Y");
$currentdate = date("Y-m-d");

$payeeid = null;  // 🔁 Define once, for global accessibility

// Handle Search
if (isset($_POST['search_submit'])) {
    $payeeid = htmlspecialchars($_POST['payeeid']);
}

// Handle Fee Payment
if (isset($_POST['makepay_submit'])) {
    $payeeid = htmlspecialchars($_POST['payeeid']);
    $reference = htmlspecialchars($_POST['reference']);
    $bank = htmlspecialchars($_POST['bank']);
    $amount = htmlspecialchars($_POST['amount']);
    $paymentdate = htmlspecialchars($_POST['paymentdate']);
    $votehead = htmlspecialchars($_POST['votehead']);
    $description = htmlspecialchars($_POST['description']);
    $financialyear = htmlspecialchars($_POST['financialyear']);
    $cashier = htmlspecialchars($_POST['username']);

    try {
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO expendituresdetails (payeeid, reference, bank, amount, paymentdate, votehead, description, financialyear, cashier) 
                VALUES (:payeeid, :reference, :bank, :amount, :paymentdate, :votehead, :description, :financialyear, :cashier)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':payeeid', $payeeid, PDO::PARAM_STR);
        $query->bindParam(':reference', $reference, PDO::PARAM_STR);
        $query->bindParam(':bank', $bank, PDO::PARAM_STR);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':paymentdate', $paymentdate, PDO::PARAM_STR);
        $query->bindParam(':votehead', $votehead, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':financialyear', $financialyear, PDO::PARAM_STR);
        $query->bindParam(':cashier', $cashier, PDO::PARAM_STR);
        $query->execute();

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Records CREATED successfully.";
    } catch (PDOException $e) {
        $mess = "Error: " . $e->getMessage();
    }
}

// Handle Deletion
if (isset($_GET['delete'])) {
    try {
        $id = intval($_GET['delete']);
        $sql = "SELECT payeeid FROM expendituresdetails WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            foreach ($results as $row) {
                $payeeid = $row->payeeid;
            }
        }

        $sql = "DELETE FROM expendituresdetails WHERE id=:id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Record deleted successfully.";
    } catch (PDOException $e) {
        $mess = "Error: " . $e->getMessage();
    }
}

// ✅ Use $payeeid safely across your logic now
if ($payeeid !== null) {
    // e.g., load payee profile, audit log, etc.
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Expenditures</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <script>
        function opentab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
                    <style>
    /* Custom Styles */
    .btn-custom {
        font-weight: bold;
        font-size: 16px;
        padding: 10px 15px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    .btn-custom:hover {
        background-color: #0056b3;
    }
    .table-custom td {
        padding: 10px;
        font-size: 14px;
    }
    .table-custom th {
        padding: 10px;
        font-size: 16px;
        background-color: #f4f4f4;
        color: #333;
    }
    .text-danger {
        font-size: 14px;
        color: red;
    }
    .text-info {
        font-size: 18px;
        color: #17a2b8;
    }
</style>

</head>
<body>
    <div id="wrapper">
        <!-- Header and Sidebar -->
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper">
            <!-- Page Header -->
            <div class="row">
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Manage Expenditures <i class="fa fa-shopping-cart fa-fw"></i></h2>
                            </td>
                            <td style="padding-left:20px;"><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Panel Section -->
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Payee Search -->            
                            <table class="table-custom" width="90%">
                                <tr>
                                    <form action="manage-expenditures.php" method="POST">
                                        <td><label for="payeeid">PayeeId:</label></td>
                                        <td width="20%">
                                            <input type="text" class="form-control" name="payeeid" id="payeeid"
                                                placeholder="Enter Payee ID or Name here"
                                                list="payeedetails-list" autocomplete="off" required autofocus
                                                onBlur="validatePayeeIdNo()" 
                                                value="<?= $rlt->payeeid ?? '' ?>">
                                            <datalist id="payeedetails-list">
                                                <?php
                                                $smt = $dbh->prepare('SELECT payeeid, payeename FROM payeedetails ORDER BY payeeid DESC');
                                                $smt->execute();
                                                $data = $smt->fetchAll();
                                                $payees = [];
                                                foreach ($data as $rw): 
                                                    $payees[$rw["payeeid"]] = $rw["payeename"];
                                                ?>
                                                    <option value="<?= $rw["payeeid"] ?>"><?= $rw["payeeid"] ?> - <?= $rw["payeename"] ?></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                            <span id="displaypayeename" class="text-danger"></span>
                                        </td>
                                        <td>&nbsp;&nbsp;
                                            <button type="submit" name="search_submit" class="btn btn-custom btn-primary">
                                                <i class="fa fa-search"></i> Search
                                            </button>
                                        </td>
                                    </form>
                                    <?php 
                                    // Search by payeeid
                                    // Prepare and execute the query using PDO
                                    $stmt = $dbh->prepare("SELECT payeeid, payeename FROM payeedetails WHERE payeeid = :payeeid");
                                    $stmt->bindParam(':payeeid', $payeeid, PDO::PARAM_STR);
                                    $stmt->execute();

                                    // Fetch result
                                    $rlt = $stmt->fetch(PDO::FETCH_OBJ);
                                    ?>

                                    <!-- Display Section -->
                                    <?php if (isset($rlt)) { ?>
                                        <td>PayeeId: <span class="text-info"><?= $rlt->payeeid ?? '' ?></span></td>
                                        <td colspan="2">Name: <span class="text-info"><?= $rlt->payeename ?? '' ?></span></td>
                                    <?php } ?>

                                    <td>
                                        <?php include('peryearexpenditurespopup.php'); ?>                    
                                        <?php include('newexpenditurepopup.php'); ?>
                                        <?php include('viewpayeedetailspopup.php'); ?>
                                        <a href="#payeedetails" data-toggle="modal" class="btn btn-custom btn-primary">
                                            <i class="fa fa-eye"></i> View Payee Details
                                        </a>
                                    </td>
                                    <td>
                                    <?php if (has_permission($accounttype, 'new_expenditure')): ?>
                                        <a href="#makeexpenditure" data-toggle="modal" class="btn btn-custom btn-success">
                                            <i class="fa fa-credit-card"></i> Make Payment
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="#peryearexpenditures" data-toggle="modal" class="btn btn-custom btn-success">
                                        <i class="fa fa-print"></i> </i> Print PerYear Expenditures </a>
                                    </td>
                                </tr>
                            </table>

                            <table style="width:40%;"><tr></tr></table>
                        </div>
                    </div>
                </div>
            </div>

    
            <!-- Transactions Table -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-primary">
                        <div class="panel-heading">Expenditure Records by Payee</div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Reference</th>
                                                <th>Amount</th>
                                                <th>Bank</th>
                                                <th>Paymentdate</th>
                                                <th>Votehead</th>
                                                <th>Description</th>
                                                <th>F/Year</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                
                                            <?php 
                                            if (isset($payeeid)) {
                                                $sql = "SELECT * FROM expendituresdetails WHERE payeeid = :payeeid ORDER BY id DESC";
                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':payeeid', $payeeid, PDO::PARAM_STR);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                $cnt = 1;
                                                foreach ($results as $row): ?>
                                                    <tr>
                                                        <td><?= htmlentities($cnt++) ?></td>
                                                        <td><?= htmlentities($row->reference) ?></td>
                                                        <td><?= htmlentities($row->bank) ?></td>
                                                        <td><?= number_format($row->amount) ?></td>
                                                        <td><?= htmlentities($row->paymentdate) ?></td>
                                                        <td><?= htmlentities($row->votehead) ?></td>
                                                        <td><?= htmlentities($row->description) ?></td>
                                                        <td><?= htmlentities($row->financialyear) ?></td>
                                                        <td style="padding: 5px">
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                    Action <span class="caret"></span>
                                                                </button>
                                                                <?php if (has_permission($accounttype, 'edit_expenditure')): ?>
                                                                <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                                                    <li><a href="edit-expendituresdetails.php?editid=<?= htmlentities($row->id) ?>"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Edit</a></li>
                                                                    <li class="divider"></li>
                                                                    <li><a href="manage-expenditures.php?delete=<?= htmlentities($row->id) ?>" onclick="return confirm('You want to delete the record?!!')"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;Delete</a></li>
                                                                </ul>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach;
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
    </div>

    <!-- JS Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <script>
        $(document).ready(function () {
            $('#dataTables-example1').dataTable();
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        document.getElementById("defaultOpen").click();



        function admnoAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "checkadmno.php",
                data: 'payeeid=' + $("#payeeid").val(),
                type: "POST",
                success: function (data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                }
            });
        }
    </script>

    <?php if ($messagestate == 'added' || $messagestate == 'deleted'): ?>
        <script>
            function hideMsg() {
                document.getElementById("popup").style.visibility = "hidden";
            }
            document.getElementById("popup").style.visibility = "visible";
            window.setTimeout(hideMsg, 5000);
        </script>
    <?php endif; ?>
</body>
</html>