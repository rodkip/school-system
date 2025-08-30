<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'])==0) {
    header('location:logout.php');
} else {
    $gradefullname=$_GET['gradefullname'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report-Fee Payments</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet" />
    <style>
        .summary-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .summary-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .summary-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: right;
        }
        .owed-by-students {
            color: red;
            font-weight: bold;
        }
        .owed-to-students {
            color: green;
            font-weight: bold;
        }
        .net-balance {
            font-weight: bold;
        }
        .highlight-red {
            color: red;
        }
        .highlight-green {
            color: green;
        }
        .bold-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .no-print {
            display: none;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            table {
                width: 100%;
                font-size: 12px;
            }
        }
        .print-button {
    margin: 20px auto;
    display: block;
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 25px;
    font-size: 14px;
    cursor: pointer;
    border-radius: 5px;
}

.print-button:hover {
    background-color: #45a049;
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
                Fee Balances Per Grade                
                <div class="report-subtitle">
                    Grade: <b><?php echo htmlentities($gradefullname); ?></b>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-left">Name</th>
                        <th class="text-center">AdmNo</th>
                        <th class="text-center">ChildStatus</th>
                        <th class="text-center">ChildTreat</th>
                        <th class="text-center">FeeTreat</th>
                        <th class="text-center">Boarding</th>
                        <th class="text-center">Bal BF</th>
                        <th class="text-center">TotalFee</th>
                        <th class="text-center">SumPaid</th>
                        <th class="text-center">Term1Bal</th>
                        <th class="text-center">Term2Bal</th>
                        <th class="text-center">Term3Bal</th>
                        <th class="text-center">YearlyBal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to get all students with fee balances, ordered by Present status first, then by yearly balance descending
                    $sql="SELECT fb.*, ce.childstatus
                            FROM feebalances fb
                            INNER JOIN classentries ce ON ce.classentryfullname = fb.feebalancecode
                            WHERE fb.gradefullname = :gradefullname
                            ORDER BY 
                                CASE WHEN ce.childstatus = 'Present' THEN 0 ELSE 1 END,
                                fb.yearlybal DESC";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
                    $query->execute();

                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    $sumarrears = $sumtotalfee = $sumtotalpaid = $sumfirsttermbal = $sumsecondtermbal = $sumthirdtermbal = $sumyearlybal = 0;
                    
                    // Variables for summary statistics
                    $sum_owed_by_students = 0;
                    $sum_owed_to_students = 0;
                    $student_count_owing = 0;
                    $student_count_owed = 0;
                    $present_students = 0;
                    $other_status_students = 0;
                    
                    if($query->rowCount() > 0) {
                        foreach($results as $row) {      
                            $sumarrears += $row->arrears; 
                            $sumtotalfee += $row->totalfee; 
                            $sumtotalpaid += $row->totalpaid; 
                            $sumfirsttermbal += $row->firsttermbal; 
                            $sumsecondtermbal += $row->secondtermbal; 
                            $sumthirdtermbal += $row->thirdtermbal; 
                            $sumyearlybal += $row->yearlybal; 
                            
                            // Track student status counts
                            if ($row->childstatus == 'Present') {
                                $present_students++;
                            } else {
                                $other_status_students++;
                            }
                            
                            // Calculate separate totals
                            if ($row->yearlybal > 0) {
                                $sum_owed_by_students += $row->yearlybal;
                                $student_count_owing++;
                            } elseif ($row->yearlybal < 0) {
                                $sum_owed_to_students += abs($row->yearlybal);
                                $student_count_owed++;
                            }
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $cnt; ?></td>
                        <td class="text-left"><?php echo htmlentities($row->studentname); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->studentadmno); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->childstatus); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->childtreatment); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->feetreatment); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->boarding); ?></td>
                        <td class="text-center"><?php echo number_format($row->arrears); ?></td>
                        <td class="text-center"><?php echo number_format($row->totalfee); ?></td>
                        <td class="text-center highlight-green"><?php echo number_format($row->totalpaid); ?></td>
                        <td class="text-center"><?php echo number_format($row->firsttermbal); ?></td>
                        <td class="text-center"><?php echo number_format($row->secondtermbal); ?></td>
                        <td class="text-center"><?php echo number_format($row->thirdtermbal); ?></td>
                        <td class="text-center <?php echo $yearlybal_class; ?>">
                            <b><?php echo number_format($row->yearlybal); ?></b>
                        </td>
                    </tr>
                    <?php $cnt++; }} ?>
                    
                    <!-- Summary Row -->
                    <tr class="bold-row">
                        <td colspan="7" class="text-left">Total (Ksh)</td>
                        <td class="text-center"><?php echo number_format($sumarrears); ?></td>
                        <td class="text-center"><?php echo number_format($sumtotalfee); ?></td>
                        <td class="text-center highlight-green"><?php echo number_format($sumtotalpaid); ?></td>
                        <td class="text-center"><?php echo number_format($sumfirsttermbal); ?></td>
                        <td class="text-center"><?php echo number_format($sumsecondtermbal); ?></td>
                        <td class="text-center"><?php echo number_format($sumthirdtermbal); ?></td>
                        <td class="text-center <?php echo $total_yearlybal_class; ?>">
                            <b><?php echo number_format($sumyearlybal); ?></b>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary Table -->
            <table class="summary-table">
                <tr>
                    <td style="text-align: left; padding: 5px; border: 1px solid #ddd; background-color: #f2f2f2;">
                        <strong>Summary:</strong> 
                        <span class="owed-by-students">Ksh <?php echo number_format($sum_owed_by_students); ?> owed by <?php echo $student_count_owing; ?> students</span> | 
                        <span class="owed-to-students">Ksh <?php echo number_format($sum_owed_to_students); ?> credit to <?php echo $student_count_owed; ?> students</span> | 
                        Net Balance: <span style="color: <?php echo ($sumyearlybal > 0 ? 'red' : ($sumyearlybal < 0 ? 'green' : 'black')); ?>; font-weight: bold;">Ksh <?php echo number_format($sumyearlybal); ?></span>
                        | Present Learners: <?php echo $present_students; ?> | Other Status: <?php echo $other_status_students; ?>
                    </td>
                </tr>
            </table>

            <?php 
            // Query to find students in the grade without fee records
            $sql = "SELECT DISTINCT ce.studentadmno, sd.studentname 
                    FROM classentries ce 
                    JOIN studentdetails sd ON ce.studentadmno = sd.studentadmno 
                    WHERE ce.gradefullname = :grade 
                    AND NOT EXISTS (
                        SELECT 1 FROM feebalances fb 
                        WHERE fb.studentadmno = ce.studentadmno 
                        AND fb.gradefullname = :grade
                    )"; 
            $query = $dbh->prepare($sql); 
            $query->bindParam(':grade', $gradefullname, PDO::PARAM_STR); 
            $query->execute(); 
            $missingStudents = $query->fetchAll(PDO::FETCH_OBJ); 
            if ($query->rowCount() > 0) { 
            ?> 
                <div style="margin-top: 15px; padding: 8px; background-color: #f9f9f9; border: 1px solid #ddd;">
                    <div style="margin-bottom: 5px; color: #333; font-weight: bold;"> 
                        Learners in <strong><?php echo htmlentities($gradefullname); ?></strong> without fee records: <strong><?php echo $query->rowCount(); ?></strong> 
                    </div> 
                    <div style="font-size: 12px; color: #444;">
                        <?php 
                        $missing_list = array();
                        foreach ($missingStudents as $student) { 
                            $missing_list[] = "<strong>" . htmlentities($student->studentadmno) . "</strong> - " . htmlentities($student->studentname);
                        } 
                        echo implode(" | ", $missing_list);
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php include('reportfooter.php'); ?>
        </div>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
</body>
</html>