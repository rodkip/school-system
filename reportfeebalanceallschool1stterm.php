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
    <title>Fee BalWHole School 1st-TERM</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet" />
    <style>
        .highlight-red {
            color: red;
            font-weight: bold;
        }
        .highlight-green {
            color: green;
            font-weight: bold;
        }
        .bold-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .text-left {
            text-align: left;
        }
        .fully-paid-population {
            font-weight: bold;
            font-family: monospace;
        }
        .fully-paid-population .fully-paid {
            margin-right: 6px;
        }
        .fully-paid-population.all-paid .fully-paid,
        .fully-paid-population.all-paid .population {
            color: green;
        }
        .fully-paid-population.not-all-paid .fully-paid {
            color: red;
        }
        .fully-paid-population.not-all-paid .population {
            color: black;
        }
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
                <?php 
                    }
                } 
                ?>
            </div>
        
            <div class="report-title">
                1st-TERM All Grades Fee Payments Analysis
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
            <th>PreviousYear Bal B/F</th>
            <th>1st-Term Fee</th>
            <th>Fee Balance</th>
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
        $total_fully_paid = 0;
        $total_population = 0;
        $total_expected = 0;
        $total_arrearsbalbf = 0;
        $total_no_payments = 0;

        $sql = "SELECT  
            cd.academicyear, 
            cd.gradefullname, 
            COUNT(fb.studentAdmNo) AS countadmno,
            SUM(fb.arrears) AS sumarrears, 
            SUM(CASE WHEN fb.arrears > 0 THEN fb.arrears ELSE 0 END) AS sum_arrears_owed,
            SUM(COALESCE(fb.arrears, 0)) AS sum_arrearsbalbf,
            SUM(fb.firsttermfee) + SUM(fb.othersfee) + SUM(fb.arrears) AS sumfee,
            SUM(CASE WHEN fb.firsttermbal IS NULL THEN 0 ELSE fb.firsttermbal END) AS sum_owed,
            COUNT(CASE WHEN fb.firsttermbal > 0 THEN 1 ELSE NULL END) AS count_owing,
            COUNT(CASE WHEN fb.firsttermbal = 0 THEN 1 ELSE NULL END) AS count_fully_paid,
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
                $percent_paid = $row->sumfee > 0 
                    ? round((($row->sumfee - $row->sum_owed) / ($row->sumfee + $row->sum_arrearsbalbf)) * 100, 2) 
                    : 0;

                $all_paid_class = ($row->count_fully_paid === $row->countadmno) ? 'all-paid' : 'not-all-paid';
        ?>
        <tr>
            <td><?php echo $cnt++; ?></td>
            <td class="text-left"><?php echo htmlentities($row->gradefullname); ?></td>                        
            <td><?php echo number_format($row->sum_arrearsbalbf); ?></td>                         
            <td><?php echo number_format($row->sumfee); ?></td>
            <td class="highlight-red"><?php echo number_format($row->sum_owed); ?></td>
            <td><?php echo $percent_paid; ?>%</td>
            <td><?php echo $row->count_no_fee_payment+$row->countadmno; ?></td>
            <td><?php echo $row->count_fully_paid; ?></td>
            <td><?php echo $row->countadmno-$row->count_fully_paid;  ?></td>
            <td><?php echo $row->count_no_fee_payment; ?></td>
            
        </tr>
        <?php
                $total_owed += $row->sum_owed;                            
                $total_arrearsbalbf += $row->sum_arrearsbalbf;
                $total_fully_paid += $row->count_fully_paid;
                $total_population += $row->countadmno;
                $total_expected += ($row->sumfee + $row->sum_arrearsbalbf);
                $total_no_payments += $row->count_no_fee_payment;
            }
        } else {
            echo '<tr><td colspan="8" class="text-center">No records found</td></tr>';
        }
        ?>
        <tr class="bold-row">
            <td colspan="2">Total</td>  
            <td><?php echo number_format($total_arrearsbalbf); ?></td>                                              
            <td><?php echo number_format($total_expected); ?></td>
            <td class="highlight-red"><?php echo number_format($total_owed); ?></td>                       
            <td><?php echo $total_expected > 0 ? round((($total_expected - $total_owed) / $total_expected) * 100, 2) : 0; ?>%</td>
            <td><?php echo number_format($total_no_payments+$total_population); ?></td>
            <td>
                <?php
                    $all_paid_class = ($total_fully_paid === $total_population) ? 'all-paid' : 'not-all-paid';
                ?>
                <span class="fully-paid-population <?php echo $all_paid_class; ?>">
                    <span class="fully-paid"><?php echo $total_fully_paid; ?></span> 
                </span>
            </td>
            <td><?php echo number_format($total_no_payments); ?></td>
            <td><?php echo number_format($total_no_payments); ?></td>
            
        </tr>
    </tbody>
</table>

        </div>
<!-- Explanation Block -->
<div class="explanation" style="font-size: 12px; margin-top: 10px;">
    <strong>Explanation of % Paid Calculation:</strong><br>
    The <strong>% Paid</strong> value reflects the portion of fees that have been cleared by learners in each grade, including balances carried forward from the previous year. It is calculated as:<br>
    <code>% Paid = ((Expected - Outstanding) / Expected) * 100</code><br>
    Where:<br>
    <ul style="margin-top: 5px; margin-left: 20px;">
        <li><strong>Expected</strong> = 1st-Term Fee + Previous Year Balance B/F</li>
        <li><strong>Outstanding</strong> = 1st-Term Balance</li>
    </ul>
    This methodology provides a holistic view of the fee compliance status for each grade during the first term.
</div>

        <?php include('reportfooter.php'); ?>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>
