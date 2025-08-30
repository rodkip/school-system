<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $mess = "";
    $academicyear = date("Y");
    $currentdate = date("Y-m-d");
    $messagestate = '';
    $searchadmno = $_GET['viewstudentadmno'] ?? '';

// Delete a record
if (isset($_GET['delete'])) {
    try {
        // Get the student admission number associated with the fee payment ID
        $id = $_GET['delete'];
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve student admission number based on ID
        $sql = "SELECT studentadmno FROM feepayments WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            $searchadmno = $result->studentadmno;
        }

        // Delete the fee payment record
        $sql = "DELETE FROM feepayments WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        // Delete related votehead records first
        $sql = "DELETE FROM feepayment_voteheads WHERE payment_id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Fee Payment Record have been deleted";

        // Step 1: Backup orphaned balances related to the student
        $sql = "CREATE TABLE IF NOT EXISTS orphaned_balances_backup AS
                SELECT *
                FROM feebalances
                WHERE studentadmno = :studentadmno
                AND NOT EXISTS (
                    SELECT 1 
                    FROM feepayments 
                    WHERE feepayments.academicyear = LEFT(feebalances.feebalancecode, 4)
                      AND feepayments.studentadmno = feebalances.studentadmno
                )";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $searchadmno, PDO::PARAM_STR);
        $query->execute();

        // Step 2: Delete orphaned balances for the specific student
        $sql = "DELETE FROM feebalances
                WHERE studentadmno = :studentadmno
                AND NOT EXISTS (
                    SELECT 1 
                    FROM feepayments 
                    WHERE feepayments.academicyear = LEFT(feebalances.feebalancecode, 4)
                      AND feepayments.studentadmno = feebalances.studentadmno
                )";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $searchadmno, PDO::PARAM_STR);
        $query->execute();

      

    } catch (PDOException $e) {
        $mess = "Error: " . $e->getMessage();
        error_log($e->getMessage()); // Log the error for debugging
    }
}

// Delete other item payment record
if (isset($_GET['deleteother'])) {
    try {
        // Get the student admission number associated with the fee payment ID
        $id = $_GET['deleteother'];
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve student admission number based on ID
        $sql = "SELECT studentadmno FROM otheritemspayments WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            $searchadmno = $result->studentadmno;
        }

        // Delete the fee payment record
        $sql = "DELETE FROM otheritemspayments WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        // Delete related votehead records first
        $sql = "DELETE FROM otheritemspayments_breakdown WHERE payment_id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Other Items Payment Record have been deleted";

        // Step 1: Backup orphaned balances related to the student
        $sql = "CREATE TABLE IF NOT EXISTS orphaned_balances_backup AS
                SELECT *
                FROM feebalances
                WHERE studentadmno = :studentadmno
                AND NOT EXISTS (
                    SELECT 1 
                    FROM feepayments 
                    WHERE feepayments.academicyear = LEFT(feebalances.feebalancecode, 4)
                      AND feepayments.studentadmno = feebalances.studentadmno
                )";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $searchadmno, PDO::PARAM_STR);
        $query->execute();

        // Step 2: Delete orphaned balances for the specific student
        $sql = "DELETE FROM feebalances
                WHERE studentadmno = :studentadmno
                AND NOT EXISTS (
                    SELECT 1 
                    FROM feepayments 
                    WHERE feepayments.academicyear = LEFT(feebalances.feebalancecode, 4)
                      AND feepayments.studentadmno = feebalances.studentadmno
                )";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $searchadmno, PDO::PARAM_STR);
        $query->execute();

      

    } catch (PDOException $e) {
        $mess = "Error: " . $e->getMessage();
        error_log($e->getMessage()); // Log the error for debugging
    }
}


    // Search by admission number
    if (isset($_POST['search_submit'])) {
        $searchadmno = $_POST['searchbyadmno'] ?? '';
    }

  // Post a fee payment
if (isset($_POST['receivepay_submit'])) {
    try {
        $dbh->beginTransaction(); // Start transaction

        // Main payment data
        $studentadmno = $_POST['studentadmno'];
        $receiptno = $_POST['receiptno'];
        $cash = $_POST['cash'];
        $bank = $_POST['bank'];
        $reference = $_POST['reference'];
        $bankpaymentdate = $_POST['bankpaymentdate'];
        $paymentdate = $_POST['paymentdate'];
        $details = $_POST['details'];
        $academicyear = $_POST['academicyear'];
        $cashier = $_POST['username'];
        $receiptcode = $studentadmno . $academicyear;

        // 1. Insert main fee payment record
        $sql = "INSERT INTO feepayments 
            (studentadmno, receiptno, cash, bank, reference, bankpaymentdate, paymentdate, details, academicyear, cashier, receiptcode)
            VALUES 
            (:studentadmno, :receiptno, :cash, :bank, :reference, :bankpaymentdate, :paymentdate, :details, :academicyear, :cashier, :receiptcode)";

        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $query->bindParam(':receiptno', $receiptno, PDO::PARAM_STR);
        $query->bindParam(':cash', $cash, PDO::PARAM_STR);
        $query->bindParam(':bank', $bank, PDO::PARAM_STR);
        $query->bindParam(':reference', $reference, PDO::PARAM_STR);
        $query->bindParam(':bankpaymentdate', $bankpaymentdate, PDO::PARAM_STR);
        $query->bindParam(':paymentdate', $paymentdate, PDO::PARAM_STR);
        $query->bindParam(':details', $details, PDO::PARAM_STR);
        $query->bindParam(':academicyear', $academicyear, PDO::PARAM_INT);
        $query->bindParam(':cashier', $cashier, PDO::PARAM_STR);
        $query->bindParam(':receiptcode', $receiptcode, PDO::PARAM_STR);
        $query->execute();

        $payment_id = $dbh->lastInsertId(); // Get the inserted payment ID

        // 2. Save votehead allocations if they exist
        if (isset($_POST['votehead_amounts']) && is_array($_POST['votehead_amounts'])) {
            $voteheadSql = "INSERT INTO feepayment_voteheads 
                (payment_id, votehead_id, amount)
                VALUES 
                (:payment_id, :votehead_id, :amount)";
            
            $voteheadStmt = $dbh->prepare($voteheadSql);
            
            foreach ($_POST['votehead_amounts'] as $votehead_id => $amount) {
                if ($amount > 0) { // Only insert if amount is positive
                    $voteheadStmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
                    $voteheadStmt->bindParam(':votehead_id', $votehead_id, PDO::PARAM_INT);
                    $voteheadStmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                    $voteheadStmt->execute();
                }
            }
        }

        $dbh->commit(); // Commit transaction if all queries succeeded

        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Payment Record ADDED successfully with votehead breakdown.";

        // Fetch student name for receipt
        $sql = "SELECT studentname FROM studentdetails WHERE studentadmno = :studentadmno";
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_OBJ);
        if ($result) {
            $viewstudentname = $result->studentname;
        }

        $academicyear = date("Y");
        $searchadmno = $studentadmno;

    } catch (PDOException $e) {
        $dbh->rollBack(); // Roll back transaction on error
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Error processing payment: " . $e->getMessage();
        error_log("Payment Error: " . $e->getMessage());
    }
}
      // Update a fee payment
  if(isset($_POST['updatefeepayment'])) {
    try {
      $id=$_POST['id'];
      $studentadmno=$_POST['studentadmno'];
      $receiptno=$_POST['receiptno'];
      $cash=$_POST['cash'];
      $bank=$_POST['bank'];
      $payer=$_POST['payer'];
      $bankpaymentdate=$_POST['bankpaymentdate'];
      $paymentdate=$_POST['paymentdate'];
      $details=$_POST['details'];
      $academicyear=$_POST['academicyear'];
      $cashier=$_POST['cashier'];
      $reference=$_POST['reference'];
      $receiptcode=$studentadmno.$academicyear;
      $votehead_amounts = $_POST['votehead_amounts'];

      // Begin transaction
      $dbh->beginTransaction();
      
      // Update main payment record
      $dbh->query("UPDATE feepayments SET studentadmno='$studentadmno',receiptno='$receiptno',cash='$cash',bank='$bank',payer='$payer',bankpaymentdate='$bankpaymentdate',paymentdate='$paymentdate',details='$details',academicyear='$academicyear',cashier='$cashier',reference='$reference',receiptcode='$receiptcode' WHERE id=$id") or die($dbh->error);
      
      // Delete existing votehead allocations
      $dbh->query("DELETE FROM feepayment_voteheads WHERE payment_id=$id");
      
      // Insert new votehead allocations
      foreach($votehead_amounts as $votehead_id => $amount) {
        if($amount > 0) {
          $dbh->query("INSERT INTO feepayment_voteheads (payment_id, votehead_id, amount) VALUES ($id, $votehead_id, $amount)");
        }
      }
      
      $dbh->commit();
      
      $_SESSION['messagestate'] = 'added';
      $_SESSION['mess'] = "Payment Record UPDATED successfully.";
      $searchadmno = $studentadmno;
    }
    catch (PDOException $e) {
      $dbh->rollBack();
      echo $row->sql."<br>".$e->getMessage();
    }
  }

// Update or Insert RegFee payment with admission date and update student status
if (isset($_POST['editregfee_submit'])) {
    try {
        $studentadmno = $_POST['studentadmno'];
        $cash = $_POST['cash'];
        $bank = $_POST['bank'];
        $username = $_POST['username'];

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction(); // Start transaction

        // First fetch student details including admission date
        $studentSql = "SELECT studentname, admdate FROM studentdetails WHERE studentadmno = :studentadmno LIMIT 1";
        $studentQuery = $dbh->prepare($studentSql);
        $studentQuery->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $studentQuery->execute();
        $studentData = $studentQuery->fetch(PDO::FETCH_OBJ);

        if (!$studentData) {
            throw new Exception("Student not found with admission number: $studentadmno");
        }

        $admdate = $studentData->admdate;
        $viewstudentname = $studentData->studentname;

        // Check if a payment record exists for this student
        $checkSql = "SELECT id FROM regfeepayments WHERE studentadmno = :studentadmno LIMIT 1";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $checkQuery->execute();
        $existingRecord = $checkQuery->fetch(PDO::FETCH_OBJ);

        if ($existingRecord) {
            // Update existing record
            $sql = "UPDATE regfeepayments SET 
                    amount = :amount,
                    bank = :bank,
                    username = :username,
                    admdate = :admdate,
                    paymentdate = NOW()
                    WHERE studentadmno = :studentadmno";
            
            $message = "UPDATED";
        } else {
            // Insert new record
            $sql = "INSERT INTO regfeepayments 
                    (studentadmno, amount, bank, username, admdate, paymentdate)
                    VALUES 
                    (:studentadmno, :amount, :bank, :username, :admdate, NOW())";
            
            $message = "ADDED";
        }

        // Process payment record
        $query = $dbh->prepare($sql);
        $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $query->bindParam(':amount', $cash, PDO::PARAM_STR);
        $query->bindParam(':bank', $bank, PDO::PARAM_STR);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':admdate', $admdate, PDO::PARAM_STR);
        $query->execute();

        // Update student's regfee status to 'Paid'
        $updateStudentSql = "UPDATE studentdetails SET regfee = 'Paid' WHERE studentadmno = :studentadmno";
        $updateStudentQuery = $dbh->prepare($updateStudentSql);
        $updateStudentQuery->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
        $updateStudentQuery->execute();

        $dbh->commit(); // Commit transaction if all queries succeed

        $searchadmno = $studentadmno;
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "Registration Fee Payment $message successfully for $viewstudentname ($studentadmno). Status updated to Paid.";

    } catch (Exception $e) {
        $dbh->rollBack(); // Rollback transaction if any error occurs
        $mess = "Error: " . $e->getMessage();
        error_log($e->getMessage());
        $_SESSION['messagestate'] = 'error';
        $_SESSION['mess'] = "Error processing payment: " . $e->getMessage();
    }
}
// Receive other Items payment
if (isset($_POST['receiveotherpay_submit'])) {
  try {
      $dbh->beginTransaction(); // Start transaction

      // Collect values from form
      $studentadmno = $_POST['studentadmno'];
      $receiptno = $_POST['receiptno'];
      $totalamount = $_POST['totalotherpayamount']; // Updated to reflect the new total amount field
      $bank = $_POST['bank'];
      $reference = $_POST['reference'] ?? null; // Optional
      $bankpaymentdate = $_POST['bankpaymentdate'];
      $paymentdate = $_POST['paymentdate'];
      $academicyear = $_POST['academicyear'];
      $details = $_POST['details'];
      $username = $_POST['username'];

      // 1. Insert main other items payment record
      $sql = "INSERT INTO otheritemspayments 
              (studentadmno, receiptno, amount, paymentmethod, reference, bankpaymentdate, 
               entrydate, financialyear, details, username)
              VALUES 
              (:studentadmno, :receiptno, :amount, :paymentmethod, :reference, :bankpaymentdate, 
               :entrydate, :academicyear, :details, :username)";
      
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
      $stmt->bindParam(':receiptno', $receiptno, PDO::PARAM_STR);
      $stmt->bindParam(':amount', $totalamount, PDO::PARAM_STR); // Use total amount here
      $stmt->bindParam(':paymentmethod', $bank, PDO::PARAM_STR);
      $stmt->bindParam(':reference', $reference, PDO::PARAM_STR);
      $stmt->bindParam(':bankpaymentdate', $bankpaymentdate, PDO::PARAM_STR);
      $stmt->bindParam(':entrydate', $paymentdate, PDO::PARAM_STR);
      $stmt->bindParam(':academicyear', $academicyear, PDO::PARAM_STR);
      $stmt->bindParam(':details', $details, PDO::PARAM_STR);
      $stmt->bindParam(':username', $username, PDO::PARAM_STR);
      $stmt->execute();

      $payment_id = $dbh->lastInsertId(); // Get the inserted payment ID

      // 2. Save other items breakdown allocations if they exist
      if (isset($_POST['itemizedotherpay']) && is_array($_POST['itemizedotherpay'])) { // Updated to match the new field name
          $breakdownSql = "INSERT INTO otheritemspayments_breakdown 
              (payment_id, item_id, amount)
              VALUES 
              (:payment_id, :item_id, :amount)";
          
          $breakdownStmt = $dbh->prepare($breakdownSql);
          
          foreach ($_POST['itemizedotherpay'] as $item_id => $item_amount) { // Updated to match the new field name
              $item_amount = floatval($item_amount);
              if ($item_amount > 0) { // Only insert if amount is positive
                  $breakdownStmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
                  $breakdownStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                  $breakdownStmt->bindParam(':amount', $item_amount, PDO::PARAM_STR);
                  $breakdownStmt->execute();
              }
          }
      }

      $dbh->commit(); // Commit transaction if all queries succeeded

      $_SESSION['messagestate'] = 'added';
      $_SESSION['mess'] = "✅ Other Payment record posted successfully for AdmNo: <strong>$studentadmno</strong>";

      // Fetch student name for receipt
      $sql = "SELECT studentname FROM studentdetails WHERE studentadmno = :studentadmno";
      $query = $dbh->prepare($sql);
      $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
      $query->execute();

      $result = $query->fetch(PDO::FETCH_OBJ);
      if ($result) {
          $viewstudentname = $result->studentname;
      }

      $searchadmno = $studentadmno;

  } catch (PDOException $e) {
      $dbh->rollBack(); // Roll back transaction on error
      error_log("Other Items Payment Error: " . $e->getMessage());
      $_SESSION['messagestate'] = 'error';
      $_SESSION['mess'] = "❌ Failed to post payment: " . $e->getMessage();
  }
}


?>


<!DOCTYPE html>
<html>
   <head>
      <title>Fee Payment</title>
      <!-- Core CSS - Include with every page -->
      <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
      <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
      <link href="assets/css/style.css" rel="stylesheet" />
      <link href="assets/css/main-style.css" rel="stylesheet" />
      <!-- Page-Level CSS -->
      <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
      <script>
         function opentab(evt, tabName) {
         // Declare all variables
         var i, tabcontent, tablinks;
         
         // Get all elements with class="tabcontent" and hide them
         tabcontent = document.getElementsByClassName("tabcontent");
         for (i = 0; i < tabcontent.length; i++) {
         tabcontent[i].style.display = "none";
         }
         
         // Get all elements with class="tablinks" and remove the class "active"
         tablinks = document.getElementsByClassName("tablinks");
         for (i = 0; i < tablinks.length; i++) {
         tablinks[i].className = tablinks[i].className.replace(" active", "");
         }
         
         // Show the current tab, and add an "active" class to the button that opened the tab
         document.getElementById(tabName).style.display = "block";
         evt.currentTarget.className += " active";
         }
      </script>
<style>
  /* Student Info Panel */
  .student-info-panel {
      background: linear-gradient(145deg, #ffffff, #f9fafb);
      border-radius: 14px;
      padding: 20px;
      margin-bottom: 24px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      border-left: 5px solid var(--primary);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .student-info-panel:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
  }

  .student-info-panel h5 {
      color: var(--dark);
      margin-bottom: 14px;
      font-weight: 700;
      font-size: 18px;
  }

 .student-details {
    display: flex;
    gap: 16px;
    height: 40px;        /* scroll if content overflows */
    align-content: flex-start; /* keep items aligned at the top */
    border-radius: 10px;
}


  .student-detail-item {
      display: flex;
      align-items: center;
      background: rgba(248, 249, 252, 0.9);
      padding: 10px 14px;
      border-radius: 10px;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
      font-size: 15px;
      transition: background 0.3s ease, transform 0.2s ease;
  }

  .student-detail-item:hover {
      background: #eef2f7;
      transform: translateY(-2px);
  }

  .student-detail-item i {
      margin-right: 10px;
      color: var(--primary);
      font-size: 16px;
  }

  /* Tab Styling */
  .tab-container {
      margin-bottom: 10px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .tab-nav {
      display: flex;
      border-bottom: 2px solid var(--primary);
      margin-bottom: -1px;
      gap: 6px;
  }

  .tablinks {
      background: rgba(152, 165, 178, 0.15);
      border: none;
      padding: 10px 14px;
      cursor: pointer;
      font-weight: 600;
      border-radius: 10px 10px 0 0;
      transition: all 0.3s ease;
      color: #334155;
      position: relative;
  }

  .tablinks:hover {
      background: rgba(152, 165, 178, 0.25);
      color: var(--primary);
      transform: translateY(-2px);
  }

  .tablinks.active {
      background: #9ccd8bff;
      border: 1px solid var(--primary);
      border-bottom: none;
      color: var(--primary);
      z-index: 2;
  }

  .tabcontent {
      border: 1px solid var(--primary);
      border-top: none;
      padding: 22px;
      background: #fff;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
      animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
  }

  /* Table Styling */
  .table-responsive {
      overflow-x: auto;
  }

  .table th {
      background: #f8f9fa;
      font-weight: 600;
      font-size: 14px;
  }

  .table td {
      font-size: 14px;
      vertical-align: middle;
  }

  /* Missing Students Alert */
  .missing-students {
      background: #f9fafb;
      padding: 16px;
      border-radius: 10px;
      margin-top: 22px;
      border-left: 4px solid #facc15;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  }

  /* Form Elements */
  .form-control {
      max-width: 320px;
      display: inline-block;
      border-radius: 8px;
      padding: 8px 12px;
      border: 1px solid #d1d5db;
      transition: border 0.3s ease, box-shadow 0.3s ease;
  }

  .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
  }

  /* Responsive Adjustments */
  @media (max-width: 768px) {
      .tab-nav {
          flex-wrap: wrap;
      }

      .tablinks {
          margin-bottom: 5px;
      }

      .student-details {
          flex-direction: column;
      }
  }

    .btn-modern {
        --btn-color: #0d6efd; /* Primary color */
        --hover-color: #0b5ed7;
        --text-color: #fff;

        position: relative;
        padding: 12px 28px;
        border-radius: 14px;
        border: none;
        font-weight: 600;
        font-size: 16px;
        color: var(--text-color);
        background: linear-gradient(
            135deg,
            var(--btn-color),
            color-mix(in srgb, var(--btn-color) 25%, black)
        );
        backdrop-filter: blur(6px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        z-index: 1;
        cursor: pointer;
    }

    /* Gradient hover effect layer */
    .btn-modern::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(
            135deg,
            var(--hover-color),
            color-mix(in srgb, var(--hover-color) 25%, black)
        );
        opacity: 0;
        transition: opacity 0.35s ease;
        z-index: -1;
        border-radius: inherit;
    }

    /* Shine effect */
    .btn-modern::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.25) 0%, transparent 60%);
        transform: scale(0);
        transition: transform 0.5s ease;
        border-radius: inherit;
        z-index: 0;
    }

    .btn-modern:hover::before {
        opacity: 1;
    }

    .btn-modern:hover::after {
        transform: scale(1);
    }

    .btn-modern:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.2);
    }

    .btn-modern:active {
        transform: translateY(0) scale(0.98);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    .btn-modern i {
        margin-left: 6px;
        transition: transform 0.35s ease;
    }

    .btn-modern:hover i {
        transform: translateX(3px);
    }
</style>


   </head>
   <body>
      <!--  wrapper -->
      <div id="wrapper">
      <!-- navbar top -->
      <?php include_once('includes/header.php');?>
      <!-- end navbar top -->
      <!-- navbar side -->
      <?php include_once('includes/sidebar.php');?>
      <!-- end navbar side -->
      <!--  page-wrapper --> 
      <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <br>
                <div class="header-container bg-gradient-primary rounded-3">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="header-title" style="flex: 1; min-width: 300px;">
                            <h1 class="text-white mb-3" style="font-weight: 700; text-shadow: 1px 1px 3px rgba(0,0,0,0.3);">
                                <i class="fa fa-credit-card me-2"></i> Manage Fee Payments
                            </h1>
                        
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="text-white fw-bold me-2" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                                    View for (WHOLE School):
                                </span>
                                
                                <a href="viewall-feepayments.php" class="btn btn-modern btn-payments" 
                                  style="--btn-color: #4e73df; --hover-color: #2e59d9;">
                                    <i class='fa fa-file-invoice-dollar me-2'></i> All FeesPayments Transactions
                                </a>
                              
                                <a href="viewall-feepaymentsvoteheads.php" class="btn btn-modern btn-payments" 
                                  style="--btn-color: #1cc88a; --hover-color: #17a673;">
                                    <i class='fa fa-tags me-2'></i> All PerVoteHeads Payments
                                </a>
                                
                                <a href="viewall-otheritemspayments.php" class="btn btn-modern btn-payments" 
                                  style="--btn-color: #f6c23e; --hover-color: #dda20a;">
                                    <i class='fa fa-receipt me-2'></i> All OtherItemsPayments Transactions
                                </a>
                                
                                <a href="feebalanceperclass.php" class="btn btn-modern btn-payments" 
                                  style="--btn-color: #e74a3b; --hover-color: #be2617;">
                                    <i class="bi bi-bar-chart-line me-2"></i> Balance Report/Grade/School
                                </a>
                               
                            </div>
                        </div>
                    
                        <div class="ms-2" style="min-width: 200px;">
                            <?php include_once('updatemessagepopup.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<br>
<div class="card shadow-sm" >
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <?php include('studentsearchpopup.php'); ?>                
                <?php include('viewstudentdetails.php'); ?>                        
                <?php include('newfeepaymentpopup.php'); ?> 
                <?php include('newotherpaymentpopup.php'); ?>      
                <?php include('editregfeepopup.php'); ?>          
 <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                       
                        <div class="card-body">
                            <?php include('studentsearchpopup.php'); ?>                
                            <?php include('viewstudentdetails.php'); ?>                        
                            <?php include('newfeepaymentpopup.php'); ?> 
                            <?php include('newotherpaymentpopup.php'); ?>      
                            <?php include('editregfeepopup.php'); ?>          

                            <!-- Student Information Panel -->
                            <div class="student-info-panel">
                                <div class="student-details">
                                    <a href="#studentsearch" data-toggle="modal" class="btn btn-primary">
                                    <i class="fa fa-search me-2"></i> Search Learner
                                </a>
                                    <?php if (!empty($rlt->studentadmno)): ?>
                                        <div class="student-detail-item">
                                            <i class="fa fa-id-card"></i>
                                            <span><strong>Adm No:</strong> <?php echo htmlspecialchars($rlt->studentadmno); ?></span>
                                        </div>
                                        <div class="student-detail-item">
                                            <i class="fa fa-user"></i>
                                            <span><strong>Name:</strong> <?php echo htmlspecialchars($rlt->studentname); ?></span>
                                        </div>
                                        <?php
                                        $admno = $rlt->studentadmno;
                                        if (!empty($admno)) {
                                            $sql = "SELECT gradefullname FROM classentries WHERE studentadmno = ? ORDER BY id DESC LIMIT 1";
                                            $query = $dbh->prepare($sql);
                                            $query->execute([$admno]);
                                            $latestGrade = $query->fetch(PDO::FETCH_OBJ);
                                            
                                            if ($latestGrade) {
                                                echo '<div class="student-detail-item">
                                                    <i class="fa fa-graduation-cap"></i>
                                                    <span><strong>Latest Grade:</strong> ' . htmlspecialchars($latestGrade->gradefullname) . '</span>
                                                </div>';
                                            }                                     
                                        }
                                        ?>
                                        <div class="student-detail-item">
                                            <i class="fa fa-money"></i>
                                            <span><strong>Registration Fee:</strong> 
                                                <?php
                                                if (!empty($admno)) {
                                                    $sql = "SELECT amount FROM regfeepayments WHERE studentadmno = ? ORDER BY id DESC LIMIT 1";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute([$admno]);
                                                    $regfeeamount = $query->fetch(PDO::FETCH_OBJ);
                                                    
                                                    if ($regfeeamount) {
                                                        echo ($rlt->regfee == 'Unpaid' ? '<span class="status-unpaid">❌ Unpaid</span>' : '<span class="status-paid">✅ Paid</span>');
                                                        echo ' <span>(' . htmlspecialchars($regfeeamount->amount) . ')</span>';
                                                    }                                     
                                                }
                                                ?>
                                            </span>
                                        </div>
                                          <div class="student-detail-item">
                                            <a href="#myModal" data-toggle="modal" class="btn btn-info">
                                                <i class="fa fa-user-circle me-2"></i> View Learner's Details 
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #ae0b68ff; font-style: italic;">
                                            Please search for a student to view details
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                              <div class="d-flex flex-wrap gap-2 mb-3"> 

                                <?php if (has_permission($accounttype, 'edit_regfeepayment')): ?>
                                    <a href="#editregfee" data-toggle="modal" class="btn btn-success">
                                        <i class="fa fa-money me-2"></i> Update RegFee 
                                    </a>
                                <?php endif; ?>

                                <?php if (has_permission($accounttype, 'receive_payment')): ?>
                                    <a href="#receivefeepayment" data-toggle="modal" class="btn btn-primary">
                                        <i class="fas fa-hand-holding-usd me-2"></i> Receive Single FEE-Payment
                                    </a>                      
                                    
                                    <a href="newfeepaymentbulk.php" class="btn btn-primary">
                                        <i class="fas fa-users me-2"></i> Receive Bulk FEE-Payment
                                    </a>

                                    <a href="#receiveotherpayment" data-toggle="modal" class="btn btn-primary">
                                        <i class="fa fa-credit-card me-2"></i> Receive Other Payments 
                                    </a>
                                <?php endif; ?>
                                
                                <a href="feebalanceperclasssms.php" class="btn btn-primary">
                                    <i class="fas fa-sms me-2"></i> SMS Sending
                                </a>
                            </div>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
       <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">                            
                            <div class="tab-container mt-3">
                                <div class="tab-nav">
                                    <button class="tablinks" onclick="opentab(event,'feepayments')" id="defaultOpen">
                                      <i class="fa-solid fa-file-invoice-dollar me-2" style="color: #0d6efd;"></i> FeePayments Transactions
                                    </button>
                                    <button class="tablinks" onclick="opentab(event,'feepaymentsvoteheads')">
                                      <i class="fa-solid fa-diagram-project me-2" style="color: #6f42c1;"></i> Payments Per Voteheads
                                    </button>
                                    <button class="tablinks" onclick="opentab(event,'feebalance')">
                                      <i class="fa-solid fa-scale-balanced me-2" style="color:rgb(85, 8, 227);"></i> Fee Balances
                                    </button>
                                    <button class="tablinks" onclick="opentab(event,'feestructure')">
                                      <i class="fa-solid fa-layer-group me-2" style="color: #fd7e14;"></i> Fee Structure
                                    </button>
                                    <button class="tablinks" onclick="opentab(event,'transportstructure')">
                                      <i class="fa-solid fa-van-shuttle me-2" style="color: #6f42c1;"></i> Transport Structure
                                    </button>
                                    <button class="tablinks" onclick="opentab(event,'otheritemspayments')">
                                      <i class="fas fa-credit-card me-2" style="color: rgb(50, 205, 50);"></i> OtherItems Transactions
                                    </button>
                                    <button class="tablinks" onclick="opentab(event,'otheritemspaymentsbreakdown')">
                                      <i class="fa-solid fa-list-check me-2" style="color: rgb(255, 140, 0);"></i> OtherItems Breakdown
                                    </button>



                                <!-- Script -->
                                <script>
                                  function opentab(evt, tabName) {
                                    var i, tabcontent, tablinks;

                                    tabcontent = document.getElementsByClassName("tabcontent");
                                    for (i = 0; i < tabcontent.length; i++) {
                                      tabcontent[i].style.display = "none";
                                    }

                                    tablinks = document.getElementsByClassName("tablinks");
                                    for (i = 0; i < tablinks.length; i++) {
                                      tablinks[i].classList.remove("active");
                                    }

                                    document.getElementById(tabName).style.display = "block";
                                    evt.currentTarget.classList.add("active");
                                  }

                                  document.addEventListener("DOMContentLoaded", function () {
                                    document.getElementById("defaultOpen").click();
                                  });
                                </script>

                                </div>
                                
                               <!-- Tab content -->
                   <!-- First tab -->
<div id="feebalance" class="tabcontent">
    <h4 style="margin: 0; color: #495057; font-weight: 600;">
        <i class="fas fa-balance-scale me-2" style="color:rgb(85, 8, 227);"></i> Fee Balances per Grade<br><br>
    </h4>
    <div class="table-responsive" style="overflow-x: auto; width: 100%">
        <?php
        // Initialize variables
        $yearlybal = 0;
        $arr = 0;

        if (!empty($searchadmno)) {
            try {
                // Include the SQL query file
                require_once 'updatefeebalancesql.php';

                // Prepare the SQL query with votehead-based calculations
                $sql = getStudentFeeQuery();
                $query = $dbh->prepare($sql);
                $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                $query->execute();

                $results = $query->fetchAll(PDO::FETCH_OBJ);
                $cnt = 1;

                if ($query->rowCount() > 0) {
        ?>
        <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Grade</th>
                    <th>Bal BF</th>
                    <th>YearlyFee</th>
                    <th>TotalPay</th>
                    <th>Instalments</th>
                    <th>First-T Bal</th>
                    <th>Second-T Bal</th>
                    <th>Third-T Bal</th>
                    <th>Yearly Bal</th>
                    <th>PrintOut/Year</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($results as $row) {
                    // Calculate term totals with transport fees and subtract transport waivers per term
                    $firstterm_total = ($row->firsttermfee + $row->firsttermtransport) - $row->firsttermtransportwaiver;
                    $secondterm_total = ($row->secondtermfee + $row->secondtermtransport) - $row->secondtermtransportwaiver;
                    $thirdterm_total = ($row->thirdtermfee + $row->thirdtermtransport) - $row->thirdtermtransportwaiver;
                    
                    // Initialize payment application
                    $adjusted = $yearlybal + $arr;
                    $remaining_payment = $row->totpayperyear;
                    
                    // 1. Apply payment to first term (including others fee)
                    $firstterm_net = max(0, $firstterm_total - $row->firsttermfeewaiver + $adjusted);
                    $firstterm_payment = min($remaining_payment, $firstterm_net);
                    $firstterm_balance = $firstterm_net - $firstterm_payment;
                    $remaining_payment -= $firstterm_payment;
                    
                    // 2. Apply remaining payment to second term
                    $secondterm_net = max(0, $secondterm_total - $row->secondtermfeewaiver);
                    $secondterm_payment = min($remaining_payment, $secondterm_net);
                    $secondterm_balance = $secondterm_net - $secondterm_payment;
                    $remaining_payment -= $secondterm_payment;
                    
                    // 3. Apply remaining payment to third term
                    $thirdterm_net = max(0, $thirdterm_total - $row->thirdtermfeewaiver);
                    $thirdterm_payment = min($remaining_payment, $thirdterm_net);
                    $thirdterm_balance = $thirdterm_net - $thirdterm_payment;
                    
                    // Apply limits (shouldn't be negative or exceed term totals)
                    $firsttermbalcal = max(0, min($firstterm_balance, ($firstterm_total + $adjusted)));
                    $secondtermbalcal = max(0, min($secondterm_balance, $secondterm_total));
                    $thirdtermbalcal = max(0, min($thirdterm_balance, $thirdterm_total));
                    
                    // Yearly balance
                    $yearly_total = $row->totcalfee - $row->totpayperyear +  $adjusted;
                    ?>
                    <tr>
                        <td><?php echo htmlentities($cnt); ?></td>
                        <td><?php echo htmlentities($row->gradefullname); ?></td>
                        <td><?php echo number_format($adjusted); ?></td>
                        <td><?php echo number_format($row->totcalfee); ?></td>
                        <td><?php echo number_format($row->totpayperyear); ?></td>
                        <td><?php echo number_format($row->instalments); ?></td>
                        <td><?php echo number_format($firsttermbalcal); ?></td>
                        <td><?php echo number_format($secondtermbalcal); ?></td>
                        <td><?php echo number_format($thirdtermbalcal); ?></td>
                        <td><?php echo number_format($yearly_total); ?></td>
                        <td>
                            <?php $feebalancecode = $row->gradefullname . $row->studentadmno; ?>
                            <a href="reportfeebalanceperchildpergrade.php?feebalancecode=<?php echo htmlentities($feebalancecode); ?>" 
                              class="print-btn">
                              <i class="bi bi-printer"></i> PrintView
                            </a>
                        </td>
                    </tr>
                    <?php 
                    // Update variables for next iteration
                    $arrears = $yearlybal + $arr;
                    $balperyear = $yearly_total;
                    $yearlybal = $yearly_total;
                    
                    // Store other variables for database update
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
                    
                    // Update or insert into feebalances table
                    $check_sql = "SELECT feebalancecode FROM feebalances WHERE feebalancecode = :feebalancecode";
                    $check_query = $dbh->prepare($check_sql);
                    $check_query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
                    $check_query->execute();
                    
                    if ($check_query->rowCount() > 0) {
                        // UPDATE with all parameters
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
                        
                        $update_query = $dbh->prepare($update_sql);
                        $update_query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
                        $update_query->bindParam(':childstatus', $childstatus, PDO::PARAM_STR);
                        $update_query->bindParam(':arrears', $arrears, PDO::PARAM_STR);
                        $update_query->bindParam(':firsttermbalcal', $firsttermbalcal, PDO::PARAM_STR);
                        $update_query->bindParam(':secondtermbalcal', $secondtermbalcal, PDO::PARAM_STR);
                        $update_query->bindParam(':thirdtermbalcal', $thirdtermbalcal, PDO::PARAM_STR);
                        $update_query->bindParam(':balperyear', $balperyear, PDO::PARAM_STR);
                        $update_query->bindParam(':feetreatment', $feetreatment, PDO::PARAM_STR);
                        $update_query->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
                        $update_query->bindParam(':studentname', $studentname, PDO::PARAM_STR);
                        $update_query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
                        $update_query->bindParam(':totcalfee', $totcalfee, PDO::PARAM_STR);
                        $update_query->bindParam(':totpayperyear', $totpayperyear, PDO::PARAM_STR);
                        $update_query->bindParam(':firsttermfeecal', $firsttermfeecal, PDO::PARAM_STR);
                        $update_query->bindParam(':secondtermfeecal', $secondtermfeecal, PDO::PARAM_STR);
                        $update_query->bindParam(':thirdtermfeecal', $thirdtermfeecal, PDO::PARAM_STR);
                        $update_query->bindParam(':othersfeecal', $othersfeecal, PDO::PARAM_STR);
                        $update_query->bindParam(':boarding', $boarding, PDO::PARAM_STR);
                        $update_query->execute();
                    } else {
                        // INSERT with all parameters
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
                        
                        $insert_query = $dbh->prepare($insert_sql);
                        $insert_query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
                        $insert_query->bindParam(':arrears', $arrears, PDO::PARAM_STR);
                        $insert_query->bindParam(':firsttermbalcal', $firsttermbalcal, PDO::PARAM_STR);
                        $insert_query->bindParam(':secondtermbalcal', $secondtermbalcal, PDO::PARAM_STR);
                        $insert_query->bindParam(':thirdtermbalcal', $thirdtermbalcal, PDO::PARAM_STR);
                        $insert_query->bindParam(':balperyear', $balperyear, PDO::PARAM_STR);
                        $insert_query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
                        $insert_query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
                        $insert_query->bindParam(':feetreatment', $feetreatment, PDO::PARAM_STR);
                        $insert_query->bindParam(':childtreatment', $childtreatment, PDO::PARAM_STR);
                        $insert_query->bindParam(':studentname', $studentname, PDO::PARAM_STR);
                        $insert_query->bindParam(':totcalfee', $totcalfee, PDO::PARAM_STR);
                        $insert_query->bindParam(':totpayperyear', $totpayperyear, PDO::PARAM_STR);
                        $insert_query->bindParam(':firsttermfeecal', $firsttermfeecal, PDO::PARAM_STR);
                        $insert_query->bindParam(':secondtermfeecal', $secondtermfeecal, PDO::PARAM_STR);
                        $insert_query->bindParam(':thirdtermfeecal', $thirdtermfeecal, PDO::PARAM_STR);
                        $insert_query->bindParam(':othersfeecal', $othersfeecal, PDO::PARAM_STR);
                        $insert_query->bindParam(':boarding', $boarding, PDO::PARAM_STR);
                        $insert_query->bindParam(':childstatus', $childstatus, PDO::PARAM_STR);
                        $insert_query->execute();
                    }
                    $cnt++;
                }
                ?>
            </tbody>
        </table>
        <?php
// ✅ Check latest yearly balance and show confetti if cleared
if (isset($yearly_total) && $yearly_total == 0): ?>
    <div id="confetti"></div>
    <div id="feeSuccess" class="alert alert-success text-center" style="font-size: 18px; font-weight: bold; margin-top:20px;">
        🎉 Congratulations! Fee Balance is fully cleared!
    </div>

    <script>
      // Generate confetti
      for(let i=0; i<80; i++) {
        let confetti = document.createElement("div");
        confetti.className = "confetti";
        confetti.style.left = Math.random() * 100 + "vw";
        confetti.style.backgroundColor = `hsl(${Math.random()*360}, 70%, 60%)`;
        confetti.style.animationDuration = (Math.random() * 2 + 3) + "s";
        document.getElementById("confetti").appendChild(confetti);
      }

      // Auto-hide after 5 seconds
      setTimeout(() => {
        document.getElementById("feeSuccess").classList.add("fade-out");
        document.getElementById("confetti").classList.add("fade-out");
        setTimeout(() => {
          document.getElementById("feeSuccess").remove();
          document.getElementById("confetti").remove();
        }, 1000); // remove after fade animation
      }, 5000);
    </script>

    <style>
      #confetti { 
        position: fixed; 
        top:0; left:0; 
        width:100%; height:100%; 
        pointer-events:none; 
        overflow:hidden; 
        z-index: 9999;
      }
      .confetti {
        position:absolute; 
        width:10px; height:10px; 
        animation: fall linear forwards;
      }
      @keyframes fall {
        to { 
          transform: translateY(100vh) rotate(720deg); 
          opacity:0; 
        }
      }
      /* Fade-out animation */
      .fade-out {
        animation: fadeOut 1s forwards;
      }
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
    </style>
<?php endif; ?>

        <?php
            } else {
                echo '<div class="alert alert-warning">No records found. Check if some payments have been made.</div>';
            }
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo '<div class="alert alert-warning">Please enter a valid admission number.</div>';
    }
    ?>
    </div>
</div>
<!-- End of First tab -->
<!-- Second Tab -->
 <div id="feepayments" class="tabcontent">
  <h4 style="margin: 0; color: #495057; font-weight: 600;">
   <i class="fa-solid fa-file-invoice-dollar me-2" style="color: #0d6efd;"></i> Learner's Fee Payment History<br><br> 
  </h4>
          <div class="table-responsive" style="overflow-x: auto; width: 100%">
            <?php
            if (!empty($searchadmno)) {
                try {
                    $sql = "SELECT * FROM feepayments WHERE studentadmno = :searchadmno ORDER BY entrydate DESC";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;

                    if ($query->rowCount() > 0): ?>
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example2">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Receipt No</th>
                          <th>Amount</th>
                          <th>Bank</th>
                          <th>Bank Date</th>
                          <th>Receipt Date</th>
                          <th>Reference</th>
                          <th>Details</th>
                          <th>Year</th>
                          <th>EntryDate</th>
                          <th>Type</th>
                          <th>Cashier</th>
                          <th>Print</th>
                          <th>Breakdown</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                          <td><?= $cnt; ?></td>
                          <td><?= htmlentities($row->receiptno); ?></td>
                          <td><?= number_format($row->cash); ?></td>
                          <td><?= htmlentities($row->bank); ?></td>
                          <td><?= htmlentities($row->bankpaymentdate); ?></td>
                          <td><?= htmlentities($row->paymentdate); ?></td>
                          <td><?= htmlentities($row->reference); ?></td>
                          <td><?= htmlentities($row->details); ?></td>
                          <td><?= htmlentities($row->academicyear); ?></td>
                          <td><?= htmlentities($row->entrydate); ?></td>
                           <td><?= htmlentities($row->paymenttype); ?></td>
                          <td><?= htmlentities($row->cashier); ?></td>
                        
                          <td>
                            <a href="#" 
                              onclick="printfeepaymentReceipt(<?= htmlentities($row->id); ?>)"
                              class="print-btn">
                              <i class="fa fa-print"></i> Print Receipt
                            </a>
                            <?php if ($row->printed): ?>
                                <br><small class="text-success" id="print-status-<?= $row->id ?>">
                                Printed on <?= date('d/m/Y H:i', strtotime($row->print_date)) ?>
                                </small>
                            <?php else: ?>
                                <br><small class="text-muted" id="print-status-<?= $row->id ?>" style="display: none;"></small>
                            <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1"
                                    onclick="loadVoteheadDetails(<?= $row->id; ?>)">
                                    <i class="fa fa-eye"></i> View/Voteheads
                                </button>
                            </td>

                          <td>
                            <div class="btn-group">
                              <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                <?php if (has_permission($accounttype, 'edit_payment')): ?>
                              <li>
                                  <a href="<?php 
                                      if ($row->paymenttype == 'Bulk') {
                                          echo 'edit-feepaymentbulk.php?editreceiptno=' . urlencode($row->receiptno);
                                      } else {
                                          echo 'edit-feepayments.php?editid=' . urlencode($row->id);
                                      }
                                  ?>">
                                    <i class="fa fa-pencil"></i> Update Payment
                                  </a>
                                </li>

                                <li class="divider"></li>
                                <li>
                                  <a href="manage-feepayments.php?delete=<?= htmlentities($row->id); ?>"
                                     onclick="return confirm('Are you sure you want to delete this record?');">
                                    <i class="fa fa-trash-o"></i> Delete
                                  </a>
                                </li>
                                <?php endif; ?>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <?php $cnt++; endforeach; ?>
                      </tbody>
                    </table>
                    <!-- Votehead Breakdown Modal -->
                    <div class="modal fade" id="voteheadModal" tabindex="-1" role="dialog" aria-labelledby="voteheadModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <div class="modal-header">
                        <h3 class="modal-title text-primary font-weight-bold" id="voteheadModalLabel">
                            <i class="fa fa-layer-group"></i> Votehead Breakdown
                        </h3>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                        </div>
                        <div class="modal-body" id="voteheadDetails">
                            <!-- AJAX Content Goes Here -->
                        </div>
                        </div>
                    </div>
                    </div>

                    <?php
                    else:
                         echo '<div class="alert alert-warning">No records found for the provided admission number.</div>';
                    endif;
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Database Error: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="alert alert-warning">Please enter a valid admission number to view fee payments.</div>';
            }
            ?>
          </div>
        </div>

<!-- End Second Tab -->

<!-- Third Tab -->
<div id="feepaymentsvoteheads" class="tabcontent">
          <h4 style="margin: 0; color: #495057; font-weight: 600;">
            <i class="fa-solid fa-diagram-project me-2" style="color: #6f42c1;"></i> Learner's Fee Payment History Per Votehead. <br><br>
            <span style="color: #e65100; font-style: italic;">Note: Balance is per Votehead/Year, If transport, the charges is picked from transportstructure if the child is allocated a route for that year</span>
          </h4>

          <div class="table-responsive" style="overflow-x: auto; width: 100%; border-radius: 8px;">
            <?php
            if (!empty($searchadmno)) {
                try {
                    // First get all active voteheads (excluding Transport)
                    $voteheadsSql = "SELECT id, votehead FROM voteheads WHERE isfeepayment = 'Yes' AND votehead != 'Transport'";
                    $voteheadsQuery = $dbh->prepare($voteheadsSql);
                    $voteheadsQuery->execute();
                    $allVoteheads = $voteheadsQuery->fetchAll(PDO::FETCH_OBJ);
                    
                    // Get the regular voteheads with payments (excluding Transport)
                    $sql = "SELECT 
                        fpv.votehead_id,
                        vh.votehead,
                        fp.studentadmno,
                        SUM(fpv.amount) AS total_paid,
                        fp.academicyear AS payment_academicyear,
                        ce.feegradename,
                        cd.academicyear AS class_academicyear,
                        fvc.firstterm,
                        fvc.secondterm,
                        fvc.thirdterm,
                        fvc.total AS total_fee_structure,
                        LEAST(SUM(fpv.amount), fvc.firstterm) AS paid_firstterm,
                        LEAST(GREATEST(SUM(fpv.amount) - fvc.firstterm, 0), fvc.secondterm) AS paid_secondterm,
                        LEAST(GREATEST(SUM(fpv.amount) - fvc.firstterm - fvc.secondterm, 0), fvc.thirdterm) AS paid_thirdterm,
                        fvc.firstterm - LEAST(SUM(fpv.amount), fvc.firstterm) AS balance_firstterm,
                        fvc.secondterm - LEAST(GREATEST(SUM(fpv.amount) - fvc.firstterm, 0), fvc.secondterm) AS balance_secondterm,
                        fvc.thirdterm - LEAST(GREATEST(SUM(fpv.amount) - fvc.firstterm - fvc.secondterm, 0), fvc.thirdterm) AS balance_thirdterm
                    FROM 
                        feepayment_voteheads fpv
                    INNER JOIN 
                        feepayments fp ON fpv.payment_id = fp.id
                    INNER JOIN 
                        classentries ce ON fp.studentadmno = ce.studentadmno
                    INNER JOIN 
                        classdetails cd ON ce.gradefullname = cd.gradefullname
                    LEFT JOIN 
                        feestructurevoteheadcharges fvc ON ce.feegradename = fvc.feestructurename AND fpv.votehead_id = fvc.votehead_id
                    LEFT JOIN
                        voteheads vh ON fpv.votehead_id = vh.id
                    WHERE 
                        cd.academicyear = fp.academicyear
                        AND fp.studentadmno = :searchadmno
                        AND vh.votehead != 'Transport'
                    GROUP BY 
                        fpv.votehead_id, vh.votehead, fp.studentadmno, fp.academicyear, ce.feegradename, cd.academicyear,
                        fvc.firstterm, fvc.secondterm, fvc.thirdterm, fvc.total
                    ORDER BY 
                        fp.academicyear DESC, fpv.votehead_id ASC";
                    
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $query->execute();
                    $paidResults = $query->fetchAll(PDO::FETCH_OBJ);
                    
                    // Get current class details for the student
                    $classDetailsSql = "SELECT ce.feegradename, cd.academicyear 
                                       FROM classentries ce 
                                       INNER JOIN classdetails cd ON ce.gradefullname = cd.gradefullname 
                                       WHERE ce.studentadmno = :searchadmno 
                                       ORDER BY cd.academicyear DESC LIMIT 1";
                    $classDetailsQuery = $dbh->prepare($classDetailsSql);
                    $classDetailsQuery->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $classDetailsQuery->execute();
                    $classDetails = $classDetailsQuery->fetch(PDO::FETCH_OBJ);
                    
                    // Create array of voteheads that have payments
                    $paidVoteheadIds = array_map(function($item) {
                        return $item->votehead_id;
                    }, $paidResults);
                    
                    // For each votehead that doesn't have payments, create a record with zero values
                    $unpaidResults = [];
                    if ($classDetails) {
                        foreach ($allVoteheads as $votehead) {
                            if (!in_array($votehead->id, $paidVoteheadIds)) {
                                // Get fee structure charges for this votehead if they exist
                                $feeStructureSql = "SELECT firstterm, secondterm, thirdterm, total 
                                                    FROM feestructurevoteheadcharges 
                                                    WHERE feestructurename = :feegradename 
                                                    AND votehead_id = :votehead_id";
                                $feeStructureQuery = $dbh->prepare($feeStructureSql);
                                $feeStructureQuery->bindParam(':feegradename', $classDetails->feegradename, PDO::PARAM_STR);
                                $feeStructureQuery->bindParam(':votehead_id', $votehead->id, PDO::PARAM_INT);
                                $feeStructureQuery->execute();
                                $feeStructure = $feeStructureQuery->fetch(PDO::FETCH_OBJ);
                                
                                $unpaidRecord = new stdClass();
                                $unpaidRecord->votehead_id = $votehead->id;
                                $unpaidRecord->votehead = $votehead->votehead;
                                $unpaidRecord->studentadmno = $searchadmno;
                                $unpaidRecord->total_paid = 0;
                                $unpaidRecord->payment_academicyear = $classDetails->academicyear;
                                $unpaidRecord->feegradename = $classDetails->feegradename;
                                $unpaidRecord->class_academicyear = $classDetails->academicyear;
                                
                                if ($feeStructure) {
                                    $unpaidRecord->firstterm = $feeStructure->firstterm;
                                    $unpaidRecord->secondterm = $feeStructure->secondterm;
                                    $unpaidRecord->thirdterm = $feeStructure->thirdterm;
                                    $unpaidRecord->total_fee_structure = $feeStructure->total;
                                } else {
                                    $unpaidRecord->firstterm = 0;
                                    $unpaidRecord->secondterm = 0;
                                    $unpaidRecord->thirdterm = 0;
                                    $unpaidRecord->total_fee_structure = 0;
                                }
                                
                                $unpaidRecord->paid_firstterm = 0;
                                $unpaidRecord->paid_secondterm = 0;
                                $unpaidRecord->paid_thirdterm = 0;
                                $unpaidRecord->balance_firstterm = $unpaidRecord->firstterm;
                                $unpaidRecord->balance_secondterm = $unpaidRecord->secondterm;
                                $unpaidRecord->balance_thirdterm = $unpaidRecord->thirdterm;
                                
                                $unpaidResults[] = $unpaidRecord;
                            }
                        }
                    }
                    
                    // Combine paid and unpaid results
                    $results = array_merge($paidResults, $unpaidResults);
                    
                    // Get Transport data from transportentries table
                    $transportSql = "SELECT 
                        'Transport' AS votehead,
                        te.studentadmno,
                        te.firsttermtransport AS firstterm,
                        te.secondtermtransport AS secondterm,
                        te.thirdtermtransport AS thirdterm,
                        (te.firsttermtransport + te.secondtermtransport + te.thirdtermtransport) AS total_fee_structure,
                        SUBSTRING(te.stagefullname, 1, 4) AS payment_academicyear,
                        te.classentryfullname AS feegradename
                    FROM 
                        transportentries te
                    WHERE 
                        te.studentadmno = :searchadmno
                    ORDER BY 
                        SUBSTRING(te.stagefullname, 1, 4) DESC";
                    
                    $transportQuery = $dbh->prepare($transportSql);
                    $transportQuery->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $transportQuery->execute();
                    $transportResults = $transportQuery->fetchAll(PDO::FETCH_OBJ);
                    
                    // Get Transport payments with academic year matching
                    $transportPaymentsSql = "SELECT 
                        fp.academicyear,
                        SUM(fpv.amount) AS total_paid,
                        LEAST(SUM(fpv.amount), te.firsttermtransport) AS paid_firstterm,
                        LEAST(GREATEST(SUM(fpv.amount) - te.firsttermtransport, 0), te.secondtermtransport) AS paid_secondterm,
                        LEAST(GREATEST(SUM(fpv.amount) - te.firsttermtransport - te.secondtermtransport, 0), te.thirdtermtransport) AS paid_thirdterm
                    FROM 
                        feepayment_voteheads fpv
                    INNER JOIN 
                        feepayments fp ON fpv.payment_id = fp.id
                    INNER JOIN
                        voteheads vh ON fpv.votehead_id = vh.id
                    INNER JOIN
                        transportentries te ON fp.studentadmno = te.studentadmno AND fp.academicyear = SUBSTRING(te.stagefullname, 1, 4)
                    WHERE 
                        fp.studentadmno = :searchadmno
                        AND vh.votehead = 'Transport'
                    GROUP BY 
                        fp.academicyear, te.firsttermtransport, te.secondtermtransport, te.thirdtermtransport
                    ORDER BY 
                        fp.academicyear DESC";
                    
                    $transportPaymentsQuery = $dbh->prepare($transportPaymentsSql);
                    $transportPaymentsQuery->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $transportPaymentsQuery->execute();
                    $transportPayments = $transportPaymentsQuery->fetchAll(PDO::FETCH_OBJ);
                    
                    // Combine all results
                    $allResults = $results;
                    if (!empty($transportResults)) {
                        foreach ($transportResults as $transportRow) {
                            $transportData = clone $transportRow;
                            $transportData->votehead = 'Transport';
                            
                            // Find matching payments for this academic year
                            $matchingPayments = array_filter($transportPayments, function($payment) use ($transportData) {
                                return $payment->academicyear == $transportData->payment_academicyear;
                            });
                            
                            if (!empty($matchingPayments)) {
                                $payment = reset($matchingPayments);
                                $transportData->total_paid = $payment->total_paid;
                                $transportData->paid_firstterm = $payment->paid_firstterm;
                                $transportData->paid_secondterm = $payment->paid_secondterm;
                                $transportData->paid_thirdterm = $payment->paid_thirdterm;
                            } else {
                                $transportData->total_paid = 0;
                                $transportData->paid_firstterm = 0;
                                $transportData->paid_secondterm = 0;
                                $transportData->paid_thirdterm = 0;
                            }
                            
                            $transportData->balance_firstterm = $transportData->firstterm - $transportData->paid_firstterm;
                            $transportData->balance_secondterm = $transportData->secondterm - $transportData->paid_secondterm;
                            $transportData->balance_thirdterm = $transportData->thirdterm - $transportData->paid_thirdterm;
                            
                            $allResults[] = $transportData;
                        }
                    }
                    
                    // Sort all results by academic year descending
                    usort($allResults, function($a, $b) {
                        return strcmp($b->payment_academicyear, $a->payment_academicyear);
                    });
                    
                    $cnt = 1;
                    if (count($allResults) > 0): ?>
                      <table class="table table-striped table-bordered table-hover" id="dataTables-example4" style="font-size: 14px; border-collapse: separate; border-spacing: 0;">
                      <thead>
                        <tr style="background-color: #f1f3f9;">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            
                            <!-- 1st Term Group -->
                            <th colspan="3" style="text-align: center; background-color: #e3f2fd; color: #0d47a1; border-left: 2px solid #fff;">1st Term</th>
                            
                            <!-- 2nd Term Group -->
                            <th colspan="3" style="text-align: center; background-color: #e8f5e9; color: #1b5e20; border-left: 2px solid #fff;">2nd Term</th>
                            
                            <!-- 3rd Term Group -->
                            <th colspan="3" style="text-align: center; background-color: #fff3e0; color: #e65100; border-left: 2px solid #fff;">3rd Term</th>
                            
                            <th></th>
                            <th></th>
                            
                            <th></th>
                        </tr>

                          <tr style="background-color: #f1f3f9;">
                            <th>#</th>
                            <th>Votehead</th>
                            <th>Paid(SUM)</th>
                            <th>AcademicYear</th>
                            
                            <!-- 1st Term Subheaders -->
                            <th style="background-color: #e3f2fd; border-left: 2px solid #fff;">Charge</th>
                            <th style="background-color: #e3f2fd;">Paid</th>
                            <th style="background-color: #e3f2fd;">Balance</th>
                            
                            <!-- 2nd Term Subheaders -->
                            <th style="background-color: #e8f5e9; border-left: 2px solid #fff;">Charge</th>
                            <th style="background-color: #e8f5e9;">Paid</th>
                            <th style="background-color: #e8f5e9;">Balance</th>
                            
                            <!-- 3rd Term Subheaders -->
                            <th style="background-color: #fff3e0; border-left: 2px solid #fff;">Charge</th>
                            <th style="background-color: #fff3e0;">Paid</th>
                            <th style="background-color: #fff3e0;">Balance</th>
                            
                            <th>Total Fee</th>
                            <th>Overall Balance</th>                            
                            <th>FullGradeName</th>

                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($allResults as $row): 
                              $firstTermFullyPaid = ($row->balance_firstterm <= 0);
                              $secondTermFullyPaid = ($row->balance_secondterm <= 0);
                              $thirdTermFullyPaid = ($row->balance_thirdterm <= 0);
                          ?>
                          <tr>
                        <td><?= $cnt; ?></td>
                        <td style="font-weight: 600; color: #495057;"><?= htmlentities($row->votehead); ?></td>
                        <td style="font-weight: 600; background-color: #f5f5f5; color: #0d6efd;"><?= number_format($row->total_paid, 0); ?></td>
                        <td><?= htmlentities($row->payment_academicyear); ?></td>

                        <!-- 1st Term Data -->
                        <td style="background-color: #e3f2fd; border-left: 2px solid #fff;"><?= number_format($row->firstterm, 0); ?></td>
                        <td style="background-color: #e3f2fd; <?= $firstTermFullyPaid ? 'color: #2e7d32;' : '' ?>"><?= number_format($row->paid_firstterm, 0); ?></td>
                        <td style="background-color: #e3f2fd; <?= !$firstTermFullyPaid ? 'color: #c62828; font-weight: bold;' : 'color: #2e7d32;' ?>">
                            <?= number_format($row->balance_firstterm, 0); ?>
                        </td>

                        <!-- 2nd Term Data -->
                        <td style="background-color: #e8f5e9; border-left: 2px solid #fff;"><?= number_format($row->secondterm, 0); ?></td>
                        <td style="background-color: #e8f5e9; <?= $secondTermFullyPaid ? 'color: #2e7d32;' : '' ?>"><?= number_format($row->paid_secondterm, 0); ?></td>
                        <td style="background-color: #e8f5e9; <?= !$secondTermFullyPaid ? 'color: #c62828; font-weight: bold;' : 'color: #2e7d32;' ?>">
                            <?= number_format($row->balance_secondterm, 0); ?>
                        </td>

                        <!-- 3rd Term Data -->
                        <td style="background-color: #fff3e0; border-left: 2px solid #fff;"><?= number_format($row->thirdterm, 0); ?></td>
                        <td style="background-color: #fff3e0; <?= $thirdTermFullyPaid ? 'color: #2e7d32;' : '' ?>"><?= number_format($row->paid_thirdterm, 0); ?></td>
                        <td style="background-color: #fff3e0; <?= !$thirdTermFullyPaid ? 'color: #c62828; font-weight: bold;' : 'color: #2e7d32;' ?>">
                            <?= number_format($row->balance_thirdterm, 0); ?>
                        </td>

                        <td style="font-weight: 600; background-color: #f5f5f5; color: #495057;"><?= number_format($row->total_fee_structure, 0); ?></td>

                        <!-- New Overall Balance Column -->
                        <?php
                        $balance = $row->total_fee_structure - $row->total_paid;
                        $color = $balance > 0 ? '#d84315' : ($balance < 0 ? 'green' : 'black');
                        ?>
                        <td style="font-weight: bold; background-color: #ffe0b2; color: <?= $color; ?>;">
                            <?= number_format($balance, 0); ?>
                        </td>

                        <td><?= htmlentities($row->feegradename); ?></td>
                        </tr>

                          <?php $cnt++; endforeach; ?>
                        </tbody>
                        
                      </table>
                    <?php
                    else:
                        echo '<div class="alert alert-danger" style="border-radius: 6px; border-left: 4px solid #dc3545; padding: 15px;">
                                <i class="fas fa-exclamation-circle me-2"></i> No records found for the provided admission number.
                              </div>';
                    endif;
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger" style="border-radius: 6px; border-left: 4px solid #dc3545; padding: 15px;">
                            <i class="fas fa-database me-2"></i> Database Error: ' . $e->getMessage() . '
                          </div>';
                }
            } else {
                echo '<div class="alert alert-warning" style="border-radius: 6px; border-left: 4px solid #ffc107; padding: 15px;">
                        <i class="fas fa-info-circle me-2"></i> Please enter a valid admission number to view fee payments.
                      </div>';
            }
            ?>
          </div>
        </div>


<style>
  /* Additional styling for better visual hierarchy */
  #feepaymentsvoteheads .table {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #dee2e6;
  }
  
  #feepaymentsvoteheads .table th {
    vertical-align: middle;
    text-align: center;
    white-space: nowrap;
    padding: 12px 8px;
    border-bottom: 2px solid #dee2e6;
  }
  
  #feepaymentsvoteheads .table td {
    vertical-align: middle;
    text-align: right;
    padding: 10px 8px;
    border-bottom: 1px solid #dee2e6;
  }
  
  #feepaymentsvoteheads .table td:nth-child(2) {
    text-align: left;
  }
  
  #feepaymentsvoteheads .table tr:hover {
    background-color: rgba(0, 0, 0, 0.02) !important;
  }
  
  #feepaymentsvoteheads .table tfoot td {
    font-weight: bold;
    border-top: 2px solid #dee2e6;
  }
  
  /* Responsive adjustments */
  @media (max-width: 1200px) {
    #feepaymentsvoteheads .table-responsive {
      border: 1px solid #ddd;
    }
  }
</style>
<!-- End Third Tab -->


<!-- Fourth tab -->
<div id="feestructure" class="tabcontent">
  <h4 style="margin: 0; color: #495057; font-weight: 600;">
    <i class="fa-solid fa-layer-group me-2" style="color: #fd7e14;"></i> Learner's Fee Structure<br><br>   
  </h4>
  <div class="table-responsive" style="overflow-x: auto; width: 100%">
    <?php
    $yearlybal = 0;
    $arr = 0;
    $cnt = 1;

    if (!empty($searchadmno)) {
        try {
            $sql = "SELECT 
                  sd.studentadmno, 
                  sd.studentname,  
                  ce.gradefullname,
                  ce.childstatus, 
                  fs.EntryTerm, 
                  fs.boarding, 
                  ce.childTreatment, 
                  ce.feeTreatment, 
                  ce.feetreatmentrate, 
                  ce.childtreatmentrate,
                  
                  -- Term fees with treatment logic
                  (
                      SELECT COALESCE(SUM(
                          CASE 
                              WHEN vh.isfeetreatmentcalculations = 'Yes' 
                              THEN fsv.firstterm * ce.feetreatmentrate * ce.childtreatmentrate
                              ELSE fsv.firstterm
                          END
                      ), 0)
                      FROM feestructurevoteheadcharges fsv
                      JOIN voteheads vh ON fsv.votehead_id = vh.id
                      WHERE fsv.feestructurename = fs.feeStructureName
                  ) AS firsttermfee,
                  
                  (
                      SELECT COALESCE(SUM(
                          CASE 
                              WHEN vh.isfeetreatmentcalculations = 'Yes' 
                              THEN fsv.secondterm * ce.feetreatmentrate * ce.childtreatmentrate
                              ELSE fsv.secondterm
                          END
                      ), 0)
                      FROM feestructurevoteheadcharges fsv
                      JOIN voteheads vh ON fsv.votehead_id = vh.id
                      WHERE fsv.feestructurename = fs.feeStructureName
                  ) AS secondtermfee,
                  
                  (
                      SELECT COALESCE(SUM(
                          CASE 
                              WHEN vh.isfeetreatmentcalculations = 'Yes' 
                              THEN fsv.thirdterm * ce.feetreatmentrate * ce.childtreatmentrate
                              ELSE fsv.thirdterm
                          END
                      ), 0)
                      FROM feestructurevoteheadcharges fsv
                      JOIN voteheads vh ON fsv.votehead_id = vh.id
                      WHERE fsv.feestructurename = fs.feeStructureName
                  ) AS thirdtermfee,
                  
                  -- Other fees
                  fs.othersfee,
                  
                  -- Transport (fees and waivers)
                  COALESCE(te.firsttermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS firsttermtransport,
                  COALESCE(te.secondtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS secondtermtransport,
                  COALESCE(te.thirdtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS thirdtermtransport,

                  COALESCE(te.firsttermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS firsttermtransportwaiver,
                  COALESCE(te.secondtermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS secondtermtransportwaiver,
                  COALESCE(te.thirdtermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate AS thirdtermtransportwaiver,

                  -- Payments and waivers
                  COALESCE(SUM(fp.Cash), 0) AS totpayperyear,
                  COALESCE(ce.firsttermfeewaiver, 0) AS firsttermfeewaiver,
                  COALESCE(ce.secondtermfeewaiver, 0) AS secondtermfeewaiver,
                  COALESCE(ce.thirdtermfeewaiver, 0) AS thirdtermfeewaiver,

                  -- Total calculation (fees + transport - waivers)
                  ROUND(((
                      (
                          SELECT COALESCE(SUM(
                              CASE 
                                  WHEN vh.isfeetreatmentcalculations = 'Yes' 
                                  THEN fsv.firstterm * ce.feetreatmentrate * ce.childtreatmentrate
                                  ELSE fsv.firstterm
                              END
                          ), 0)
                          FROM feestructurevoteheadcharges fsv
                          JOIN voteheads vh ON fsv.votehead_id = vh.id
                          WHERE fsv.feestructurename = fs.feeStructureName
                      ) +
                      (
                          SELECT COALESCE(SUM(
                              CASE 
                                  WHEN vh.isfeetreatmentcalculations = 'Yes' 
                                  THEN fsv.secondterm * ce.feetreatmentrate * ce.childtreatmentrate
                                  ELSE fsv.secondterm
                              END
                          ), 0)
                          FROM feestructurevoteheadcharges fsv
                          JOIN voteheads vh ON fsv.votehead_id = vh.id
                          WHERE fsv.feestructurename = fs.feeStructureName
                      ) +
                      (
                          SELECT COALESCE(SUM(
                              CASE 
                                  WHEN vh.isfeetreatmentcalculations = 'Yes' 
                                  THEN fsv.thirdterm * ce.feetreatmentrate * ce.childtreatmentrate
                                  ELSE fsv.thirdterm
                              END
                          ), 0)
                          FROM feestructurevoteheadcharges fsv
                          JOIN voteheads vh ON fsv.votehead_id = vh.id
                          WHERE fsv.feestructurename = fs.feeStructureName
                      )
                      + fs.othersfee
                      + (COALESCE(te.firsttermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
                      + (COALESCE(te.secondtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
                      + (COALESCE(te.thirdtermtransport, 0) * ce.feetreatmentrate * ce.childtreatmentrate)
                      - (COALESCE(ce.firsttermfeewaiver, 0) + COALESCE(ce.secondtermfeewaiver, 0) + COALESCE(ce.thirdtermfeewaiver, 0))
                      - (
                          COALESCE(te.firsttermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate +
                          COALESCE(te.secondtermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate +
                          COALESCE(te.thirdtermtransportwaiver, 0) * ce.feetreatmentrate * ce.childtreatmentrate
                      )
                  ))) AS totcalfee,

                  COUNT(fp.Cash) AS instalments

              FROM studentdetails sd
              INNER JOIN classentries ce ON sd.studentadmno = ce.studentAdmNo
              INNER JOIN classdetails cd ON cd.gradefullName = ce.gradefullname
              INNER JOIN feestructure fs ON fs.feeStructureName = ce.feegradename
              LEFT JOIN feepayments fp ON fp.studentadmno = sd.studentadmno AND fp.academicyear = cd.academicyear
              LEFT JOIN transportentries te ON te.classentryfullname = ce.classentryfullname

              WHERE sd.studentadmno = :searchadmno

              GROUP BY 
                  sd.studentadmno, sd.studentname, ce.gradefullname, 
                  fs.EntryTerm, fs.boarding, ce.childTreatment, ce.feeTreatment, 
                  fs.firsttermfee, fs.secondtermfee, fs.thirdtermfee, fs.othersfee, 
                  ce.feetreatmentrate, ce.childtreatmentrate,
                  ce.firsttermfeewaiver, ce.secondtermfeewaiver, ce.thirdtermfeewaiver,
                  te.firsttermtransport, te.secondtermtransport, te.thirdtermtransport,
                  te.firsttermtransportwaiver, te.secondtermtransportwaiver, te.thirdtermtransportwaiver
              ORDER BY ce.gradefullname ASC";

            $query = $dbh->prepare($sql);
            $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

           echo '<table class="table table-striped table-bordered table-hover" id="dataTables-example3">
        <thead>
            <tr>
                <th>#</th>
                <th>Grade</th>
                <th>Entry Term</th>
                <th>Board?</th>
                <th>Child Treat</th>
                <th>Status</th>
                <th>Fee Treat</th>
                <th>1st Term</th>
                <th>2nd Term</th>
                <th>3rd Term</th>
                <th>Total Fee</th>
            </tr>
        </thead>
        <tbody>';

$cnt = 1;
foreach ($results as $row) {
    $first = number_format($row->firsttermfee - $row->firsttermfeewaiver);
    $second = number_format($row->secondtermfee - $row->secondtermfeewaiver);
    $third = number_format($row->thirdtermfee - $row->thirdtermfeewaiver);

    $style = 'style="color:rgb(241, 22, 25); font-style:italic; "'; // muted gray and italic style

    $first .= $row->firsttermfeewaiver > 0 ? ' (<span ' . $style . '>Incl ' . number_format($row->firsttermfeewaiver) . ' waiver</span>)' : '';
    $second .= $row->secondtermfeewaiver > 0 ? ' (<span ' . $style . '>Incl ' . number_format($row->secondtermfeewaiver) . ' waiver</span>)' : '';
    $third .= $row->thirdtermfeewaiver > 0 ? ' (<span ' . $style . '>Incl ' . number_format($row->thirdtermfeewaiver) . ' waiver</span>)' : '';


    $totalFee = 
        $row->firsttermfee + $row->secondtermfee + $row->thirdtermfee
        - ($row->firsttermfeewaiver + $row->secondtermfeewaiver + $row->thirdtermfeewaiver);

    echo '<tr>
            <td>' . $cnt++ . '</td>
            <td>' . htmlentities($row->gradefullname) . '</td>
            <td>' . htmlentities($row->EntryTerm) . '</td>
            <td>' . htmlentities($row->boarding) . '</td>
            <td>' . htmlentities($row->childTreatment) . '</td>
            <td>' . htmlentities($row->childstatus) . '</td>
            <td>' . htmlentities($row->feeTreatment) . '</td>
            <td>' . $first . '</td>
            <td>' . $second . '</td>
            <td>' . $third . '</td>
            <td>' . number_format($totalFee) . '</td>
        </tr>';
}

echo '</tbody></table>';

        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<div class='alert alert-warning'>Please enter a valid admission number.</div>";
    }
    ?>
  </div>
</div>
<!-- End of Fourth tab -->

<!-- Fifth tab -->
<div id="transportstructure" class="tabcontent">
<h4 style="margin: 0; color: #495057; font-weight: 600;">
    <i class="fas fa-bus me-2" style="color: rgb(255, 99, 71);"></i> Learner's Transport Structure<br><br>
</h4>

          <div class="table-responsive" style="overflow-x: auto; width: 100%">
            <?php
            if (!empty($searchadmno)) {
                try {
                    $sql = "SELECT 
                            transportentries.id,
                            transportentries.studentadmno,
                            transportentries.stagefullname,
                            transportentries.childtreatment,
                            transportentries.childtreatmentrate,
                            transportentries.transporttreatment,
                            transportentries.firsttermtransport,
                            transportentries.secondtermtransport,
                            transportentries.thirdtermtransport,
                            transportentries.firsttermtransportwaiver,
                            transportentries.secondtermtransportwaiver,
                            transportentries.thirdtermtransportwaiver,
                            studentdetails.studentname,
                            transportstructure.academicyear
                        FROM 
                            transportentries 
                        INNER JOIN 
                            studentdetails ON transportentries.studentadmno = studentdetails.studentadmno
                        INNER JOIN 
                            transportstructure ON transportentries.stagefullname = transportstructure.stagefullname
                        WHERE 
                            transportentries.studentadmno = :searchadmno
                        ORDER BY 
                            transportstructure.academicyear DESC";

                    $query = $dbh->prepare($sql);
                    $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;

                    if ($query->rowCount() > 0): ?>
                      <table class="table table-striped table-bordered table-hover" id="dataTables-example5">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Academic Year</th>
                            <th>Stage Fullname</th>
                            <th>Child-Treatment</th>
                            <th>Transport-Treatment</th>
                            <th>1st Term</th>
                            <th>2nd Term</th>
                            <th>3rd Term</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                       <tbody>
  <?php foreach ($results as $row): 
      $noTransport = !$row->firsttermtransport && !$row->secondtermtransport && !$row->thirdtermtransport;

      // Calculate net transport fees after waiver per term
      $firstTermNet = $row->firsttermtransport - $row->firsttermtransportwaiver;
      $secondTermNet = $row->secondtermtransport - $row->secondtermtransportwaiver;
      $thirdTermNet = $row->thirdtermtransport - $row->thirdtermtransportwaiver;

      // Helper function to format term transport and waiver display
      function formatTransportWithWaiver($net, $waiver, $noTransport) {
          if ($noTransport) return "No Transport Assigned";
          $netFormatted = number_format($net);
          return $waiver > 0 
              ? "$netFormatted <small style='color:rgb(241, 22, 25); font-style:italic; '>(Incl $waiver waiver)</small>" 
              : $netFormatted;
      }
  ?>
  <tr>
    <td><?= $cnt++; ?></td>
    <td><?= htmlentities($row->academicyear); ?></td>
    <td><?= htmlentities($row->stagefullname); ?></td>
    <td><?= htmlentities($row->childtreatment); ?></td>
    <td><?= htmlentities($row->transporttreatment); ?></td>
    <td><?= formatTransportWithWaiver($firstTermNet, $row->firsttermtransportwaiver, $noTransport); ?></td>
    <td><?= formatTransportWithWaiver($secondTermNet, $row->secondtermtransportwaiver, $noTransport); ?></td>
    <td><?= formatTransportWithWaiver($thirdTermNet, $row->thirdtermtransportwaiver, $noTransport); ?></td>
     <td><?= htmlentities($firstTermNet + $secondTermNet + $thirdTermNet); ?></td>
  </tr>
  <?php endforeach; ?>
</tbody>

                      </table>
                    <?php else: ?>
                      <div class="alert alert-warning">No transport records found for this Learner.</div>
                    <?php endif;
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="alert alert-warning">Please enter a valid admission number to view transport structure.</div>';
            }
            ?>
          </div>
        </div>


<!-- End of Fifth tab -->
<!-- Sixth tab -->
<div id="otheritemspayments" class="tabcontent">
<h4 style="margin: 0; color: #495057; font-weight: 600;">
    <i class="fas fa-credit-card me-2" style="color: rgb(50, 205, 50);"></i> OtherItems Payments History<br><br>
</h4>

          <div class="table-responsive" style="overflow-x: auto; width: 100%">
            <?php
            if (!empty($searchadmno)) {
                try {
                    $sql = "SELECT * FROM otheritemspayments WHERE studentadmno = :searchadmno ORDER BY id DESC";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;

                    if ($query->rowCount() > 0): ?>
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example6">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Receipt No</th>
                          <th>Amount</th>
                          <th>Mode</th>
                          <th>Reference</th>
                          <th>Bank Date</th>
                          <th>Receipt Date</th>
                          <th>Year</th>
                          <th>Details</th>
                          <th>Entry Date</th>
                          <th>Cashier</th>
                          <th>Print</th>
                          <th>Breakdown</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                          <td><?= $cnt; ?></td>
                          <td><?= htmlentities($row->receiptno); ?></td>
                          <td><?= number_format($row->amount); ?></td>
                          <td><?= htmlentities($row->paymentmethod); ?></td>
                          <td><?= htmlentities($row->reference); ?></td>
                          <td><?= htmlentities($row->bankpaymentdate); ?></td>
                          <td><?= htmlentities($row->paymentdate); ?></td>
                          <td><?= htmlentities($row->financialyear); ?></td>
                          <td><?= htmlentities($row->details); ?></td>
                          <td><?= htmlentities($row->entrydate); ?></td>
                          <td><?= htmlentities($row->username); ?></td>                          
                          <td>
                            <a href="#" onclick="printotheritemspaymentReceipt(<?= htmlentities($row->id); ?>)">
                              <i class="fa fa-print"></i> Print Receipt
                            </a>
                            <?php if ($row->printed): ?>
                                <br><small class="text-success" id="print-status-<?= $row->id ?>">
                                Printed on <?= date('d/m/Y H:i', strtotime($row->print_date)) ?>
                                </small>
                            <?php else: ?>
                                <br><small class="text-muted" id="print-status-<?= $row->id ?>" style="display: none;"></small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1"
                                onclick="loadOtherItemsDetails(<?= $row->id; ?>)">
                                <i class="fa fa-eye"></i> View/Items
                            </button>
                          </td>
                          <td>
                            <div class="btn-group">
                              <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                <?php if (has_permission($accounttype, 'edit_payment')): ?>
                                
                                <li class="divider"></li>
                                <li>
                                  <a href="manage-feepayments.php?deleteother=<?= htmlentities($row->id); ?>"
                                     onclick="return confirm('Are you sure you want to delete this record?');">
                                    <i class="fa fa-trash-o"></i> Delete
                                  </a>
                                </li>
                                <?php endif; ?>
                              </ul>
                            </div>
                          </td>
                        </tr>
                        <?php $cnt++; endforeach; ?>
                      </tbody>
                    </table>
                    
                    <!-- Items Breakdown Modal -->
                    <div class="modal fade" id="itemsModal" tabindex="-1" role="dialog" aria-labelledby="itemsModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h3 class="modal-title text-primary font-weight-bold" id="itemsModalLabel">
                                    <i class="fa fa-list-alt"></i> Payment Items Breakdown
                                </h3>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body" id="itemsDetails">
                                    <!-- AJAX Content Goes Here -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    function loadOtherItemsDetails(payment_id) {
                        $.ajax({
                            url: 'fetch_otheritems_breakdown.php',
                            type: 'GET',
                            data: { payment_id: payment_id },
                            success: function(response) {
                                $('#itemsDetails').html(response);
                                $('#itemsModal').modal('show');
                            },
                            error: function(xhr, status, error) {
                                $('#itemsDetails').html('<div class="alert alert-danger">Error loading details: ' + error + '</div>');
                                $('#itemsModal').modal('show');
                            }
                        });
                    }
                    </script>
                    
                    <?php
                    else:
                        echo '<div class="alert alert-warning">No records found for the provided admission number.</div>';
                    endif;
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Database Error: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="alert alert-warning">Please enter a valid admission number to view other payments.</div>';
            }
            ?>
          </div>
        </div>
 
<!-- End Sixth tab -->

<!-- Seventh tab -->
<div id="otheritemspaymentsbreakdown" class="tabcontent">
<h4 style="margin: 0; color: #495057; font-weight: 600;">
    <i class="fas fa-piggy-bank me-2" style="color: rgb(255, 140, 0);"></i> OtherItems Payments Breakdown <br><br>
</h4>

         
 
          <div class="table-responsive" style="overflow-x: auto; width: 100%">
            <?php
              if (!empty($searchadmno)) {
                  try {
                      $sql = "SELECT 
                          b.`studentadmno`,
                          c.`studentname`,
                          b.`financialyear`,
                          a.`item_id`,
                          d.`otherpayitemname`,
                          SUM(a.`amount`) AS total_amount,
                          e.`gradefullname`
                      FROM 
                          `otheritemspayments_breakdown` a
                      INNER JOIN 
                          `otheritemspayments` b ON a.`payment_id` = b.`id`
                      INNER JOIN 
                          `studentdetails` c ON b.`studentadmno` = c.`studentadmno`
                      INNER JOIN 
                          `otherpayitems` d ON a.`item_id` = d.`id`
                      LEFT JOIN 
                          `classentries` e ON b.`studentadmno` = e.`studentadmno`
                          AND SUBSTRING(e.`gradefullname`, 1, 4) = b.`financialyear`
                      WHERE b.studentadmno = :searchadmno 
                      GROUP BY 
                          b.`studentadmno`, b.`financialyear`, a.`item_id`
                      ORDER BY 
                           b.`financialyear` desc, d.`otherpayitemname`";

                      $query = $dbh->prepare($sql);
                      $query->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                      $query->execute();
                      $results = $query->fetchAll(PDO::FETCH_OBJ);
                      $cnt = 1;

                      if ($query->rowCount() > 0): ?>
                       Note: <span style="color: red;">This the SUM payments per item per year for the Learner</span>
                      <table class="table table-striped table-bordered table-hover" id="dataTables-example7">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Year</th>
                                  <th>Grade</th>
                                  <th>Item</th>
                                  <th>SUM Paid</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php foreach ($results as $row): ?>
                              <tr>
                                  <td><?= $cnt; ?></td>
                                  <td><?= htmlentities($row->financialyear); ?></td>
                                  <td><?= htmlentities($row->gradefullname); ?></td>
                                  <td><?= htmlentities($row->otherpayitemname); ?></td>
                                  <td><?= number_format($row->total_amount, 2); ?></td>
                              </tr>
                              <?php $cnt++; endforeach; ?>
                          </tbody>
                      </table>
              <?php
                      else:
                          echo "<div class='alert alert-warning'>No payment records found for this admission number.</div>";
                      endif;
                  } catch (PDOException $e) {
                      echo "<div class='alert alert-danger'>Error fetching data: " . $e->getMessage() . "</div>";
                  }
              }
              ?>
               
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Seventh tab -->


</div>
</div>

      <!-- End Wrapper -->

<!-- Core Scripts - Include with every page -->
<script src="assets/plugins/jquery-1.10.2.js"></script>
<script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
<script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="assets/plugins/pace/pace.js"></script>
<script src="assets/scripts/siminta.js"></script>

<!-- DataTables Plugin Scripts -->
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

<!-- DataTable Initialization -->
<script>
  $(document).ready(function () {
    $('#dataTables-example1').dataTable();
    $('#dataTables-example2').dataTable();
    $('#dataTables-example3').dataTable();
    $('#dataTables-example4').dataTable();
    $('#dataTables-example5').dataTable();
    $('#dataTables-example6').dataTable();
    $('#dataTables-example7').dataTable();

  });
</script>

<!-- Prevent Form Resubmission on Page Reload -->
<script>
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>

<!-- Trigger Default Tab -->
<script>
  document.getElementById("defaultOpen").click();
</script>

<!-- Admission Number Availability Check -->
<script>
  function admnoAvailability() {
    $("#loaderIcon").show();
    $.ajax({
      url: "checkadmno.php",
      data: 'studentadmno=' + $("#studentadmno").val(),
      type: "POST",
      success: function (data) {
        $("#user-availability-status1").html(data);
        $("#loaderIcon").hide();
      },
      error: function () {
        $("#loaderIcon").hide();
      }
    });
  }
</script>

<!-- Print Fee Payment Receipt -->
<script>
  function printfeepaymentReceipt(paymentId) {
    var receiptWindow = window.open(
      'reportfeepaymentreceiptvoteheads.php?id=' + paymentId,
      'Receipt_' + paymentId,
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

  function printotheritemspaymentReceipt(paymentId) {
    var receiptWindow = window.open(
      'reportotheritemspaymentreceipt.php?id=' + paymentId,
      'Receipt_' + paymentId,
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

<!-- Auto-clear Zero in Number Inputs -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('input[type="number"]');
    inputs.forEach(function (input) {
      input.addEventListener('focus', function () {
        if (parseFloat(this.value) === 0) {
          this.value = '';
        }
      });

      input.addEventListener('blur', function () {
        if (this.value === '') {
          this.value = '0';
        }
      });
    });
  });
</script>

<script>
function loadVoteheadDetails(paymentId) {
  $.ajax({
    url: 'fetch_votehead_details.php',
    method: 'POST',
    data: { payment_id: paymentId },
    success: function(response) {
      $('#voteheadDetails').html(response);
      $('#voteheadModal').modal('show');
    },
    error: function() {
      $('#voteheadDetails').html('<p class="text-danger">Error loading data.</p>');
      $('#voteheadModal').modal('show');
    }
  });
}
</script>

<!-- Flash Message Auto-hide -->
<?php if ($messagestate == 'added' || $messagestate == 'deleted'): ?>
  <script type="text/javascript">
    function hideMsg() {
      document.getElementById("popup").style.visibility = "hidden";
    }
    document.getElementById("popup").style.visibility = "visible";
    window.setTimeout(hideMsg, 5000);
  </script>
<?php endif; ?>

</body>
</html>
<?php }?>