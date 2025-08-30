<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Function to generate CSV (same as before)
function generateCSV() {
    global $dbh;
    $paymentmethod = $_GET['paymentmethod'] ?? '';
    $financialyear = $_GET['financialyear'] ?? '';
    $itemname = $_GET['itemname'] ?? '';

    $sql = "SELECT 
    c.`studentname`,
    a.`payment_id`,
    a.`item_id`,
    d.`otherpayitemname`,  -- Added from otherpayitems
    a.`amount`,
    a.`created_at` AS breakdown_created_at,
    b.`itemname`,
    b.`studentadmno`,
    b.`amount` AS payment_amount,
    b.`financialyear`,
    b.`receiptno`,
    b.`reference`,
    b.`paymentmethod`,
    b.`bankpaymentdate`,
    b.`details`,
    b.`entrydate`,
    b.`username`,
    b.`printed`,
    b.`print_date`,
    e.`gradefullname` -- Added from classentries
FROM 
    `otheritemspayments_breakdown` a
INNER JOIN 
    `otheritemspayments` b ON a.`payment_id` = b.`id`
INNER JOIN 
    `studentdetails` c ON b.`studentadmno` = c.`studentadmno`
INNER JOIN 
    `otherpayitems` d ON a.`item_id` = d.`id`  -- New JOIN for item name
LEFT JOIN 
    `classentries` e ON b.`studentadmno` = e.`studentadmno` AND 
    SUBSTRING(e.`gradefullname`, 1, 4) = b.`financialyear` -- Match first 4 chars of gradefullname with financialyear
WHERE 1";

    if ($paymentmethod) {
        $sql .= " AND paymentmethod LIKE :paymentmethod";
    }
    if ($financialyear) {
        $sql .= " AND financialyear = :financialyear";
    }
    if ($itemname) {
        $sql .= " AND itemname LIKE :itemname";
    }

    $sql .= " ORDER BY entrydate DESC";
    $query = $dbh->prepare($sql);

    if ($paymentmethod) {
        $query->bindValue(':paymentmethod', '%' . $paymentmethod . '%');
    }
    if ($financialyear) {
        $query->bindValue(':financialyear', $financialyear);
    }
    if ($itemname) {
        $query->bindValue(':itemname', '%' . $itemname . '%');
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="other_item_payments_' . date("Ymd_His") . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['#', 'AdmNo', 'Name', 'ReceiptNo', 'Amount', 'Bank', 'BankDate', 'ReceiptDate', 'Details', 'AcademicYear', 'Class', 'Cashier', 'Trans Date']);
    
    $cnt = 1;
    foreach ($results as $row) {
        fputcsv($output, [
            $cnt++, 
            $row->studentadmno, 
            $row->studentname, 
            $row->receiptno, 
            $row->amount, 
            $row->paymentmethod, 
            $row->bankpaymentdate, 
            $row->paymentdate, 
            $row->details, 
            $row->financialyear, 
            $row->gradefullname ?? 'N/A', // Added class/grade
            $row->username, 
            $row->entrydate
        ]);
    }
    fclose($output);
    exit();
}

if (isset($_GET['download_csv'])) {
    generateCSV();
}

// Get all records for initial load
$allRecordsSql = "SELECT 
    c.`studentname`,
    a.`payment_id`,
    a.`item_id`,
    d.`otherpayitemname`,  -- Added from otherpayitems
    a.`amount`,
    a.`created_at` AS breakdown_created_at,
    b.`itemname`,
    b.`studentadmno`,
    b.`amount` AS payment_amount,
    b.`financialyear`,
    b.`receiptno`,
    b.`reference`,
    b.`paymentmethod`,
    b.`bankpaymentdate`,
    b.`details`,
    b.`entrydate`,
    b.`username`,
    b.`printed`,
    b.`print_date`,
    e.`gradefullname` -- Added from classentries
FROM 
    `otheritemspayments_breakdown` a
INNER JOIN 
    `otheritemspayments` b ON a.`payment_id` = b.`id`
INNER JOIN 
    `studentdetails` c ON b.`studentadmno` = c.`studentadmno`
INNER JOIN 
    `otherpayitems` d ON a.`item_id` = d.`id`  -- New JOIN for item name
LEFT JOIN 
    `classentries` e ON b.`studentadmno` = e.`studentadmno` AND 
    SUBSTRING(e.`gradefullname`, 1, 4) = b.`financialyear` -- Match first 4 chars of gradefullname with financialyear
WHERE 1";
$allRecordsQuery = $dbh->prepare($allRecordsSql);
$allRecordsQuery->execute();
$allRecords = $allRecordsQuery->fetchAll(PDO::FETCH_OBJ);

// Get filter options for dropdowns
$paymentmethodSql = "SELECT DISTINCT paymentmethod FROM otheritemspayments WHERE paymentmethod IS NOT NULL AND paymentmethod != '' ORDER BY paymentmethod ASC";
$allBanks = $dbh->query($paymentmethodSql)->fetchAll(PDO::FETCH_OBJ);

$yearSql = "SELECT DISTINCT financialyear FROM otheritemspayments ORDER BY financialyear DESC";
$allYears = $dbh->query($yearSql)->fetchAll(PDO::FETCH_OBJ);

$itemnameSql = "SELECT DISTINCT otherpayitemname FROM otherpayitems WHERE otherpayitemname IS NOT NULL AND otherpayitemname != '' ORDER BY otherpayitemname ASC";
$allItems = $dbh->query($itemnameSql)->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Other Item Payments</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link rel="icon" href="images/tabpic.png">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
      #myTable.loading-overlay {
        position: relative;
      }

      #myTable.loading-overlay:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
      }

      #myTable.loading-overlay:after {
        content: "Loading...";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 1.2em;
        z-index: 10000;
      }

      @keyframes tickAnimation {
        0% {
          transform: scale(0);
          opacity: 0;
        }

        50% {
          transform: scale(1.2);
          opacity: 1;
        }

        100% {
          transform: scale(1);
          opacity: 1;
        }
      }

      .ticking-icon {
        animation: tickAnimation 0.5s ease-in-out;
      }
      /* Add margin between rows on small screens */
      @media (max-width: 767.98px) {
          .row > div {
              margin-bottom: 15px;
          }
      }
      
      /* Filter styling */
      .filter-container {
          background: #f8f9fa;
          padding: 15px;
          border-radius: 5px;
          margin-bottom: 20px;
      }
      
      .filter-label {
          font-weight: bold;
          margin-bottom: 5px;
          display: block;
      }
      
      .filter-select {
          margin-bottom: 10px;
      }
      
      .btn-download {
          background-color: #4e73df;
          color: white;
          border: none;
          padding: 8px 15px;
          border-radius: 4px;
          cursor: pointer;
      }
      
      .btn-download:hover {
          background-color: #2e59d9;
      }
      
      #selectedFilters {
          font-weight: bold;
          color: #4e73df;
          margin-bottom: 10px;
          display: inline-block;
      }
    </style>
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
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h1 class="page-header">Other Item Payments</h1>
                            </td>
                            <td>
                                <button onclick="downloadCSV()" class="btn-download">Download Filtered CSV</button>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="viewall-otheritemspaymentsanalysis.php" class="btn btn-success">
                                    <i class="fa fa-map-signs"></i> Analysis
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
                <!--end page header -->
            </div>
            <div class="panel panel-primary">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- Advanced Tables -->
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="form-group">
                                    <div class="container-fluid">
                                        <div class="row align-items-center">
                                            <div class="col-12 mb-3">
                                                <script>
                                                    document.addEventListener("DOMContentLoaded", function () {
                                                        const filters = [
                                                            { id: "paymentmethodFilter", label: "Payment Method", color: "#FF5733" },
                                                            { id: "financialyearFilter", label: "Financial Year", color: "#33FF57" },
                                                            { id: "itemnameFilter", label: "Item Name", color: "#3357FF" }
                                                        ];

                                                        function updateSelectedFilters() {
                                                            let selectedFilters = [];

                                                            filters.forEach(filter => {
                                                                const selectElement = document.getElementById(filter.id);
                                                                if (selectElement && selectElement.value !== "") {
                                                                    const filterText = `${filter.label}: ${selectElement.options[selectElement.selectedIndex].text}`;
                                                                    selectedFilters.push(`<span style="color: ${filter.color};">${filterText}</span>`);
                                                                }
                                                            });

                                                            document.getElementById("selectedFilters").innerHTML = selectedFilters.length > 0 ? selectedFilters.join(" | ") : "None";
                                                        }

                                                        // Attach event listener to all filter dropdowns
                                                        filters.forEach(filter => {
                                                            const selectElement = document.getElementById(filter.id);
                                                            if (selectElement) {
                                                                selectElement.addEventListener("change", updateSelectedFilters);
                                                            }
                                                        });

                                                        // Call once to initialize if filters are pre-selected
                                                        updateSelectedFilters();
                                                    });
                                                </script>

                                                <div>
                                                    <label style="font-size: 16px; font-weight: bold;">Selected Filters:</label>
                                                    <span id="selectedFilters" style="font-size: 16px; color: #004B6E; font-weight: bold;">None</span>
                                                </div>
                                                
                                                <!-- Filter by Payment Method -->
                                                <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                                    <label for="paymentmethodFilter" style="font-size: 12px;">Filter by Payment Method:</label>
                                                    <select id="paymentmethodFilter" class="form-control" style="font-size: 12px; background-color: #d5c7dd;">
                                                        <option value="">All Methods</option>
                                                        <?php foreach ($allBanks as $bank): ?>
                                                        <option value="<?= htmlentities($bank->paymentmethod) ?>">
                                                            <?= htmlentities($bank->paymentmethod) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <!-- Filter by Financial Year -->
                                                <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                                    <label for="financialyearFilter" style="font-size: 12px;">Filter by Financial Year:</label>
                                                    <select id="financialyearFilter" class="form-control" style="font-size: 12px; background-color: #cdddc7;">
                                                        <option value="">All Years</option>
                                                        <?php foreach ($allYears as $year): ?>
                                                        <option value="<?= htmlentities($year->financialyear) ?>">
                                                            <?= htmlentities($year->financialyear) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <!-- Filter by Item Name -->
                                                <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                                    <label for="itemnameFilter" style="font-size: 12px;">Filter by Item Name:</label>
                                                    <select id="itemnameFilter" class="form-control" style="font-size: 12px; background-color: #e0d5f0;">
                                                        <option value="">All Items</option>
                                                        <?php foreach ($allItems as $item): ?>
                                                        <option value="<?= htmlentities($item->otherpayitemname) ?>">
                                                            <?= htmlentities($item->otherpayitemname) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                <div id="table-wrapper">
                                    <!-- Table loading animation -->
                                <?php include('tableloadinganimation.php'); ?>  
                                <!-- Table loading animation end-->
                                <div id="table-container" style="display: none;">
                                    <br>
                                    <?php if (count($allRecords) > 0): ?>
                                        <table class="table table-striped table-bordered table-hover" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>AdmNo</th>
                                                    <th>Name</th>
                                                    <th>ReceiptNo</th>
                                                    <th>Item Name</th>
                                                    <th>Amount</th>
                                                    <th>Payment Method</th>
                                                    <th>Bank Date</th>
                                                    <th>Details</th>
                                                    <th>Academic Year</th>
                                                    <th>Class</th>
                                                    <th>Cashier</th>
                                                    <th>Trans Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $cnt = 1; ?>
                                                <?php foreach ($allRecords as $row): ?>
                                                <tr>
                                                    <td><?= $cnt++ ?></td>
                                                    <td><?= htmlentities($row->studentadmno) ?></td>
                                                    <td><?= htmlentities($row->studentname) ?></td>
                                                    <td><?= htmlentities($row->receiptno) ?></td>
                                                    <td><?= htmlentities($row->otherpayitemname) ?></td>
                                                    <td><?= htmlentities($row->amount) ?></td>
                                                    <td><?= htmlentities($row->paymentmethod) ?></td>
                                                    <td><?= htmlentities($row->bankpaymentdate) ?></td>
                                                    <td><?= htmlentities($row->details) ?></td>
                                                    <td><?= htmlentities($row->financialyear) ?></td>
                                                    <td><?= htmlentities($row->gradefullname ?? 'N/A') ?></td>
                                                    <td><?= htmlentities($row->username) ?></td>
                                                    <td><?= htmlentities($row->entrydate) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p>No records found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <!-- Page-Level Plugin Scripts-->
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": [0, 8, 10] } // Disable sorting for #, Details, and Cashier columns
                ]
            });

            // Apply payment method filter
            $('#paymentmethodFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(6).search(filterValue).draw();
            });
          
            // Apply financial year filter
            $('#financialyearFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(9).search(filterValue).draw();
            });

            // Apply item name filter
            $('#itemnameFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(4).search(filterValue).draw();
            });
        });
    </script>
    
    <script>
        function downloadCSV() {
            // Use DataTables API to get the filtered rows and column headers
            var table = $('#dataTable').DataTable();
            var header = table.columns().header().toArray().map(col => col.innerText);
            var rows = table.rows({ search: 'applied' }).data().toArray(); // Get only filtered rows
            var csvData = [];

            // Include column headers as the first row
            csvData.push(header);

            // Loop through filtered rows
            for (var i = 0; i < rows.length; i++) {
                var rowData = Object.values(rows[i]);
                csvData.push(rowData);
            }

            // Convert the CSV data to a blob
            var csvContent = csvData.map(row => row.join(',')).join('\n');
            var blob = new Blob([csvContent], { type: 'text/csv' });

            // Create a link element and trigger a click event to download the CSV file
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'filtered_other_item_payments.csv';
            link.click();
        }
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