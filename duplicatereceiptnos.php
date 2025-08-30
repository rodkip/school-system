<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kipmetz-SMS | Clean ReceiptNOs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
</head>
<body>
<div id="wrapper">
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header"><i class="fa fa-refresh"></i> Clean Receipt Numbers</h2>
            </div>
        </div>

        <div class="panel panel-primary">
            <div class="panel-heading">Duplicate Receipt Records</div>
            <div class="panel-body">
                <div class="table-responsive">
                <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
        <thead>
            <tr>
                <th>#</th>
                <th>Receipt No</th>
                <th>Adm No</th>
                <th>Cash</th>
                <th>Bank</th>
                <th>Bank Payment Date</th>
                <th>Receipt Date</th>
                <th>Academic Year</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT feepayments.receiptno, feepayments.studentadmno AS studentadmno, feepayments.cash, feepayments.bank, feepayments.bankpaymentdate, feepayments.paymentdate, feepayments.academicyear
                    FROM feepayments
                    WHERE feepayments.receiptno IN (
                        SELECT receiptno FROM feepayments AS Tmp GROUP BY receiptno HAVING COUNT(*) > 1
                    )
                    AND feepayments.cash > 0
                    ORDER BY feepayments.receiptno DESC";

            $query = $dbh->prepare($sql);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $cnt = 1;

            if ($query->rowCount() > 0) {
                foreach ($results as $row) {
                    echo "<tr>
                            <td>{$cnt}</td>
                            <td>{$row->receiptno}</td>
                            <td>{$row->studentadmno}</td>
                            <td>" . number_format($row->cash) . "</td>
                            <td>{$row->bank}</td>
                            <td>{$row->bankpaymentdate}</td>
                            <td>{$row->paymentdate}</td>
                            <td>{$row->academicyear}</td>
                        </tr>";
                    $cnt++;
                }
            } 
            ?>
        </tbody>
    </table>
</div>

<!-- Place message outside the table -->
<div class="text-info" style="margin-top: 10px;">
    <strong><?php echo ($cnt > 1) ? "There are " . ($cnt - 1) . " duplicate receipt numbers." : ""; ?></strong>
</div>

                </div>

                <!-- Record Count Notice -->
                <div class="alert alert-info mt-3">
                    <?php
                    $totalRecords = $cnt - 1;
                    echo "There " . ($totalRecords === 1 ? "is" : "are") . " <strong>{$totalRecords}</strong> duplicate receipt number" . ($totalRecords === 1 ? "" : "s") . " found.";
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
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

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
</script>
</body>
</html>
