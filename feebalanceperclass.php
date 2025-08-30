<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$searchgradefullname = isset($_POST['gradefullname']) ? trim($_POST['gradefullname']) : '';
$searchstreamname = isset($_POST['streamname']) ? trim($_POST['streamname']) : '';
$showAllStreams = isset($_POST['show_all_streams']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | Per Class Fee Balance</title>
    
    <!-- CSS Links -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    
    <style>
        /* Tab Styling */
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 2px solid #337ab7;
            margin-bottom: -1px;
        }
        
        .tablinks {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
            transition: all 0.3s ease;
            color: #495057;
        }
        
        .tablinks:hover {
            background-color: #e9ecef;
        }
        
        .tablinks.active {
            background-color: #ffffff;
            border-color: #337ab7;
            border-bottom: 1px solid #fff;
            color: #337ab7;
            position: relative;
            top: 1px;
        }
        
        .tabcontent {
            border: 1px solid #337ab7;
            border-top: none;
            padding: 20px;
            background-color: #fff;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Table Styling */
        .table-responsive {
            overflow-x: auto;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            position: relative;
            cursor: pointer;
            padding-right: 20px;
        }
        
        .table th.sort-asc::after {
            content: " ↑";
            position: absolute;
            right: 8px;
        }
        
        .table th.sort-desc::after {
            content: " ↓";
            position: absolute;
            right: 8px;
        }
        
        .global-search-container {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .global-search {
            width: 300px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .report-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
      
        /* Missing Students Alert */
        .missing-students {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        /* Form Elements */
        .form-control {
            max-width: 300px;
            display: inline-block;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .tab-nav {
                flex-wrap: wrap;
            }
            
            .tablinks {
                margin-bottom: 5px;
            }
            
            .form-control {
                max-width: 100%;
                width: 100%;
                margin-bottom: 10px;
            }
            
            .global-search {
                width: 100%;
                margin-top: 10px;
            }
            
            .global-search-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .report-buttons {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>
        <?php include('peryearfeebalanceperclasspopup.php');?>
        
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <table style="width: 100%;">
                        <tr>
                            <td>
                                <h2 class="page-header">
                                    Fees &amp; OtherItems reports/Grade 
                                    <i class="fas fa-users" aria-hidden="true" style="margin-left: 8px;"></i>
                                </h2>
                            </td>
                            <td style="width: 1%;">
                                <a href="#peryearfeebalanceperclass" data-toggle="modal" class="btn btn-warning">
                                    <i class="fas fa-file-alt"></i> Balance/Year/Grade Report
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form method="POST" class="form-inline justify-content-between align-items-center mb-3 p-3 bg-light border rounded shadow-sm flex-wrap">
                                <div class="form-group d-flex align-items-center flex-wrap">
                                    <label for="gradefullname" class="mr-2 font-weight-bold">Select GRADE:</label>
                                    <select name="gradefullname" id="gradefullname" class="form-control mr-3 mb-2 mb-md-0" required>
                                        <option value="">-- select grade --</option>
                                        <?php
                                        try {
                                            $stmt = $dbh->prepare('SELECT gradefullname FROM classdetails ORDER BY gradefullname DESC');
                                            $stmt->execute();
                                            $grades = $stmt->fetchAll();
                                            foreach ($grades as $grade): ?>
                                                <option value="<?= htmlentities($grade['gradefullname']) ?>" <?= ($grade['gradefullname'] == $searchgradefullname) ? 'selected' : '' ?>>
                                                    <?= htmlentities($grade['gradefullname']) ?>
                                                </option>
                                            <?php endforeach;
                                        } catch (PDOException $e) {
                                            echo "<option value=''>Error loading grades</option>";
                                        }
                                        ?>
                                    </select>

                                    <label for="streamname" class="mr-2 font-weight-bold">Select STREAM:</label>
                                    <select name="streamname" id="streamname" class="form-control mr-3 mb-2 mb-md-0" <?= $showAllStreams ? 'disabled' : '' ?>>
                                        <option value="">-- select stream --</option>
                                        <?php
                                        try {
                                            $stmt = $dbh->prepare('SELECT streamname FROM streams ORDER BY streamname DESC');
                                            $stmt->execute();
                                            $streams = $stmt->fetchAll();
                                            foreach ($streams as $stream): ?>
                                                <option value="<?= htmlentities($stream['streamname']) ?>" <?= ($stream['streamname'] == $searchstreamname) ? 'selected' : '' ?>>
                                                    <?= htmlentities($stream['streamname']) ?>
                                                </option>
                                            <?php endforeach;
                                        } catch (PDOException $e) {
                                            echo "<option value=''>Error loading streams</option>";
                                        }
                                        ?>
                                    </select>
                                    
                                    <input type="checkbox" class="form-check-input" id="show_all_streams" name="show_all_streams" <?= $showAllStreams ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="show_all_streams">Show all streams</label>
                        
                                    <button type="submit" name="submit" class="btn btn-primary mr-3">
                                        <i class="fa fa-search"></i> Search
                                    </button>

                                    <?php if (!empty($searchgradefullname)): ?>
                                        <span class="text-primary font-weight-bold" style="font-size: 20px;">
                                            Grade: <?= htmlentities($searchgradefullname); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Please select a grade</span>
                                    <?php endif; ?>

                                    <?php if (!empty($searchstreamname) && !$showAllStreams): ?>
                                        <span class="text-primary font-weight-bold" style="font-size: 20px;">
                                            Stream: <?= htmlentities($searchstreamname); ?>
                                        </span>
                                    <?php elseif ($showAllStreams): ?>
                                        <span class="text-success font-weight-bold" style="font-size: 20px;">
                                            Showing all streams
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Please select a stream</span>
                                    <?php endif; ?>
                                </div>
    
                                <?php include_once('updatemessagepopup.php'); ?>
                            </form>
                            
                            <script>
                                // Enable/disable stream select based on checkbox
                                document.getElementById('show_all_streams').addEventListener('change', function() {
                                    document.getElementById('streamname').disabled = this.checked;
                                });
                            </script>
                            
                            <div class="tab-container mt-3">
                                <div class="tab-nav">
                                    <button class="tablinks active" onclick="opentab(event, 'FeeBalance')">
                                        <i class="fa fa-money"></i> Fee Balance
                                    </button>
                                    <button class="tablinks" onclick="opentab(event, 'OtherPayments')">
                                        <i class="fa fa-list-alt"></i> Other Payments
                                    </button>
                                </div>
                                
                                <!-- Fee Balance Tab -->
                                <div id="FeeBalance" class="tabcontent" style="display: block;">
                                    <?php if (!empty($searchgradefullname)): ?>
                                        <div class="global-search-container">
                                            <div class="report-buttons">
                                                <a href="reportfeebalancepergrade.php?gradefullname=<?= urlencode($searchgradefullname); ?>&streamname=<?= urlencode($searchstreamname); ?>" 
                                                class="btn btn-outline-primary btn-sm report-btn full-year-btn" 
                                                title="Print full-year fee balance report" 
                                                target="_blank">
                                                <i class="fas fa-file-alt me-1"></i> Full-Year REPORT
                                                </a>

                                                <a href="reportfeebalancepergrade1stterm.php?gradefullname=<?= urlencode($searchgradefullname); ?>&streamname=<?= urlencode($searchstreamname); ?>" 
                                                class="btn btn-outline-success btn-sm report-btn first-term-btn" 
                                                title="Print 1st term fee balance report" 
                                                target="_blank">
                                                <i class="fas fa-leaf me-1"></i> 1st-Term REPORT
                                                </a>

                                                <a href="reportfeebalancepergrade2ndterm.php?gradefullname=<?= urlencode($searchgradefullname); ?>&streamname=<?= urlencode($searchstreamname); ?>" 
                                                class="btn btn-outline-warning btn-sm report-btn second-term-btn" 
                                                title="Print 2nd term fee balance report" 
                                                target="_blank">
                                                <i class="fas fa-seedling me-1"></i> 2nd-Term REPORT
                                                </a>

                                                <a href="reportfeebalancepergrade3rdterm.php?gradefullname=<?= urlencode($searchgradefullname); ?>&streamname=<?= urlencode($searchstreamname); ?>" 
                                                class="btn btn-outline-danger btn-sm report-btn third-term-btn" 
                                                title="Print 3rd term fee balance report" 
                                                target="_blank">
                                                <i class="fas fa-tree me-1"></i> 3rd-Term REPORT
                                                </a>
                                            </div>
                                            
                                            <input type="text" id="feeBalanceSearch" class="global-search" placeholder="Search all columns...">
                                        </div>

                                        <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                            <table class="table table-striped table-bordered table-hover" id="feeBalanceTable">
                                                <thead>
                                                    <tr>
                                                        <th data-sort="number">#</th>
                                                        <th data-sort="text">Name</th>
                                                        <th data-sort="text">AdmNo</th>
                                                        <th data-sort="text">Stream</th>
                                                        <th data-sort="text">Child Treatment</th>
                                                        <th data-sort="text">Fee Treatment</th>
                                                        <th data-sort="text">Status</th>
                                                        <th data-sort="number">Bal B/F</th>
                                                        <th data-sort="number">1st-Term Bal</th>
                                                        <th data-sort="number">2nd-Term Bal</th>
                                                        <th data-sort="number">3rd-Term Bal</th>
                                                        <th data-sort="number">Yearly Bal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    try {
                                                        $sql = "SELECT feebalances.*, classentries.stream
                                                        FROM feebalances
                                                        INNER JOIN classentries ON classentries.classentryfullname = feebalances.feebalancecode
                                                        WHERE feebalances.gradefullname = :grade";
                                                        
                                                        if (!empty($searchstreamname) && !$showAllStreams) {
                                                            $sql .= " AND classentries.stream = :stream";
                                                        }
                                                        
                                                        $sql .= " ORDER BY feebalances.yearlybal DESC";
                                                        
                                                        $query = $dbh->prepare($sql);
                                                        $query->bindParam(':grade', $searchgradefullname, PDO::PARAM_STR);
                                                        
                                                        if (!empty($searchstreamname) && !$showAllStreams) {
                                                            $query->bindParam(':stream', $searchstreamname, PDO::PARAM_STR);
                                                        }
                                                        
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        
                                                        if ($query->rowCount() > 0) {
                                                            $cnt = 1;
                                                            foreach ($results as $row): ?>
                                                                <tr>
                                                                    <td><?= $cnt++; ?></td>
                                                                    <td><?= htmlentities($row->studentname); ?></td>
                                                                    <td><?= htmlentities($row->studentadmno); ?></td>
                                                                    <td><?= htmlentities($row->stream); ?></td>
                                                                    <td><?= htmlentities($row->childtreatment); ?></td>
                                                                    <td><?= htmlentities($row->feetreatment); ?></td>
                                                                    <td><?= htmlentities($row->childstatus); ?></td>
                                                                    <td data-sort-value="<?= $row->arrears; ?>"><?= number_format($row->arrears); ?></td>
                                                                    <td data-sort-value="<?= $row->firsttermbal; ?>"><?= number_format($row->firsttermbal); ?></td>
                                                                    <td data-sort-value="<?= $row->secondtermbal; ?>"><?= number_format($row->secondtermbal); ?></td>
                                                                    <td data-sort-value="<?= $row->thirdtermbal; ?>"><?= number_format($row->thirdtermbal); ?></td>
                                                                    <td data-sort-value="<?= $row->yearlybal; ?>">
                                                                        <a href="manage-feepayments.php?viewstudentadmno=<?= htmlentities($row->studentadmno); ?>">
                                                                            <?= number_format($row->yearlybal); ?>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach;
                                                        } else {
                                                            echo '<tr><td colspan="12">No fee balance records found for this selection</td></tr>';
                                                        }
                                                    } catch (PDOException $e) {
                                                        echo '<tr><td colspan="12">Error loading fee balance data</td></tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <?php
                                            if (!empty($searchgradefullname)) {
                                                $sql = "SELECT DISTINCT ce.studentadmno, sd.studentname, ce.stream
                                                        FROM classentries ce
                                                        JOIN studentdetails sd ON ce.studentadmno = sd.studentadmno
                                                        WHERE ce.gradefullname = :grade";
                                                        
                                                if (!empty($searchstreamname) && !$showAllStreams) {
                                                    $sql .= " AND ce.stream = :stream";
                                                }
                                                
                                                $sql .= " AND NOT EXISTS (
                                                            SELECT 1 FROM feebalances fb
                                                            WHERE fb.studentadmno = ce.studentadmno AND fb.gradefullname = :grade
                                                        )";
                                                        
                                                $query = $dbh->prepare($sql);
                                                $query->bindParam(':grade', $searchgradefullname, PDO::PARAM_STR);
                                                
                                                if (!empty($searchstreamname) && !$showAllStreams) {
                                                    $query->bindParam(':stream', $searchstreamname, PDO::PARAM_STR);
                                                }
                                                
                                                $query->execute();
                                                $missingStudents = $query->fetchAll(PDO::FETCH_OBJ);

                                                if ($query->rowCount() > 0): ?>
                                                    <h5 class="mt-3 mb-2 text-danger">
                                                        Learners in <?= htmlentities($searchgradefullname); 
                                                        if (!empty($searchstreamname) && !$showAllStreams) {
                                                            echo " (" . htmlentities($searchstreamname) . ")";
                                                        }
                                                        ?> without fee records (Total: <?= $query->rowCount(); ?>)
                                                    </h5>
                                                    <div style="font-size: 13px; color: #444; line-height: 1.6; word-wrap: break-word; white-space: normal;">
                                                        <?php 
                                                        $formatted = [];
                                                        foreach ($missingStudents as $student) {
                                                            $formatted[] = "<strong>AdmNo: " . htmlentities($student->studentadmno) . "</strong> – " . 
                                                                           htmlentities($student->studentname) . 
                                                                           (!empty($student->stream) ? " (" . htmlentities($student->stream) . ")" : "");
                                                        }
                                                        echo implode(" | ", $formatted);
                                                        ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info mt-3">
                                                        All students in <?= htmlentities($searchgradefullname); 
                                                        if (!empty($searchstreamname) && !$showAllStreams) {
                                                            echo " (" . htmlentities($searchstreamname) . ")";
                                                        }
                                                        ?> have fee records.
                                                    </div>
                                                <?php endif;
                                            }
                                        ?>

                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> Please select a grade to view fee balances.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Other Payments Tab -->
                                <div id="OtherPayments" class="tabcontent">
                                    <?php if (!empty($searchgradefullname)): ?>   
                                        <div class="global-search-container">
                                            <div class="report-buttons">
                                                <a href="reportotheritemspaymentspergrade.php?gradefullname=<?= urlencode($searchgradefullname); ?>" 
                                                    class="btn btn-outline-primary btn-sm print-btn" 
                                                    title="Print other items payments report" 
                                                    target="_blank">
                                                    <i class="fa fa-print"></i> Print Report
                                                </a>
                                            </div>
                                            <input type="text" id="otherPaymentsSearch" class="global-search" placeholder="Search all columns...">
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="otherPaymentsTable">
                                                <thead>
                                                    <tr>
                                                        <th data-sort="number">#</th>
                                                        <th data-sort="text">Student Name</th>
                                                        <th data-sort="text">Adm No</th>
                                                        <th data-sort="text">Stream</th>
                                                        <?php
                                                        $items = [];
                                                        try {
                                                            $itemSql = "SELECT id, otherpayitemname FROM otherpayitems ORDER BY otherpayitemname ASC";
                                                            $itemStmt = $dbh->prepare($itemSql);
                                                            $itemStmt->execute();
                                                            $items = $itemStmt->fetchAll(PDO::FETCH_OBJ);

                                                            foreach ($items as $item) {
                                                                echo '<th data-sort="number">' . htmlentities($item->otherpayitemname) . '</th>';
                                                            }
                                                        } catch (PDOException $e) {
                                                            echo "<th colspan='1' class='text-danger'>Items Load Error</th>";
                                                        }
                                                        ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    try {
                                                        if (!empty($items)) {
                                                            $caseStatements = '';
                                                            foreach ($items as $item) {
                                                                $col = "item_" . $item->id;
                                                                $caseStatements .= ", SUM(CASE WHEN a.item_id = {$item->id} THEN a.amount ELSE 0 END) AS `$col`";
                                                            }

                                                            $sql = "SELECT 
                                                                        c.studentname,
                                                                        c.studentadmno,
                                                                        e.stream
                                                                        $caseStatements
                                                                    FROM otheritemspayments_breakdown a
                                                                    INNER JOIN otheritemspayments b ON a.payment_id = b.id
                                                                    INNER JOIN studentdetails c ON b.studentadmno = c.studentadmno
                                                                    LEFT JOIN classentries e ON b.studentadmno = e.studentadmno AND SUBSTRING(e.gradefullname, 1, 4) = b.financialyear
                                                                    WHERE e.gradefullname = :grade";

                                                            if (!empty($searchstreamname) && !$showAllStreams) {
                                                                $sql .= " AND e.stream = :stream";
                                                            }

                                                            $sql .= " GROUP BY c.studentadmno, c.studentname, e.stream
                                                                    ORDER BY c.studentname ASC";

                                                            $stmt = $dbh->prepare($sql);
                                                            $stmt->bindParam(':grade', $searchgradefullname, PDO::PARAM_STR);
                                                            if (!empty($searchstreamname) && !$showAllStreams) {
                                                                $stmt->bindParam(':stream', $searchstreamname, PDO::PARAM_STR);
                                                            }

                                                            $stmt->execute();
                                                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                            $cnt = 1;

                                                            if ($stmt->rowCount() > 0) {
                                                                foreach ($results as $row) {
                                                                    echo "<tr>";
                                                                    echo "<td>" . $cnt++ . "</td>";
                                                                    echo "<td>" . htmlentities($row['studentname']) . "</td>";
                                                                    echo "<td>" . htmlentities($row['studentadmno']) . "</td>";
                                                                    echo "<td>" . htmlentities($row['stream']) . "</td>";
                                                                    foreach ($items as $item) {
                                                                        $key = "item_" . $item->id;
                                                                        echo '<td data-sort-value="' . ($row[$key] ?? 0) . '">' . number_format($row[$key] ?? 0) . "</td>";
                                                                    }
                                                                    echo "</tr>";
                                                                }
                                                            } else {
                                                                echo "<tr>";
                                                                echo "<td colspan='1' class='text-muted text-center'>-</td>";
                                                                echo "<td colspan='1' class='text-muted text-center'>No data</td>";
                                                                echo "<td colspan='1' class='text-muted text-center'>-</td>";
                                                                echo "<td colspan='1' class='text-muted text-center'>-</td>";
                                                                foreach ($items as $item) {
                                                                    echo "<td class='text-muted text-center'>-</td>";
                                                                }
                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='4' class='text-center text-warning'>No payment items configured</td></tr>";
                                                        }
                                                    } catch (PDOException $e) {
                                                        echo "<tr><td colspan='" . (4 + count($items)) . "' class='text-center text-danger'>Error loading other payments data</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> Please select a grade to view other payments.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>

    <!-- Enhanced Table Functionality -->
    <script>
        // Tab Functionality
        function opentab(evt, tabName) {
            var i, tabcontent, tablinks;
            
            // Hide all tab content
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            
            // Remove active class from all tab buttons
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            
            // Show the current tab and add active class
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Enhanced Table Functionality
        $(document).ready(function() {
            // Initialize tables
            if ($('#feeBalanceTable').length) {
                initEnhancedTable('#feeBalanceTable', '#feeBalanceSearch');
            }
            
            if ($('#otherPaymentsTable').length) {
                initEnhancedTable('#otherPaymentsTable', '#otherPaymentsSearch');
            }
            
            // Show the first tab by default
            document.getElementById('FeeBalance').style.display = 'block';
            var tabBtns = document.getElementsByClassName('tablinks');
            if (tabBtns.length > 0) tabBtns[0].className += ' active';
        });

        function initEnhancedTable(tableId, searchId) {
            const $table = $(tableId);
            const $search = $(searchId);
            const $headers = $table.find('th[data-sort]');
            let sortColumn = 0;
            let sortDirection = 1; // 1 = asc, -1 = desc
            
            // Global search functionality
            $search.on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                const $rows = $table.find('tbody tr');
                
                if (searchText.length === 0) {
                    $rows.show();
                    return;
                }
                
                $rows.each(function() {
                    const $row = $(this);
                    let found = false;
                    
                    $row.find('td').each(function() {
                        const cellText = $(this).text().toLowerCase();
                        if (cellText.includes(searchText)) {
                            found = true;
                            return false; // break out of loop
                        }
                    });
                    
                    $row.toggle(found);
                });
            });
            
            // Column sorting functionality
            $headers.on('click', function() {
                const column = $(this).index();
                const sortType = $(this).data('sort');
                
                // Update sort direction if clicking same column
                if (column === sortColumn) {
                    sortDirection *= -1;
                } else {
                    sortColumn = column;
                    sortDirection = 1;
                }
                
                // Clear previous sort indicators
                $headers.removeClass('sort-asc sort-desc');
                
                // Add new sort indicator
                $(this).addClass(sortDirection === 1 ? 'sort-asc' : 'sort-desc');
                
                // Sort the table
                sortTable($table, column, sortType, sortDirection);
            });
            
            function sortTable($table, column, sortType, direction) {
                const $tbody = $table.find('tbody');
                const $rows = $tbody.find('tr').get();
                
                $rows.sort(function(a, b) {
                    const aVal = $(a).find('td').eq(column).data('sort-value') || $(a).find('td').eq(column).text();
                    const bVal = $(b).find('td').eq(column).data('sort-value') || $(b).find('td').eq(column).text();
                    
                    if (sortType === 'number') {
                        const numA = parseFloat(aVal.toString().replace(/[^0-9.-]/g, '')) || 0;
                        const numB = parseFloat(bVal.toString().replace(/[^0-9.-]/g, '')) || 0;
                        return (numA - numB) * direction;
                    } else {
                        return aVal.toString().localeCompare(bVal.toString()) * direction;
                    }
                });
                
                $.each($rows, function(index, row) {
                    $tbody.append(row);
                });
            }
        }
    </script>
</body>
</html>