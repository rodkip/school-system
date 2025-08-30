<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $eid = $_GET['editid'];  
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kipmetz-SMS | Update S-Details</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        .form-column {
            width: 48%;
            float: left;
            margin-right: 2%;
        }
        .form-column:last-child {
            margin-right: 0;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        @media (max-width: 768px) {
            .form-column {
                width: 100%;
                float: none;
                margin-right: 0;
            }
        }
        .term-fee-header {
            font-weight: bold;
            text-align: center;
            background-color: #f5f5f5;
        }
        .votehead-table th {
            vertical-align: middle;
            text-align: center;
        }
    </style>
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

        <!-- Page Wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- Page Header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Update Fee Structure</h1>
                </div>
                <!-- End Page Header -->
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">                           
                                    <form method="POST" enctype="multipart/form-data" action="manage-feestructure.php">
                                        <?php
                                        $sql = "SELECT * from feestructure where id=$eid";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                        ?>
                                                <div class="clearfix">
                                                    <input type="hidden" name="id" value="<?php echo $row->id ?>">
                                                    
                                                    <div class="form-column">
                                                        <div class="form-group">
                                                            <label for="gradefullname">Grade Fullname:</label>
                                                            <select name="gradefullname" class="form-control">
                                                                <option value="<?php echo $row->gradefullname; ?>"><?php echo $row->gradefullname; ?></option>
                                                                <?php
                                                                $sgl = "SELECT gradefullname as grd from classdetails";
                                                                $smt = $dbh->prepare($sgl);
                                                                $smt->execute();
                                                                $gradename = $smt->fetchAll(PDO::FETCH_ASSOC);
                                                                foreach ($gradename as $grdnm) {
                                                                ?>
                                                                    <option value="<?= $grdnm["grd"] ?>"><?= $grdnm["grd"] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="entryterm">Entry Term:</label>
                                                            <select name="entryterm" class="form-control">
                                                                <option value="<?php echo $row->entryterm; ?>"><?php echo $row->entryterm; ?></option>
                                                                <option value="FirstTerm">1st Term</option>
                                                                <option value="SecondTerm">2nd Term</option>
                                                                <option value="ThirdTerm">3rd Term</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="boarding">Boarding:</label>
                                                            <select name="boarding" class="form-control">
                                                                <option value="<?php echo $row->boarding; ?>"><?php echo $row->boarding; ?></option>
                                                                <option value="Day">Day</option>
                                                                <option value="Boarder">Boarder</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="othersfee">Others Fee:</label>
                                                            <input type="text" class="form-control" name="othersfee" id="othersfee" placeholder="Enter other fees" value="<?php echo $row->othersfee; ?>">
                                                        </div>
                                                    </div>

                                                    <div class="form-column">
                                                        <div class="form-group">
                                                            <label for="firsttermfee">First Term Fee:</label>
                                                            <input type="text" class="form-control" id="firsttermfee" name="firsttermfee" placeholder="Enter first term fee" value="<?php echo $row->firsttermfee; ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="secondtermfee">Second Term Fee:</label>
                                                            <input type="text" class="form-control" id="secondtermfee" name="secondtermfee" placeholder="Enter second term fee" value="<?php echo $row->secondtermfee; ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="thirdtermfee">Third Term Fee:</label>
                                                            <input type="text" class="form-control" id="thirdtermfee" name="thirdtermfee" placeholder="Enter third term fee" value="<?php echo $row->thirdtermfee; ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="totalfee">Total Fee:</label>
                                                            <input type="text" class="form-control" name="totalfee" id="totalfee" value="<?php echo $row->totalfee; ?>" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center" style="clear: both; margin-top: 20px;">
                                                    <button type="submit" name="submit_update" class="btn btn-primary">Submit</button>
                                                </div>
                                        <?php 
                                            }
                                        } ?>
                                    </form>                     
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Form Elements -->
                </div>
            </div>
        </div>
        <!-- End Page Wrapper -->
    </div>

    <!-- Core JavaScript -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>

    <script>
        // Update total fee based on the individual term fees
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', () => {
                const firstTerm = parseFloat(document.getElementById('firsttermfee').value) || 0;
                const secondTerm = parseFloat(document.getElementById('secondtermfee').value) || 0;
                const thirdTerm = parseFloat(document.getElementById('thirdtermfee').value) || 0;
                const totalFee = firstTerm + secondTerm + thirdTerm;
                document.getElementById('totalfee').value = totalFee.toFixed(2);
            });
        });
    </script>
</body>
</html>
