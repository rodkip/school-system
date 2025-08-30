<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (!isset($_SESSION['cpmsaid']) || strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$eid = isset($_GET['editid']) ? intval($_GET['editid']) : 0;

// Fetch fee structure details
$feeStructure = null;
if ($eid > 0) {
    $sql = "SELECT * FROM feestructure WHERE id = ?";
    $query = $dbh->prepare($sql);
    $query->execute([$eid]);
    $feeStructure = $query->fetch(PDO::FETCH_OBJ);
}

if (!$feeStructure) {
    $_SESSION['messagestate'] = 'deleted';
    $_SESSION['mess'] = "Fee structure not found";
    header('location:manage-feestructure.php');
    exit();
}

// Handle form submission
if (isset($_POST['update_feestructurepervotehead'])) {
    try {
        $feestructurename = $feeStructure->feestructurename;
        $voteheadData = $_POST['votehead'] ?? [];
        
        // Validate input
        $hasValidData = false;
        $totals = ['first' => 0, 'second' => 0, 'third' => 0];
        
        foreach ($voteheadData as $voteheadId => $terms) {
            $voteheadId = intval($voteheadId);
            $terms['first'] = isset($terms['first']) ? floatval($terms['first']) : 0;
            $terms['second'] = isset($terms['second']) ? floatval($terms['second']) : 0;
            $terms['third'] = isset($terms['third']) ? floatval($terms['third']) : 0;
            
            if ($terms['first'] > 0 || $terms['second'] > 0 || $terms['third'] > 0) {
                $hasValidData = true;
                $totals['first'] += $terms['first'];
                $totals['second'] += $terms['second'];
                $totals['third'] += $terms['third'];
            }
        }
        
        if (!$hasValidData) {
            throw new Exception("Please enter amounts for at least one votehead");
        }
        
        // Verify totals match the fee structure (including othersfee in first term)
        $tolerance = 0; // Allow for floating point rounding differences
        if (abs($totals['first'] - ($feeStructure->firsttermfee + $feeStructure->othersfee)) > $tolerance ||
            abs($totals['second'] - $feeStructure->secondtermfee) > $tolerance ||
            abs($totals['third'] - $feeStructure->thirdtermfee) > $tolerance) {
            throw new Exception("Votehead totals do not match the fee structure amounts");
        }
        
        $dbh->beginTransaction();

        // 1. Delete existing entries
        $deleteSql = "DELETE FROM feestructurevoteheadcharges WHERE feestructurename = ?";
        $deleteStmt = $dbh->prepare($deleteSql);
        $deleteStmt->execute([$feestructurename]);

        // 2. Insert new records
        $insertSql = "INSERT INTO feestructurevoteheadcharges 
                     (feestructurename, votehead_id, firstterm, secondterm, thirdterm, total) 
                     VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $dbh->prepare($insertSql);
        $insertCount = 0;

        foreach ($voteheadData as $voteheadId => $terms) {
            $voteheadId = intval($voteheadId);
            $first = floatval($terms['first'] ?? 0);
            $second = floatval($terms['second'] ?? 0);
            $third = floatval($terms['third'] ?? 0);
            $total = $first + $second + $third;

            // Skip if all terms are zero
            if ($total == 0) continue;

            // Verify votehead exists
            $validateSql = "SELECT COUNT(*) FROM voteheads WHERE id = ? AND isfeepayment = 'Yes'";
            $validateStmt = $dbh->prepare($validateSql);
            $validateStmt->execute([$voteheadId]);
            
            if ($validateStmt->fetchColumn() == 0) {
                throw new Exception("Invalid votehead ID: $voteheadId");
            }

            // Insert record
            $insertStmt->execute([
                $feestructurename,
                $voteheadId,
                $first,
                $second,
                $third,
                $total
            ]);
            $insertCount++;
        }

        $dbh->commit();
        
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Fee structure updated successfully with $insertCount voteheads";
        
    } catch (Exception $e) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = $e->getMessage();
    }

    header("Location: edit-feestructurepervoteaheads.php?editid=$eid");
    exit();
}

// Fetch existing votehead charges for this fee structure
$existingCharges = [];
$chargesSql = "SELECT vh.votehead, vh.id, vc.firstterm, vc.secondterm, vc.thirdterm 
               FROM feestructurevoteheadcharges vc
               JOIN voteheads vh ON vc.votehead_id = vh.id
               WHERE vc.feestructurename = ?";
$chargesStmt = $dbh->prepare($chargesSql);
$chargesStmt->execute([$feeStructure->feestructurename]);
$existingCharges = $chargesStmt->fetchAll(PDO::FETCH_OBJ);

// Fetch all voteheads
$voteheadsSql = "SELECT id, votehead FROM voteheads WHERE isfeepayment = 'Yes' ORDER BY votehead";
$voteheadsStmt = $dbh->prepare($voteheadsSql);
$voteheadsStmt->execute();
$voteheads = $voteheadsStmt->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Update Fee Structure Voteheads</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        .term-fee-header {
            font-weight: bold;
            text-align: center;
            background-color: #f0f8ff;
        }
        .votehead-table th, .votehead-table td {
            text-align: center;
            vertical-align: middle;
        }
        .votehead-table thead {
            background-color: #e9ecef;
        }
        .votehead-table tfoot {
            background-color: #f1f3f5;
            font-weight: bold;
        }
        .form-control[readonly] {
            background-color: #f9f9f9;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .alert {
            margin: 15px 0;
        }
    </style>
</head>

<body>
<div id="wrapper">
    <!-- navbar top -->
    <?php include_once('includes/header.php'); ?>
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php'); ?>
      

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Update Fee Structure Voteheads</h1>
                <?php include_once('updatemessagepopup.php'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading" style="font-size: 20px; font-weight: bold;">
                        Fee Structure: <span style="color:green;"> <?= htmlspecialchars($feeStructure->gradefullname) ?> 
                        (<?= htmlspecialchars($feeStructure->entryterm) ?>, 
                        <?= htmlspecialchars($feeStructure->boarding) ?>) </span>
                    </div>
                    <div class="panel-body">
                        <form method="POST" action="">
                            <input type="hidden" name="feestructurename" value="<?= htmlspecialchars($feeStructure->feestructurename) ?>">
                            
                            <div class="table-responsive">
                                <table class="table table-bordered votehead-table">
                                    <thead>
                                        <tr>
                                            <th colspan="5" class="text-center bg-primary text-white">
                                                Term Fee Breakdown (Total: <?= number_format($feeStructure->totalfee, 2) ?>)
                                            </th>
                                        </tr>
                                        <tr>
                                            <th>Votehead</th>
                                            <th class="term-fee-header">1st Term (<?= number_format($feeStructure->firsttermfee + $feeStructure->othersfee, 2) ?>)</th>
                                            <th class="term-fee-header">2nd Term (<?= number_format($feeStructure->secondtermfee, 2) ?>)</th>
                                            <th class="term-fee-header">3rd Term (<?= number_format($feeStructure->thirdtermfee, 2) ?>)</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($voteheads as $votehead): 
                                            // Find existing values for this votehead
                                            $first = 0;
                                            $second = 0;
                                            $third = 0;
                                            
                                            foreach ($existingCharges as $charge) {
                                                if ($charge->id == $votehead->id) {
                                                    $first = $charge->firstterm;
                                                    $second = $charge->secondterm;
                                                    $third = $charge->thirdterm;
                                                    break;
                                                }
                                            }
                                        ?>
                                        <tr class="votehead-row">
                                            <td><?= htmlspecialchars($votehead->votehead) ?></td>
                                            <td>
                                                <input type="number" name="votehead[<?= $votehead->id ?>][first]" 
                                                    class="form-control term-first" step="0" min="0" 
                                                    value="<?= number_format($first, 0, '.', '') ?>" />
                                            </td>
                                            <td>
                                                <input type="number" name="votehead[<?= $votehead->id ?>][second]" 
                                                    class="form-control term-second" step="0" min="0" 
                                                    value="<?= number_format($second, 0, '.', '') ?>" />
                                            </td>
                                            <td>
                                                <input type="number" name="votehead[<?= $votehead->id ?>][third]" 
                                                    class="form-control term-third" step="0" min="0" 
                                                    value="<?= number_format($third, 0, '.', '') ?>" />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control row-total" readonly 
                                                    value="<?= number_format($first + $second + $third, 0) ?>" />
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th><input type="text" id="totalFirst" class="form-control" readonly 
                                                value="<?= number_format($feeStructure->firsttermfee + $feeStructure->othersfee, 2) ?>" /></th>
                                            <th><input type="text" id="totalSecond" class="form-control" readonly 
                                                value="<?= number_format($feeStructure->secondtermfee, 2) ?>" /></th>
                                            <th><input type="text" id="totalThird" class="form-control" readonly 
                                                value="<?= number_format($feeStructure->thirdtermfee, 2) ?>" /></th>
                                            <th><input type="text" id="grandTotal" class="form-control" readonly 
                                                value="<?= number_format($feeStructure->totalfee, 2) ?>" /></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="text-center" style="margin-top: 20px;">
                                <button type="submit" name="update_feestructurepervotehead" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Changes
                                </button>
                                <a href="manage-feestructure.php" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> Back to FeeStructureList
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script>
$(document).ready(function() {
    // Function to update totals
    function updateTotals() {
        let totalFirst = 0, totalSecond = 0, totalThird = 0, grandTotal = 0;

        $('.votehead-row').each(function() {
            const first = parseFloat($(this).find('.term-first').val()) || 0;
            const second = parseFloat($(this).find('.term-second').val()) || 0;
            const third = parseFloat($(this).find('.term-third').val()) || 0;
            const rowTotal = first + second + third;
            
            $(this).find('.row-total').val(rowTotal.toFixed(2));
            
            totalFirst += first;
            totalSecond += second;
            totalThird += third;
        });

        grandTotal = totalFirst + totalSecond + totalThird;
        
        $('#totalFirst').val(totalFirst.toFixed(2));
        $('#totalSecond').val(totalSecond.toFixed(2));
        $('#totalThird').val(totalThird.toFixed(2));
        $('#grandTotal').val(grandTotal.toFixed(2));
    }

    // Update totals when inputs change
    $(document).on('input', '.term-first, .term-second, .term-third', updateTotals);

    // Form validation
    $('form').on('submit', function(e) {
        const firstTotal = parseFloat($('#totalFirst').val()) || 0;
        const secondTotal = parseFloat($('#totalSecond').val()) || 0;
        const thirdTotal = parseFloat($('#totalThird').val()) || 0;
        
        const expectedFirst = <?= $feeStructure->firsttermfee + $feeStructure->othersfee ?>;
        const expectedSecond = <?= $feeStructure->secondtermfee ?>;
        const expectedThird = <?= $feeStructure->thirdtermfee ?>;
        
        // Allow small rounding differences
        if (Math.abs(firstTotal - expectedFirst) > 0 || 
            Math.abs(secondTotal - expectedSecond) > 0 || 
            Math.abs(thirdTotal - expectedThird) > 0) {
            alert('The votehead totals must exactly match the fee structure amounts for each term');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('input[type="number"]');

        inputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                if (parseFloat(this.value) === 0) {
                    this.value = '';
                }
            });

            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = '0';
                }
            });
        });
    });
</script>

</body>
</html>