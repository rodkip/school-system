<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
    header('location:logout.php');
} else {
 $feebalancecode = $_GET['feebalancecode'];

$sql = "SELECT 
            fb.studentadmno,
            fb.gradefullname,
            ce.firsttermfeewaiver,
            ce.secondtermfeewaiver,
            ce.thirdtermfeewaiver
        FROM feebalances fb
        INNER JOIN classentries ce 
            ON ce.classentryfullname = fb.feebalancecode
        WHERE fb.feebalancecode = :feebalancecode";

$query = $dbh->prepare($sql);
$query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() > 0) {
    foreach ($results as $row) {
        $studentadmno = $row->studentadmno;
        $gradefullname = $row->gradefullname;
        $firsttermfeewaiver = $row->firsttermfeewaiver;
        $secondtermfeewaiver = $row->secondtermfeewaiver;
        $thirdtermfeewaiver = $row->thirdtermfeewaiver;
        $academicyearfortransport = substr($gradefullname, 0, 4);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Report: Per Child/Grade</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet" />

</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <?php      
                $searchquery="SELECT * from schooldetails";
                $qry = $dbh -> prepare($searchquery);
                $qry->execute();
                $row=$qry->fetchAll(PDO::FETCH_OBJ);
                $cnt=1;
                if($qry->rowCount() > 0) {
                    foreach($row as $rlt) {   
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
                <?php $cnt=$cnt+1;}} ?>
            </div>
          Per Child/Grade Payments(Fee & OtherItems)BreakDown
        </div>
       
       
<!-- Main Container -->
<div style="display: flex; flex-wrap: nowrap; gap: 2px; overflow-x: auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; margin: 2px auto; max-width: 100%; font-size: 12px;">

<?php
$searchadmno = $_POST['searchbyadmno'];
$searchquery = "SELECT * FROM studentdetails WHERE studentadmno = '$studentadmno'";
$qry = $dbh->prepare($searchquery);
$qry->execute();
$row = $qry->fetchAll(PDO::FETCH_OBJ);

if ($qry->rowCount() > 0) {
    foreach ($row as $rlt) {
?>

<!-- Student Details -->
<div style="flex: 1; min-width: 20px; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); padding: 1px; font-size: 12px;">

    <h3 style="color: #004B6E; border-bottom: 2px solid #004B6E; ">🎓 Student Details</h3>
    <div >
        <strong>Learner Name:</strong> <?php echo $rlt->studentname; ?>,
        <strong>AdmNo:</strong> <?php echo $rlt->studentadmno; ?> <br> 
        <strong>Grade:</strong> <?php echo htmlentities($gradefullname); ?>,   
        <?php  
    $transportquery = "SELECT transportentries.studentadmno, transportentries.childtreatment, transportentries.stagefullname, 
        transportstructure.academicyear, transportstructure.stagename 
        FROM transportentries 
        INNER JOIN transportstructure ON transportentries.stagefullname = transportstructure.stagefullname 
        WHERE transportentries.studentadmno = :studentadmno 
        AND transportstructure.academicyear = :academicyearfortransport 
        GROUP BY transportentries.studentadmno, transportentries.childtreatment, transportentries.stagefullname, 
        transportstructure.academicyear, transportstructure.stagename";

    $qry2 = $dbh->prepare($transportquery);
    $qry2->bindParam(':studentadmno', $studentadmno);
    $qry2->bindParam(':academicyearfortransport', $academicyearfortransport);
    $qry2->execute();
    $transportrows = $qry2->fetchAll(PDO::FETCH_OBJ);

    if ($qry2->rowCount() > 0) {
        foreach ($transportrows as $rt) {
?>
<strong>Transport Stage:</strong> <?php echo htmlentities($rt->stagefullname); ?><br>
<?php 
        } 
    } 
?>

    </div>
</div>

<!-- Fee Balance -->
<div style="flex: 1; min-width: 200px; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); padding: 1px; font-size: 12px;">

    <h3 style="color: #004B6E; border-bottom: 2px solid #004B6E; ">💰 Fee Balance Details</h3>
    <div >
        <?php
        $sql = "SELECT * FROM feebalances WHERE feebalancecode='$feebalancecode'";
        $query = $dbh->prepare($sql);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        if ($query->rowCount() > 0) {
            foreach ($results as $row) {
        ?>
        <strong>Sum Paid:</strong> <?php echo number_format($row->totalpaid); ?><br>
        <?php
        function getBalanceColor($amount) {
            if ($amount > 0) return 'red';
            if ($amount < 0) return 'green';
            return 'black';
        }
        ?>
        Balance:
    <strong>1stTerm:</strong> 
        <span style="color: <?php echo getBalanceColor($row->firsttermbal); ?>">
            <?php echo ($row->firsttermbal == 0) ? 'Cleared' : number_format($row->firsttermbal); ?>
        </span>,
        <strong>2ndTerm:</strong> 
        <span style="color: <?php echo getBalanceColor($row->secondtermbal); ?>">
            <?php echo ($row->secondtermbal == 0) ? 'Cleared' : number_format($row->secondtermbal); ?>
        </span>,
        <strong>3rdTerm:</strong> 
        <span style="color: <?php echo getBalanceColor($row->thirdtermbal); ?>">
            <?php echo ($row->thirdtermbal == 0) ? 'Cleared' : number_format($row->thirdtermbal); ?>
        </span>,
        <strong>Yearly:</strong> 
        <b>
            <span style="color: <?php echo getBalanceColor($row->yearlybal); ?>">
                <?php echo ($row->yearlybal == 0) ? 'Cleared' : number_format($row->yearlybal); ?>
            </span>
        </b><br>


        <?php } } ?>
    </div>
</div>


<?php } } ?>

</div>
<div class="section-title">Combined Fee & Transport Structure</div>
<div class="table-container">
    <table cellpadding="1" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align: left; white-space: nowrap;">Source</th>
            <th>ChildTreatment</th>
            <th>FeeTreatment</th>
            <th>Boarding</th>
            <th>Bal B/F</th>
            <th>1st Term</th>
            <th>2nd Term</th>
            <th>3rd Term</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    <?php
        // Initialize totals
        $total_others = 0;
        $total_first = 0;
        $total_second = 0;
        $total_third = 0;
        $total_expected = 0;
        $total_arrears = 0;


        $sql = "SELECT * FROM transportentries WHERE classentryfullname = :feebalancecode";
        $transport_query = $dbh->prepare($sql);
        $transport_query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
        $transport_query->execute();
        $transport_results = $transport_query->fetchAll(PDO::FETCH_OBJ);

        // Initialize accumulators
        $transport_others = 0;
        $transport_first = 0;
        $transport_second = 0;
        $transport_third = 0;

        if ($transport_query->rowCount() > 0) {
            foreach ($transport_results as $row) {
                $transport_others += $row->othersfee;

                // Apply waivers correctly by subtracting them
                $transport_first  += max(0, $row->firsttermtransport - $row->firsttermtransportwaiver);
                $transport_second += max(0, $row->secondtermtransport - $row->secondtermtransportwaiver);
                $transport_third  += max(0, $row->thirdtermtransport - $row->thirdtermtransportwaiver);
            }
        }


        // Step 2: Fetch main fee structure
        $sql = "SELECT * FROM `feebalances` 
                WHERE `studentadmno` = :admno AND `feebalancecode` = :feebalancecode";

        $query = $dbh->prepare($sql);
        $query->bindParam(':admno', $studentadmno, PDO::PARAM_STR);
        $query->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
        $query->execute();
        $record = $query->fetch(PDO::FETCH_OBJ);

        if ($record) {
            // Original values
            $feetreatment = $record->feetreatment;
            $childtreatment = $record->childtreatment;
            $boarding = $record->boarding;
            $arrears = $record->arrears;

            // Subtract transport from fees
            $pure_others = $record->othersfee;
            $pure_first = $record->firsttermfee - $firsttermfeewaiver ;
            $pure_second = $record->secondtermfee - $secondtermfeewaiver;
            $pure_third = $record->thirdtermfee - $thirdtermfeewaiver;
            $pure_totalfee = $pure_first + $pure_second + $pure_third;

            $fee_structure_total = $pure_totalfee + $arrears;

            // Display Fee Structure
            ?>
            <tr>
                <td style="text-align: left; white-space: nowrap;">Fee Structure</td>
                <td><?php echo htmlspecialchars($childtreatment); ?></td>
                <td><?php echo htmlspecialchars($feetreatment); ?></td>
                <td><?php echo htmlspecialchars($boarding); ?></td>
                <td><?php echo number_format($arrears); ?></td>
                <td><?php echo number_format($pure_first); ?></td>
                <td><?php echo number_format($pure_second); ?></td>
                <td><?php echo number_format($pure_third); ?></td>
                <td><b><?php echo number_format($fee_structure_total); ?></b></td>
            </tr>
            <?php

            // Accumulate totals
            $total_others += $pure_others;
            $total_first += $pure_first;
            $total_second += $pure_second;
            $total_third += $pure_third;
            $total_arrears += $arrears;
            $total_expected += $fee_structure_total;
        } else {
            echo '<tr><td colspan="10" style="text-align:center; color: gray;">Student fee balance record not found</td></tr>';
        }

        // Step 3: Show Transport Structure separately
        if (count($transport_results) > 0) {
            foreach ($transport_results as $row) {
                $term_total = $row->firsttermtransport + $row->secondtermtransport + $row->thirdtermtransport - $row->firsttermtransportwaiver - $row->secondtermtransportwaiver - $row->thirdtermtransportwaiver;
                ?>
                <tr>
                    <td style="text-align: left; white-space: nowrap;">Transport Structure</td>
                    <td><?php echo htmlentities($row->childtreatment); ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo number_format($row->firsttermtransport - $row->firsttermtransportwaiver); ?></td>
                    <td><?php echo number_format($row->secondtermtransport - $row->secondtermtransportwaiver); ?></td>
                    <td><?php echo number_format($row->thirdtermtransport - $row->thirdtermtransportwaiver); ?></td>
                    <td><b><?php echo number_format($term_total); ?></b></td>
                </tr>
                <?php

                // Add transport to totals
                $total_others += $row->othersfee;
                $total_first += $row->firsttermtransport - $row->firsttermtransportwaiver;
                $total_second += $row->secondtermtransport - $row->secondtermtransportwaiver;
                $total_third += $row->thirdtermtransport - $row->thirdtermtransportwaiver;
                $total_expected += $term_total;
            }
        } else {
            echo '<tr><td colspan="10" style="text-align:center; color: gray; font-style: italic;">No transport structure found</td></tr>';
        }
    ?>

    <!-- Final TOTAL row -->
    <tr style="font-weight: bold; background-color: #f0f0f0;">
        <td colspan="5" style="text-align:right;">TOTAL</td>
        <td><?php echo number_format($total_first); ?></td>
        <td><?php echo number_format($total_second); ?></td>
        <td><?php echo number_format($total_third); ?></td>
        <td><b><?php echo number_format($total_expected); ?></b></td>
    </tr>
    </tbody>
</table>

</div>

        <div class="section-title">Fee Payments Details</div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ReceiptNo</th>
                        <th>Amount</th>
                        <th>Bank</th>
                        <th>PaymentDate</th>
                        <th>Reference(MpesaCode)</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql="SELECT classdetails.academicyear, classdetails.gradefullname, feebalances.studentadmno, feepayments.receiptno, feepayments.cash, feepayments.bank, feepayments.paymentdate,feepayments.reference, feepayments.details
                    FROM feepayments INNER JOIN (feebalances INNER JOIN classdetails ON feebalances.gradefullname = classdetails.gradefullName) ON (feepayments.academicYear = classdetails.AcademicYear) AND (feepayments.studentadmno = feebalances.studentAdmNo)
                    GROUP BY classdetails.academicYear, classdetails.gradefullName, feebalances.studentAdmNo, feepayments.ReceiptNo, feepayments.Cash, feepayments.Bank, feepayments.PaymentDate, feepayments.details
                    HAVING (((classdetails.gradefullName)='$gradefullname') AND ((feebalances.studentAdmNo)='$studentadmno'))
                    ORDER BY feepayments.PaymentDate DESC";
                    $query = $dbh -> prepare($sql);
                    $query->execute();
                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    if($query->rowCount() > 0) {
                        foreach($results as $row) {      
                    ?>      
                    <tr>
                        <td><?php echo $cnt;?></td>
                        <td><?php echo htmlentities($row->receiptno);?></td>
                        <td class="positive"><?php echo number_format($row->cash);?></td>
                        <td><?php echo htmlentities($row->bank);?></td>
                        <td><?php echo htmlentities($row->paymentdate);?></td>
                        <td><?php echo htmlentities($row->reference);?></td>
                        <td><?php echo htmlentities($row->details);?></td>
                    </tr>
                    <?php $cnt=$cnt+1;}} ?>
                </tbody>
            </table>
        </div>
        <?php
$sql = "SELECT * FROM otheritemspayments WHERE studentadmno = :admno AND financialyear = :year";
$query = $dbh->prepare($sql);
$query->bindParam(':admno', $studentadmno, PDO::PARAM_STR);
$query->bindParam(':year', $academicyearfortransport, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() > 0) {
?>
    <div class="section-title">OtherItems Payments</div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Receipt No</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Reference</th>
                    <th>BankDate</th>
                    <th>Breakdown</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cnt = 1;
                foreach ($results as $row) {
                ?>
                    <tr>
                        <td><?= $cnt; ?></td>
                        <td><?= htmlentities($row->receiptno); ?></td>
                        <td><?= number_format($row->amount, 2); ?></td>
                        <td><?= htmlentities($row->paymentmethod); ?></td>
                        <td><?= htmlentities($row->reference); ?></td>
                        <td><?= htmlentities($row->bankpaymentdate); ?></td>
                        <td>
                            <?php
                            try {
                                $payment_id = $row->id;
                                $stmt = $dbh->prepare("
                                    SELECT 
                                        p.otherpayitemname, b.amount AS item_amount
                                    FROM 
                                        otheritemspayments_breakdown b
                                    JOIN 
                                        otherpayitems p ON b.item_id = p.id
                                    WHERE 
                                        b.payment_id = :payment_id
                                    ORDER BY 
                                        p.otherpayitemname ASC
                                ");
                                $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
                                $stmt->execute();
                                $items = $stmt->fetchAll(PDO::FETCH_OBJ);

                                if ($stmt->rowCount() > 0) {
                                    foreach ($items as $item) {
                                        echo htmlentities($item->otherpayitemname) . ': ' . number_format($item->item_amount, 2) . ' | ';
                                    }
                                } else {
                                    echo '<span class="text-muted">No items</span>';
                                }
                            } catch (PDOException $e) {
                                echo '<span class="text-danger">Error loading items</span>';
                            }
                            ?>
                        </td>
                        <td><?= htmlentities($row->details); ?></td>
                    </tr>
                <?php $cnt++; } ?>
            </tbody>
        </table>
    </div>
<?php
} else {
    echo '<p class="text-muted"><i>There are no payments for other items.</i></p>';
}
?>

    <?php
    include('reportfooter.php');
    ?>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>
<?php } ?>