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
<html lang="en">
<head>
    <title>Report - Fee Balances 2nd Term</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
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
            $qry = $dbh->prepare("SELECT * FROM schooldetails LIMIT 1");
            $qry->execute();
            $row = $qry->fetch(PDO::FETCH_OBJ);
            if ($row) {
                echo '<img src="images/schoollogo.png" alt="School Logo" />';
                echo '<div class="school-info">';
                echo '<div class="school-name">' . htmlentities($row->schoolname) . '</div>';
                echo '<div class="school-details">Tel: ' . htmlentities($row->phonenumber) . '<br>'
                    . htmlentities($row->postaladdress) . '<br>Email: ' . htmlentities($row->emailaddress) . '</div>';
                echo '</div>';
            }
            ?>
        </div>
        <div class="report-title">
            <i class="fas fa-scale-balanced me-2" style="color: #6f42c1;"></i>2nd-TERM Fee Balances Per Grade
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
                    <th>#</th>
                    <th class="text-left">Name</th>
                    <th>AdmNo</th>
                    <th>ChildStatus</th>
                    <th>ChildTreat</th>
                    <th>FeeTreat</th>
                    <th>Boarding</th>
                    <th>Bal B/F</th>
                    <th>Term2Bal</th>
                    <th>TotalBal</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT fb.*, ce.stream, ce.childstatus
                    FROM feebalances fb
                    INNER JOIN classentries ce ON ce.classentryfullname = fb.feebalancecode
                    WHERE fb.gradefullname = :grade";

            if ($stream !== '') {
                $sql .= " AND ce.stream = :stream";
            }
            // Order by Present status first, then by total balance descending
            $sql .= " ORDER BY 
                      CASE WHEN ce.childstatus = 'Present' THEN 0 ELSE 1 END,
                      (fb.firsttermbal + fb.secondtermbal) DESC";

            $query = $dbh->prepare($sql);
            $query->bindParam(':grade', $gradefullname, PDO::PARAM_STR);
            if ($stream !== '') {
                $query->bindParam(':stream', $stream, PDO::PARAM_STR);
            }
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            $cnt = 1;
            $sum_firsttermbal = 0;
            $sum_secondtermbal = 0;
            $sum_total = 0;
            $sum_owed = 0;
            $sum_credit = 0;
            $count_owing = 0;
            $count_credit = 0;
            $count_zero = 0;
            $present_students = 0;
            $other_status_students = 0;

            foreach ($results as $row) {
                $term1 = (float)$row->firsttermbal;
                $term2 = (float)$row->secondtermbal;
                $total = $term1 + $term2;

                $sum_firsttermbal += $term1;
                $sum_secondtermbal += $term2;
                $sum_total += $total;

                // Track student status counts
                if ($row->childstatus == 'Present') {
                    $present_students++;
                } else {
                    $other_status_students++;
                }

                if ($total > 0) {
                    $sum_owed += $total;
                    $count_owing++;
                } elseif ($total < 0) {
                    $sum_credit += abs($total);
                    $count_credit++;
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

                echo '<tr>';
                echo '<td>' . $cnt++ . '</td>';
                echo '<td class="text-left">' . htmlentities($row->studentname) . '</td>';
                echo '<td>' . htmlentities($row->studentadmno) . '</td>';
                echo '<td>' . htmlentities($row->childstatus) . '</td>';
                echo '<td>' . htmlentities($row->childtreatment) . '</td>';
                echo '<td>' . htmlentities($row->feetreatment) . '</td>';
                echo '<td>' . htmlentities($row->boarding) . '</td>';
                echo '<td>' . number_format($term1) . '</td>';
                echo '<td>' . number_format($term2) . '</td>';
                echo '<td class="' . $total_class . '"><strong>' . number_format($total) . '</strong></td>';
                echo '</tr>';
            }
            ?>
            <tr class="bold-row">
                <td colspan="7" class="text-left">Total (Ksh)</td>
                <td><?php echo number_format($sum_firsttermbal); ?></td>
                <td><?php echo number_format($sum_secondtermbal); ?></td>
                <td class="<?php echo ($sum_total > 0 ? 'highlight-red' : ($sum_total < 0 ? 'highlight-green' : '')); ?>">
                    <?php echo number_format($sum_total); ?>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Summary Table -->
        <table class="summary-table">
            <tr>
                <td>
                    <strong>Summary:</strong>
                    <span class="owed-by-students">Ksh <?php echo number_format($sum_owed); ?> owed (<?php echo $count_owing; ?> learners)</span> |
                    <span class="owed-to-students">Ksh <?php echo number_format($sum_credit); ?> credit (<?php echo $count_credit; ?> learners)</span> |
                    <span style="color: #333;">Zero Balance: <strong>(<?php echo $count_zero; ?> learners)</strong></span> |
                    Net: <span class="net-balance" style="color: <?php echo ($sum_total > 0 ? 'red' : ($sum_total < 0 ? 'green' : '#333')); ?>;">
                        Ksh <?php echo number_format($sum_total); ?>
                    </span>
                    | Present Learners: <?php echo $present_students; ?> | Other Status: <?php echo $other_status_students; ?>
                </td>
            </tr>
        </table>

        <!-- Missing Students Section -->  
        <?php
        // Query for students without fee records for this grade
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

    <button class="print-button no-print" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Report</button>
</div>

<!-- Font Awesome CDN for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>