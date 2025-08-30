<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Session validation
if (empty($_SESSION['cpmsaid'])) {
    header('Location: logout.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kipmetz-SMS | Update Parent Details</title>

    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/main-style.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <!-- Top and Side Navigation -->
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <!-- Main Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Update Parent Details</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Panel -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">

                                    <!-- Session Message -->
                                    <?php
                                    if (isset($_SESSION['message'])) {
                                        echo '<div>' . $_SESSION['message'] . '</div>';
                                        unset($_SESSION['message']);
                                    }
                                    ?>

                                    <form method="post" action="manage-parentdetails.php" enctype="multipart/form-data">
                                        <?php
                                        if (isset($_GET['editid'])) {
                                            $eid = intval($_GET['editid']); // Enforce integer type for security
                                            
                                            $sql = "SELECT * FROM parentdetails WHERE id = :eid";
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) {
                                        ?>
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($eid); ?>">

                                        <table class="table table-bordered">
                                            <tr>
                                                <td><label for="parentno">Parent No:</label></td>
                                                <td><input type="text" name="parentno" id="parentno" value="<?php echo htmlspecialchars($row->parentno); ?>" class="form-control" readonly></td>
                                            </tr>
                                            <tr>
                                                <td><label for="idno">ID No:</label></td>
                                                <td><input type="text" name="idno" id="idno" value="<?php echo htmlspecialchars($row->idno); ?>" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="parentname">Name:</label></td>
                                                <td><input type="text" name="parentname" id="parentname" value="<?php echo htmlspecialchars($row->parentname); ?>" class="form-control" required></td>
                                            </tr>
                                            <tr>
                                                <td><label for="parentcontact">Contact:</label></td>
                                                <td><input type="text" name="parentcontact" id="parentcontact" value="<?php echo htmlspecialchars($row->parentcontact); ?>" class="form-control" required></td>
                                            </tr>
                                            <tr>
                                                <td><label for="homearea">Home Area:</label></td>
                                                <td><input type="text" name="homearea" id="homearea" value="<?php echo htmlspecialchars($row->homearea); ?>" class="form-control"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="proffesion">Profession:</label></td>
                                                <td><input type="text" name="proffesion" id="proffesion" value="<?php echo htmlspecialchars($row->proffesion); ?>" class="form-control"></td>
                                            </tr>
                                        </table>

                                        <div class="text-center">
                                            <button type="submit" name="update" id="submit" class="btn btn-primary">Update</button>
                                        </div>

                                        <?php
                                                } // End foreach
                                            } else {
                                                echo '<div class="alert alert-warning">No parent record found.</div>';
                                            }
                                        } else {
                                            echo '<div class="alert alert-danger">No Parent ID provided!</div>';
                                        }
                                        ?>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Form Panel -->
                </div>
            </div>
        </div>
        <!-- End Main Content -->

    </div>
    <!-- End Wrapper -->

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
</body>

</html>
