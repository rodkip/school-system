<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
} else {
    $currentyear = date("Y");
    $academicyear = date("Y");
?> 
<script type="text/javascript">
    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
        var t = setTimeout(startTime, 500);
    }
    
    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        }; // add zero in front of numbers < 10 
        return i;
    }
</script>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title> <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link rel="icon" href="images/tabpic.png">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    

    <script type="text/javascript">
        bkLib.onDomLoaded(nicEditors.allTextAreas);
    </script>
    
<script>
        window.onload = function() {
          // Function to show the pop-up
          function showPopup() {
            document.getElementById('popup').style.display = 'block';
          }
        
          // Function to close the pop-up
          function closePopup() {
            document.getElementById('popup').style.display = 'none';
          }
        
          // Set the content of the pop-up
          document.getElementById('popup-data').textContent = 'This is the updated pop-up content.';
        
          // Show the pop-up initially
          showPopup();
        
          // Event listener to close the pop-up when the close button is clicked
          document.getElementById('popup-close').addEventListener('click', closePopup);
        };
    </script>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .table {
        width: 100%;
        background-color: #ffffff;  
        
        border-color: #007bff;
        border-radius: 10px;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table th,
    .table td {
        padding: 14px;
        text-align: left;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }

    .table th {
        background-color: #4682B4; /* navy blue for academic tone */
        color: #ffffff;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .table td {
        font-size: 14px;
        color: #212529;
    }

    .table-light {
        background-color: #f1f5f9;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9fafb;
    }

    .table-hover tbody tr:hover {
        background-color: #e2e8f0;
    }

    .table td a {
        text-decoration: none;
        color: #0d6efd;
        font-weight: 500;
    }

    .table td a:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    .text-center {
        text-align: center;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .fw-bold {
        font-weight: bold;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    /* Popup Styles */
    #popup {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #f1f1f1;
        padding: 20px;
        display: none;
    }

    #popup-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #popup-close {
        background-color: #ccc;
        border: none;
        color: #000;
        padding: 8px 16px;
        cursor: pointer;
    }

    /* Table Loading Overlay */
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

    /* Expandable Cell */
    .expandable-cell {
        word-wrap: break-word; /* Allows long words to break and wrap to the next line */
        white-space: pre-wrap; /* Preserves whitespace and allows wrapping */
    }

    /* Info Box General Styles */
    .info-box {
        background-color: white;
        border: 2px solid;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .info-box:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    .info-box:active {
        transform: scale(0.95);
    }

    .icon {
        float: left;
        font-size: 26px;
        transition: transform 0.3s ease;
    }

    .info-box:hover .icon {
        transform: rotate(15deg);
    }

    .counter {
        font-size: 60px;
        font-weight: bold;
        transition: transform 0.3s ease;
    }

    .info-box:hover .counter {
        transform: scale(1.1);
    }

    .title {
        font-size: 18px;
        margin-top: 5px;
        text-align: right;
        color:rgb(11, 11, 11); 
    }

    /* Hover and Click Animations for Buttons */
    .big-button:hover #currentyearstudents,
    .big-button:hover #totalstudent {
        transform: scale(1.05); /* Slightly enlarge on hover */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
    }

    .big-button:active #currentyearstudents,
    .big-button:active #totalstudent {
        transform: scale(0.95); /* Slightly shrink on click */
    }

    .big-button:hover #currentyearstudents i,
    .big-button:hover #totalstudent i {
        transform: rotate(15deg); /* Rotate icon on hover */
    }
    .card-body {
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
    <!--  wrapper -->
    <div id="wrapper">
        <!-- navbar top --> <?php include_once('includes/header.php');?>
         <?php include_once('includes/popupmessage.php');?>
        <!-- end navbar top -->
        <!-- navbar side --> <?php include_once('includes/sidebar.php');?>
        <!-- end navbar side -->
        <!--  page-wrapper -->
        <div id="page-wrapper">
        <div class="row">
    <!-- Page Header Row -->
    <div class="col-lg-12">
        <br>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <!-- Dashboard Title -->
            <h1 class="page-header mb-0">
                Dashboard <i class="fa fa-bar-chart-o fa-fw"></i>
                <span class="sr-only">Loading...</span>
            </h1>

            <!-- Birthday Button -->
            <div>
                <?php include('viewbirthdayspopup.php'); ?>
                <a href="#viewbirthdays" data-toggle="modal" 
                   class="btn" 
                   style="background: linear-gradient(to right, #ff7eb9, #ff758c); 
                          color: white; 
                          font-weight: bold; 
                          border: none; 
                          border-radius: 25px; 
                          padding: 10px 20px; 
                          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
                          transition: all 0.3s ease;">
                    <i class="fa fa-birthday-cake"></i>&nbsp;&nbsp;Celebrate Birthdays
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Update Message Popup -->
<?php include_once('updatemessagepopup.php'); ?>  

<!-- CEO Quote -->
<?php
$sql = "SELECT * FROM ceoquotes ORDER BY RAND() LIMIT 1";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;

if ($query->rowCount() > 0) {
    foreach ($results as $row) {
        echo '<div class="quote mt-3" style="white-space: normal; word-wrap: break-word; overflow-wrap: break-word;">' . htmlentities($row->quote) . '</div>';

        $cnt++;
    }
}
?>
<br>
<!-- Quick Info Section -->
<!-- Add your quick info cards or widgets here -->

<!-- Table Section -->
<div class="row">


<!-- Total Staff -->
<div class="col-lg-2">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="manage-studentdetails.php" class="big-button" style="text-decoration: none;">
                <div class="alert text-center info-box" style="border-color: #007bff; color: #007bff; height: 120px;">
                    <i class="fa fa-id-badge icon" style="font-size: 50px;"></i>
                    <div id="staffCounter" class="counter" style="font-size: 35px; margin-top: 1px; text-align:right;">
                        <?php
                        $sql = "SELECT id FROM studentdetails";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $totalStaff = $query->rowCount();
                        echo $totalStaff;
                        ?>
                    </div>
                    <div class="title">Total Learners</div>
                </div>
            </a>
        </div>
    </div>
</div>
<!-- Total Grades Details Entries -->
<div class="col-lg-2">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="manage-classdetails.php" class="big-button" style="text-decoration: none;">
                <div id="currentyearstudents" class="alert text-center info-box" style="border-color: #FFBF00; color: #FFBF00; padding: 10px; height: 120px;">
      
                <i class="fa fa-users icon" style="font-size: 50px; align-items: center"></i>
                    <div id="currenyearstudents" class="counter" style="font-size: 35px; margin-top: 1px; text-align:right;">
                        <?php
                        $sql = "SELECT * FROM classentries WHERE gradefullname LIKE '$academicyear%'";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $currentYearStudents = $query->rowCount();
                        echo $currentYearStudents;
                        ?>
                    </div>
                    <div class="title">Current Year Learners</div>
                </div>
            </a>
        </div>
    </div>
</div>
<!-- Learners Registered Today -->
<div class="col-lg-2">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="manage-studentdetails.php?filter=today" class="big-button" style="text-decoration: none;">
                <div class="alert text-center info-box" style="border-color: #00BFFF; color: #00BFFF; height: 120px;">
                    <i class="fa fa-user-plus icon" style="font-size: 50px;"></i>
                    <div id="studentregtoday" class="counter" style="font-size: 35px; margin-top: 1px; text-align:right;">
                        <?php
                        $sql = "SELECT id FROM studentdetails WHERE DATE(entrydate) = CURDATE()";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $studentsRegToday = $query->rowCount();
                        echo $studentsRegToday;
                        ?>
                    </div>
                    <div class="title">Learners Added Today</div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Payments Today -->
<div class="col-lg-2">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="viewall-feepayments.php?filter=today" class="big-button" style="text-decoration: none;">
                <div class="alert text-center info-box" style="border-color: #C0C0C0; color: #C0C0C0; height: 120px;">
                    <i class="fa fa-money icon" style="font-size: 50px;"></i>
                    <div id="paymentstoday" class="counter" style="font-size: 35px; margin-top: 1px; text-align:right;">
                        <?php
                        $sql = "SELECT id FROM feepayments WHERE DATE(entrydate) = CURDATE()";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $paymentsToday = $query->rowCount();
                        echo $paymentsToday;
                        ?>
                    </div>
                    <div class="title">Payments Today</div>
                </div>
            </a>
        </div>
    </div>
</div>


<!-- Total Payments -->
<div class="col-lg-2">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="manage-feepayments.php" class="big-button" style="text-decoration: none;">
                <div class="alert text-center info-box" style="border-color: #28a745; color: #28a745; height: 120px;">
                    <i class="fa fa-credit-card icon" style="font-size: 50px;"></i>
                    <div  id="totalPayments" class="counter" style="font-size: 35px; margin-top: 1px; text-align:right;">
                        <?php
                        $sql = "SELECT id FROM feepayments";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $totalPayments = $query->rowCount();
                        echo $totalPayments;
                        ?>
                    </div>
                    <div class="title">Total Payments</div>
                </div>
            </a>
        </div>
    </div>
</div>


<!-- Total Payees -->
<div class="col-lg-2">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <a href="manage-payeedetails.php" class="big-button" style="text-decoration: none;">
                <div class="alert text-center info-box" style="border-color: #17a2b8; color: #17a2b8; height: 120px;">
                    <i class="fa fa-address-book icon" style="font-size: 50px;"></i>
                    <div  id="totalPayees" class="counter" style="font-size: 35px; margin-top: 1px; text-align:right;">
                        <?php
                        $sql = "SELECT id FROM payeedetails";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $totalPayees = $query->rowCount();
                        echo $totalPayees;
                        ?>
                    </div>
                    <div class="title">Total Payees</div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- JavaScript for Counter Animations -->
<script>
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    document.addEventListener('DOMContentLoaded', () => {

        
         
        animateValue(document.getElementById('currenyearstudents'), 0, <?php echo $currentYearStudents; ?>, 1500);
        animateValue(document.getElementById('paymentstoday'), 0, <?php echo $paymentsToday; ?>, 1500);
        animateValue(document.getElementById('studentregtoday'), 0, <?php echo $studentsRegToday; ?>, 1500);
        animateValue(document.getElementById('totalPayments'), 0, <?php echo $totalPayments; ?>, 1500);
    
        animateValue(document.getElementById('staffCounter'), 0, <?php echo $totalStaff; ?>, 1500);      
        animateValue(document.getElementById('totalPayees'), 0, <?php echo $totalPayees; ?>, 1500);
        
    });
</script>
</div>

<!-- Balances per year Section -->
<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">

<div class="col-lg-8">
    <div class="card shadow-sm border-0 mb-4">
       
        <div class="card-body p-3">
           
            <?php
// Fetch academic years for dropdown
$smt = $dbh->prepare('SELECT academicyear FROM classdetails GROUP BY academicyear ORDER BY academicyear DESC');
$smt->execute();
$years = $smt->fetchAll(PDO::FETCH_COLUMN);

// Get selected year from GET, else use the first (latest) one
$selectedYear = isset($_GET['academicyear']) && $_GET['academicyear'] !== '' ? $_GET['academicyear'] : $years[0];
?>

<!-- Academic Year Dropdown Form -->
<form method="get" class="mb-3">
    <label for="academicyear"><strong>Select Academic Year:</strong></label>
    <select name="academicyear" id="academicyear" class="form-control" required onchange="this.form.submit()">
        <option value="">-- select academic year --</option>
        <?php foreach ($years as $year): ?>
            <option value="<?= $year ?>" <?= ($selectedYear == $year) ? 'selected' : '' ?>>
                <?= $year ?>
            </option>
        <?php endforeach ?>
    </select>
</form>

<?php
// SQL to fetch chart data
$sql = "SELECT 
            classdetails.academicyear, 
            classdetails.gradefullname, 
            COUNT(feebalances.studentAdmNo) AS countadmno, 
            SUM(feebalances.arrears) AS sumarrears, 
            SUM(feebalances.totalfee) AS sumfee, 
            SUM(feebalances.totalpaid) AS sumpaid, 
            SUM(feebalances.yearlybal) AS sumbal,
            ((SUM(feebalances.totalpaid)) / ((SUM(feebalances.totalfee)) + (SUM(feebalances.arrears))) * 100) AS percentpaid
        FROM feebalances 
        INNER JOIN classdetails ON feebalances.gradefullname = classdetails.gradefullName 
        WHERE academicyear = :academicyear 
        GROUP BY classdetails.academicyear, classdetails.gradefullName 
        ORDER BY percentpaid DESC";

$query = $dbh->prepare($sql);
$query->bindParam(':academicyear', $selectedYear, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if (count($results) > 0):
?>



<!-- Chart Canvas -->
<canvas id="feeSummaryChart" width="100%" height="45"></canvas>

<script>
    const chartData = {
        labels: [<?php foreach($results as $row) echo "'$row->gradefullname'," ?>],
        datasets: [
            {
                label: 'Population',
                data: [<?php foreach($results as $row) echo "$row->countadmno," ?>],
                backgroundColor: 'rgba(0, 123, 255, 0.6)',
                yAxisID: 'y1'
            },
            {
                label: 'Total Fee',
                data: [<?php foreach($results as $row) echo "$row->sumfee," ?>],
                backgroundColor: 'rgba(255, 193, 7, 0.6)',
                yAxisID: 'y1'
            },
            {
                label: 'Sum Paid',
                data: [<?php foreach($results as $row) echo "$row->sumpaid," ?>],
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                yAxisID: 'y1'
            },
            {
                label: 'Total Balance',
                data: [<?php foreach($results as $row) echo "$row->sumbal," ?>],
                backgroundColor: 'rgba(220, 53, 69, 0.6)',
                yAxisID: 'y1'
            },
            {
                label: '% Paid',
                data: [<?php foreach($results as $row) echo round($row->percentpaid, 2) . "," ?>],
                type: 'line',
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'transparent',
                yAxisID: 'y2'
            }
        ]
    };

    const config = {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y1: {
                    beginAtZero: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Amount / Population'
                    }
                },
                y2: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '% Paid'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
    title: {
        display: true,
        text: 'Grade-wise Fee Summary - Academic Year <?= $selectedYear ?>',
        font: {
            size: 18
        },
        color: '#000' // darker title
    },
    legend: {
        labels: {
            font: {
                size: 14
            },
            color: '#333'
        }
    }
},
scales: {
    y1: {
        beginAtZero: true,
        position: 'left',
        title: {
            display: true,
            text: 'Amount / Population',
            font: {
                size: 14
            },
            color: '#111'
        },
        ticks: {
            font: {
                size: 12
            },
            color: '#333'
        }
    },
    y2: {
        beginAtZero: true,
        position: 'right',
        title: {
            display: true,
            text: '% Paid',
            font: {
                size: 14
            },
            color: '#111'
        },
        ticks: {
            callback: function(value) {
                return value + '%';
            },
            font: {
                size: 12
            },
            color: '#333'
        },
        grid: {
            drawOnChartArea: false
        }
    },
    x: {
        ticks: {
            font: {
                size: 12
            },
            color: '#333'
        }
    }
}

        }
    };

    new Chart(document.getElementById('feeSummaryChart'), config);
</script>

<?php else: ?>
    <div class='alert alert-warning mt-3'>No data found for academic year <strong><?= $selectedYear ?></strong>.</div>
<?php endif; ?>

            </div>
        </div>
    </div>

<!-- Simplified School Summary Pie Chart -->
<div class="col-lg-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">

            <h5 class="mb-3" style="font-weight: bold; color: #004B6E;">
                Paid vs Balance - <?= $selectedYear ?>
            </h5>

            <?php
            // Query to get total paid and balance only
            $sql = "SELECT 
                        SUM(totalpaid) AS totalPaid, 
                        SUM(yearlybal) AS totalBalance 
                    FROM feebalances 
                    INNER JOIN classdetails ON feebalances.gradefullname = classdetails.gradefullName 
                    WHERE classdetails.academicyear = :academicyear";

            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':academicyear', $selectedYear, PDO::PARAM_STR);
            $stmt->execute();
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($summary && ($summary['totalPaid'] > 0 || $summary['totalBalance'] > 0)):
            ?>

            <canvas id="paidVsBalancePieChart" width="300" height="300"></canvas>

            <script>
                const paidVsCtx = document.getElementById('paidVsBalancePieChart').getContext('2d');
                new Chart(paidVsCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Total Paid', 'Total Balance'],
                        datasets: [{
                            data: [
                                <?= round($summary['totalPaid'], 2) ?>,
                                <?= round($summary['totalBalance'], 2) ?>
                            ],
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.7)',   // Green - Paid
                                'rgba(220, 53, 69, 0.7)'    // Red - Balance
                            ],
                            borderColor: [
                                'rgba(40, 167, 69, 1)',
                                'rgba(220, 53, 69, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Fee Payment Distribution-Whole School',
                                font: {
                                    size: 16
                                },
                                color: '#000'
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 14
                                    }
                                }
                            }
                        }
                    }
                });
            </script>

            <?php else: ?>
                <div class='alert alert-warning'>
                    No payment data found for academic year <strong><?= $selectedYear ?></strong>.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</div>
</div>

<!-- Top learners with most balance-->

<div class="col-lg-4">
    <div class="card shadow-sm border-0 mb-4">

        <!-- Card Body -->
        <div class="card-body p-3">
            
            <!-- Card Header Title -->
            <div class="card-header bg-info text-white text-center fw-semibold">
                Top 10 Learners with Most Fee Balance Overall
            </div>

            <!-- Responsive Table Wrapper -->
            <div class="table-responsive">

                <!-- Table Start -->
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    
                    <!-- Table Headings -->
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Adm No</th>
                            <th>Name</th>
                            <th>Grade</th>
                            <th>Balance (Ksh)</th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody>
                        <?php
                        // SQL query to get top 10 students with highest yearly balance
                        $sql = "SELECT * FROM feebalances ORDER BY yearlybal DESC LIMIT 10";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $cnt = 1;

                        // Check if any records are returned
                        if ($query->rowCount() > 0) {
                            // Loop through each result row
                            foreach ($results as $row) {
                        ?>
                                <tr>
                                    <!-- Serial number -->
                                    <td class="text-center"><?php echo htmlentities($cnt); ?></td>
                                    
                                    <!-- Admission number -->
                                    <td class="text-center"><?php echo htmlentities($row->studentadmno); ?></td>
                                    
                                    <!-- Student name -->
                                    <td><?php echo htmlentities($row->studentname); ?></td>
                                    
                                    <!-- Grade name -->
                                    <td class="text-center"><?php echo htmlentities($row->gradefullname); ?></td>
                                    
                                    <!-- Balance displayed as clickable link to payment management page -->
                                    <td class="text-center">
                                        <a href="manage-feepayments.php?viewstudentadmno=<?php echo htmlentities($row->studentadmno); ?>" class="text-danger fw-bold">
                                            <?php echo "Ksh " . number_format($row->yearlybal); ?>
                                        </a>
                                    </td>
                                </tr>
                        <?php
                                $cnt++; // Increment counter
                            }
                        } else {
                        ?>
                            <!-- Display this row if no records found -->
                            <tr>
                                <td colspan="5" class="text-center text-muted">No records found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <!-- Table End -->

            </div>
        </div>
    </div>
</div>


 <!-- Enrollment Summary by Grade per Academic Year -->
<div class="col-lg-8 mx-auto">
    <div class="card shadow rounded-4 border-0">
        <!-- Card Body -->
        <div class="card-body p-4 bg-light">
            
            <!-- Header Section -->
            <div class="card-header bg-info text-white text-center fw-semibold">
                📊 Enrollment Summary by Grade per Academic Year
            </div>
            
            <div class="table-responsive">

                <!-- Toggle Buttons to switch between Chart and Table -->
                <div class="text-end mb-3">
                    <button class="btn btn-sm btn-primary" id="showTableBtn">📋 Show Table</button>
                    <button class="btn btn-sm btn-success" id="showChartBtn">📈 Show Chart</button>
                </div>

                <!-- Chart Display Section (initially hidden) -->
                <div id="chartSection" style="display: none;">
                    <canvas id="enrollmentLineChart" height="100"></canvas>
                </div>

                <!-- Table Display Section -->
                <div id="tableSection">
                    <table id="enrollmentTable" class="table table-bordered table-hover table-striped align-middle border border-dark-subtle">
                        <!-- Table Headings -->
                        <thead class="table-dark text-center">
                            <tr>
                                <th class="text-start">Year</th>
                                <th class="text-center">PG</th>
                                <th class="text-center">PP1</th>
                                <th class="text-center">PP2</th>
                                <th class="text-center">G1</th>
                                <th class="text-center">G2</th>
                                <th class="text-center">G3</th>
                                <th class="text-center">G4</th>
                                <th class="text-center">G5</th>
                                <th class="text-center">G6</th>
                                <th class="text-center">G7</th>
                                <th class="text-center">G8</th>
                                <th class="text-center">G9</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>

                        <!-- Table Body: Data fetched from the database -->
                        <tbody class="bg-white text-center">
                            <?php
                            $grandTotal = 0;

                            // SQL query to count students per grade per academic year
                            $sql = "SELECT 
                                classdetails.academicyear,
                                COUNT(CASE WHEN classdetails.gradename='pg' THEN classentries.studentadmno END) AS pg, 
                                COUNT(CASE WHEN classdetails.gradename='pp1' THEN classentries.studentadmno END) AS pp1,
                                COUNT(CASE WHEN classdetails.gradename='pp2' THEN classentries.studentadmno END) AS pp2,
                                COUNT(CASE WHEN classdetails.gradename='grade1' THEN classentries.studentadmno END) AS grade1,
                                COUNT(CASE WHEN classdetails.gradename='grade2' THEN classentries.studentadmno END) AS grade2, 
                                COUNT(CASE WHEN classdetails.gradename='grade3' THEN classentries.studentadmno END) AS grade3,
                                COUNT(CASE WHEN classdetails.gradename='grade4' THEN classentries.studentadmno END) AS grade4, 
                                COUNT(CASE WHEN classdetails.gradename='grade5' THEN classentries.studentadmno END) AS grade5,
                                COUNT(CASE WHEN classdetails.gradename='grade6' THEN classentries.studentadmno END) AS grade6, 
                                COUNT(CASE WHEN classdetails.gradename='grade7' THEN classentries.studentadmno END) AS grade7,
                                COUNT(CASE WHEN classdetails.gradename='grade8' THEN classentries.studentadmno END) AS grade8,
                                COUNT(CASE WHEN classdetails.gradename='grade9' THEN classentries.studentadmno END) AS grade9
                                FROM classentries 
                                INNER JOIN classdetails ON classentries.gradefullname = classdetails.gradefullname 
                                GROUP BY classdetails.academicyear 
                                ORDER BY classdetails.academicyear DESC";

                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            // Loop through results and populate table
                            if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                                    $total = $row->pg + $row->pp1 + $row->pp2 + $row->grade1 + $row->grade2 + $row->grade3 + $row->grade4 + $row->grade5 + $row->grade6 + $row->grade7 + $row->grade8 + $row->grade9;
                                    $grandTotal += $total;
                            ?>
                            <tr>
                                <td class="text-start fw-semibold text-primary"><?php echo htmlentities($row->academicyear); ?></td>
                                <td><?php echo htmlentities($row->pg); ?></td>
                                <td><?php echo htmlentities($row->pp1); ?></td>
                                <td><?php echo htmlentities($row->pp2); ?></td>
                                <td><?php echo htmlentities($row->grade1); ?></td>
                                <td><?php echo htmlentities($row->grade2); ?></td>
                                <td><?php echo htmlentities($row->grade3); ?></td>
                                <td><?php echo htmlentities($row->grade4); ?></td>
                                <td><?php echo htmlentities($row->grade5); ?></td>
                                <td><?php echo htmlentities($row->grade6); ?></td>
                                <td><?php echo htmlentities($row->grade7); ?></td>
                                <td><?php echo htmlentities($row->grade8); ?></td>
                                <td><?php echo htmlentities($row->grade9); ?></td>
                                <td class="fw-bold bg-info-subtle"><?php echo htmlentities($total); ?></td>
                            </tr>
                            <?php 
                                }
                            } 
                            ?>
                        </tbody>
                    </table>

                    <!-- Chart Rendering Script using Chart.js -->

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const ctx = document.getElementById('enrollmentLineChart').getContext('2d');

                            const labels = [<?php foreach ($results as $row) echo '"' . $row->academicyear . '",'; ?>];
                            const datasets = [
                                                    { label: 'PG', data: [<?php foreach ($results as $r) echo $r->pg . ','; ?>], borderColor: 'red', fill: false },
                                                    { label: 'PP1', data: [<?php foreach ($results as $r) echo $r->pp1 . ','; ?>], borderColor: 'green', fill: false },
                                                    { label: 'PP2', data: [<?php foreach ($results as $r) echo $r->pp2 . ','; ?>], borderColor: 'blue', fill: false },
                                                    { label: 'G1', data: [<?php foreach ($results as $r) echo $r->grade1 . ','; ?>], borderColor: 'orange', fill: false },
                                                    { label: 'G2', data: [<?php foreach ($results as $r) echo $r->grade2 . ','; ?>], borderColor: 'purple', fill: false },
                                                    { label: 'G3', data: [<?php foreach ($results as $r) echo $r->grade3 . ','; ?>], borderColor: 'brown', fill: false },
                                                    { label: 'G4', data: [<?php foreach ($results as $r) echo $r->grade4 . ','; ?>], borderColor: 'teal', fill: false },
                                                    { label: 'G5', data: [<?php foreach ($results as $r) echo $r->grade5 . ','; ?>], borderColor: 'darkgreen', fill: false },
                                                    { label: 'G6', data: [<?php foreach ($results as $r) echo $r->grade6 . ','; ?>], borderColor: 'deeppink', fill: false },
                                                    { label: 'G7', data: [<?php foreach ($results as $r) echo $r->grade7 . ','; ?>], borderColor: 'crimson', fill: false },
                                                    { label: 'G8', data: [<?php foreach ($results as $r) echo $r->grade8 . ','; ?>], borderColor: 'gold', fill: false },
                                                    { label: 'G9', data: [<?php foreach ($results as $r) echo $r->grade9 . ','; ?>], borderColor: 'gray', fill: false }
                                                ];

                            const config = {
                                type: 'line',
                                data: { labels, datasets },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: { position: 'bottom' },
                                        title: { display: false }
                                    },
                                    scales: {
                                        y: { beginAtZero: true, title: { display: true, text: 'Number of Students' }},
                                        x: { title: { display: true, text: 'Academic Year' }}
                                    }
                                }
                            };

                            new Chart(ctx, config);
                        });
                        </script>

                </div> <!-- End Table Section -->
            </div> <!-- End Table Responsive -->
        </div> <!-- End Card Body -->
    </div> <!-- End Card -->
    
</div> <!-- End Column -->




            </div> <!-- end wrapper -->
            <!-- Core Scripts - Include with every page -->
            <script src="assets/plugins/jquery-1.10.2.js"></script>
            <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
            <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
            <script src="assets/plugins/pace/pace.js"></script>
            <script src="assets/scripts/siminta.js"></script> <!-- Page-Level Plugin Scripts-->
            <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
            <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
            
            <script>
    $(document).ready(function () {
        $('#showChartBtn').click(function () {
            $('#tableSection').hide();
            $('#chartSection').show();
        });

        $('#showTableBtn').click(function () {
            $('#chartSection').hide();
            $('#tableSection').show();
        });
    });
</script>

            <script>
                $(document).ready(function() {
                    $('#dataTables-example').dataTable();
                    $('#dataTables-example1').dataTable();
                });
            </script>
            <script>
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script> 


            
        <?php
      if ($messagestate=='added' or $messagestate=='deleted'){
        echo '<script type="text/javascript">
        function hideMsg()
        {
          document.getElementById("popup").style.visibility="hidden";
        }
        document.getElementById("popup").style.visibility="visible";
        window.setTimeout("hideMsg()",5000);
        </script>';
      }
      ?>
</body>

</html> <?php }  ?>