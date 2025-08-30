<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

// Authentication check
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit;
}

// Fetch all students with their latest grade
$students_stmt = $dbh->prepare("
    SELECT 
        s.studentadmno, 
        s.studentname, 
        ce.gradefullname
    FROM studentdetails s
    LEFT JOIN (
        SELECT ce1.studentadmno, ce1.gradefullname
        FROM classentries ce1
        INNER JOIN (
            SELECT studentadmno, MAX(id) AS max_id
            FROM classentries
            GROUP BY studentadmno
        ) ce2 ON ce1.studentadmno = ce2.studentadmno AND ce1.id = ce2.max_id
    ) ce ON s.studentadmno = ce.studentadmno
    ORDER BY s.id ASC
");
$students_stmt->execute();
$students = $students_stmt->fetchAll(PDO::FETCH_OBJ);

// Process bulk payment submission
if (isset($_POST['receivepay_submit'])) {
    try {
        $dbh->beginTransaction();

        // Validate required fields
        $required = ['receiptno', 'bank', 'paymentdate', 'academicyear', 'username', 'selected_students'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Prepare payment data
        $paymentData = [
            'receiptno' => $_POST['receiptno'],
            'bank' => $_POST['bank'],
            'reference' => $_POST['reference'] ?? '',
            'bankpaymentdate' => $_POST['bankpaymentdate'] ?? $_POST['paymentdate'],
            'paymentdate' => $_POST['paymentdate'],
            'details' => $_POST['details'] ?? '',
            'academicyear' => (int)$_POST['academicyear'],
            'cashier' => $_POST['username']
        ];

        // Process students
        $studentAdmNos = array_filter(explode(',', $_POST['selected_students']));
        if (empty($studentAdmNos)) {
            throw new Exception("No students selected for payment");
        }

        // Prepare statements
        $paymentStmt = $dbh->prepare("
            INSERT INTO feepayments 
            (studentadmno, receiptno, cash, bank, reference, bankpaymentdate, paymentdate, details, academicyear, cashier, receiptcode)
            VALUES 
            (:studentadmno, :receiptno, :cash, :bank, :reference, :bankpaymentdate, :paymentdate, :details, :academicyear, :cashier, :receiptcode)
        ");

        $voteheadStmt = $dbh->prepare("
            INSERT INTO feepayment_voteheads 
            (payment_id, votehead_id, amount)
            VALUES 
            (:payment_id, :votehead_id, :amount)
        ");

        $totalAllocated = 0;
        $processedCount = 0;
        $grandTotal = 0;

        foreach ($studentAdmNos as $admNo) {
            // Calculate individual student total from voteheads
            $studentTotal = 0;
            $studentVoteheads = $_POST['student_voteheads'][$admNo] ?? [];
            
            foreach ($studentVoteheads as $amount) {
                $studentTotal += (float)$amount;
            }
            
            $grandTotal += $studentTotal;

            // Insert payment record with individual student total
            $receiptcode = $admNo . $paymentData['academicyear'];
            
            $paymentStmt->execute([
                ':studentadmno' => $admNo,
                ':receiptno' => $paymentData['receiptno'],
                ':cash' => $studentTotal, // Using individual student total instead of global cash
                ':bank' => $paymentData['bank'],
                ':reference' => $paymentData['reference'],
                ':bankpaymentdate' => $paymentData['bankpaymentdate'],
                ':paymentdate' => $paymentData['paymentdate'],
                ':details' => $paymentData['details'],
                ':academicyear' => $paymentData['academicyear'],
                ':cashier' => $paymentData['cashier'],
                ':receiptcode' => $receiptcode
            ]);

            $paymentId = $dbh->lastInsertId();
            $processedCount++;

            // Process voteheads for this student
            foreach ($studentVoteheads as $voteheadId => $amount) {
                if ($amount > 0) {
                    $voteheadStmt->execute([
                        ':payment_id' => $paymentId,
                        ':votehead_id' => $voteheadId,
                        ':amount' => $amount
                    ]);
                    $totalAllocated += $amount;
                }
            }
        }

        // Validate allocation (now comparing grand total with sum of all voteheads)
        if (abs($grandTotal - $totalAllocated) > 0.01) {
            throw new Exception(sprintf(
                "Allocation mismatch: Sum of student totals (Ksh %.2f) doesn't match sum of voteheads (Ksh %.2f)",
                $grandTotal,
                $totalAllocated
            ));
        }

        $dbh->commit();
        
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Successfully processed payments for $processedCount students (Total: Ksh ".number_format($grandTotal, 2).")";
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();

    } catch (PDOException $e) {
        $dbh->rollBack();
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Database error: ".$e->getMessage();
        error_log("Payment Error: ".$e->getMessage());
    } catch (Exception $e) {
        $dbh->rollBack();
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = $e->getMessage();
        error_log("Payment Error: ".$e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | Bulk Fee Payment</title>
    
    <!-- CSS Links -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    color: #343a40;
}

.table thead th {
    background-color:rgb(89, 117, 159);
    color: #fff;
    padding: 14px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    border-radius: 6px;
}

.table tbody td {
    padding: 1px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.table tbody tr:hover {
    background-color: #edf2ff;
    transition: background-color 0.2s ease-in-out;
}

.total-column {
    font-weight: 600;
    color: #212529;
}

.total-cell {
    font-weight: 600;
    color: #d63384;
}

/* Form Controls */
.form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    font-size: 14px;
    padding: 8px 12px;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Buttons */
.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 5px;
    font-weight: 500;
    background-color: #0d6efd;
    color: #fff;
    border: none;
}

.btn-sm:hover {
    background-color: #0b5ed7;
}

.remove-student-row {
    transition: transform 0.2s ease;
}

.remove-student-row:hover {
    transform: scale(1.05);
    color: #dc3545;
}

/* Modal Styling */
#bulkFeePaymentModal .modal-dialog {
    max-width: 1800px;
    width: 100%;
}

#bulkFeePaymentModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#bulkFeePaymentModal .modal-title i {
    color: #0d6efd;
}

#bulkFeePaymentModal .close {
    font-size: 1.5rem;
    color: #6c757d;
}

#bulkFeePaymentModal .modal-body {
    max-height: 90vh;
    overflow-y: auto;
    padding: 1.5rem;
}

#bulkFeePaymentModal label {
    font-weight: 500;
    color: #495057;
    font-size: 13px;
    margin-bottom: 6px;
}

#bulkFeePaymentModal .form-group {
    margin-bottom: 1rem;
}

#bulkFeePaymentModal .form-control {
    font-size: 13px;
}

#bulkFeePaymentModal table {
    margin-bottom: 0;
}

#bulkFeePaymentModal .panel-heading {
    background-color: #e2e6ea;
    font-weight: 600;
    padding: 10px 14px;
    text-align: center;
    border-bottom: 1px solid #dee2e6;
}

#bulkFeePaymentModal .text-right {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 12px 14px;
    font-size: 13px;
}

/* Submit Button */
#bulkFeePaymentModal #submitBtn {
    background-color: #6c757d;
    color: #fff;
    font-size: 15px;
    padding: 12px 36px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 6px;
    border: none;
    cursor: not-allowed;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

/* Select2 Enhancements */
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

.select2-container--bootstrap-5 .select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    padding: 0;
    line-height: 28px;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

/* Highlight */
.amount-mismatch {
    color: #dc3545;
    font-weight: 600;
}
/* Adjust font size for the rendered select element */
.select2-container--bootstrap-5 .select2-selection {
    font-size: 15px;
}

/* Adjust font size in the dropdown options */
.select2-container--bootstrap-5 .select2-results__option {
    font-size: 15px;
}
.select2-container--bootstrap-5 .select2-results__option {
    padding: 6px 12px;
    line-height: 1.4;
}

</style>

</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>
        
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">

                 <table>
                        <tr>
                            <td width="100%">
                                <h1 class="page-header">BULK-Fee Payments</h1>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary"
                                    style="--btn-color: #e74a3b; --hover-color: #be2617;"
                                    onclick="window.location.href='manage-feepaymentbulk.php';">
                                    <i class="bi bi-bar-chart-line me-2"></i> Manage/View Bulk Fee-Payments
                                </button>
                            </td>
                            <td>                                  
                            <?php include_once('updatemessagepopup.php'); ?>
                            </td>
                        </tr>
                    </table>
                    
                </div>
            </div>
             
                               
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">                                              
                                            <form method="post" enctype="multipart/form-data" action="manage-feepaymentbulk.php">
                                                <input type="hidden" name="id" value="<?php echo $row->id ?>">
                                                <input type="hidden" name="username" value="<?php echo $username ?>">

                                                <div class="row g-3">
                                                    <!-- Receipt Number -->
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <!-- Receipt Number -->
                                                        <td>
                                                            <label for="receiptno">Receipt Number</label>
                                                            <?php
                                                            $currentYear = date('Y');
                                                            $stmt = $dbh->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(receiptno, '-', -1) AS UNSIGNED)) AS max_suffix FROM feepayments WHERE receiptno LIKE :yearprefix");
                                                            $stmt->execute([':yearprefix' => "$currentYear-%"]);
                                                            $lastSuffix = $stmt->fetchColumn() ?? 0;
                                                            $newreceiptno = sprintf("%s-%04d", $currentYear, $lastSuffix + 1);
                                                            ?>
                                                            <input type="text" class="form-control" name="receiptno" id="receiptno" required 
                                                                value="<?= htmlspecialchars($newreceiptno) ?>" 
                                                                style="background-color: #fff8e1; font-weight: bold; color: #d35400; border: 1px solid #ffd699;">
                                                        </td>

                                                        <!-- Receipt Date -->
                                                        <td>
                                                            <label for="paymentdate">Receipt Date</label>
                                                            <input type="date" class="form-control" name="paymentdate" id="paymentdate" 
                                                                value="<?= date('Y-m-d') ?>" required>
                                                        </td>

                                                        <!-- Bank Payment Date -->
                                                        <td>
                                                            <label for="bankpaymentdate">Bank Payment Date</label>
                                                            <input type="date" class="form-control" name="bankpaymentdate" id="bankpaymentdate" 
                                                                value="<?= date('Y-m-d') ?>" required>
                                                        </td>

                                                        <!-- Payment Mode -->
                                                        <td>
                                                            <label for="bank">Payment Mode <i class="fa fa-bank" style="color: #9b59b6;"></i></label>
                                                            <select name="bank" id="bank" class="form-control" required
                                                                    style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=\'%239b59b6\' height=\'24\' viewBox=\'0 0 24 24\' width=\'24\' xmlns=\'http://www.w3.org/2000/svg\'><path d=\'M7 10l5 5 5-5z\'/></svg>'); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px;">
                                                                <option value="">-- Select Payment Mode --</option>
                                                                <?php
                                                                $smt = $dbh->prepare("SELECT bankname FROM bankdetails");
                                                                $smt->execute();
                                                                foreach ($smt->fetchAll(PDO::FETCH_ASSOC) as $rw): ?>
                                                                    <option value="<?= htmlspecialchars($rw['bankname']) ?>"><?= htmlspecialchars($rw['bankname']) ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>

                                                        <!-- Academic Year -->
                                                        <td>
                                                            <label for="academicyear">Academic Year</label>
                                                            <input type="text" class="form-control" name="academicyear" id="academicyear" 
                                                                value="<?= $currentYear ?>" required>
                                                        </td>

                                                        <!-- Reference -->
                                                        <td>
                                                            <label for="reference">Reference (Mpesa Code, if any)</label>
                                                            <input type="text" class="form-control" name="reference" id="reference" placeholder="Reference (optional)">
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <!-- Payer -->
                                                        <td colspan="2">
                                                            <label for="payer">Payer</label>
                                                            <input type="text" class="form-control" name="payer" id="payer" 
                                                                placeholder="Payer (MUST)" required>
                                                        </td>

                                                        <!-- Payment Description -->
                                                        <td colspan="3">
                                                            <label for="details" class="form-label">Payment Description</label>
                                                            <input type="text" class="form-control" name="details" id="details" placeholder="Enter payment description">
                                                        </td>

                                                        <!-- Amount -->
                                                        <td>
                                                            <label for="cash" class="form-label">SUM-Amount <i class="fa fa-money" style="color: #27ae60;"></i></label>
                                                            <input type="number" class="form-control" name="cash" id="cash" 
                                                                placeholder="Enter amount in Ksh" required pattern="\d*">
                                                        </td>
                                                    </tr>
                                                </table>


                                                <hr class="my-3">

                                                <!-- Students Table Section -->
                                                <div class="mb-3">
                                                    <div class="panel panel-info">                                                      
                                                        <div class="panel-body p-3">
                                                            <table class="table align-middle mb-3" style="background-color: #eaf2f8; font-weight: bold; padding: 10px; border-radius: 8px; color: #2c3e50;">
                                                                <tr>
                                                                    <!-- Label: Select Learner -->
                                                                    <td >
                                                                        <i class="fas fa-project-diagram me-2" style="color: #2980b9;"></i>
                                                                        Distribute Payment Per Child/Voteheads
                                                                    </td>
                                                                    <td style="white-space: nowrap;"><label for="studentSelect" class="form-label">Select Learner</label></td>
                                                                    <!-- Input: Select Learner -->
                                                                    <td style="width: 25%; white-space: nowrap;">
                                                                        <select class="form-control" id="studentSelect" >
                                                                            <option value="">-- Select Learner --</option>
                                                                            <?php foreach ($students as $student): ?>
                                                                            <option value="<?= htmlspecialchars($student->studentadmno) ?>" 
                                                                                    data-name="<?= htmlspecialchars($student->studentname) ?>"
                                                                                    data-grade="<?= htmlspecialchars($student->gradefullname ?? 'N/A') ?>">
                                                                                <?= htmlspecialchars($student->studentadmno) ?> - <?= htmlspecialchars($student->studentname) ?>
                                                                                <?= $student->gradefullname ? ' ('.htmlspecialchars($student->gradefullname).')' : '' ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </td>                                                                   
                                                                    <!-- Button: Add Student -->
                                                                    <td style="width: 10%; white-space: nowrap;">
                                                                        <button type="button" class="btn btn-outline-success" id="addStudentBtn">
                                                                            <i class="fa fa-plus"></i> Add to List
                                                                        </button>
                                                                    </td>

                                                                      
                                                                </tr>
                                                            </table>


                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-hover" id="studentsTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Name</th>
                                                                            <th>AdmNo</th>                                                                            
                                                                            <th>LatestGrade</th>
                                                                            <?php 
                                                                            $fee_voteheads_stmt = $dbh->prepare("SELECT id, votehead FROM voteheads WHERE isfeepayment = 'Yes' ORDER BY votehead ASC");
                                                                            $fee_voteheads_stmt->execute();
                                                                            $fee_voteheads = $fee_voteheads_stmt->fetchAll(PDO::FETCH_OBJ);

                                                                            foreach ($fee_voteheads as $vh): ?>
                                                                                <th><?= htmlspecialchars($vh->votehead) ?></th>
                                                                            <?php endforeach; ?>
                                                                            <th class="total-column">Total</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <!-- Rows added dynamically -->
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <input type="hidden" name="selected_students" id="selectedStudents">

                                                            <div class="text-right mt-2">
                                                                <strong style="color: #2c3e50;">Total Allocated: Ksh <span id="allocatedTotal" class="total-otherpay">0.00</span></strong>
                                                            </div>

                                                            <div id="amountMatchStatus" class="alert alert-danger text-right mt-2" style="display: none;">
                                                                <i class="fa fa-exclamation-triangle"></i> <span id="mismatchMessage"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                     <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                        <button type="submit" name="receivepay_submit" id="submitBtn" class="btn" disabled>
                                                            <i class="fa fa-check-circle me-2"></i> POST THE BULK FEE-PAYMENT
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Links -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
$(document).ready(function() {
    // Array to store selected students
    let selectedStudents = [];
    
    // Add student to table
$('#addStudentBtn').click(function() {
    const studentSelect = $('#studentSelect');
    const selectedOption = studentSelect.find('option:selected');
    const admNo = selectedOption.val();
    const studentName = selectedOption.data('name');
    const grade = selectedOption.data('grade');
    
    if (!admNo) {
        Swal.fire({
            title: 'Error',
            text: 'Please select a student first',
            icon: 'error',
            confirmButtonColor: '#e74c3c'
        });
        return;
    }
    
    // Check if student already added
    if (selectedStudents.includes(admNo)) {
        Swal.fire({
            title: 'Warning',
            text: 'This student has already been added',
            icon: 'warning',
            confirmButtonColor: '#f39c12'
        });
        return;
    }
    
    // Add to array
    selectedStudents.push(admNo);
    

    // Create votehead cells and calculate initial total
    let voteheadCells = '';
    let initialTotal = 0;
    
    <?php foreach ($fee_voteheads as $vh): ?>
        voteheadCells += `
            <td>
                <input type="number" 
                       class="form-control student-votehead" 
                       name="student_voteheads[${admNo}][<?= $vh->id ?>]" 
                       step="1" 
                       min="0" 
                       value="0" 
                       style="width: 100px;"
                       data-votehead-id="<?= $vh->id ?>">
            </td>`;
    <?php endforeach; ?>
    
    // Add Total cell
    const totalCell = `<td class="total-cell">0</td>`;
    
 function updateRowNumbers() {
    $('#studentsTable tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}

// Add row to table with the grade
$('#studentsTable tbody').append(`
    <tr data-admno="${admNo}">
        <td></td> <!-- Row number to be filled dynamically -->
        <td>${studentName}</td>
        <td>${admNo}</td>
        <td class="grade-cell">
            <span style="color:${grade === 'N/A' ? 'red' : 'blue'}; font-size:14px;">
                ${grade}
            </span>
        </td>
        ${voteheadCells}
        ${totalCell}
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-student-row">
                <i class="fa fa-trash"></i> Remove
            </button>
        </td>
    </tr>
`);

updateRowNumbers();

    
    // Update hidden field with comma-separated list of admission numbers
    $('#selectedStudents').val(selectedStudents.join(','));
    
    // Reset dropdown
    studentSelect.val('');
    
    // Initialize the student-votehead event handlers
    $('.student-votehead').off('input').on('input', calculateTotalAllocation);
});
    

    // Remove student from table
    $(document).on('click', '.remove-student-row', function() {
        const row = $(this).closest('tr');
        const admNo = row.data('admno');
        
        // Remove from array
        selectedStudents = selectedStudents.filter(item => item !== admNo);
        
        // Remove row
        row.remove();
        
        // Update hidden field
        $('#selectedStudents').val(selectedStudents.join(','));
        
        // Recalculate totals
        calculateTotalAllocation();
    });
    
    
        // Function to calculate row total
        function calculateRowTotal(row) {
            let rowTotal = 0;
            $(row).find('.student-votehead').each(function() {
                rowTotal += parseFloat($(this).val()) || 0;
            });
            $(row).find('.total-cell').text(rowTotal.toFixed(2));
            return rowTotal;
        }
        
        // Update calculateTotalAllocation function to include row totals
   function calculateTotalAllocation() {
    let total = 0;

    // Calculate from student-specific votehead inputs and update row totals
    $('tr[data-admno]').each(function () {
        total += calculateRowTotal(this);
    });

    // Update display
    $('#allocatedTotal').text(total.toFixed(2));

    // Validate against cash amount
    const cashAmount = parseFloat($('#cash').val()) || 0;
    const difference = total - cashAmount; // preserve sign

    if (cashAmount > 0 && Math.abs(difference) > 0.01) {
        $('#amountMatchStatus').show();

        const signLabel = difference > 0 ? 'Overallocated' : 'Underallocated';
        const displayDiff = Math.abs(difference).toFixed(2);

        $('#mismatchMessage').html(
            `Total allocated (<strong>Ksh ${total.toFixed(2)}</strong>) ` +
            `doesn't match entered amount (<strong>Ksh ${cashAmount.toFixed(2)}</strong>). ` +
            `<span class="amount-mismatch">${signLabel}: Ksh ${displayDiff}</span>`
        );

        $('#submitBtn').prop('disabled', true)
            .css('background', '#95a5a6')
            .css('cursor', 'not-allowed');
    } else {
        $('#amountMatchStatus').hide();
        $('#submitBtn').prop('disabled', false)
            .css('background', '#27ae60')
            .css('cursor', 'pointer');
    }
}

        // Initialize event handlers
        $(document).on('input', '.student-votehead', function() {
            calculateRowTotal($(this).closest('tr'));
            calculateTotalAllocation();
        });
        
        $('#cash').on('input', calculateTotalAllocation);
        
        // Form submission validation
        $('form').submit(function(e) {
            if (selectedStudents.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Please add at least one student',
                    icon: 'error',
                    confirmButtonColor: '#e74c3c'
                });
                return;
            }
            
            const cash = parseFloat($('#cash').val()) || 0;
            let total = 0;
            
            $('.student-votehead').each(function() {
                total += parseFloat($(this).val()) || 0;
            });

            if (Math.abs(cash - total) > 0.01) {
                e.preventDefault();
                const difference = Math.abs(cash - total);
                
                Swal.fire({
                    title: 'Amount Mismatch',
                    html: `Total allocated (<strong>Ksh ${total.toFixed(2)}</strong>) must match the cash entered (<strong>Ksh ${cash.toFixed(2)}</strong>).<br><br>
                           <span class="amount-mismatch">Difference: Ksh ${difference.toFixed(2)}</span>`,
                    icon: 'error',
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>
<script>
$(document).ready(function () {
    $('#studentSelect').select2({
        placeholder: "-- Select Learner --",
        allowClear: true,
        width: 'resolve',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;

            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const name = $(data.element).data('name')?.toLowerCase() || '';
            const grade = $(data.element).data('grade')?.toLowerCase() || '';

            if (text.includes(term) || name.includes(term) || grade.includes(term)) {
                return data;
            }

            return null;
        }
    });
});
</script>
</body>
</html>