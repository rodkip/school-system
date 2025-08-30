<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Function to generate CSV
function generateCSV() {
    global $dbh;
    $bank = $_GET['bank'] ?? '';
    $academicyear = $_GET['academicyear'] ?? '';

    $sql = "SELECT feepayments.*, studentdetails.studentname 
            FROM feepayments
            JOIN studentdetails ON feepayments.studentadmno = studentdetails.studentadmno
            WHERE 1=1";

    if ($bank) {
        $sql .= " AND bank LIKE :bank";
    }
    if ($academicyear) {
        $sql .= " AND academicyear = :academicyear";
    }

    $sql .= " ORDER BY entrydate DESC";
    $query = $dbh->prepare($sql);

    if ($bank) {
        $query->bindValue(':bank', '%' . $bank . '%');
    }
    if ($academicyear) {
        $query->bindValue(':academicyear', $academicyear);
    }

    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="fee_payments_' . date("Ymd_His") . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['#', 'AdmNo', 'Name', 'ReceiptNo', 'Amount', 'Payment Type', 'Bank', 'BankDate', 'ReceiptDate', 'Details', 'AcademicYear', 'Cashier', 'Trans Date', 'PrintStatus', 'PrintDate']);
    
    $cnt = 1;
    foreach ($results as $row) {
        fputcsv($output, [
            $cnt++, 
            $row->studentadmno, 
            $row->studentname, 
            $row->receiptno, 
            $row->cash, 
            $row->paymenttype, // Added payment type column
            $row->bank, 
            $row->bankpaymentdate, 
            $row->paymentdate, 
            $row->details, 
            $row->academicyear, 
            $row->cashier, 
            $row->entrydate,
            $row->printed ? 'Yes' : 'No',
            $row->print_date
        ]);
    }
    fclose($output);
    exit();
}

if (isset($_GET['download_csv'])) {
    generateCSV();
}

// Get all records for initial load
$allRecordsSql = "SELECT feepayments.*, studentdetails.studentname 
                 FROM feepayments
                 JOIN studentdetails ON feepayments.studentadmno = studentdetails.studentadmno
                 ORDER BY id DESC";
$allRecordsQuery = $dbh->prepare($allRecordsSql);
$allRecordsQuery->execute();
$allRecords = $allRecordsQuery->fetchAll(PDO::FETCH_OBJ);

// Get all unique banks for the dropdown
$bankSql = "SELECT DISTINCT bank FROM feepayments WHERE bank IS NOT NULL AND bank != '' ORDER BY bank ASC";
$allBanks = $dbh->query($bankSql)->fetchAll(PDO::FETCH_OBJ);

// Get all unique academic years for the dropdown
$yearSql = "SELECT DISTINCT academicyear FROM feepayments ORDER BY academicyear DESC";
$allYears = $dbh->query($yearSql)->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Fee Payments</title>
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
      
      .print-status-yes {
          color: #28a745;
      }
      
      .print-status-no {
          color: #dc3545;
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
                                <h1 class="page-header">Fee Payments</h1>
                            </td>
                            <td>
                                <button onclick="downloadCSV()" class="btn-download">Download Filtered CSV</button>
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
                                                            { id: "bankFilter", label: "Bank", color: "#FF5733" },
                                                            { id: "academicyearFilter", label: "Academic Year", color: "#33FF57" }
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
                                                
                                                <!-- Filter by Bank -->
                                                <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                                    <label for="bankFilter" style="font-size: 12px;">Filter by Bank:</label>
                                                    <select id="bankFilter" class="form-control" style="font-size: 12px; background-color: #d5c7dd;">
                                                        <option value="">All Banks</option>
                                                        <?php foreach ($allBanks as $bank): ?>
                                                        <option value="<?= htmlentities($bank->bank) ?>">
                                                            <?= htmlentities($bank->bank) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <!-- Filter by Academic Year -->
                                                <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                                    <label for="academicyearFilter" style="font-size: 12px;">Filter by Academic Year:</label>
                                                    <select id="academicyearFilter" class="form-control" style="font-size: 12px; background-color: #cdddc7;">
                                                        <option value="">All Years</option>
                                                        <?php foreach ($allYears as $year): ?>
                                                        <option value="<?= htmlentities($year->academicyear) ?>">
                                                            <?= htmlentities($year->academicyear) ?>
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
                                                    <th>Amount</th>
                                                    <th>Payment Type</th>
                                                    <th>Bank</th>
                                                    <th>BankDate</th>
                                                    <th>ReceiptDate</th>
                                                    <th>Details</th>
                                                    <th>AcademicYear</th>
                                                    <th>Cashier</th>
                                                    <th>Trans Date</th>
                                                    <th>PrintStatus</th>
                                                    <th>PrintDate</th>
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
                                                    <td><?= number_format($row->cash) ?></td>
                                                    <td><?= htmlentities($row->paymenttype) ?></td>
                                                    <td><?= htmlentities($row->bank) ?></td>
                                                    <td><?= htmlentities($row->bankpaymentdate) ?></td>
                                                    <td><?= htmlentities($row->paymentdate) ?></td>
                                                    <td><?= htmlentities($row->details) ?></td>
                                                    <td><?= htmlentities($row->academicyear) ?></td>
                                                    <td><?= htmlentities($row->cashier) ?></td>
                                                    <td><?= htmlentities($row->entrydate) ?></td>
                                                    <td class="<?= $row->printed ? 'print-status-yes' : 'print-status-no' ?>">
                                                        <?= $row->printed ? '<i class="fa fa-check-circle"></i> Yes' : '<i class="fa fa-times-circle"></i> No' ?>
                                                    </td>
                                                    <td><?= htmlentities($row->print_date) ?></td>
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
            var table = $('#dataTable').DataTable();

            // Apply bank filter
            $('#bankFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(6).search(filterValue).draw(); // Updated column index to 6 (was 5 before adding paymenttype)
            });
          
            // Apply academic year filter
            $('#academicyearFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(10).search(filterValue).draw(); // Updated column index to 10 (was 9 before adding paymenttype)
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
                // Format the print status for CSV
                rowData[13] = rowData[13].includes('Yes') ? 'Yes' : 'No'; // Updated index to 13 (was 12 before adding paymenttype)
                csvData.push(rowData);
            }

            // Convert the CSV data to a blob
            var csvContent = csvData.map(row => row.join(',')).join('\n');
            var blob = new Blob([csvContent], { type: 'text/csv' });

            // Create a link element and trigger a click event to download the CSV file
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'filtered_fee_payments.csv';
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