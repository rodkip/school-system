<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
} else {
    $gradefullname = $_GET['gradefullname'] ?? '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report - Other Items Payments</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Google Fonts -->
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
                $rows = $qry->fetchAll(PDO::FETCH_OBJ);
                if ($qry->rowCount() > 0) {
                    foreach ($rows as $rlt) {
                ?>
                <img src="images/schoollogo.png" alt="School Logo" />
                <div>
                    <div class="school-name"><?php echo htmlentities($rlt->schoolname); ?></div>
                    <div class="school-details">
                        Tel: <?php echo htmlentities($rlt->phonenumber); ?>,<br />
                        <?php echo htmlentities($rlt->postaladdress); ?>,<br />
                        Email: <?php echo htmlentities($rlt->emailaddress); ?>
                    </div>
                </div>
                <?php
                    }
                }
                ?>
            </div>
            <div class="report-title">
                Other Items Payments Breakdown Per Grade
                <div class="report-subtitle">
                    Grade: <b><?php echo htmlentities($gradefullname); ?></b>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-left">Student Name</th>
                        <th>Adm No</th>
                         <th>Stream</th>
                        <?php
                        try {
                            $itemSql = "SELECT id, otherpayitemname FROM otherpayitems ORDER BY otherpayitemname ASC";
                            $itemStmt = $dbh->prepare($itemSql);
                            $itemStmt->execute();
                            $items = $itemStmt->fetchAll(PDO::FETCH_OBJ);

                            foreach ($items as $item) {
                                echo "<th>" . htmlentities($item->otherpayitemname) . "</th>";
                            }
                        } catch (PDOException $e) {
                            echo "<th colspan='3' class='highlight-red'>Error loading payment items</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($items)) {
                        try {
                            $caseStatements = '';
                            foreach ($items as $item) {
                                $col = "item_" . $item->id;
                                $caseStatements .= ", SUM(CASE WHEN a.item_id = {$item->id} THEN a.amount ELSE 0 END) AS `$col`";
                            }

                            $sql = "SELECT 
                                    c.studentname,
                                    c.studentadmno,
                                    e.stream AS streamname
                                    $caseStatements
                                FROM otheritemspayments_breakdown a
                                INNER JOIN otheritemspayments b ON a.payment_id = b.id
                                INNER JOIN studentdetails c ON b.studentadmno = c.studentadmno
                                LEFT JOIN classentries e ON b.studentadmno = e.studentadmno 
                                    AND SUBSTRING(e.gradefullname, 1, 4) = b.financialyear
                                WHERE e.gradefullname = :grade
                                GROUP BY c.studentadmno, c.studentname, e.stream
                                ORDER BY streamname ASC, c.studentname ASC
                                ";

                            $stmt = $dbh->prepare($sql);
                            $stmt->bindParam(':grade', $gradefullname, PDO::PARAM_STR);
                            $stmt->execute();
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $cnt = 1;

                            if ($stmt->rowCount() > 0) {
                                foreach ($results as $row) {
                                    echo "<tr>";
                                    echo "<td>" . $cnt++ . "</td>";
                                    echo "<td class='text-left'>" . htmlentities($row['studentname']) . "</td>";
                                    echo "<td>" . htmlentities($row['studentadmno']) . "</td>";
                                    echo "<td>" . htmlentities($row['streamname']) . "</td>";
                                    foreach ($items as $item) {
                                        $key = "item_" . $item->id;
                                        echo "<td>" . number_format($row[$key]) . "</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='" . (3 + count($items)) . "' class='text-center'>No other payment records found for this grade.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='" . (3 + count($items)) . "' class='text-center highlight-red'>Error loading other payments data</td></tr>";
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="bold-row">
                        <td colspan="4" class="text-left">Total</td>
                        <?php
                        // Initialize totals array
                        $columnTotals = array_fill_keys(array_map(fn($i) => "item_{$i->id}", $items), 0.0);

                        // Sum totals
                        foreach ($results ?? [] as $row) {
                            foreach ($items as $item) {
                                $key = "item_" . $item->id;
                                $columnTotals[$key] += $row[$key];
                            }
                        }

                        // Output totals
                        foreach ($items as $item) {
                            $key = "item_" . $item->id;
                            echo "<td>" . number_format($columnTotals[$key]) . "</td>";
                        }
                        ?>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php include('reportfooter.php'); ?>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>
