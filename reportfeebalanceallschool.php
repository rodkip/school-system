<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'])==0) {
    header('location:logout.php');
} else {
    $academicyear=$_GET['academicyear'];
    if(isset($_POST['submit'])) {
        $academicyear= $_POST['academicyear'];           
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Balance Whole School</title>
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
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
            <div class="logo">
                <?php      
                $searchquery="SELECT * from schooldetails";
                $qry = $dbh -> prepare($searchquery);
                $qry->execute();
                $row=$qry->fetchAll(PDO::FETCH_OBJ);
                $cnt=1;
                if($qry->rowCount() > 0) {
                    foreach($row as $rlt) {   
                ?>
                <img src="images/schoollogo.png" alt="School Logo">
                <div>
                    <div class="school-name"><?php echo $rlt->schoolname; ?></div>
                    <div class="school-details">
                        Tel: <?php echo htmlentities($rlt->phonenumber); ?>,<br>
                        <?php echo htmlentities($rlt->postaladdress); ?>,<br>
                        Email: <?php echo htmlentities($rlt->emailaddress); ?>
                    </div>
                </div>
                <?php $cnt=$cnt+1;}} ?>
            </div>
        
            <div class="report-title">
            All Grades Fee Payments Analysis
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
                        <th>Population</th>
                        <th title="Brought Forward from Previous Academic Year">Accrual</th>

                        <th>Prepay</th>
                        <th>TotalExpectedFee</th>
                        <th>Sum Paid</th>
                        <th>Owed by Learners</th>
                        <th>Credit to Learners</th>
                        <th>% Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT  
                                classdetails.academicyear, 
                                classdetails.gradefullname, 
                                COUNT(feebalances.studentAdmNo) AS countadmno, 

                                SUM(feebalances.arrears) AS sumarrears, 
                                SUM(CASE WHEN feebalances.arrears > 0 THEN feebalances.arrears ELSE 0 END) AS sum_arrears_owed,
                                SUM(CASE WHEN feebalances.arrears < 0 THEN ABS(feebalances.arrears) ELSE 0 END) AS sum_arrears_credit,

                                SUM(feebalances.totalfee) AS sumfee, 
                                SUM(feebalances.totalpaid) AS sumpaid, 
                                SUM(feebalances.yearlybal) AS sumbal,

                                SUM(CASE WHEN feebalances.yearlybal > 0 THEN feebalances.yearlybal ELSE 0 END) AS sum_owed,
                                SUM(CASE WHEN feebalances.yearlybal < 0 THEN ABS(feebalances.yearlybal) ELSE 0 END) AS sum_credit,

                                COUNT(CASE WHEN feebalances.yearlybal > 0 THEN 1 ELSE NULL END) AS count_owing,
                                COUNT(CASE WHEN feebalances.yearlybal < 0 THEN 1 ELSE NULL END) AS count_credit,

                                (
                                    (SUM(feebalances.totalpaid) - 
                                    SUM(CASE WHEN feebalances.yearlybal < 0 THEN ABS(feebalances.yearlybal) ELSE 0 END)
                                    ) / 
                                    NULLIF(
                                        SUM(feebalances.totalfee) + 
                                        SUM(CASE WHEN feebalances.arrears > 0 THEN feebalances.arrears ELSE 0 END),
                                    0)
                                ) * 100 AS percentpaid

                            FROM feebalances 
                            INNER JOIN classdetails 
                                ON feebalances.gradefullname = classdetails.gradefullname 
                            WHERE classdetails.academicyear = :academicyear
                            GROUP BY classdetails.academicyear, classdetails.gradefullname 
                            ORDER BY percentpaid DESC";
                                                
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':academicyear', $academicyear, PDO::PARAM_STR);
                    $query->execute();
                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    $classfeebalance = $total_owed = $total_credit = $total_arrears_owed = $total_arrears_credit = 0;
                    
                    if($query->rowCount() > 0) {
                        foreach($results as $row) {      
                    ?>   
                    <tr>
                        <td><?php echo $cnt;?></td>
                        <td class="text-left"><?php echo htmlentities($row->gradefullname);?></td>
                        <td><?php echo htmlentities($row->countadmno);?></td>
                        <td class="highlight-red"><?php echo number_format($row->sum_arrears_owed); ?></td>
                        <td class="highlight-green"><?php echo number_format($row->sum_arrears_credit); ?></td>
                        <td><?php echo number_format(($row->sumfee)+($row->sum_arrears_owed));?></td>
                        <td ><?php echo number_format($row->sumpaid);?></td>
                        <td class="highlight-red">
                            <?php 
                                echo number_format($row->sum_owed);
                                echo " (".$row->count_owing.")";
                            ?>
                        </td>
                        <td class="highlight-green">
                            <?php 
                                echo number_format($row->sum_credit);
                                echo " (".$row->count_credit.")";
                            ?>
                        </td>
                        
                        <td><?php echo is_numeric($row->percentpaid) ? number_format($row->percentpaid,2) : '0.00'; ?>%</td>
                    </tr>
                    <?php 
                        $classfeebalance += $row->sumbal;
                        $total_owed += $row->sum_owed;
                        $total_credit += $row->sum_credit;
                        $total_arrears_owed += $row->sum_arrears_owed;
                        $total_arrears_credit += $row->sum_arrears_credit;
                        $cnt=$cnt+1;
                        } 
                    } else {
                        echo '<tr><td colspan="12" class="text-center">No records found</td></tr>';
                    }
                    ?> 
                    <!-- Summary Row -->
                    <tr class="bold-row">
                        <td colspan="3">Total</td>
                        <td class="highlight-red"><?php echo number_format($total_arrears_owed); ?></td>
                        <td class="highlight-green"><?php echo number_format($total_arrears_credit); ?></td>
                        
                        <td><?php echo isset($results[0]->sumfee) ? number_format(array_sum(array_column($results, 'sumfee'))) : '0'; ?></td>
                        <td class="highlight-green"><?php echo isset($results[0]->sumpaid) ? number_format(array_sum(array_column($results, 'sumpaid'))) : '0'; ?></td>
                        <td class="highlight-red"><?php echo number_format($total_owed); ?></td>
                        <td class="highlight-green"><?php echo number_format($total_credit); ?></td>
                        
                        <td><?php 
                            $totalPaid = isset($results[0]->sumpaid) ? array_sum(array_column($results, 'sumpaid')) : 0;
                            $totalFee = (isset($results[0]->sumfee) ? array_sum(array_column($results, 'sumfee')) : 0) + ($total_arrears_owed - $total_arrears_credit);
                            $percentPaid = ($totalFee != 0) ? ($totalPaid / $totalFee) * 100 : 0;
                            echo number_format($percentPaid, 2); ?>%
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="margin: 15px 0; font-size: 14px; color: #555;">
    <strong>Note:</strong> 
    <ul style="margin-top: 5px; padding-left: 20px; list-style: disc;">
        <li><strong>TotalExpectedFee</strong> is the sum of the current academic year’s fee structure and the <em>Bal BF (Owed)</em> from the previous year.</li>
        <li><strong>% Paid</strong> is calculated as: 
            <code>(Sum Paid − Credit to Learners) ÷ TotalExpectedFee × 100</code>.
        </li>
    </ul>
</div>

        </div>
        <?php include('reportfooter.php'); ?>
        
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>