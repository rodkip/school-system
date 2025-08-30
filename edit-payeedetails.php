<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'] == 0)) {
    header('location:logout.php');
} else {

}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kipmetz-SMS|Update Payee Details</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>

<body>
    <!--  wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php'); ?>
        <!-- end navbar top -->

        <!-- navbar side -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- end navbar side -->

        <!--  page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Update Payee Details:</h1>
                </div>
                <!--end page header -->
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div>
                                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                                    </div>
                                    <form method="post" enctype="multipart/form-data" action="manage-payeedetails.php">
                                        <?php
                                        // Get the edit ID from the URL query string
                                        if (isset($_GET['editid'])) {
                                            $eid = $_GET['editid'];

                                            // Query to fetch the payee's details using the provided edit ID
                                            $sql = "SELECT * from payeedetails WHERE payeeidno = :eid";
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                            $query->execute();

                                            // Fetch the results
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) {
                                        ?>
                                        <div class="form-group">
                                            <input type="hidden" name="id" value="<?php echo $row->id ?>">

                                            <label for="payeeidno">PayeeNo:</label>
                                            <input type="text" name="payeeidno" id="payeeidno" required="required" placeholder="Enter Payee ID No here" value="<?php echo $row->payeeidno; ?>" class="form-control" readonly>

                                            <label for="payeename">Payee Name:</label>
                                            <input type="text" class="form-control" name="payeename" id="payeename" required="required" placeholder="Enter Payee name" value="<?php echo $row->payeename; ?>">

                                            <label for="gender">Gender:</label>
                                            <select name="gender" class="form-control">
                                                <option value="<?php echo $row->gender; ?>"><?php echo $row->gender; ?></option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>

                                            <label for="postaladdress">Postal Address:</label>
                                            <input type="text" class="form-control" name="postaladdress" id="postaladdress" placeholder="Enter Postal Address" value="<?php echo $row->postaladdress; ?>">

                                            <label for="mobileno">Mobile Number:</label>
                                            <input type="text" class="form-control" name="mobileno" id="mobileno" placeholder="Enter Mobile No" value="<?php echo $row->mobileno; ?>">

                                            <label for="proffession">Profession:</label>
                                            <input type="text" class="form-control" name="proffession" id="proffession" placeholder="Enter Profession" value="<?php echo $row->proffession; ?>">

                                            <label for="email">Email Address:</label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email Address" value="<?php echo $row->email; ?>">
                                        </div>
                                        <p style="padding-left: 450px">
                                            <button type="submit" class="btn btn-primary" name="update" id="submit">Update</button>
                                        </p>
                                        <?php
                                                } // end foreach
                                            } else {
                                                echo "No data found for this Payee ID.";
                                            }
                                        } else {
                                            echo "No Payee ID provided!";
                                        }
                                        ?>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                     <!-- End Form Elements -->
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

</body>

</html>
