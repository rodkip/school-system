<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$selected_year = $_GET['year'] ?? '';

// Get the list of available financial years
$years_stmt = $dbh->query("SELECT DISTINCT financialyear FROM budget ORDER BY financialyear DESC");
$years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

// Prepare the query to fetch votehead data with optional year filter
$votehead_sql = "
    SELECT 
        b.financialyear,
        b.votehead,
        SUM(b.allocated_amount) AS total_budgeted,
        COALESCE(SUM(e.amount), 0) AS total_spent
    FROM budget b
    LEFT JOIN expendituresdetails e 
        ON b.votehead = e.votehead 
        AND b.financialyear = e.financialyear";

if ($selected_year) {
    $votehead_sql .= " WHERE b.financialyear = :year";
}

$votehead_sql .= " GROUP BY b.financialyear, b.votehead";
$vh_query = $dbh->prepare($votehead_sql);

if ($selected_year) {
    $vh_query->bindParam(':year', $selected_year);
}

$vh_query->execute();
$votehead_data = $vh_query->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | Budgets per Votehead Analysis</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container { padding: 20px; }
        .page-title { border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; padding-bottom: 10px; }
        .card { border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .card-header { background: #2c3e50; color: white; padding: 15px 20px; font-weight: 600; }
        .positive-balance { color: #28a745; }
        .negative-balance { color: #dc3545; }
        .table { width: 100%; background-color: #fff; border-radius: 10px; }
        .table th, .table td { padding: 12px 15px; text-align: center; }
        .form-inline select { width: auto; }
        .form-inline label { font-weight: bold; }
        .table-responsive { margin-top: 20px; }
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
                    <h1 class="page-header">
                        <i class="fa fa-bar-chart-o"></i> Per Votehead Analysis
                    </h1>
                </div>
            </div>

            <!-- Filter Form for Financial Year -->
            <form method="get" class="form-inline mb-3">
                <label for="year" class="mr-2"><strong>Filter by Financial Year:</strong></label>
                <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                    <option value="">-- All Years --</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>" <?= ($year == $selected_year) ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Budget vs Expenditure Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-balance-scale"></i> Budgeted Voteheads vs. Expenditure (Per Financial Year)
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Financial Year</th>
                                            <th>Votehead</th>
                                            <th>Budgeted Amount</th>
                                            <th>Actual Expenditure</th>
                                            <th>Variance</th>
                                            <th>% Utilized</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if ($votehead_data):
                                        $counter = 1;
                                        foreach ($votehead_data as $vh):
                                            $variance = floatval($vh->total_budgeted) - floatval($vh->total_spent);
                                            $utilization = ($vh->total_budgeted > 0) ? ($vh->total_spent / $vh->total_budgeted * 100) : 0;
                                            $remarks = $utilization >= 90 ? "Well Utilized" : ($utilization >= 50 ? "Moderate" : "Underutilized");
                                    ?>
                                            <tr>
                                                <td><?= $counter++ ?></td>
                                                <td><b><?= htmlentities($vh->financialyear) ?></b></td>
                                                <td><?= htmlentities($vh->votehead) ?></td>
                                                <td><?= number_format($vh->total_budgeted, 2) ?></td>
                                                <td><?= number_format($vh->total_spent, 2) ?></td>
                                                <td><span class="<?= $variance >= 0 ? 'positive-balance' : 'negative-balance' ?>"><?= number_format($variance, 2) ?></span></td>
                                                <td><?= number_format($utilization, 1) ?>%</td>
                                                <td><?= $remarks ?></td>
                                            </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No budget data available.</td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Votehead Budget vs Expenditure Chart -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">Votehead Budget vs Expenditure Chart</div>
                        <div class="card-body">
                            <canvas id="voteheadChart" height="100"></canvas>
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

        <!-- Chart Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('voteheadChart')?.getContext('2d');
                if (!ctx) return;

                const voteheads = <?= json_encode(array_column($votehead_data, 'votehead')) ?>;
                const budgeted = <?= json_encode(array_column($votehead_data, 'total_budgeted')) ?>;
                const spent = <?= json_encode(array_column($votehead_data, 'total_spent')) ?>;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: voteheads,
                        datasets: [
                            {
                                label: 'Budgeted (Ksh)',
                                data: budgeted,
                                backgroundColor: '#007bff'
                            },
                            {
                                label: 'Spent (Ksh)',
                                data: spent,
                                backgroundColor: '#28a745'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.dataset.label + ': Ksh ' + ctx.raw.toLocaleString()
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: val => 'Ksh ' + val.toLocaleString()
                                }
                            }
                        }
                    }
                });
            });
        </script>
        <script>
    $(document).ready(function () {
        $('#dataTables-example').dataTable();
    });
</script>
    </div>
</body>
</html>
