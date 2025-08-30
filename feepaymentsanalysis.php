<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Get current academic year
$currentYear = date('Y');
$currentAcademicYear = ($currentYear - 1) . "/" . $currentYear;

// Get summary data: fees, reg fees, other item payments, and expenses
$summary_sql = "SELECT 
    (SELECT SUM(cash) FROM feepayments) AS total_income,
    (SELECT SUM(amount) FROM regfeepayments) AS total_regfee,
    (SELECT SUM(amount) FROM otheritemspayments) AS total_otherincome,
    (SELECT SUM(amount) FROM expendituresdetails) AS total_expenses";

$summary_query = $dbh->prepare($summary_sql);
$summary_query->execute();
$summary = $summary_query->fetch(PDO::FETCH_OBJ);

// Fetch individual components with null coalescing for safety
$totalFeeIncome = $summary->total_income ?? 0;
$totalRegFeeIncome = $summary->total_regfee ?? 0;
$totalOtherIncome = $summary->total_otherincome ?? 0;
$totalExpenses = $summary->total_expenses ?? 0;

// Calculate total income (fee + reg + other items)
$totalIncome = $totalFeeIncome + $totalRegFeeIncome + $totalOtherIncome;

// Calculate final balance
$totalBalance = $totalIncome - $totalExpenses;

// Yearly fee payments summary
$yearly_sql = "SELECT academicyear, SUM(cash) AS sumreceived 
               FROM feepayments GROUP BY academicyear ORDER BY academicyear DESC";
$yearly_query = $dbh->prepare($yearly_sql);
$yearly_query->execute();
$yearly_results = $yearly_query->fetchAll(PDO::FETCH_OBJ);

// Expenditure voteheads for selected financial year
$year_sql = "SELECT DISTINCT financialyear FROM expendituresdetails ORDER BY financialyear DESC";
$year_query = $dbh->prepare($year_sql);
$year_query->execute();
$available_years = $year_query->fetchAll(PDO::FETCH_COLUMN, 0);
$selected_year = $_POST['financialyear'] ?? ($available_years[0] ?? date('Y'));

$votehead_sql = "SELECT votehead, SUM(amount) AS total_amount 
                 FROM expendituresdetails 
                 WHERE financialyear = :year 
                 GROUP BY votehead";
$votehead_query = $dbh->prepare($votehead_sql);
$votehead_query->bindParam(':year', $selected_year, PDO::PARAM_STR);
$votehead_query->execute();
$votehead_results = $votehead_query->fetchAll(PDO::FETCH_ASSOC);

// Format data for chart or presentation
$voteheads = [];
$amounts = [];
foreach ($votehead_results as $row) {
    $voteheads[] = $row['votehead'];
    $amounts[] = $row['total_amount'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Fee Payments Analysis</title>
    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
      <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />

      <link href="assets/css/main-style.css" rel="stylesheet" />
      <!-- Page-Level CSS -->
      <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .dashboard-container {
            padding: 20px;
        }
        
        .page-title {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            border: none;
        }
        
        .card-header {
            background: #2c3e50;
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
            border-bottom: none;
        }
        
        .card-header .fa {
            margin-right: 10px;
        }
        
        .summary-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid;
        }
        
        .summary-card h4 {
            color: #555;
            font-size: 16px;
            margin-top: 0;
        }
        
        .summary-card .value {
            font-size: 24px;
            font-weight: 700;
        }
        
        .positive-balance {
            color: #28a745;
        }
        
        .negative-balance {
            color: #dc3545;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            padding: 15px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .year-filter-form {
            display: flex;
            align-items: center;
        }
        
        .year-selector {
            width: 150px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: blue;
        }
        
        .chart-type-btn {
            padding: 0.25rem 0.5rem;
            margin-left: 5px;
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
        }
        
        .chart-type-btn.active {
            background: rgba(255,255,255,0.3);
        }
        
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
            
            .year-filter-form {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .year-selector {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        .table {
        width: 100%;
        background-color: #ffffff;  
        
        border-color: #007bff;
        border-radius: 10px;
    }
    .card{
  padding: 10px;
  border-style: solid;
  border-color: rgb(167, 140, 157);
  border-width: 1px;
  border-radius: 7px;
  box-shadow: 0 4px 8px rgba(167, 40, 120, 0.2);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper" class="dashboard-container">
            <div class="row">
                <div class="col-lg-12">
                <br>

                    <table class="tr" width="50%">
                     <tr>
                        <td>   <h1 class="page-header">
                        <i class="fa fa-bar-chart-o"></i> Payments Analysis
                        </h1>
                        </td>                        
                     </tr>
                  </table>
                </div>
            </div>

            <!-- Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="summary-card" style="border-left-color: #4e73df;">
            <h4>Total Income (All Years)</h4>
            <div class="value">
                Ksh <?= number_format($totalIncome) ?>
                <br>
                <span style="font-size: 0.7em; color: #007bff;">
                    Fee: Ksh <?= number_format($totalFeeIncome) ?>
                </span> |
                <span style="font-size: 0.7em; color: rgb(255, 0, 225);">
                    RegFee: Ksh <?= number_format($totalRegFeeIncome) ?>
                </span> | <br>
                <span style="font-size: 0.7em; color: #17a2b8;">
                    OtherItems: Ksh <?= number_format($totalOtherIncome) ?>
                </span>
            </div>    
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="summary-card" style="border-left-color: #1cc88a;">
            <h4>Total Expenses (All Years)</h4>
            <div class="value">Ksh <?= number_format($totalExpenses) ?></div>
            <small>All Years</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="summary-card" style="border-left-color: #36b9cc;">
            <h4>Total Balance</h4>
            <div class="value <?= $totalBalance >= 0 ? 'positive-balance' : 'negative-balance' ?>">
                Ksh <?= number_format(abs($totalBalance)) ?>
                <?= $totalBalance >= 0 ? '' : '(Deficit)' ?>
            </div>
            <small>All Years</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="summary-card" style="border-left-color: #f6c23e;">
            <h4>Financial Health</h4>
            <div class="value">
                <?= $totalIncome > 0 ? round(($totalBalance / $totalIncome) * 100, 1) . '%' : 'N/A' ?>
            </div>
            <small>Net Income Ratio</small>
        </div>
    </div>
</div>


            <div class="row">
                <!-- Yearly Summary Table -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-calendar"></i> Income/Expenditure Summary Per Year
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Year</th>
            <th>Admissin Fee</th>
            <th>Fee Payments</th>            
            <th>Other Items</th> <!-- New column -->
            <th>Total Income</th>
            <th>Expenses</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php if(count($yearly_results) > 0): ?>
            <?php foreach($yearly_results as $i => $row): 
                // Fetch the sum of registration fee payments for the current academic year
                $regfee_sql = "SELECT SUM(amount) AS totalregfee 
                            FROM regfeepayments 
                            WHERE YEAR(admdate) = :year";
                $regfee_query = $dbh->prepare($regfee_sql);
                $regfee_query->bindParam(':year', $row->academicyear, PDO::PARAM_STR);
                $regfee_query->execute();
                $regfee = $regfee_query->fetch(PDO::FETCH_OBJ);

                // Fetch the sum of other items payments for the current financial year
                $other_sql = "SELECT SUM(amount) AS totalother 
                              FROM otheritemspayments 
                              WHERE financialyear = :year";
                $other_query = $dbh->prepare($other_sql);
                $other_query->bindParam(':year', $row->academicyear, PDO::PARAM_STR);
                $other_query->execute();
                $other = $other_query->fetch(PDO::FETCH_OBJ);

                // Fetch the total expenditures for the current financial year
                $exp_sql = "SELECT SUM(amount) AS sumpaid 
                            FROM expendituresdetails 
                            WHERE financialyear = :year";
                $exp_query = $dbh->prepare($exp_sql);
                $exp_query->bindParam(':year', $row->academicyear, PDO::PARAM_STR);
                $exp_query->execute();
                $exp = $exp_query->fetch(PDO::FETCH_OBJ);

                // Calculate the total income
                $total_income = 
                    ($row->sumreceived ?? 0) + 
                    ($regfee->totalregfee ?? 0) + 
                    ($other->totalother ?? 0);

                // Calculate the balance
                $balance = $total_income - ($exp->sumpaid ?? 0);
            ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><b><?= htmlentities($row->academicyear) ?></b></td> 
                    <td><?= number_format($regfee->totalregfee ?? 0) ?></td>
                    <td><?= number_format($row->sumreceived ?? 0) ?></td>                   
                    <td><?= number_format($other->totalother ?? 0) ?></td> <!-- Display other items -->
                    <td style="font-weight: bold;"><?= number_format($total_income) ?></td>
                    <td style="color: #dc3545;"><?= number_format($exp->sumpaid ?? 0) ?></td>
                    <td>
                        <span class="<?= $balance >= 0 ? 'positive-balance' : 'negative-balance' ?>">
                            <?= number_format($balance) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No data found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


                            </div>
                        </div>
                    </div>
                </div>

                <!-- Votehead Expenditure Chart -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-pie-chart"></i> Expenditure by Votehead
                            <div class="card-tools">
                                <form method="post" class="year-filter-form">
                                    <select name="financialyear" class="form-control form-control-sm year-selector">
                                        <?php foreach($available_years as $year): ?>
                                            <option value="<?= htmlspecialchars($year) ?>" 
                                                <?= $year == $selected_year ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($year) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="chart-type-btn" data-type="bar">
                                        <i class="fa fa-bar-chart"></i>
                                    </button>
                                    <button type="button" class="chart-type-btn" data-type="pie">
                                        <i class="fa fa-pie-chart"></i>
                                    </button>
                                    <button type="button" class="chart-type-btn" data-type="doughnut">
                                        <i class="fa fa-circle"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="voteheadChart"></canvas>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <small>Showing: <strong><?= htmlspecialchars($selected_year) ?></strong></small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <small>Total: <strong>Ksh <?= number_format(array_sum($amounts)) ?></strong></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    
    <!-- Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Votehead Chart
            const voteheadCtx = document.getElementById('voteheadChart').getContext('2d');
            const voteheadChart = new Chart(voteheadCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($voteheads) ?>,
                    datasets: [{
                        label: 'Amount Spent (Ksh)',
                        data: <?= json_encode($amounts) ?>,
                        backgroundColor: [
                            '#004b6e', '#006da8', '#0086c4', '#00a0e0',
                            '#00bafc', '#3dc7ff', '#6fd4ff', '#9fe0ff'
                        ].map(c => hexToRgba(c, 0.7)),
                        borderColor: '#004B6E',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 20,
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map(function(label, i) {
                                            return {
                                                text: label + ': Ksh ' + data.datasets[0].data[i].toLocaleString(),
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                hidden: isNaN(data.datasets[0].data[i]),
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Ksh ' + context.raw.toLocaleString();
                                },
                                afterLabel: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `Percentage: ${percentage}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Ksh ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Helper functions
            function hexToRgba(hex, alpha) {
                const r = parseInt(hex.slice(1, 3), 16);
                const g = parseInt(hex.slice(3, 5), 16);
                const b = parseInt(hex.slice(5, 7), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            // Year filter change
            document.querySelector('.year-selector').addEventListener('change', function() {
                this.form.submit();
            });

            // Chart type buttons
            document.querySelectorAll('.chart-type-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    voteheadChart.config.type = this.dataset.type;
                    voteheadChart.update();
                });
            });

            // Activate bar chart by default
            document.querySelector('.chart-type-btn[data-type="bar"]').classList.add('active');
        });
    </script>
    <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
      <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
      <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
      <script src="assets/plugins/pace/pace.js"></script>
      <script src="assets/scripts/siminta.js"></script>
      <!-- Page-Level Plugin Scripts-->
      <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
      <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
</body>
</html>