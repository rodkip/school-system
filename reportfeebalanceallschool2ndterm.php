<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Redirect if not logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$academicyear = $_GET['academicyear'] ?? '';
if (isset($_POST['submit'])) {
    $academicyear = $_POST['academicyear'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Balance - Whole School 2nd TERM</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet">
    <style>
        .highlight-red { color: red; font-weight: bold; }
        .bold-row { font-weight: bold; background-color: #f0f0f0; }
        .text-left { text-align: left; }
        .fully-paid-population { font-weight: bold; font-family: monospace; }
        .fully-paid-population .fully-paid { margin-right: 6px; }
        .all-paid .fully-paid, .all-paid .population { color: green; }
        .not-all-paid .fully-paid { color: red; }
        .not-all-paid .population { color: black; }
    </style>
</head>
<body>
    <div class="container">
        <!-- School Header -->
        <div class="header">
            <div class="logo">
                <?php
                $qry = $dbh->prepare("SELECT * FROM schooldetails");
                $qry->execute();
                $school = $qry->fetch(PDO::FETCH_OBJ);
                if ($school):
                ?>
                <img src="images/schoollogo.png" alt="School Logo">
                <div>
                    <div class="school-name"><?= htmlentities($school->schoolname) ?></div>
                    <div class="school-details">
                        Tel: <?= htmlentities($school->phonenumber) ?>,<br>
                        <?= htmlentities($school->postaladdress) ?>,<br>
                        Email: <?= htmlentities($school->emailaddress) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="report-title">
                2nd-TERM All Grades Fee Payments Analysis
                <div class="report-subtitle">Academic Year: <b><?= htmlentities($academicyear) ?></b></div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-container">
           <table>
    <thead>
        <tr>
            <th>#</th>
            <th class="text-left">Grade</th>
            <th>1st-Term Bal B/F</th>
            <th>2nd-Term Fee</th>
            <th>2nd-Term Balance</th>
            <th>% Paid</th> 
            <th>Population</th>
            <th>Fully Paid</th>
            <th>With Balances</th>
            <th>No Payments</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_firsttermbalbf = 0;
        $total_expected = 0;
        $total_owed = 0;
        $total_fully_paid = 0;
        $total_population = 0;
        $total_no_payments = 0;

        $sql = "
            SELECT 
                cd.academicyear,
                cd.gradefullname,
                COUNT(fb.studentAdmNo) AS countadmno,
                SUM(fb.secondtermfee) AS sumfee,
                SUM(COALESCE(fb.firsttermbal, 0)) AS sum_firsttermbalbf,
                SUM(COALESCE(fb.secondtermbal, 0)) AS sum_owed,
                COUNT(CASE WHEN fb.secondtermbal = 0 THEN 1 END) AS count_fully_paid,
                (
                    SELECT COUNT(ce.studentadmno)
                    FROM classentries ce
                    LEFT JOIN feepayments fp ON ce.studentadmno = fp.studentadmno
                    WHERE ce.gradefullname = cd.gradefullname
                        AND cd.academicyear = :academicyear
                        AND fp.studentadmno IS NULL
                ) AS count_no_fee_payment
            FROM feebalances fb
            INNER JOIN classdetails cd ON fb.gradefullname = cd.gradefullname
            WHERE cd.academicyear = :academicyear
            GROUP BY cd.academicyear, cd.gradefullname
            ORDER BY cd.gradefullname ASC";

        $query = $dbh->prepare($sql);
        $query->bindParam(':academicyear', $academicyear, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            $cnt = 1;
            foreach ($results as $row) {
                $total_payable = $row->sumfee + $row->sum_firsttermbalbf;
                $percent_paid = $total_payable > 0
                    ? round((($total_payable - $row->sum_owed) / $total_payable) * 100, 2)
                    : 0;

                $count_with_balance = $row->countadmno - $row->count_fully_paid;

                echo "<tr>
                    <td>{$cnt}</td>
                    <td class='text-left'>" . htmlentities($row->gradefullname) . "</td>
                    <td>" . number_format($row->sum_firsttermbalbf) . "</td>
                    <td>" . number_format($row->sumfee) . "</td>
                    <td class='highlight-red'>" . number_format($row->sum_owed) . "</td>
                    <td>{$percent_paid}%</td>
                    <td>" . number_format($row->countadmno + $row->count_no_fee_payment) . "</td>
                    <td>" . number_format($row->count_fully_paid) . "</td>
                    <td>" . number_format($count_with_balance) . "</td>
                    <td>" . number_format($row->count_no_fee_payment) . "</td>
                </tr>";

                $total_firsttermbalbf += $row->sum_firsttermbalbf;
                $total_expected += $total_payable;
                $total_owed += $row->sum_owed;
                $total_fully_paid += $row->count_fully_paid;
                $total_population += $row->countadmno;
                $total_no_payments += $row->count_no_fee_payment;
                $cnt++;
            }
        } else {
            echo '<tr><td colspan="10" class="text-center">No records found</td></tr>';
        }

        $overall_percent_paid = $total_expected > 0
            ? round((($total_expected - $total_owed) / $total_expected) * 100, 2)
            : 0;

        $total_status_class = ($total_fully_paid === $total_population) ? 'all-paid' : 'not-all-paid';
        ?>
        <tr class="bold-row">
            <td colspan="2">Total</td>
            <td><?= number_format($total_firsttermbalbf) ?></td>
            <td><?= number_format($total_expected) ?></td>
            <td class="highlight-red"><?= number_format($total_owed) ?></td>
            <td><?= $overall_percent_paid ?>%</td>
            <td><?= number_format($total_no_payments + $total_population) ?></td>
            <td>
                <span class="fully-paid-population <?= $total_status_class ?>">
                    <span class="fully-paid"><?= number_format($total_fully_paid) ?></span>
                </span>
            </td>
            <td><?= number_format($total_population - $total_fully_paid) ?></td>
            <td><?= number_format($total_no_payments) ?></td>
        </tr>
    </tbody>
</table>

        </div>
<!-- Explanation Block -->
<div class="explanation" style="font-size: 12px; margin-top: 10px;">
    <strong>Explanation of % Paid Calculation:</strong><br>
    The <strong>% Paid</strong> value reflects the portion of fees that have been cleared by learners in each grade, accounting for both current and previous term balances. It is calculated as:<br>
    <code>% Paid = ((Expected - Outstanding) / Expected) * 100</code><br>
    Where:<br>
    <ul style="margin-top: 5px; margin-left: 20px;">
        <li><strong>Expected</strong> = 2nd-Term Fee + 1st-Term Bal B/F</li>
        <li><strong>Outstanding</strong> = 2nd-Term Balance</li>
    </ul>
    This provides a comprehensive view of fee compliance across all grades.
</div>

<?php include('reportfooter.php'); ?>

    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>
