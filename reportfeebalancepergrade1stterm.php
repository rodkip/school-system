<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $gradefullname = isset($_GET['gradefullname']) ? $_GET['gradefullname'] : '';
    $stream = isset($_GET['streamname']) ? trim($_GET['streamname']) : '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report-Fee Balance 1st Term</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            $searchquery = "SELECT * FROM schooldetails";
            $qry = $dbh->prepare($searchquery);
            $qry->execute();
            $row = $qry->fetchAll(PDO::FETCH_OBJ);
            if ($qry->rowCount() > 0) {
                foreach ($row as $rlt) {
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
            <?php }} ?>
        </div>
        <div class="report-title">
            1st-TERM Fee Balances Per Grade
            <div class="report-subtitle">
                Grade: <b><?php echo htmlentities($gradefullname); ?></b> | 
                Stream: <b><?php echo ($stream === '') ? 'All Streams' : htmlentities($stream); ?></b>
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
                    <th class="text-center">Term1Bal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Construct base SQL with childstatus and ordering
                $sql = "SELECT feebalances.*, classentries.stream, classentries.childstatus
                        FROM feebalances
                        INNER JOIN classentries ON classentries.classentryfullname = feebalances.feebalancecode
                        WHERE feebalances.gradefullname = :grade";

                if ($stream !== '') {
                    $sql .= " AND classentries.stream = :stream";
                }

                // Order by Present status first, then by firsttermbal descending
                $sql .= " ORDER BY 
                          CASE WHEN classentries.childstatus = 'Present' THEN 0 ELSE 1 END,
                          feebalances.firsttermbal DESC";

                $query = $dbh->prepare($sql);
                $query->bindParam(':grade', $gradefullname, PDO::PARAM_STR);

                if ($stream !== '') {
                    $query->bindParam(':stream', $stream, PDO::PARAM_STR);
                }

                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                $cnt = 1;
                $sumarrears = $sumfirsttermbal = $sumyearlybal = 0;
                $sum_owed_by_students = $sum_owed_to_students = 0;
                $student_count_owing = $student_count_owed = $student_count_zero = 0;
                $present_students = 0;
                $other_status_students = 0;

                if ($query->rowCount() > 0) {
                    foreach ($results as $row) {
                        $sumarrears += $row->arrears;
                        $sumfirsttermbal += $row->firsttermbal;
                        $sumyearlybal += $row->firsttermbal;

                        // Track student status counts
                        if ($row->childstatus == 'Present') {
                            $present_students++;
                        } else {
                            $other_status_students++;
                        }

                        if ($row->firsttermbal > 0) {
                            $sum_owed_by_students += $row->firsttermbal;
                            $student_count_owing++;
                        } elseif ($row->firsttermbal < 0) {
                            $sum_owed_to_students += abs($row->firsttermbal);
                            $student_count_owed++;
                        } else {
                            $student_count_zero++;
                        }

                        // Determine term balance color
                        $termbal_class = '';
                        if ($row->firsttermbal > 0) {
                            $termbal_class = 'highlight-red';
                        } elseif ($row->firsttermbal < 0) {
                            $termbal_class = 'highlight-green';
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
                    <td class="text-center <?php echo $termbal_class; ?>">
                        <?php echo number_format($row->firsttermbal); ?>
                    </td>
                </tr>
                <?php $cnt++; }} ?>
                <tr class="bold-row">
                    <td colspan="7" class="text-left">Total (Ksh)</td>
                    <td class="text-center <?php echo ($sumfirsttermbal > 0 ? 'highlight-red' : ($sumfirsttermbal < 0 ? 'highlight-green' : '')); ?>">
                        <?php echo number_format($sumfirsttermbal); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td style="text-align: left; padding: 5px; border: 1px solid #ddd; background-color: #f2f2f2;">
                    <strong>Summary:</strong> 
                    <span class="owed-by-students">Ksh <?php echo number_format($sum_owed_by_students); ?> owed (<?php echo $student_count_owing; ?> learners)</span> | 
                    <span class="owed-to-students">Ksh <?php echo number_format($sum_owed_to_students); ?> credit (<?php echo $student_count_owed; ?> learners)</span> | 
                    <span style="color: #555;">Zero balance: <strong>(<?php echo $student_count_zero; ?></strong> learners)</span> | 
                    Net: <span style="color: <?php echo ($sumyearlybal > 0 ? 'red' : ($sumyearlybal < 0 ? 'green' : 'black')); ?>; font-weight: bold;">Ksh <?php echo number_format($sumyearlybal); ?></span>
                    | Present Learners: <?php echo $present_students; ?> | Other Status: <?php echo $other_status_students; ?>
                </td>
            </tr>
        </table>

        <!-- Missing Students Section -->  
        <?php
        // Missing students query with conditional stream filter
        $sql = "SELECT DISTINCT ce.studentadmno, sd.studentname, ce.childstatus
                FROM classentries ce 
                JOIN studentdetails sd ON ce.studentadmno = sd.studentadmno 
                WHERE ce.gradefullname = :grade";

        if ($stream !== '') {
            $sql .= " AND ce.stream = :stream";
        }

        $sql .= " AND NOT EXISTS (
                    SELECT 1 FROM feebalances fb 
                    WHERE fb.studentadmno = ce.studentadmno 
                    AND fb.gradefullname = :grade)";

        $query = $dbh->prepare($sql);
        $query->bindParam(':grade', $gradefullname, PDO::PARAM_STR);

        if ($stream !== '') {
            $query->bindParam(':stream', $stream, PDO::PARAM_STR);
        }

        $query->execute();
        $missingStudents = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
        ?>
        <div style="margin-top: 15px; padding: 8px; background-color: #f9f9f9; border: 1px solid #ddd;">
            <div style="margin-bottom: 5px; color: #333; font-weight: bold;">
                Learners in <strong><?php echo htmlentities($gradefullname); ?><?php echo ($stream !== '' ? " - " . htmlentities($stream) : ""); ?></strong> without fee records:
                <strong>(<?php echo $query->rowCount(); ?>)</strong>
            </div>
            <div style="font-size: 12px; color: #444;">
                <?php
                $missing_list = array();
                foreach ($missingStudents as $student) {
                    $missing_list[] = "<strong>" . htmlentities($student->studentadmno) . "</strong> - " . 
                                     htmlentities($student->studentname) . 
                                     " (" . htmlentities($student->childstatus) . ")";
                }
                echo implode(" | ", $missing_list);
                ?>
            </div>
        </div>
        <?php } ?>
        <?php include('reportfooter.php'); ?>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
</body>
</html>