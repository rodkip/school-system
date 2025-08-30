<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

$target_dir = "pfpics/";
$logo_filename = "schoollogo.png";
$logo_full_path = $target_dir . $logo_filename;

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    if (isset($_POST['submit'])) {
        try {
            $id = $_POST['id'];
            $schoolname = $_POST['schoolname'];
            $schoolcode = $_POST['schoolcode'];
            $postaladdress = $_POST['postaladdress'];
            $motto = $_POST['motto'];
            $phonenumber = $_POST['phonenumber'];
            $emailaddress = $_POST['emailaddress'];
            $logo_updated = false;

            // Handle logo upload
            if (!empty($_FILES["schoollogo"]["name"])) {
                $logo_tmp = $_FILES["schoollogo"]["tmp_name"];
                $logo_ext = strtolower(pathinfo($_FILES["schoollogo"]["name"], PATHINFO_EXTENSION));

                if ($logo_ext === "png") {
                    if (file_exists($logo_full_path)) {
                        unlink($logo_full_path); // Delete old logo
                    }
                    move_uploaded_file($logo_tmp, $logo_full_path);
                    $logo_updated = true;
                } else {
                    $_SESSION['messagestate'] = 'deleted';
                    $_SESSION['mess'] = "Only PNG files allowed for logo.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            }

            if ($logo_updated) {
                $sql = "UPDATE schooldetails SET schoolname=?, schoolcode=?, postaladdress=?, motto=?, phonenumber=?, emailaddress=?, logo=? WHERE id=?";
                $params = [$schoolname, $schoolcode, $postaladdress, $motto, $phonenumber, $emailaddress, $logo_full_path, $id];
            } else {
                $sql = "UPDATE schooldetails SET schoolname=?, schoolcode=?, postaladdress=?, motto=?, phonenumber=?, emailaddress=? WHERE id=?";
                $params = [$schoolname, $schoolcode, $postaladdress, $motto, $phonenumber, $emailaddress, $id];
            }

            $stmt = $dbh->prepare($sql);
            $stmt->execute($params);

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "School details updated successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | School Details</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        .logo-preview {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 1px solid #ccc;
            border-radius: 5px;
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
                                <h2 class="page-header"><i class="fa fa-building"></i> School Details</h2>
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
                        <div class="panel-heading">School Information - (You can update from here)</div>
                        <div class="panel-body">
                            <form method="post" enctype="multipart/form-data">
                                <?php
                                $sql = "SELECT * FROM schooldetails";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                if ($query->rowCount() > 0):
                                    foreach ($results as $row):
                                ?>
                                <input type="hidden" name="id" value="<?= $row->id ?>">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><label>School Name:</label></td>
                                            <td><input type="text" name="schoolname" value="<?= htmlentities($row->schoolname); ?>" class="form-control" required></td>
                                            <td><label>School Code:</label></td>
                                            <td><input type="text" name="schoolcode" value="<?= htmlentities($row->schoolcode); ?>" class="form-control"></td>
                                            <td><label>School Logo (PNG):</label></td>
                                        </tr>
                                        <tr>
                                            <td><label>Postal Address:</label></td>
                                            <td><input type="text" name="postaladdress" value="<?= htmlentities($row->postaladdress); ?>" class="form-control" required></td>
                                            <td><label>Motto:</label></td>
                                            <td><textarea name="motto" class="form-control" required><?= htmlentities($row->motto); ?></textarea></td>
                                            <td rowspan="3">
                                                <input type="file" name="schoollogo" accept="image/png" class="form-control">
                                                <?php if (file_exists($logo_full_path)): ?>
                                                    <p class="mt-2"><img src="<?= $logo_full_path ?>" alt="School Logo" class="logo-preview"></p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label>Phone Number:</label></td>
                                            <td><input type="text" name="phonenumber" value="<?= htmlentities($row->phonenumber); ?>" class="form-control" required></td>
                                            <td><label>Email Address:</label></td>
                                            <td><input type="email" name="emailaddress" value="<?= htmlentities($row->emailaddress); ?>" class="form-control"></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-center">
                                    <button type="submit" name="submit" class="btn btn-primary">Update</button>
                                </div>
                                <?php endforeach; endif; ?>
                            </form>
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
</body>
</html>
