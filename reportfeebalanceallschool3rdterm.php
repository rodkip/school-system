<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
} else {
    $academicyear = $_GET['academicyear'] ?? '';
    if (isset($_POST['submit'])) {
        $academicyear = $_POST['academicyear'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee BalWHole School 3rd-TERM</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet" />
    <style>
        .highlight-red { color: red; font-weight: bold; }
        .highlight-green { color: green; font-weight: bold; }
        .bold-row { font-weight: bold; background-color: #f0f0f0; }
        .text-left { text-align: left; }
        .fully-paid-population { font-weight: bold; font-family: monospace; }
        .fully-paid-population .fully-paid { margin-right: 6px; }
        .fully-paid-population.all-paid .fully-paid,
        .fully-paid-population.all-paid .population { color: green; }
        .fully-paid-population.not-all-paid .fully-paid { color: red; }
        .fully-paid-population.not-all-paid .population { color: black; }
        .explanation { margin-top: 20px; font-size: 14px; line-height: 1.6; background: #f9f9f9; padding: 15px; border-left: 5px solid #007bff; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">
            <?php
            $searchquery = "SELECT * from schooldetails";
            $qry = $dbh->prepare($searchquery);
            $qry->execute();
            $rows = $qry->fetchAll(PDO::FETCH_OBJ);
            if ($qry->rowCount() > 0) {
                foreach ($rows as $rlt) {
            ?>
            <img src="images/schoollogo.png" alt="School Logo">
            <div>
                <div class="school-name"><?php echo htmlentities($rlt->schoolname); ?></div>
                <div class="school-details">
                    Tel: <?php echo htmlentities($rlt->phonenumber); ?>,<br>
                    <?php echo htmlentities($rlt->postaladdress); ?>,<br>
                    Email: <?php echo htmlentities($rlt->emailaddress); ?>
                </div>
            </div>
            <?php } } ?>
        </div>

        <div class="report-title">
            3rd-TERM All Grades Fee Payments Analysis
            <div class="report-subtitle">
                Academic Year: <b><?php echo htmlentities($academicyear); ?></b>
            </div>
        </div>
    </div>

    <div class="table-container">
   <table>
    <thead>
        <tr>
            <th>#</th>
            <th class="text-left">Grade</th>
            <th>Previous Terms Bal B/F</th>
            <th>3rd-Term Fee</th>
            <th>3rd-Term Balance</th>
            <th>% Paid</th>
            <th>Population</th>
            <th>Fully Paid</th>
            <th>With Balances</th>
            <th>No Payments</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_owed = 0;
        $total_firsttermbalbf = 0;
        $total_secondtermbalbf = 0;
        $total_fully_paid = 0;
        $total_population = 0;
        $total_expected = 0;
        $total_no_payments = 0;

        $sql = "
            SELECT 
                cd.academicyear,
                cd.gradefullname,
                COUNT(fb.studentAdmNo) AS countadmno,
                SUM(fb.thirdtermfee) AS sumfee,
                SUM(COALESCE(fb.firsttermbal, 0)) AS sum_firsttermbalbf,
                SUM(COALESCE(fb.secondtermbal, 0)) AS sum_secondtermbalbf,
                SUM(COALESCE(fb.thirdtermbal, 0)) AS sum_owed,
                COUNT(CASE WHEN fb.thirdtermbal = 0 THEN 1 END) AS count_fully_paid,
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
                $previous_bal = $row->sum_firsttermbalbf + $row->sum_secondtermbalbf;
                $total_payable = $row->sumfee + $previous_bal;
                $percent_paid = $total_payable > 0
                    ? round((($total_payable - $row->sum_owed) / $total_payable) * 100, 2)
                    : 0;

                $count_with_balance = $row->countadmno - $row->count_fully_paid;

                echo "<tr>
                    <td>{$cnt}</td>
                    <td class='text-left'>" . htmlentities($row->gradefullname) . "</td>
                    <td>" . number_format($previous_bal) . "</td>
                    <td>" . number_format($row->sumfee) . "</td>
                    <td class='highlight-red'>" . number_format($row->sum_owed) . "</td>
                    <td>{$percent_paid}%</td>
                    <td>" . number_format($row->countadmno + $row->count_no_fee_payment) . "</td>
                    <td>" . number_format($row->count_fully_paid) . "</td>
                    <td>" . number_format($count_with_balance) . "</td>
                    <td>" . number_format($row->count_no_fee_payment) . "</td>
                </tr>";

                $total_owed += $row->sum_owed;
                $total_firsttermbalbf += $row->sum_firsttermbalbf;
                $total_secondtermbalbf += $row->sum_secondtermbalbf;
                $total_fully_paid += $row->count_fully_paid;
                $total_population += $row->countadmno;
                $total_expected += $total_payable;
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
            <td><?= number_format($total_firsttermbalbf + $total_secondtermbalbf) ?></td>
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

    <div class="explanation" style="font-size: 12px;">
        <strong>Explanation of % Paid Calculation:</strong><br>
        The <strong>% Paid</strong> value reflects the portion of fees that have been cleared by learners in each grade, accounting for both current and previous term balances. It is calculated as:<br>
        <code>% Paid = 100 - ((3rdTerm Bal + 1stTerm Bal B/F + 2ndTerm Bal B/F) / (Expected3rdTerm Fee + 1stTerm Bal B/F + 2ndTerm Bal B/F)) * 100</code><br>
        This ensures a comprehensive overview of fee compliance per grade.
    </div>

    <?php include('reportfooter.php'); ?>
</div>

<button class="print-button no-print" onclick="window.print()">Print Report</button>
<a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>