<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit;
} else {
    $eid = $_GET['editid']; 
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Update Fee Payment</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .modal-style {
            border-radius: 8px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .header-gradient {
            background: linear-gradient(135deg, rgb(174, 25, 201) 0%, #207cca 51%, #2989d8 100%);
            color: white;
            border-radius: 8px 8px 0 0;
            border-bottom: none;
            padding: 15px 20px;
        }
        .form-label {
            font-weight: 500;
            color: #555;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .form-control-custom {
            border: 1px solid #ddd;
            width: 100%;
            padding: 6px 10px;
            border-radius: 3px;
            font-size: 13px;
        }
        .amount-input {
            border: 1px solid #2ecc71;
            font-weight: bold;
        }
        .receipt-input {
            background-color: #fff8e1;
            font-weight: bold;
            color: #d35400;
            border: 1px solid #ffd699;
        }
        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }
        .btn-update {
            background: linear-gradient(135deg, #27ae60 0%,#219653 100%);
            border: none;
            padding: 8px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            color: white;
            border-radius: 3px;
            font-size: 13px;
        }
        .total-allocated {
            font-size: 1.1em;
            color: #2a6496;
            font-weight: bold;
        }
        .validation-alert {
            display: none;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        .disabled-field {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }
        .btn-update {
    background: linear-gradient(135deg, #27ae60 0%,#219653 100%);
    border: none;
    padding: 16px 30px; /* Increased vertical padding */
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    color: white;
    border-radius: 3px;
    font-size: 14px;
}

    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('includes/header.php');?>
        <?php include_once('includes/sidebar.php');?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">               
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Update Fee Payment Details</h2>
                            </td>                          
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default modal-style">
                        <div class="panel-heading header-gradient">
                            <h2 class="panel-title text-center" style="font-weight: 600; font-size: 18px;">
                                <i class="bi bi-wallet2"></i> UPDATE FEE PAYMENT
                            </h2>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form method="POST" enctype="multipart/form-data" action="manage-feepayments.php" id="updatePaymentForm">                                      
                                        <?php
                                            $sql="SELECT * from feepayments where id=$eid";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                                            if($query->rowCount() > 0) {
                                                foreach($results as $row) { 
                                                    // Get student name from studentdetail table
                                                    $student_name = "";
                                                    $admno = $row->studentadmno;
                                                    $student_query = $dbh->prepare("SELECT studentname FROM studentdetails WHERE studentadmno = ?");
                                                    $student_query->execute([$admno]);
                                                    if ($student_query->rowCount() > 0) {
                                                        $student_data = $student_query->fetch(PDO::FETCH_OBJ);
                                                        $student_name = $student_data->studentname;
                                                    }

                                                    // Get existing votehead allocations
                                                    $votehead_stmt = $dbh->prepare("SELECT vh.votehead, pv.votehead_id, pv.amount 
                                                                                FROM feepayment_voteheads pv 
                                                                                JOIN voteheads vh ON pv.votehead_id = vh.id 
                                                                                WHERE pv.payment_id = ?");
                                                    $votehead_stmt->execute([$row->id]);
                                                    $votehead_allocations = $votehead_stmt->fetchAll(PDO::FETCH_OBJ);
                                                    
                                                    $allocated_amounts = [];
                                                    foreach($votehead_allocations as $alloc) {
                                                        $allocated_amounts[$alloc->votehead_id] = $alloc->amount;
                                                    }
                                                    
                                                    $isBulkPayment = ($row->paymenttype == "Bulk");
                                            ?>
                                                    <input type="hidden" name="id" value="<?php echo $row->id ?>">
                                                    <input type="hidden" name="paymenttype" value="<?php echo $row->paymenttype ?>">
                                                    
                                                    <div class="row" style="padding: 15px;">
                                                        <div class="col-md-1">
                                                            <div class="form-group">
                                                                <label for="studentadmno" class="form-label">AdmNo</label>
                                                                <input type="text" name="studentadmno" id="studentadmno" required 
                                                                    class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" 
                                                                    value="<?php echo htmlspecialchars($row->studentadmno); ?>" 
                                                                    <?php echo $isBulkPayment ? 'readonly' : 'readonly'; ?>>
                                                            </div>
                                                        </div>  
                                                         <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="studentname" class="form-label">Name</label>
                                                                <input type="text" name="studentname" id="studentname" required 
                                                                    class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" 
                                                                    value="<?php echo htmlspecialchars($student_name); ?>" 
                                                                    <?php echo $isBulkPayment ? 'readonly' : 'readonly'; ?>>
                                                            </div>
                                                        </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for="receiptno" class="form-label">Receipt No</label>
                                                            <input type="text" class="form-control form-control-custom receipt-input <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="receiptno" id="receiptno" required value="<?php echo $row->receiptno; ?>" <?php echo $isBulkPayment ? 'readonly' : 'readonly'; ?>>
                                                        </div>
                                                    </div>
                                                      <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for="cash" class="form-label">Amount <i class="fa fa-money" style="color: #27ae60;"></i></label>
                                                            <input type="number" class="form-control form-control-custom amount-input <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="cash" id="cash" required value="<?php echo $row->cash; ?>" <?php echo $isBulkPayment ? 'readonly' : ''; ?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for="academicyear" class="form-label">Academic Year</label>
                                                            <input type="text" class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="academicyear" value="<?php echo $row->academicyear; ?>" <?php echo $isBulkPayment ? 'readonly' : ''; ?>>
                                                        </div>
                                                    </div>
                                                  
                                                    
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for="bank" class="form-label">Payment Mode <i class="fa fa-bank" style="color: #9b59b6;"></i></label>
                                                            <select name="bank" class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" required <?php echo $isBulkPayment ? 'disabled' : ''; ?>>
                                                                <option value="<?php echo $row->bank; ?>"><?php echo $row->bank; ?></option>
                                                                <?php
                                                                if (!$isBulkPayment) {
                                                                    $smt=$dbh->prepare('SELECT bankname from bankdetails');
                                                                    $smt->execute();
                                                                    $data=$smt->fetchAll();
                                                                    foreach ($data as $rw):
                                                                ?>
                                                                    <option value="<?=$rw["bankname"]?>"><?=$rw["bankname"]?></option>
                                                                <?php 
                                                                    endforeach;
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="reference" class="form-label">Reference (Mpesa Code, if any)</label>
                                                            <input type="text" class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="reference" value="<?php echo $row->reference; ?>" placeholder="Reference (optional)" <?php echo $isBulkPayment ? 'readonly' : ''; ?>>
                                                        </div>
                                                    </div>
                                                
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for="reference" class="form-label">Type</label>
                                                            <input type="text" class="form-control form-control-custom disabled-field" name="paymenttype_display" value="<?php echo $row->paymenttype; ?>" readonly>
                                                        </div>
                                                    </div>
                                                      
                                                  
                                                    <!-- Votehead Allocation Section -->
                                                    <?php
                                                    $fee_voteheads_stmt = $dbh->prepare("SELECT id, votehead FROM voteheads WHERE isfeepayment = 'Yes' ORDER BY votehead ASC");
                                                    $fee_voteheads_stmt->execute();
                                                    $fee_voteheads = $fee_voteheads_stmt->fetchAll(PDO::FETCH_OBJ);
                                                    ?>
                                                    <div class="col-md-8">
                                                        <div class="panel panel-info">
                                                            <div class="panel-heading text-center">
                                                                <strong>Distribute Payment Per Voteheads.
                                                                    <?php if($isBulkPayment): ?>
                                                                        <span style="color: red;">Note: If it was from a Bulk payment, MOST of above details are disabled</span>
                                                                    <?php endif; ?>
                                                                </strong>
                                                            </div>
                                                            <div class="panel-body">
                                                                <table class="table table-bordered table-hover">
                                                                    <thead class="bg-info">
                                                                        <tr>
                                                                            <th style="width: 60%;">Votehead</th>
                                                                            <th style="width: 40%;">Amount (Ksh)</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($fee_voteheads as $vh): ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($vh->votehead) ?></td>
                                                                            <td>
                                                                                <input type="number" class="form-control form-control-custom votehead-amount" 
                                                                                       name="votehead_amounts[<?= $vh->id ?>]" 
                                                                                       step="1" min="0" 
                                                                                       value="<?= $allocated_amounts[$vh->id] ?? 0 ?>" 
                                                                                       style="text-align: right;"
                                                                                       <?php echo $isBulkPayment ? '' : 'required'; ?>>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                                <div class="text-right" style="padding: 10px 12px; background-color: #f8f9fa; border-top: 2px solid #eee; font-size: 13px;">
                                                                    <strong style="color: #2c3e50;">Total Allocated: Ksh <span id="allocatedTotal" class="total-allocated">0.00</span></strong>
                                                                </div>
                                                                <div id="amountMatchStatus" class="validation-alert alert-danger text-right">
                                                                    <i class="fa fa-exclamation-triangle"></i> Total allocated per items must match the entered total amount.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End Votehead Allocation -->
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="payer" class="form-label">Payer</label>
                                                            <input type="text" class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="payer" value="<?php echo $row->payer; ?>" placeholder="Payer" <?php echo $isBulkPayment ? 'readonly' : ''; ?>>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="bankpaymentdate" class="form-label">Bank Payment Date</label>
                                                            <input type="date" class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="bankpaymentdate" value="<?php echo $row->bankpaymentdate; ?>" <?php echo $isBulkPayment ? 'readonly' : ''; ?>>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="paymentdate" class="form-label">Receipt Date</label>
                                                            <input type="date" class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="paymentdate" value="<?php echo $row->paymentdate; ?>" <?php echo $isBulkPayment ? 'readonly' : ''; ?>>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="details" class="form-label">Payment Details</label>
                                                            <textarea class="form-control form-control-custom <?php echo $isBulkPayment ? 'disabled-field' : ''; ?>" name="details" placeholder="Enter payment description" style="min-height: 40px;" <?php echo $isBulkPayment ? 'readonly' : ''; ?>><?php echo $row->details; ?></textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="cashier" class="form-label">Cashier</label>
                                                            <input type="text" class="form-control form-control-custom disabled-field" name="cashier" value="<?php echo $username; ?>" readonly>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4 text-center" style="margin-top: 20px;">
                                                        <button type="submit" name="updatefeepayment" id="submitBtn" class="btn btn-update">
                                                            <i class="fa fa-check-circle"></i> UPDATE PAYMENT
                                                        </button>
                                                    </div>
                                                </div>
                                        <?php }} ?>
                                    </form>
                                </div>
                            </div>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Check if this is a Bulk payment
        const isBulkPayment = "<?php echo $isBulkPayment ? 'true' : 'false'; ?>";
        
        // Calculate initial total allocation
        updateTotalAllocation();
        
        // Event listeners for amount changes
        $(".votehead-amount").on("input", updateTotalAllocation);
        
        // Only add cash input listener if not Bulk payment
        if (!isBulkPayment) {
            $("#cash").on("input", updateTotalAllocation);
        }
        
        // Form submission handler
        $("#updatePaymentForm").on("submit", function(e) {
            const cash = parseFloat($("#cash").val()) || 0;
            let total = 0;
            
            $(".votehead-amount").each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            
            if (cash < 0 || total < 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Invalid Amount',
                    text: 'Amounts cannot be negative',
                    icon: 'error',
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'OK'
                });
            } else if (Math.abs(cash - total) > 0.01) {
                e.preventDefault();
                Swal.fire({
                    title: 'Amount Mismatch',
                    html: `
                        Total allocated (<strong>Ksh ${total.toFixed(2)}</strong>) must match 
                        the cash entered (<strong>Ksh ${cash.toFixed(2)}</strong>).
                    `,
                    icon: 'error',
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'OK'
                });
            }
        });
        
        function updateTotalAllocation() {
            let total = 0;
            $(".votehead-amount").each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            
            $("#allocatedTotal").text(total.toFixed(2));
            
            const cash = parseFloat($("#cash").val()) || 0;
            const statusElement = $("#amountMatchStatus");
            const submitBtn = $("#submitBtn");
            
            // Modified condition to allow zero
            if (cash >= 0 && total >= 0) {
                const difference = (cash - total).toFixed(2);
                const absDiff = Math.abs(difference).toFixed(2);
                
                if (Math.abs(total - cash) <= 0.01) {
                    // Amounts match (including zero)
                    $("#allocatedTotal").css("color", "#27ae60");
                    statusElement.css({
                        "display": "block",
                        "background-color": "#e8f5e9",
                        "color": "#27ae60"
                    }).html('<i class="fa fa-check-circle"></i> Amounts match! Ready to submit.');
                    submitBtn.prop("disabled", false);
                    submitBtn.css({
                        "background": "linear-gradient(135deg, #27ae60 0%,#219653 100%)",
                        "cursor": "pointer"
                    });
                } else {
                    // Amounts don't match
                    $("#allocatedTotal").css("color", "#e74c3c");
                    statusElement.css({
                        "display": "block",
                        "background-color": "#ffebee",
                        "color": "#e74c3c"
                    }).html(`<i class="fa fa-exclamation-triangle"></i> 
                            Amounts don't match! Difference: <strong>Ksh ${absDiff}</strong><br> 
                            (${difference > 0 ? 'Under' : 'Over'} allocated)
                            <br><small>(Allocated: Ksh ${total.toFixed(2)} | Cash: Ksh ${cash.toFixed(2)})</small>`);
                    submitBtn.prop("disabled", true);
                    submitBtn.css({
                        "background": "#95a5a6",
                        "cursor": "not-allowed"
                    });
                }
            } else {
                // Negative input
                $("#allocatedTotal").css("color", "#000");
                statusElement.css({
                    "display": "block",
                    "background-color": "#ffebee",
                    "color": "#e74c3c"
                }).html('<i class="fa fa-exclamation-triangle"></i> Amounts cannot be negative!');
                submitBtn.prop("disabled", true);
                submitBtn.css({
                    "background": "#95a5a6",
                    "cursor": "not-allowed"
                });
            }
        }
        
    
    });
    
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</body>
</html>