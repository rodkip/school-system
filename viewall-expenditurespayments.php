<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Function to generate CSV
function generateCSV() {
    global $dbh;

    // Fetch filter values
    $payeename = isset($_GET['payeename']) ? $_GET['payeename'] : '';
    $financialyear = isset($_GET['financialyear']) ? $_GET['financialyear'] : '';

    // Modify SQL query to apply filters
    $sql = "SELECT ed.*, pd.payeename FROM expendituresdetails ed 
            LEFT JOIN payeedetails pd ON ed.payeeid = pd.payeeid 
            WHERE 1=1";
    
    if ($payeename) {
        $sql .= " AND pd.payeename LIKE :payeename";
    }

    if ($financialyear) {
        $sql .= " AND ed.financialyear = :financialyear";
    }

    $sql .= " ORDER BY ed.id DESC";

    $query = $dbh->prepare($sql);

    // Bind parameters if filters are applied
    if ($payeename) {
        $query->bindValue(':payeename', '%' . $payeename . '%');
    }

    if ($financialyear) {
        $query->bindValue(':financialyear', $financialyear);
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    $filename = "expenditures_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // Column headers
    fputcsv($output, ['#', 'PayeeId', 'PayeeName', 'Amount', 'Bank', 'Reference', 'Paymentdate', 'Votehead', 'Description', 'F/Year']);
    
    // Data rows
    $cnt = 1;
    foreach ($results as $row) {
        fputcsv($output, [
            $cnt++, 
            $row->payeeid, 
            $row->payeename, 
            number_format($row->amount), 
            $row->bank, 
            $row->reference, 
            $row->paymentdate, 
            $row->votehead, 
            $row->description, 
            $row->financialyear
        ]);
    }
    
    fclose($output);
    exit();
}

if (isset($_GET['download_csv'])) {
    generateCSV();
}

// Get all records for initial load
$allRecordsSql = "SELECT ed.*, pd.payeename FROM expendituresdetails ed 
                 LEFT JOIN payeedetails pd ON ed.payeeid = pd.payeeid 
                 ORDER BY ed.id DESC";
$allRecordsQuery = $dbh->prepare($allRecordsSql);
$allRecordsQuery->execute();
$allRecords = $allRecordsQuery->fetchAll(PDO::FETCH_OBJ);

// Get filtered records if filters are applied
$payeename = isset($_GET['payeename']) ? $_GET['payeename'] : '';
$financialyear = isset($_GET['financialyear']) ? $_GET['financialyear'] : '';

$filteredRecords = $allRecords;
if ($payeename || $financialyear) {
    $filterSql = "SELECT ed.*, pd.payeename FROM expendituresdetails ed 
                 LEFT JOIN payeedetails pd ON ed.payeeid = pd.payeeid 
                 WHERE 1=1";
    
    if ($payeename) {
        $filterSql .= " AND pd.payeename LIKE :payeename";
    }
    
    if ($financialyear) {
        $filterSql .= " AND ed.financialyear = :financialyear";
    }
    
    $filterSql .= " ORDER BY ed.id DESC";
    
    $filterQuery = $dbh->prepare($filterSql);
    
    if ($payeename) {
        $filterQuery->bindValue(':payeename', '%' . $payeename . '%');
    }
    
    if ($financialyear) {
        $filterQuery->bindValue(':financialyear', $financialyear);
    }
    
    $filterQuery->execute();
    $filteredRecords = $filterQuery->fetchAll(PDO::FETCH_OBJ);
}

// Get all unique financial years for the dropdown
$yearSql = "SELECT DISTINCT financialyear FROM expendituresdetails ORDER BY financialyear DESC";
$yearQuery = $dbh->prepare($yearSql);
$yearQuery->execute();
$allYears = $yearQuery->fetchAll(PDO::FETCH_OBJ);

// Get all unique payee names for the dropdown
$payeeSql = "SELECT DISTINCT payeename FROM payeedetails ORDER BY payeename ASC";
$payeeQuery = $dbh->prepare($payeeSql);
$payeeQuery->execute();
$allPayees = $payeeQuery->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Expenditures Payments</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Wait for window load
        $(window).load(function () {
            // Animate loader off screen
            $(".se-pre-con").fadeOut("slow");
        });

        $(document).ready(function () {
            // Initialize Select2 for both dropdowns
            $('#payeename').select2({
                placeholder: "All Payees",
                allowClear: true,
                width: '200px'
            });
            
            $('#financialyear').select2({
                placeholder: "All Years",
                allowClear: true,
                width: '150px'
            });

            // Initialize DataTable with all records
            var table = $('#dataTables-example').DataTable({
                "pageLength": 25,
                "dom": '<"top"f>rt<"bottom"lip><"clear">'
            });
            
            // Apply filters when dropdowns change
            $('#payeename, #financialyear').change(function() {
                var payeename = $('#payeename').val();
                var financialyear = $('#financialyear').val();
                
                // Build query string
                var queryParams = [];
                if (payeename) queryParams.push('payeename=' + encodeURIComponent(payeename));
                if (financialyear) queryParams.push('financialyear=' + encodeURIComponent(financialyear));
                
                // Reload page with filters
                if (queryParams.length > 0) {
                    window.location.href = '?' + queryParams.join('&');
                } else {
                    window.location.href = window.location.pathname;
                }
            });
            
            // Update download link when filters change
            $('#payeename, #financialyear').on('change', updateDownloadLink);
            
            // Initial update of download link
            updateDownloadLink();
            
            // Function to update CSV download link
            function updateDownloadLink() {
                var payeename = $('#payeename').val();
                var financialyear = $('#financialyear').val();
                
                var params = [];
                if (payeename) params.push('payeename=' + encodeURIComponent(payeename));
                if (financialyear) params.push('financialyear=' + encodeURIComponent(financialyear));
                params.push('download_csv=1');
                
                $('#download-csv').attr('href', '?' + params.join('&'));
            }
        });
    </script>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>
        <div id="page-wrapper">
            <br>
            <div class="row">
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                            <h2 class="page-header">
                            View All Expenditures Payments <i class="bi bi-cash-coin"></i> 
                            </h2>

                            </td>
                            <td>
                                <!-- Filter Form -->
                                <div class="form-inline">
                                    <div class="form-group" style="margin-right: 15px;">
                                    Filter to download specific data:
                                        <label for="payeename" style="margin-right: 5px;">Payee: </label>
                                        <select name="payeename" id="payeename" class="form-control">
                                            <option value=""></option>
                                            <?php foreach ($allPayees as $payee): ?>
                                                <option value="<?= htmlentities($payee->payeename) ?>" <?= ($payeename == $payee->payeename) ? 'selected' : '' ?>>
                                                    <?= htmlentities($payee->payeename) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="financialyear" style="margin-right: 5px;">Year: </label>
                                        <select name="financialyear" id="financialyear" class="form-control">
                                            <option value=""></option>
                                            <?php foreach ($allYears as $year): ?>
                                                <option value="<?= htmlentities($year->financialyear) ?>" <?= ($financialyear == $year->financialyear) ? 'selected' : '' ?>>
                                                    <?= htmlentities($year->financialyear) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td style="padding-left:20px;">
                                <!-- CSV Download Button with Filters -->
                                <a id="download-csv" href="#" class="btn btn-success">Download CSV</a>
                            </td>
                            <td style="padding-left:20px;"><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="panel panel-primary">
    <div class="panel-heading">
        Payment Records
    </div>
        <div class="panel-body">
        
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payee ID</th>
                                <th>Payee Name</th>
                                <th>Amount</th>
                                <th>Bank</th>
                                <th>Reference</th>
                                <th>Payment Date</th>
                                <th>Votehead</th>
                                <th>Description</th>
                                <th>F/Year</th>
                                <th>Captured By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cnt = 1;
                            foreach ($filteredRecords as $row) {
                                ?>
                                <tr>
                                    <td><?= htmlentities($cnt++) ?></td>
                                    <td><?= htmlentities($row->payeeid) ?></td>
                                    <td><?= htmlentities($row->payeename) ?></td>
                                    <td><?= number_format($row->amount) ?></td>
                                    <td><?= htmlentities($row->bank) ?></td>
                                    <td><?= htmlentities($row->reference) ?></td>
                                    <td><?= htmlentities($row->paymentdate) ?></td>
                                    <td><?= htmlentities($row->votehead) ?></td>
                                    <td><?= htmlentities($row->description) ?></td>
                                    <td><?= htmlentities($row->financialyear) ?></td>
                                    <td><?= htmlentities($row->cashier) ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Simulate table loading
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        document.getElementById("spinner").style.display = "none"; // Hide spinner
        document.getElementById("table-container").style.display = "block"; // Show table
      }, 3000); // Adjust delay as per actual loading time
    });
  </script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
</body>
</html>