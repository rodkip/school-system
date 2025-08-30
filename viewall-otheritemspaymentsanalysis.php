<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Initialize filter variables
$financialYear = isset($_POST['financialYear']) ? $_POST['financialYear'] : '';
$paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : '';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

// Build the WHERE clause for filters
$whereClause = " WHERE 1";
if (!empty($financialYear)) {
    $whereClause .= " AND b.`financialyear` = :financialYear";
}
if (!empty($paymentMethod)) {
    $whereClause .= " AND b.`paymentmethod` = :paymentMethod";
}
if (!empty($startDate) && !empty($endDate)) {
    $whereClause .= " AND DATE(b.`entrydate`) BETWEEN :startDate AND :endDate";
}

// Get all records for analysis with filters
$allRecordsSql = "SELECT
    c.`studentname`,
    a.`payment_id`,
    a.`item_id`,
    d.`otherpayitemname`, 
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
    b.`print_date`
FROM
    `otheritemspayments_breakdown` a
INNER JOIN
    `otheritemspayments` b ON a.`payment_id` = b.`id`
INNER JOIN
    `studentdetails` c ON b.`studentadmno` = c.`studentadmno`
INNER JOIN
    `otherpayitems` d ON a.`item_id` = d.`id`
$whereClause";

$allRecordsQuery = $dbh->prepare($allRecordsSql);

// Bind parameters if filters are set
if (!empty($financialYear)) {
    $allRecordsQuery->bindParam(':financialYear', $financialYear, PDO::PARAM_STR);
}
if (!empty($paymentMethod)) {
    $allRecordsQuery->bindParam(':paymentMethod', $paymentMethod, PDO::PARAM_STR);
}
if (!empty($startDate) && !empty($endDate)) {
    $allRecordsQuery->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $allRecordsQuery->bindParam(':endDate', $endDate, PDO::PARAM_STR);
}

$allRecordsQuery->execute();
$allRecords = $allRecordsQuery->fetchAll(PDO::FETCH_OBJ);

// Get distinct values for filter dropdowns
$financialYearsQuery = $dbh->query("SELECT DISTINCT financialyear FROM otheritemspayments ORDER BY financialyear DESC");
$financialYears = $financialYearsQuery->fetchAll(PDO::FETCH_OBJ);

$paymentMethodsQuery = $dbh->query("SELECT DISTINCT paymentmethod FROM otheritemspayments");
$paymentMethods = $paymentMethodsQuery->fetchAll(PDO::FETCH_OBJ);

// Calculate totals for summary cards
$totalAmount = 0;
$totalPayments = count($allRecords);
$paymentMethodsCount = [];
$itemsCount = [];

foreach ($allRecords as $record) {
    $totalAmount += $record->amount;
    
    // Count payment methods
    if (!isset($paymentMethodsCount[$record->paymentmethod])) {
        $paymentMethodsCount[$record->paymentmethod] = 0;
    }
    $paymentMethodsCount[$record->paymentmethod]++;
    
    // Count items
    if (!isset($itemsCount[$record->otherpayitemname])) {
        $itemsCount[$record->otherpayitemname] = 0;
    }
    $itemsCount[$record->otherpayitemname]++;
}

// Prepare data for monthly trends chart
$monthlyData = [];
foreach ($allRecords as $record) {
    $monthYear = date('Y-m', strtotime($record->entrydate));
    if (!isset($monthlyData[$monthYear])) {
        $monthlyData[$monthYear] = 0;
    }
    $monthlyData[$monthYear] += $record->amount;
}

// Prepare data for top students chart
$studentData = [];
foreach ($allRecords as $record) {
    if (!isset($studentData[$record->studentadmno])) {
        $studentData[$record->studentadmno] = [
            'name' => $record->studentname,
            'amount' => 0
        ];
    }
    $studentData[$record->studentadmno]['amount'] += $record->amount;
}

// Sort students by amount and get top 10
usort($studentData, function($a, $b) {
    return $b['amount'] - $a['amount'];
});
$topStudents = array_slice($studentData, 0, 10);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Other Item Payments Analysis</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link rel="icon" href="images/tabpic.png">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        .graph-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 15px;
        }
        .chart-container {
            width: 48%;
            margin-bottom: 20px;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 380px; /* Fixed height for all charts */
            display: flex;
            flex-direction: column;
        }
        .chart-container h4 {
            margin: 0 0 10px 0;
            padding: 0;
            font-size: 16px;
            font-weight: 600;
            color: #4e73df;
        }
        .chart-container canvas {
            flex-grow: 1;
            width: 100% !important;
            height: calc(100% - 25px) !important;
        }
        .summary-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .summary-card h3 {
            margin: 10px 0 5px 0;
            color: #5a5c69;
            font-size: 14px;
            font-weight: 600;
        }
        .summary-card .value {
            font-size: 22px;
            font-weight: 700;
            color: #2e59d9;
            margin-bottom: 5px;
        }
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: white;
            font-size: 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .card-icon.bg-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }
        .card-icon.bg-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }
        .card-icon.bg-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }
        .card-icon.bg-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }
        .filter-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .panel-heading {
            padding: 10px 15px;
            font-size: 16px;
            font-weight: 600;
        }
        .panel-body {
            padding: 15px;
        }
        @media (max-width: 768px) {
            .chart-container {
                width: 100%;
            }
            .summary-card {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php'); ?>
        <!-- end navbar top -->
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- end navbar side -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Other Item Payments Analysis</h1>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="panel panel-primary">
                <div class="panel-heading">Filters</div>
                <div class="panel-body">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Financial Year</label>
                                    <select class="form-control" name="financialYear">
                                        <option value="">All Years</option>
                                        <?php foreach ($financialYears as $year): ?>
                                            <option value="<?php echo $year->financialyear; ?>" <?php echo ($year->financialyear == $financialYear) ? 'selected' : ''; ?>>
                                                <?php echo $year->financialyear; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select class="form-control" name="paymentMethod">
                                        <option value="">All Methods</option>
                                        <?php foreach ($paymentMethods as $method): ?>
                                            <option value="<?php echo $method->paymentmethod; ?>" <?php echo ($method->paymentmethod == $paymentMethod) ? 'selected' : ''; ?>>
                                                <?php echo $method->paymentmethod; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="text" class="form-control datepicker" name="startDate" value="<?php echo $startDate; ?>" placeholder="Select start date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="text" class="form-control datepicker" name="endDate" value="<?php echo $endDate; ?>" placeholder="Select end date">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="viewall-otheritemspaymentsanalysis.php" class="btn btn-default">Reset Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="card-icon bg-primary">
                            <i class="fa fa-money"></i>
                        </div>
                        <h3>Total Amount</h3>
                        <div class="value">Ksh <?php echo number_format($totalAmount, 2); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="card-icon bg-success">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <h3>Total Payments</h3>
                        <div class="value"><?php echo $totalPayments; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="card-icon bg-info">
                            <i class="fa fa-payment"></i>
                        </div>
                        <h3>Payment Methods</h3>
                        <div class="value"><?php echo count($paymentMethodsCount); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card">
                        <div class="card-icon bg-warning">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <h3>Items Paid</h3>
                        <div class="value"><?php echo count($itemsCount); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="panel panel-primary">
                <div class="panel-heading">Payment Analysis Charts</div>
                <div class="panel-body">
                    <div class="graph-container">
                        <!-- Pie Chart for Payment Method Distribution -->
                        <div class="chart-container">
                            <h4>Payment Method Distribution</h4>
                            <canvas id="paymentMethodPieChart"></canvas>
                        </div>

                        <!-- Bar Chart for Amount by Item -->
                        <div class="chart-container">
                            <h4>Amount by Item</h4>
                            <canvas id="amountByItemBarChart"></canvas>
                        </div>
                        
                        <!-- Monthly Trends Chart -->
                        <div class="chart-container">
                            <h4>Monthly Payment Trends</h4>
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                        
                        <!-- Top Students Chart -->
                        <div class="chart-container">
                            <h4>Top 10 Students by Payments</h4>
                            <canvas id="topStudentsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    
    <!-- Chart.js for Graphs -->
    <script>
        // Initialize datepicker
        $(document).ready(function(){
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
        });

        // Pie Chart for Payment Method Distribution
        var paymentMethodData = <?php echo json_encode($allRecords); ?>;
        var paymentMethodCounts = {};
        paymentMethodData.forEach(function(record) {
            if (paymentMethodCounts[record.paymentmethod]) {
                paymentMethodCounts[record.paymentmethod]++;
            } else {
                paymentMethodCounts[record.paymentmethod] = 1;
            }
        });

        var paymentMethodLabels = Object.keys(paymentMethodCounts);
        var paymentMethodValues = Object.values(paymentMethodCounts);

        var ctx1 = document.getElementById('paymentMethodPieChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: paymentMethodLabels,
                datasets: [{
                    data: paymentMethodValues,
                    backgroundColor: [
                        '#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#33FFF6',
                        '#FFC300', '#C70039', '#900C3F', '#581845', '#DAF7A6'
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' (' + 
                                    Math.round(tooltipItem.raw / paymentMethodData.length * 100) + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Bar Chart for Amount by Item
        var itemAmountData = {};
        paymentMethodData.forEach(function(record) {
            var itemName = record.otherpayitemname;
            if (!itemAmountData[itemName]) {
                itemAmountData[itemName] = 0;
            }
            itemAmountData[itemName] += parseFloat(record.amount);
        });

        var itemLabels = Object.keys(itemAmountData);
        var itemAmounts = Object.values(itemAmountData);

        var ctx2 = document.getElementById('amountByItemBarChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: itemLabels,
                datasets: [{
                    label: 'Total Amount (Ksh)',
                    data: itemAmounts,
                    backgroundColor: '#4e73df',
                    borderColor: '#2e59d9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (Ksh)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Payment Items'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return 'Ksh ' + tooltipItem.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Monthly Trends Chart
        var monthlyData = <?php echo json_encode($monthlyData); ?>;
        var monthlyLabels = Object.keys(monthlyData).sort();
        var monthlyValues = monthlyLabels.map(function(month) {
            return monthlyData[month];
        });

        var ctx3 = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Payments (Ksh)',
                    data: monthlyValues,
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointHoverBorderColor: '#fff',
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (Ksh)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month-Year'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return 'Ksh ' + tooltipItem.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Top Students Chart
        var topStudentsData = <?php echo json_encode($topStudents); ?>;
        var studentLabels = topStudentsData.map(function(student) {
            return student.name;
        });
        var studentAmounts = topStudentsData.map(function(student) {
            return student.amount;
        });

        var ctx4 = document.getElementById('topStudentsChart').getContext('2d');
        new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: studentLabels,
                datasets: [{
                    label: 'Total Paid (Ksh)',
                    data: studentAmounts,
                    backgroundColor: '#1cc88a',
                    borderColor: '#17a673',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (Ksh)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Students'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return 'Ksh ' + tooltipItem.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>