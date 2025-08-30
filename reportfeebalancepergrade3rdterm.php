<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $gradefullname = isset($_GET['gradefullname']) ? trim($_GET['gradefullname']) : '';
    $stream = isset($_GET['streamname']) ? trim($_GET['streamname']) : '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report-Fee Balance 3rd Term</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/reports.css" rel="stylesheet">
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
            $stmt = $dbh->prepare("SELECT * FROM schooldetails");
            $stmt->execute();
            $school = $stmt->fetch(PDO::FETCH_OBJ);
            ?>
            <img src="images/schoollogo.png" alt="School Logo">
            <div>
                <div class="school-name"><?php echo $school->schoolname; ?></div>
                <div class="school-details">
                    Tel: <?php echo $school->phonenumber; ?><br>
                    <?php echo $school->postaladdress; ?><br>
                    Email: <?php echo $school->emailaddress; ?>
                </div>
            </div>
        </div>
        <div class="report-title">
            <i class="fas fa-scale-balanced me-2" style="color: #6f42c1;"></i>3rd-TERM Fee Balances Per Grade
            <div class="report-subtitle">
                Grade: <strong><?php echo htmlentities($gradefullname); ?></strong> |
                Stream: <strong><?php echo ($stream === '') ? 'All Streams' : htmlentities($stream); ?></strong>
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
                    <th class="text-center">Bal B/F</th>
                    <th class="text-center">Term3Bal</th>
                    <th class="text-center">TotalBal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT fb.*, ce.stream, ce.childstatus,
                       (fb.firsttermbal + fb.secondtermbal + fb.thirdtermbal) AS totalbalance, 
                       (fb.firsttermbal + fb.secondtermbal) AS balbf
                        FROM feebalances fb
                        INNER JOIN classentries ce ON ce.classentryfullname = fb.feebalancecode
                        WHERE fb.gradefullname = :grade";

                if ($stream !== '') {
                    $sql .= " AND ce.stream = :stream";
                }

                // Order by Present status first, then by total balance descending
                $sql .= " ORDER BY 
                          CASE WHEN ce.childstatus = 'Present' THEN 0 ELSE 1 END,
                          totalbalance DESC";

                $query = $dbh->prepare($sql);
                $query->bindParam(':grade', $gradefullname, PDO::PARAM_STR);

                if ($stream !== '') {
                    $query->bindParam(':stream', $stream, PDO::PARAM_STR);
                }

                $query->execute();
                $rows = $query->fetchAll(PDO::FETCH_OBJ);

                $cnt = 1;
                $sum_bf = $sum_term3 = $sum_total = 0;
                $sum_owed_by_students = $sum_owed_to_students = 0;
                $count_positive = $count_negative = $count_zero = 0;
                $present_students = 0;
                $other_status_students = 0;

                foreach ($rows as $row) {
                    $bf = $row->balbf;
                    $term3 = $row->thirdtermbal;
                    $total = $row->totalbalance;

                    $sum_bf += $bf;
                    $sum_term3 += $term3;
                    $sum_total += $total;

                    // Track student status counts
                    if ($row->childstatus == 'Present') {
                        $present_students++;
                    } else {
                        $other_status_students++;
                    }

                    if ($total > 0) {
                        $sum_owed_by_students += $total;
                        $count_positive++;
                    } elseif ($total < 0) {
                        $sum_owed_to_students += abs($total);
                        $count_negative++;
                    } else {
                        $count_zero++;
                    }

                    // Determine total balance color
                    $total_class = '';
                    if ($total > 0) {
                        $total_class = 'highlight-red';
                    } elseif ($total < 0) {
                        $total_class = 'highlight-green';
                    }

                    echo '<tr>
                            <td class="text-center">' . $cnt++ . '</td>
                            <td class="text-left">' . htmlentities($row->studentname) . '</td>
                            <td class="text-center">' . htmlentities($row->studentadmno) . '</td>
                            <td class="text-center">' . htmlentities($row->childstatus) . '</td>
                            <td class="text-center">' . htmlentities($row->childtreatment) . '</td>
                            <td class="text-center">' . htmlentities($row->feetreatment) . '</td>
                            <td class="text-center">' . htmlentities($row->boarding) . '</td>
                            <td class="text-center">' . number_format($bf) . '</td>
                            <td class="text-center">' . number_format($term3) . '</td>
                            <td class="text-center ' . $total_class . '">' . number_format($total) . '</td>
                          </tr>';
                }
                ?>
                <tr class="bold-row">
                    <td colspan="7" class="text-left">Total (Ksh)</td>
                    <td class="text-center"><?php echo number_format($sum_bf); ?></td>
                    <td class="text-center"><?php echo number_format($sum_term3); ?></td>
                    <td class="text-center <?php echo ($sum_total > 0 ? 'highlight-red' : ($sum_total < 0 ? 'highlight-green' : '')); ?>">
                        <?php echo number_format($sum_total); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <table class="summary-table">
            <tr>
                <td style="text-align: left; padding: 5px; border: 1px solid #ddd; background-color: #f2f2f2;">
                    <strong>Summary:</strong> 
                    <span class="owed-by-students">Ksh <?php echo number_format($sum_owed_by_students); ?> owed (<?php echo $count_positive; ?> learners)</span> | 
                    <span class="owed-to-students">Ksh <?php echo number_format($sum_owed_to_students); ?> credit (<?php echo $count_negative; ?> learners)</span> | 
                    <span style="color: #555;">Zero balance: <strong>(<?php echo $count_zero; ?> learners)</strong></span> | 
                    Net: <span style="color: <?php echo ($sum_total > 0 ? 'red' : ($sum_total < 0 ? 'green' : 'black')); ?>; font-weight: bold;">Ksh <?php echo number_format($sum_total); ?></span>
                    | Present Learners: <?php echo $present_students; ?> | Other Status: <?php echo $other_status_students; ?>
                </td>
            </tr>
        </table>

        <!-- Missing Students Section -->      
        <?php
        $sql_missing = "SELECT DISTINCT ce.studentadmno, sd.studentname, ce.childstatus
                        FROM classentries ce
                        JOIN studentdetails sd ON ce.studentadmno = sd.studentadmno
                        WHERE ce.gradefullname = :grade";

        if ($stream !== '') {
            $sql_missing .= " AND ce.stream = :stream";
        }

        $sql_missing .= " AND NOT EXISTS (
                            SELECT 1 FROM feebalances fb 
                            WHERE fb.studentadmno = ce.studentadmno 
                            AND fb.gradefullname = :grade
                        )";

        $query_missing = $dbh->prepare($sql_missing);
        $query_missing->bindParam(':grade', $gradefullname, PDO::PARAM_STR);

        if ($stream !== '') {
            $query_missing->bindParam(':stream', $stream, PDO::PARAM_STR);
        }

        $query_missing->execute();
        $missingStudents = $query_missing->fetchAll(PDO::FETCH_OBJ);

        if ($query_missing->rowCount() > 0) {
            ?>
            <div style="margin-top: 15px; padding: 8px; background-color: #f9f9f9; border: 1px solid #ddd;">
                <div style="margin-bottom: 5px; color: #333; font-weight: bold;">
                    Learners in <strong><?php echo htmlentities($gradefullname); ?><?php echo ($stream !== '' ? " - " . htmlentities($stream) : ""); ?></strong> without fee records:
                    <strong>(<?php echo $query_missing->rowCount(); ?>)</strong>
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
        <?php
        }
        ?>

        <?php include('reportfooter.php'); ?>
    </div>
    <button class="print-button no-print" onclick="window.print()">Print Report</button>
</body>
</html>