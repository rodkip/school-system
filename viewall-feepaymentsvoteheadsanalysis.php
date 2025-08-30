<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Redirect if not logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Get filter values
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';
$selectedVotehead = isset($_GET['votehead']) ? $_GET['votehead'] : '';

// Base SQL query
$sql = "SELECT 
            v.votehead, 
            SUM(f.amount) as total_amount,
            COUNT(f.id) as payment_count,
            p.academicyear
        FROM feepayment_voteheads f
        LEFT JOIN voteheads v ON f.votehead_id = v.id
        LEFT JOIN feepayments p ON f.payment_id = p.id
        WHERE 1=1";

// Add filters if selected
if (!empty($selectedYear)) {
    $sql .= " AND p.academicyear = :year";
}
if (!empty($selectedVotehead)) {
    $sql .= " AND v.votehead = :votehead";
}

$sql .= " GROUP BY v.votehead, p.academicyear
          ORDER BY total_amount DESC";

$query = $dbh->prepare($sql);

// Bind parameters if filters are set
if (!empty($selectedYear)) {
    $query->bindParam(':year', $selectedYear, PDO::PARAM_STR);
}
if (!empty($selectedVotehead)) {
    $query->bindParam(':votehead', $selectedVotehead, PDO::PARAM_STR);
}

$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Prepare data for charts
$voteheads = [];
$amounts = [];
$years = [];
$yearlyData = [];

foreach ($results as $row) {
    $voteheads[] = $row->votehead;
    $amounts[] = $row->total_amount;
    
    if (!isset($yearlyData[$row->academicyear])) {
        $yearlyData[$row->academicyear] = [];
    }
    $yearlyData[$row->academicyear][$row->votehead] = $row->total_amount;
    
    if (!in_array($row->academicyear, $years)) {
        $years[] = $row->academicyear;
    }
}

// Get unique voteheads for filter dropdown
$voteheadSql = "SELECT DISTINCT votehead FROM voteheads ORDER BY votehead";
$voteheadQuery = $dbh->prepare($voteheadSql);
$voteheadQuery->execute();
$uniqueVoteheads = $voteheadQuery->fetchAll(PDO::FETCH_COLUMN);

// Get unique years for filter dropdown
$yearSql = "SELECT DISTINCT academicyear FROM feepayments ORDER BY academicyear DESC";
$yearQuery = $dbh->prepare($yearSql);
$yearQuery->execute();
$uniqueYears = $yearQuery->fetchAll(PDO::FETCH_COLUMN);

// Get payment trends by month with filters
$monthlyTrends = [];
$monthlySql = "SELECT 
                DATE_FORMAT(f.created_at, '%Y-%m') as month,
                SUM(f.amount) as monthly_total
              FROM feepayment_voteheads f
              LEFT JOIN voteheads v ON f.votehead_id = v.id
              LEFT JOIN feepayments p ON f.payment_id = p.id
              WHERE 1=1";

if (!empty($selectedYear)) {
    $monthlySql .= " AND p.academicyear = :year";
}
if (!empty($selectedVotehead)) {
    $monthlySql .= " AND v.votehead = :votehead";
}

$monthlySql .= " GROUP BY DATE_FORMAT(f.created_at, '%Y-%m')
                ORDER BY month";

$monthlyQuery = $dbh->prepare($monthlySql);

if (!empty($selectedYear)) {
    $monthlyQuery->bindParam(':year', $selectedYear, PDO::PARAM_STR);
}
if (!empty($selectedVotehead)) {
    $monthlyQuery->bindParam(':votehead', $selectedVotehead, PDO::PARAM_STR);
}

$monthlyQuery->execute();
$monthlyResults = $monthlyQuery->fetchAll(PDO::FETCH_OBJ);

$monthlyLabels = [];
$monthlyValues = [];

foreach ($monthlyResults as $row) {
    $monthlyLabels[] = $row->month;
    $monthlyValues[] = $row->monthly_total;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FeePayments/Votehead Analysis Dashboard</title>

    <!-- Stylesheets -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filters-container {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .chart-container:hover {
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .chart-title {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .dashboard-row {
            margin-bottom: 20px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .summary-card:hover {
            background: #f9f9f9;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }
        
        .summary-card h3 {
            color: #555;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .summary-card .subtext {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .chart-container canvas {
            max-height: 220px;
        }
        
        .btn-apply-filters {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-apply-filters:hover {
            background-color: #2980b9;
        }
        
        .btn-reset-filters {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-reset-filters:hover {
            background-color: #c0392b;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .summary-card {
                margin-bottom: 15px;
            }
            
            .chart-container {
                height: auto;
            }
            
            .chart-container canvas {
                max-height: none;
            }
            
            .filter-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div id="wrapper">
    <?php include_once('includes/header.php'); ?>
    <?php include_once('includes/sidebar.php'); ?>

    <div id="page-wrapper">
        <div class="row mt-4">
            <div class="col-lg-12">
                <h2 class="page-header">
                    Fee Payments/Votehead Analysis Dashboard
                    <small class="text-muted">Financial Insights</small>
                </h2>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row dashboard-row">
            <div class="col-lg-12">
                <div class="filters-container">
                    <form method="get" action="">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label">Academic Year</label>
                                <select class="form-control" name="year" id="year">
                                    <option value="">All Years</option>
                                    <?php foreach ($uniqueYears as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $selectedYear == $year ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($year); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Votehead</label>
                                <select class="form-control" name="votehead" id="votehead">
                                    <option value="">All Voteheads</option>
                                    <?php foreach ($uniqueVoteheads as $votehead): ?>
                                        <option value="<?php echo htmlspecialchars($votehead); ?>" <?php echo $selectedVotehead == $votehead ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($votehead); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" class="btn-apply-filters">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                            <a href="?" class="btn-reset-filters">
                                <i class="fa fa-times"></i> Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row dashboard-row">
            <?php
            // Get summary data
            $totalPayments = array_sum($amounts);
            $totalTransactions = count($results);
            $averagePayment = $totalTransactions > 0 ? $totalPayments / $totalTransactions : 0;
            $uniqueVoteheads = count(array_unique($voteheads));
            
            // Get most recent payment date
            $recentSql = "SELECT MAX(created_at) as last_payment FROM feepayment_voteheads";
            $recentQuery = $dbh->prepare($recentSql);
            $recentQuery->execute();
            $recentResult = $recentQuery->fetch(PDO::FETCH_OBJ);
            $lastPayment = $recentResult ? $recentResult->last_payment : 'N/A';
            ?>
            
            <div class="col-md-3">
                <div class="summary-card">
                    <h3>Total Payments</h3>
                    <div class="value"><?php echo number_format($totalPayments, 2); ?></div>
                    <div class="subtext">All voteheads combined</div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="summary-card">
                    <h3>Transactions</h3>
                    <div class="value"><?php echo number_format($totalTransactions); ?></div>
                    <div class="subtext">Individual payments</div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="summary-card">
                    <h3>Avg. Payment</h3>
                    <div class="value"><?php echo number_format($averagePayment, 2); ?></div>
                    <div class="subtext">Per transaction</div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-info" style="width: 100%"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="summary-card">
                    <h3>Last Updated</h3>
                    <div class="value"><?php echo $lastPayment != 'N/A' ? date('M d, Y', strtotime($lastPayment)) : 'N/A'; ?></div>
                    <div class="subtext">Most recent payment</div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row dashboard-row">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Payment Distribution by Votehead</div>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Monthly Payment Trends</div>
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row dashboard-row">
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Top Voteheads by Amount</div>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Yearly Comparison</div>
                    <canvas id="stackedBarChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="assets/plugins/jquery-1.10.2.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
<script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="assets/plugins/pace/pace.js"></script>
<script src="assets/scripts/siminta.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

<script>
$(document).ready(function () {
    // Initialize Select2 dropdowns
    $('#year').select2({
        placeholder: "Select Academic Year",
        allowClear: true,
        width: '100%'
    });
    
    $('#votehead').select2({
        placeholder: "Select Votehead",
        allowClear: true,
        width: '100%'
    });

    // DataTable initialization
    const table = $('#dataTables-example').DataTable({
        pageLength: 10,
        dom: '<"top"f>rt<"bottom"lip><"clear">'
    });
});

// Chart Data from PHP
const voteheads = <?php echo json_encode($voteheads); ?>;
const amounts = <?php echo json_encode($amounts); ?>;
const monthlyLabels = <?php echo json_encode($monthlyLabels); ?>;
const monthlyValues = <?php echo json_encode($monthlyValues); ?>;
const yearlyData = <?php echo json_encode($yearlyData); ?>;
const years = <?php echo json_encode($years); ?>;

// Generate colors for charts
function generateColors(count) {
    const colors = [];
    const hueStep = 360 / count;
    for (let i = 0; i < count; i++) {
        const hue = i * hueStep;
        colors.push(`hsl(${hue}, 70%, 60%)`);
    }
    return colors;
}

// Pie Chart - Payment Distribution by Votehead
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: voteheads,
        datasets: [{
            data: amounts,
            backgroundColor: generateColors(voteheads.length),
            borderWidth: 1
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
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Line Chart - Monthly Payment Trends
const lineCtx = document.getElementById('lineChart').getContext('2d');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Monthly Payments',
            data: monthlyValues,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Bar Chart - Top Voteheads by Amount
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: voteheads.slice(0, 10), // Show top 10
        datasets: [{
            label: 'Amount',
            data: amounts.slice(0, 10),
            backgroundColor: generateColors(10),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Stacked Bar Chart - Yearly Comparison
const stackedBarCtx = document.getElementById('stackedBarChart').getContext('2d');

// Prepare datasets for each votehead
const voteheadNames = [...new Set(voteheads)];
const yearlyDatasets = [];

voteheadNames.forEach((votehead, i) => {
    const data = years.map(year => yearlyData[year]?.[votehead] || 0);
    
    yearlyDatasets.push({
        label: votehead,
        data: data,
        backgroundColor: generateColors(voteheadNames.length)[i],
        borderWidth: 1
    });
});

new Chart(stackedBarCtx, {
    type: 'bar',
    data: {
        labels: years,
        datasets: yearlyDatasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true,
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y.toLocaleString()}`;
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>