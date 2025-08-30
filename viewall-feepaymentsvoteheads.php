<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$votehead_filter = isset($_GET['votehead']) ? $_GET['votehead'] : '';
$academicyear_filter = isset($_GET['academicyear']) ? $_GET['academicyear'] : '';

// Handle CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=feepayments_voteheads_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, array('#', 'Name', 'AdmNo', 'PaymentID', 'Votehead', 'Amount', 'Year', 'Added On'));
    
    // Build query
    $sql = "SELECT f.id, f.payment_id, p.studentadmno, s.studentname, v.votehead, f.amount, p.academicyear, f.created_at
            FROM feepayment_voteheads f
            LEFT JOIN voteheads v ON f.votehead_id = v.id
            LEFT JOIN feepayments p ON f.payment_id = p.id
            LEFT JOIN studentdetails s ON p.studentadmno = s.studentadmno
            WHERE 1";

    if (!empty($votehead_filter)) {
        $sql .= " AND f.votehead_id = :votehead";
    }
    if (!empty($academicyear_filter)) {
        $sql .= " AND p.academicyear = :academicyear";
    }
    $sql .= " ORDER BY f.created_at DESC";

    $stmt = $dbh->prepare($sql);

    if (!empty($votehead_filter)) {
        $stmt->bindParam(':votehead', $votehead_filter);
    }
    if (!empty($academicyear_filter)) {
        $stmt->bindParam(':academicyear', $academicyear_filter);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    $cnt = 1;
    foreach ($results as $row) {
        fputcsv($output, array(
            $cnt++,
            $row->studentname,
            $row->studentadmno,
            $row->payment_id,
            $row->votehead,
            $row->amount,
            $row->academicyear,
            $row->created_at
        ));
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FeePayments/Votehead/Voteheads</title>

    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .filter-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-label {
            font-weight: bold;
            margin-right: 10px;
            color: #495057;
        }
        .filter-select {
            margin-right: 20px;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        #votehead {
            background-color: #e3f2fd;
        }
        #academicyear {
            background-color: #fff8e1;
        }
        .export-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .export-btn:hover {
            background-color: #218838;
        }
        .filter-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .filter-group {
            display: flex;
            align-items: center;
        }
    </style>

    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#votehead, #academicyear').select2({ allowClear: true, width: '200px' });

            $('#votehead, #academicyear').on('change', function () {
                const votehead = $('#votehead').val();
                const academicyear = $('#academicyear').val();
                const params = [];

                if (votehead) params.push('votehead=' + encodeURIComponent(votehead));
                if (academicyear) params.push('academicyear=' + encodeURIComponent(academicyear));

                window.location.href = params.length ? '?' + params.join('&') : window.location.pathname;
            });
            
            // Export button click handler
            $('.export-btn').click(function() {
                const votehead = $('#votehead').val();
                const academicyear = $('#academicyear').val();
                let exportUrl = '?export=1';
                
                if (votehead) exportUrl += '&votehead=' + encodeURIComponent(votehead);
                if (academicyear) exportUrl += '&academicyear=' + encodeURIComponent(academicyear);
                
                window.location.href = exportUrl;
            });
        });
    </script>
</head>
<body>
<div id="wrapper">
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <div id="page-wrapper">
        <div class="row mt-4">
            <div class="col-lg-12">
                <h2 class="page-header">View All FeePayments/Votehead <i class="bi bi-cash-coin"></i></h2>
                <div class="filter-container">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="votehead" class="filter-label">Votehead:</label>
                            <select id="votehead" class="form-control filter-select">
                                <option value="">All</option>
                                <?php
                                $stmt = $dbh->query("SELECT id, votehead FROM voteheads");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($row['id'] == $votehead_filter) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['votehead']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="academicyear" class="filter-label">Academic Year:</label>
                            <select id="academicyear" class="form-control filter-select">
                                <option value="">All</option>
                                <?php
                                $stmt = $dbh->query("SELECT DISTINCT academicyear FROM feepayments ORDER BY academicyear DESC");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($row['academicyear'] == $academicyear_filter) ? 'selected' : '';
                                    echo "<option value='{$row['academicyear']}' $selected>{$row['academicyear']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button class="export-btn">
                            <i class="fa fa-download"></i> Export to CSV
                        </button>
                       
                                <a href="viewall-feepaymentsvoteheadsanalysis.php" class="btn btn-success">
                                    <i class="fa fa-map-signs"></i> Analysis
                                </a>
                            
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">All FeePayments/Votehead</div>
                    <div class="panel-body">
                        <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                <div id="table-wrapper">
                                    <!-- Table loading animation -->
                                <?php include('tableloadinganimation.php'); ?>  
                                <!-- Table loading animation end-->
                                <div id="table-container" style="display: none;">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>AdmNo</th>
                                        <th>PaymentID</th>
                                        <th>Votehead</th>
                                        <th>Amount</th>
                                        <th>Year</th>
                                        <th>Added On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $sql = "SELECT f.id, f.payment_id, p.studentadmno, s.studentname, v.votehead, f.amount, p.academicyear, f.created_at
                                        FROM feepayment_voteheads f
                                        LEFT JOIN voteheads v ON f.votehead_id = v.id
                                        LEFT JOIN feepayments p ON f.payment_id = p.id
                                        LEFT JOIN studentdetails s ON p.studentadmno = s.studentadmno
                                        WHERE 1";

                                if (!empty($votehead_filter)) {
                                    $sql .= " AND f.votehead_id = :votehead";
                                }
                                if (!empty($academicyear_filter)) {
                                    $sql .= " AND p.academicyear = :academicyear";
                                }
                                $sql .= " ORDER BY f.created_at DESC";

                                $stmt = $dbh->prepare($sql);

                                if (!empty($votehead_filter)) {
                                    $stmt->bindParam(':votehead', $votehead_filter);
                                }
                                if (!empty($academicyear_filter)) {
                                    $stmt->bindParam(':academicyear', $academicyear_filter);
                                }

                                $stmt->execute();
                                $results = $stmt->fetchAll(PDO::FETCH_OBJ);

                                $cnt = 1;
                                if ($stmt->rowCount() > 0) {
                                    foreach ($results as $row) {
                                        echo '<tr>';
                                        echo '<td>' . htmlentities($cnt++) . '</td>';
                                        echo '<td>' . htmlentities($row->studentname) . '</td>';
                                        echo '<td>' . htmlentities($row->studentadmno) . '</td>';
                                        echo '<td>' . htmlentities($row->payment_id) . '</td>';
                                        echo '<td>' . htmlentities($row->votehead) . '</td>';
                                        echo '<td>' . htmlentities($row->amount) . '</td>';
                                        echo '<td>' . htmlentities($row->academicyear) . '</td>';
                                        echo '<td>' . htmlentities($row->created_at) . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo 'No matching records found.';
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
</script>
<script>
    // Simulate table loading
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        document.getElementById("spinner").style.display = "none"; // Hide spinner
        document.getElementById("table-container").style.display = "block"; // Show table
      }, 3000); // Adjust delay as per actual loading time
    });
  </script>
</body>
</html>