<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$messagestate = false;
$mess = "";
$currentdate = date("Y");

// ADD NEW PAYEE
if (isset($_POST['submit'])) {
    try {
        $payeeid = $_POST['payeeid'];
        $payeename = $_POST['payeename'];
        $gender = $_POST['gender'];
        $postaladdress = $_POST['postaladdress'];
        $mobileno = $_POST['mobileno'];
        $proffession = $_POST['proffession'];
        $email = $_POST['email'];

        $checkSql = "SELECT COUNT(*) FROM payeedetails WHERE payeeid = :payeeid";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':payeeid', $payeeid, PDO::PARAM_STR);
        $checkQuery->execute();
        $existingPayee = $checkQuery->fetchColumn();

        if ($existingPayee > 0) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Duplicate IdNo. NOT Saved.";
        } else {
            $sql = "INSERT INTO payeedetails (payeeid, payeename, gender, postaladdress, mobileno, proffession, email) 
                    VALUES (:payeeid, :payeename, :gender, :postaladdress, :mobileno, :proffession, :email)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':payeeid', $payeeid, PDO::PARAM_STR);
            $query->bindParam(':payeename', $payeename, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':postaladdress', $postaladdress, PDO::PARAM_STR);
            $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $query->bindParam(':proffession', $proffession, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Payee Records CREATED successfully.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// UPDATE PAYEE
if (isset($_POST['update'])) {
    try {
        $payeename = $_POST['payeename'];
        $gender = $_POST['gender'];
        $postaladdress = $_POST['postaladdress'];
        $mobileno = $_POST['mobileno'];
        $proffession = $_POST['proffession'];
        $email = $_POST['email'];
        $id = $_POST['id'];

        $sql = "UPDATE payeedetails SET 
                payeename = :payeename, gender = :gender, postaladdress = :postaladdress, 
                mobileno = :mobileno, proffession = :proffession, email = :email 
                WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':payeename', $payeename, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':postaladdress', $postaladdress, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':proffession', $proffession, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Payee Records UPDATED successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// DELETE PAYEE
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $sql = "DELETE FROM payeedetails WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Payee Records DELETED successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// EXPORT CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=payees.csv');

    $output = fopen("php://output", "w");
    fputcsv($output, ['PayeeIdNo', 'Payee Name', 'Gender', 'Postal Address', 'Mobile No', 'Email', 'Proffession']);

    $sql = "SELECT * FROM payeedetails ORDER BY id DESC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        fputcsv($output, [
            $row['payeeid'],
            $row['payeename'],
            $row['gender'],
            $row['postaladdress'],
            $row['mobileno'],
            $row['email'],
            $row['proffession']
        ]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Payee Details</title>
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
        <div class="row" >
                <div class="col-lg-12" >
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Manage Payee Details <i class="fa fa-user-check"></i></h2>
                            </td>
                            <td>
                           <?php if (has_permission($accounttype, 'new_payee')): ?>
                            <?php include('newpayeepopup.php'); ?>
                            <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa fa-plus-circle"></i> New Payee</a>
                            <?php endif; ?>  
                            </td> 
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>  
                            <td>
                              <!-- Add CSV download button here -->
                                <a href="?download_csv=true" class="btn btn-info"><i class="fa fa-download"></i> Download CSV</a>
                           
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

        <div class="panel panel-primary">
        <div class="panel-heading">
            <i class="fa fa-user-check" aria-hidden="true"></i> Registered Payees
        </div>

    <div class="panel-body"> 
        <div class="table-responsive" style="overflow-x: auto; width: 100%">
          
            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Payee No</th>
                        <th>Payee Name</th>
                        <th>Gender</th>
                        <th>Postal Address</th>
                        <th>Mobile No</th>
                        <th>Email</th>
                        <th>Profession</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM payeedetails ORDER BY id DESC";
                    $query = $dbh->prepare($sql);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                    $cnt = 1;
                    if ($query->rowCount() > 0):
                        foreach ($results as $row):
                    ?>
                    <tr>
                        <td><?= $cnt; ?></td>
                        <td><?= htmlentities($row->payeeid); ?></td>
                        <td><?= htmlentities($row->payeename); ?></td>
                        <td><?= htmlentities($row->gender); ?></td>
                        <td><?= htmlentities($row->postaladdress); ?></td>
                        <td><?= htmlentities($row->mobileno); ?></td>
                        <td><?= htmlentities($row->email); ?></td>
                        <td><?= htmlentities($row->proffession); ?></td>
                        <td>
                            <div class="btn-group dropup">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                    Action <span class="caret"></span>
                                </button>
                                <?php if (has_permission($accounttype, 'edit_payee')): ?>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li>
                                        <a href="edit-payeedetails.php?editid=<?= urlencode($row->payeeid); ?>">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="?delete=<?= urlencode($row->id); ?>" 
                                           onclick="return confirm('You want to delete the record?!!');">
                                            <i class="fa fa-trash-o"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                        $cnt++;
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>

            <h4 class="mt-3">Total Payees Registered: <strong><span style="color:green"><?= $cnt - 1; ?></span></strong></h4>
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
    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
</body>
</html>
