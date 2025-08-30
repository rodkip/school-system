<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

class FeeBalanceUpdater {
    private $dbh;
    
    public function __construct($dbh) {
        $this->dbh = $dbh;
    }
    
    public function updateStudentBalances(array $studentAdmNos) {
        if (empty($studentAdmNos)) {
            return;
        }
        
        foreach ($studentAdmNos as $searchadmno) {
            try {
               // Include the SQL query file
                require_once 'updatefeebalancesql.php';
                // Prepare the SQL query with votehead-based calculations
                $sql = getStudentFeeQuery();
                $query = $this->dbh->prepare($sql);
                $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                $query->execute();

                $results = $query->fetchAll(PDO::FETCH_OBJ);

                if ($query->rowCount() > 0) {
                    foreach ($results as $row) {
                        $yearlybal = 0;
                        $arr = 0;
                        
                        // Calculate term totals with transport fees and subtract transport waivers per term
                        $firstterm_total = ($row->firsttermfee + $row->firsttermtransport) - $row->firsttermtransportwaiver;
                        $secondterm_total = ($row->secondtermfee + $row->secondtermtransport) - $row->secondtermtransportwaiver;
                        $thirdterm_total = ($row->thirdtermfee + $row->thirdtermtransport) - $row->thirdtermtransportwaiver;
                        
                        $adjusted = $yearlybal + $arr;
                        $remaining_payment = $row->totpayperyear;
                        
                        $firstterm_net = max(0, $firstterm_total - $row->firsttermfeewaiver + $adjusted);
                        $firstterm_payment = min($remaining_payment, $firstterm_net);
                        $firstterm_balance = $firstterm_net - $firstterm_payment;
                        $remaining_payment -= $firstterm_payment;
                        
                        $secondterm_net = max(0, $secondterm_total - $row->secondtermfeewaiver);
                        $secondterm_payment = min($remaining_payment, $secondterm_net);
                        $secondterm_balance = $secondterm_net - $secondterm_payment;
                        $remaining_payment -= $secondterm_payment;
                        
                        $thirdterm_net = max(0, $thirdterm_total - $row->thirdtermfeewaiver);
                        $thirdterm_payment = min($remaining_payment, $thirdterm_net);
                        $thirdterm_balance = $thirdterm_net - $thirdterm_payment;
                        
                        $firsttermbalcal = max(0, min($firstterm_balance, ($firstterm_total + $adjusted)));
                        $secondtermbalcal = max(0, min($secondterm_balance, $secondterm_total));
                        $thirdtermbalcal = max(0, min($thirdterm_balance, $thirdterm_total));
                        
                        $yearly_total = $row->totcalfee - $row->totpayperyear +  $adjusted;
                        
                        $arrears = $yearlybal + $arr;
                        $balperyear = $yearly_total;
                        $yearlybal = $yearly_total;
                        
                        $feetreatment = $row->feeTreatment;
                        $childtreatment = $row->childTreatment;
                        $totcalfee = $row->totcalfee;
                        $firsttermfeecal = $row->firsttermfee;
                        $secondtermfeecal = $row->secondtermfee;
                        $thirdtermfeecal = $row->thirdtermfee;
                        $othersfeecal = $row->othersfee;
                        $totpayperyear = $row->totpayperyear;
                        $studentname = $row->studentname;
                        $boarding = $row->boarding;
                        $feebalancecode = $row->gradefullname.$row->studentadmno;
                        $gradefullname = $row->gradefullname;
                        $childstatus = $row->childstatus;
                        $studentadmno = $row->studentadmno;
                        
                        $check_sql = "SELECT feebalancecode FROM feebalances WHERE feebalancecode = :feebalancecode";
                        $check_query = $this->dbh->prepare($check_sql);
                        $check_query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
                        $check_query->execute();
                        
                        if ($check_query->rowCount() > 0) {
                            $update_sql = "UPDATE feebalances SET 
                                childstatus = :childstatus,
                                arrears = :arrears,
                                firsttermbal = :firsttermbalcal,
                                secondtermbal = :secondtermbalcal,
                                thirdtermbal = :thirdtermbalcal,
                                yearlybal = :balperyear,
                                feetreatment = :feetreatment,
                                childtreatment = :childtreatment,
                                studentname = :studentname,
                                gradefullname = :gradefullname,
                                totalfee = :totcalfee,
                                totalpaid = :totpayperyear,
                                firsttermfee = :firsttermfeecal,
                                secondtermfee = :secondtermfeecal,
                                thirdtermfee = :thirdtermfeecal,
                                othersfee = :othersfeecal,
                                boarding = :boarding 
                            WHERE feebalancecode = :feebalancecode";
                            
                            $update_query = $this->dbh->prepare($update_sql);
                            $update_query->execute([
                                ':feebalancecode' => $feebalancecode,
                                ':childstatus' => $childstatus,
                                ':arrears' => $arrears,
                                ':firsttermbalcal' => $firsttermbalcal,
                                ':secondtermbalcal' => $secondtermbalcal,
                                ':thirdtermbalcal' => $thirdtermbalcal,
                                ':balperyear' => $balperyear,
                                ':feetreatment' => $feetreatment,
                                ':childtreatment' => $childtreatment,
                                ':studentname' => $studentname,
                                ':gradefullname' => $gradefullname,
                                ':totcalfee' => $totcalfee,
                                ':totpayperyear' => $totpayperyear,
                                ':firsttermfeecal' => $firsttermfeecal,
                                ':secondtermfeecal' => $secondtermfeecal,
                                ':thirdtermfeecal' => $thirdtermfeecal,
                                ':othersfeecal' => $othersfeecal,
                                ':boarding' => $boarding
                            ]);
                        } else {
                            $insert_sql = "INSERT INTO feebalances 
                                (feebalancecode, arrears, firsttermbal, secondtermbal, thirdtermbal, 
                                yearlybal, gradefullname, studentadmno, feetreatment, childtreatment, 
                                studentname, totalfee, totalpaid, firsttermfee, secondtermfee, 
                                thirdtermfee, othersfee, boarding, childstatus) 
                            VALUES
                                (:feebalancecode, :arrears, :firsttermbalcal, :secondtermbalcal, :thirdtermbalcal, 
                                :balperyear, :gradefullname, :studentadmno, :feetreatment, :childtreatment, 
                                :studentname, :totcalfee, :totpayperyear, :firsttermfeecal, :secondtermfeecal, 
                                :thirdtermfeecal, :othersfeecal, :boarding, :childstatus)";
                            
                            $insert_query = $this->dbh->prepare($insert_sql);
                            $insert_query->execute([
                                ':feebalancecode' => $feebalancecode,
                                ':arrears' => $arrears,
                                ':firsttermbalcal' => $firsttermbalcal,
                                ':secondtermbalcal' => $secondtermbalcal,
                                ':thirdtermbalcal' => $thirdtermbalcal,
                                ':balperyear' => $balperyear,
                                ':gradefullname' => $gradefullname,
                                ':studentadmno' => $studentadmno,
                                ':feetreatment' => $feetreatment,
                                ':childtreatment' => $childtreatment,
                                ':studentname' => $studentname,
                                ':totcalfee' => $totcalfee,
                                ':totpayperyear' => $totpayperyear,
                                ':firsttermfeecal' => $firsttermfeecal,
                                ':secondtermfeecal' => $secondtermfeecal,
                                ':thirdtermfeecal' => $thirdtermfeecal,
                                ':othersfeecal' => $othersfeecal,
                                ':boarding' => $boarding,
                                ':childstatus' => $childstatus
                            ]);
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Balance update error for student $searchadmno: " . $e->getMessage());
                continue;
            }
        }
    }
}

// Rest of your payment processing code remains unchanged...
if (isset($_POST['receivepay_submit'])) {
    try {
        $dbh->beginTransaction();

        // Validate required fields
        $required = ['receiptno', 'bank', 'paymentdate', 'academicyear', 'username', 'selected_students', 'payer'];
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
            'cashier' => $_POST['username'],
            'payer' => $_POST['payer'],
            'paymenttype' => "Bulk"
        ];

       
        // Process students
        $studentAdmNos = array_filter(explode(',', $_POST['selected_students']));
        if (empty($studentAdmNos)) {
            throw new Exception("No students selected for payment");
        }

        // Prepare statements
        $paymentStmt = $dbh->prepare("
            INSERT INTO feepayments 
            (studentadmno, receiptno, cash, bank, reference, bankpaymentdate, paymentdate, details, academicyear, cashier, receiptcode, payer,paymenttype)
            VALUES 
            (:studentadmno, :receiptno, :cash, :bank, :reference, :bankpaymentdate, :paymentdate, :details, :academicyear, :cashier, :receiptcode, :payer, :paymenttype)
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
            $studentTotal = 0;
            $studentVoteheads = $_POST['student_voteheads'][$admNo] ?? [];

            foreach ($studentVoteheads as $amount) {
                $studentTotal += (float)$amount;
            }

            $grandTotal += $studentTotal;

            $receiptcode = $admNo . $paymentData['academicyear'];
            $paymentStmt->execute([
                ':studentadmno' => $admNo,
                ':receiptno' => $paymentData['receiptno'],
                ':cash' => $studentTotal,
                ':bank' => $paymentData['bank'],
                ':reference' => $paymentData['reference'],
                ':bankpaymentdate' => $paymentData['bankpaymentdate'],
                ':paymentdate' => $paymentData['paymentdate'],
                ':details' => $paymentData['details'],
                ':academicyear' => $paymentData['academicyear'],
                ':cashier' => $paymentData['cashier'],
                ':receiptcode' => $receiptcode,
                ':payer' => $paymentData['payer'],
                ':paymenttype' => $paymentData['paymenttype']
            ]);

            $paymentId = $dbh->lastInsertId();
            $processedCount++;

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

        if (abs($grandTotal - $totalAllocated) > 0.01) {
            throw new Exception(sprintf(
                "Allocation mismatch: Sum of student totals (Ksh %.2f) doesn't match sum of voteheads (Ksh %.2f)",
                $grandTotal,
                $totalAllocated
            ));
        }

        $dbh->commit();

        // Update balances for paying students in background
        try {
            $updater = new FeeBalanceUpdater($dbh);
            $updater->updateStudentBalances($studentAdmNos);
            
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Successfully processed payments for $processedCount students (Total: Ksh " . 
                number_format($grandTotal, 2) . ") and updated their balances.";
                
        } catch (Exception $e) {
            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Payments processed successfully for $processedCount students (Total: Ksh " . 
                number_format($grandTotal, 2) . "), but balance update failed: " . $e->getMessage();
            error_log("Balance Update Error: " . $e->getMessage());
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } catch (PDOException $e) {
        $dbh->rollBack();
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Database error: " . $e->getMessage();
        error_log("Payment Error: " . $e->getMessage());
    } catch (Exception $e) {
        $dbh->rollBack();
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = $e->getMessage();
        error_log("Payment Error: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Bulk-Fee Payments</title>
    <!-- Core CSS -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link rel="icon" href="images/tabpic.png">
    
    <!-- Font Awesome -->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    
    <!-- Other plugins -->
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link rel="icon" href="images/tabpic.png">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
      #myTable.loading-overlay {
        position: relative;
      }

      #myTable.loading-overlay:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
      }

      #myTable.loading-overlay:after {
        content: "Loading...";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 1.2em;
        z-index: 10000;
      }

      @keyframes tickAnimation {
        0% {
          transform: scale(0);
          opacity: 0;
        }

        50% {
          transform: scale(1.2);
          opacity: 1;
        }

        100% {
          transform: scale(1);
          opacity: 1;
        }
      }

      .ticking-icon {
        animation: tickAnimation 0.5s ease-in-out;
      }
      /* Add margin between rows on small screens */
      @media (max-width: 767.98px) {
          .row > div {
              margin-bottom: 15px;
          }
      }
      
      .print-status-yes {
          color: #28a745;
      }
      
      .print-status-no {
          color: #dc3545;
      }
      
      .btn-download {
          background-color: #4e73df;
          color: white;
          border: none;
          padding: 8px 15px;
          border-radius: 4px;
          cursor: pointer;
      }
      
      .btn-download:hover {
          background-color: #2e59d9;
      }
      
      #selectedFilters {
          font-weight: bold;
          color: #4e73df;
          margin-bottom: 10px;
          display: inline-block;
      }
      
      /* Student link style */
      .show-students {
          color: #337ab7;
          text-decoration: none;
          font-weight: bold;
          cursor: pointer;
      }
      .show-students:hover {
          color: #23527c;
          text-decoration: underline;
      }
      
      /* Modal styles */
      .modal-header {
          background-color: #4e73df;
          color: white;
      }
      
      .student-list-item {
          padding: 8px 0;
          border-bottom: 1px solid #eee;
      }
      
      .student-list-item:last-child {
          border-bottom: none;
      }
      
      .student-admno {
          font-weight: bold;
          color: #4e73df;
          display: inline-block;
          width: 100px;
      }
    </style>
</head>
<body>
    <!--  wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php'); ?>
        <!-- end navbar top -->
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- end navbar side -->
        <!--  page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h1 class="page-header">BULK-Fee Payments</h1>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary"
                                    style="--btn-color: #e74a3b; --hover-color: #be2617;"
                                    onclick="window.location.href='newfeepaymentbulk.php';">
                                    <i class="bi bi-bar-chart-line me-2"></i> New Bulk Payment
                                </button>
                            </td>

                            <td>&nbsp;&nbsp;
                                <button onclick="downloadCSV()" class="btn-download">Download Filtered CSV</button>
                            </td>
                            <td>                                  
                            <?php include_once('updatemessagepopup.php'); ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <!--end page header -->
            </div>
            <div class="panel panel-primary">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- Advanced Tables -->
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="form-group">
                                    <div class="container-fluid">
                                        <div class="row align-items-center">
                                            <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                                <span style="color:rgb(242, 8, 16);">Note: The listed are ReceiptNOs with two or more Learners paid to.</span> If you pay for only Learner, you wont see the records here.
                                                <div id="table-wrapper">                                            
                                                    <?php
                                                        $sql = "
                                                            SELECT
                                                                fp.receiptno,
                                                                COUNT(*) AS record_count,
                                                                SUM(fp.cash) AS total_cash,
                                                                fp.bank,
                                                                fp.bankpaymentdate,
                                                                fp.paymentdate,
                                                                fp.reference,
                                                                fp.details,
                                                                fp.academicyear,
                                                                fp.cashier,
                                                                fp.entrydate,
                                                                fp.payer,
                                                                GROUP_CONCAT(DISTINCT CONCAT(sd.studentadmno, ' - ', sd.studentname, ' (', COALESCE(ce.gradefullname, 'N/A'), ')') ORDER BY sd.studentadmno SEPARATOR '||') AS student_info
                                                            FROM feepayments fp
                                                            LEFT JOIN studentdetails sd ON fp.studentadmno = sd.studentadmno
                                                            LEFT JOIN (
                                                                SELECT ce1.studentadmno, ce1.gradefullname
                                                                FROM classentries ce1
                                                                INNER JOIN (
                                                                    SELECT studentadmno, MAX(id) AS max_id
                                                                    FROM classentries
                                                                    GROUP BY studentadmno
                                                                ) ce2 ON ce1.studentadmno = ce2.studentadmno AND ce1.id = ce2.max_id
                                                            ) ce ON fp.studentadmno = ce.studentadmno
                                                            WHERE fp.receiptno IN (
                                                                SELECT receiptno
                                                                FROM feepayments
                                                                GROUP BY receiptno
                                                                HAVING COUNT(*) > 1
                                                            )
                                                            GROUP BY
                                                                fp.receiptno,
                                                                fp.bank,
                                                                fp.bankpaymentdate,
                                                                fp.paymentdate,
                                                                fp.reference,
                                                                fp.details,
                                                                fp.academicyear,
                                                                fp.cashier,
                                                                fp.entrydate,                                                                
                                                                fp.payer
                                                            ORDER BY fp.receiptno DESC
                                                        ";


                                                    $stmt = $dbh->prepare($sql);
                                                    $stmt->execute();
                                                    $summaryRecords = $stmt->fetchAll(PDO::FETCH_OBJ);
                                                    ?>

                                                    <table class="table table-striped table-bordered table-hover" id="dataTable">
                                                       <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>ReceiptNo</th>
                                                                <th>Learners</th>
                                                                <th>Amount</th>
                                                                <th>Mode</th>
                                                                <th>BankPayment Date</th>
                                                                <th>Receipt Date</th>
                                                                <th>Reference</th>
                                                                <th>Details</th>
                                                                <th>Year</th>
                                                                <th>Cashier</th>
                                                                <th>Payer</th> <!-- NEW COLUMN -->
                                                                <th>EntryDate</th>
                                                                <th>Print</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $cnt = 1; ?>
                                                            <?php foreach ($summaryRecords as $row): ?>
                                                            <tr>
                                                                <td><?= $cnt ?></td>
                                                                <td><?= htmlentities($row->receiptno) ?></td>
                                                                  <td>
                                                                    <?php if ($row->record_count > 0): ?>
                                                                        <a class="show-students" data-toggle="modal" data-target="#studentModal<?= $cnt ?>">
                                                                            <?= htmlentities($row->record_count) ?> Learner(s)
                                                                        </a>
                                                                    <?php else: ?>
                                                                        No students
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= number_format($row->total_cash) ?></td>                                                               
                                                                <td><?= htmlentities($row->bank) ?></td>
                                                                <td><?= htmlentities($row->bankpaymentdate) ?></td>
                                                                <td><?= htmlentities($row->paymentdate) ?></td>
                                                                <td><?= htmlentities($row->reference) ?></td>
                                                                <td><?= htmlentities($row->details) ?></td>
                                                                <td><?= htmlentities($row->academicyear) ?></td>
                                                                <td><?= htmlentities($row->cashier) ?></td>
                                                                <td><?= htmlentities($row->payer) ?></td> <!-- PAYER COLUMN -->
                                                                <td><?= htmlentities($row->entrydate) ?></td>

                                                                <td>
                                                                <a href="#" 
                                                                    onclick="printbulkfeepaymentReceipt('<?= htmlspecialchars($row->receiptno, ENT_QUOTES); ?>')"
                                                                    class="print-btn">
                                                                    <i class="fa fa-print"></i> Print Receipt
                                                                </a>
                                                                <?php if ($row->printed): ?>
                                                                    <br>
                                                                    <small class="text-success" id="print-status-<?= intval($row->id) ?>">
                                                                    Printed on <?= date('d/m/Y H:i', strtotime($row->print_date)) ?>
                                                                    </small>
                                                                <?php else: ?>
                                                                    <br>
                                                                    <small class="text-muted" id="print-status-<?= intval($row->id) ?>" style="display: none;"></small>
                                                                <?php endif; ?>
                                                                </td>
                                                               <td style="padding: 5px">
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        Action <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu pull-right" role="menu">
                                                                        <?php if (has_permission($accounttype, 'edit_bulkfeepayment')): ?>
                                                                            <li>
                                                                                <a href="edit-feepaymentbulk.php?editreceiptno=<?php echo htmlentities($row->receiptno); ?>">
                                                                                    <i class="fa fa-pencil"></i> Edit
                                                                                </a>
                                                                            </li>                                                        
                                                                            <li class="divider"></li>
                                                                            <li>
                                                                                <a href="newfeepaymentbulk.php?delete=<?php echo htmlentities($row->receiptno); ?>"
                                                                                onclick="return confirm('You want to delete the record?!!')">
                                                                                    <i class="fa fa-trash-o"></i> Delete
                                                                                </a>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                    </ul>
                                                                </div>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- Student Modal -->
                                                            <div class="modal fade" id="studentModal<?= $cnt ?>" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel<?= $cnt ?>" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                     <div class="modal-header bg-light border-bottom border-primary">
                                                                        <h5 class="modal-title font-weight-bold text-primary" id="studentModalLabel<?= $cnt ?>">
                                                                            <i class="fa fa-users mr-2"></i> Students for Receipt: <?= htmlentities($row->receiptno) ?>
                                                                            <span class="d-block mt-1" style="font-size: 16px; color: #343a40;">
                                                                                Total: <?= htmlentities($row->record_count) ?> student(s) | Ksh <?= number_format($row->total_cash, 2) ?>
                                                                            </span>
                                                                        </h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                        <div class="modal-body">
                                                                         <div class="student-list">
                                                                            <?php 
                                                                            $students = explode('||', $row->student_info);
                                                                            $studentCount = 1; // Initialize numbering here for each modal
                                                                            foreach ($students as $student): 
                                                                                list($admno, $name) = explode(' - ', $student, 2);
                                                                            ?>
                                                                            <div class="student-list-item">
                                                                                <strong><?= $studentCount++ ?>.</strong>                                                                                
                                                                                <span class="student-name"><?= htmlentities($name) ?></span>
                                                                                <span class="student-admno">(<?= htmlentities($admno) ?>)</span>
                                                                            </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <?php $cnt++; endforeach; ?>
                                                        </tbody>
                                                    </table>
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
        </div>
        <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->
    
    <!-- Load jQuery once -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                dom: '<"top"lf>rt<"bottom"ip>',
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                responsive: true
            });

            // Apply bank filter
            $('#bankFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(4).search(filterValue).draw();
            });
          
            // Apply academic year filter
            $('#academicyearFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(9).search(filterValue).draw();
            });
        });
        
 function downloadCSV() {
    // Use DataTables API to get the filtered rows and column headers
    var table = $('#dataTable').DataTable();
    var header = table.columns().header().toArray().map(col => col.innerText);
    var rows = table.rows({ search: 'applied' }).data().toArray(); // Get only filtered rows
    var csvData = [];

    // Include column headers as the first row
    csvData.push(header);

    // Function to extract text from HTML content
    function extractText(html) {
        if (!html) return '';
        
        // Create a temporary div to parse HTML
        var temp = document.createElement('div');
        temp.innerHTML = html;
        
        // Get text content and clean it up
        var text = temp.textContent || temp.innerText || '';
        return text.replace(/\s+/g, ' ').trim();
    }

    // Loop through filtered rows
    for (var i = 0; i < rows.length; i++) {
        var rowData = [];
        
        // Process each cell in the row
        for (var j = 0; j < rows[i].length; j++) {
            var cellContent = rows[i][j];
            
            // Special handling for specific columns
            if (j === 2) { // Learner column
                // Extract just the number from "X Learner(s)"
                var match = extractText(cellContent).match(/(\d+)/);
                rowData.push(match ? match[0] : '0');
            } 
            else if (j === 12) { // Print column
                // Just indicate if printable or not
                rowData.push('Printable');
            }
            else {
                // For all other columns, extract clean text
                rowData.push(extractText(cellContent));
            }
        }
        
        csvData.push(rowData);
    }

    // Convert the CSV data to a blob
    var csvContent = csvData.map(row => 
        row.map(field => `"${field.replace(/"/g, '""')}"`).join(',')
    ).join('\n');

    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

    // Create a link element and trigger a click event to download the CSV file
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'bulk_fee_payments_' + new Date().toISOString().slice(0,10) + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printbulkfeepaymentReceipt(receiptno) {
   var receiptWindow = window.open(
  'reportbulkfeepaymentreceiptlearners.php?receiptno=' + encodeURIComponent(receiptno),
  'Receipt_' + receiptno,
  'width=600,height=800'
);


    if (!receiptWindow) {
      alert('Popup was blocked. Please allow popups for this site.');
      return;
    }

    receiptWindow.onload = function () {
      setTimeout(function () {
        receiptWindow.print();
        // receiptWindow.close(); // Optional: Uncomment to auto-close
      }, 500);
    };
  }
    </script>
</body>
</html>