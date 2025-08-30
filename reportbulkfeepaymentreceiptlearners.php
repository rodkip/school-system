<?php 
include('includes/dbconnection.php');

// Converts numeric values to English words
function numberToWords($number) {
    $hyphen = '-';
    $conjunction = ' and ';
    $separator = ', ';
    $negative = 'negative ';
    $decimal = ' point ';
    $dictionary = [
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
        5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
        15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty',
        60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety',
        100 => 'hundred', 1000 => 'thousand', 1000000 => 'million',
        1000000000 => 'billion', 1000000000000 => 'trillion',
        1000000000000000 => 'quadrillion', 1000000000000000000 => 'quintillion'
    ];
    
    if (!is_numeric($number)) return false;
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        trigger_error('numberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING);
        return false;
    }
    if ($number < 0) return $negative . numberToWords(abs($number));
    
    $string = $fraction = null;
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            $string = $dictionary[$tens];
            if ($units) $string .= $hyphen . $dictionary[$units];
            break;
        case $number < 1000:
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . numberToWords($remainder);
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= numberToWords($remainder);
            }
            break;
    }

    if ($fraction !== null && is_numeric($fraction)) {
        $string .= $decimal;
        foreach (str_split((string) $fraction) as $digit) {
            $string .= $dictionary[$digit] . ' ';
        }
        $string = rtrim($string);
    }

    return $string;
}

// Fetch school info
$school = $dbh->query("SELECT * FROM schooldetails LIMIT 1")->fetch(PDO::FETCH_OBJ);

// Initialize variables
$payment = null;
$payments = [];
$total = 0;

if (isset($_GET['receiptno'])) {
    $receiptno = $_GET['receiptno'];
    
    // Get payment header information
    $stmt = $dbh->prepare("SELECT * FROM feepayments WHERE receiptno = :receiptno LIMIT 1");
    $stmt->bindParam(':receiptno', $receiptno, PDO::PARAM_STR);
    $stmt->execute();
    $payment = $stmt->fetch(PDO::FETCH_OBJ);
    
    // Get all payments with this receipt number
    $stmt = $dbh->prepare("
        SELECT 
            fp.studentadmno, 
            sd.studentname, 
            fp.cash,
            ce.gradefullname
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
        WHERE fp.receiptno = :receiptno
        ORDER BY sd.studentname ASC
    ");
    $stmt->bindParam(':receiptno', $receiptno, PDO::PARAM_STR);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    // Calculate total
    foreach ($payments as $p) {
        $total += $p->cash;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Payment Receipt - <?php echo htmlentities($payment->receiptno ?? ''); ?></title>
    <style>
    .container {
        width: 100%;
        max-width: 100%;
    }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        text-align: center;
        margin-bottom: 3mm;
        border-bottom: 2px solid #ccc;
        padding-bottom: 3px;
    }
    .logo-container {
        flex: 0 0 auto;
        margin-bottom: 2mm;
    }
    .logo {
        max-height: 25mm;
        max-width: 100%;
    }
    .details-container {
        flex: 1;
        text-align: right;
    }
    .school-name {
        font-weight: bold;
        font-size: 14px;
        color: #2c3e50;
        margin-bottom: 1mm;
        text-transform: uppercase;
    }
    .school-motto {
        font-style: italic;
        font-size: 9px;
        color: #7f8c8d;
        margin-top: -5px;
    }
    .school-details {
        font-size: 9px;
        color: #7f8c8d;
        line-height: 1.4;
        margin-top: 4px;
    }
    .payment-info {
        background-color: #f8f9fa;
        padding: 3mm;
        margin: 3mm 0;
        border-radius: 3px;
        font-size: 9px;
        text-align: center;
        border: 1px dashed #bdc3c7;
    }
    .payment-info strong {
        color: #2c3e50;
    }
    .receipt-title {
        font-weight: bold;
        text-align: center;
        margin: 3mm 0;
        font-size: 12px;
        color: #2c3e50;
        position: relative;
    }
    .receipt-title::after {
        content: "";
        display: block;
        width: 50%;
        height: 1px;
        background: linear-gradient(to right, transparent, #2c3e50, transparent);
        margin: 2px auto 0;
    }
    .receipt-number {
        text-align: center;
        font-weight: bold;
        font-size: 11px;
        color: #e74c3c;
        margin-bottom: 2mm;
    }
    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 3mm;
        font-size: 12px;
    }
    .details-table td {
        padding: 0.5mm 0;
        vertical-align: top;
        border-bottom: 1px solid #ecf0f1;
    }
    .label {
        font-weight: bold;
        width: 25%;
        color: #7f8c8d;
    }
    .section-title {
        font-size: 10px;
        font-weight: bold;
        color: #000;
        padding: 3px 5px;
        border-left: 3px solid #6f42c1;
        margin-bottom: 2px;
    }
    /* Votehead Table */
    .votehead-table {
        width: 100%;
        border-collapse: collapse;
        margin: 2mm 0;
        font-size: 8px;
    }
    .votehead-table th,
    .votehead-table td {
        padding: 1mm;
        border: 1px solid #ccc;
    }
    .votehead-table th {
        background-color: #f5f5f5;
        text-align: left;
        color: #7f8c8d;
        font-weight: bold;
    }
    .votehead-table .amount-cell {
        text-align: right;
    }
    .votehead-table .total-row {
        background-color: #eaeaea;
        font-weight: bold;
    }
    .highlight {
        background-color: #f8f9fa;
    }
    .signature {
        margin-top: 5mm;
        padding-top: 2mm;
        text-align: center;
        position: relative;
    }
    .signature::before {
        content: "";
        display: block;
        width: 50%;
        height: 1px;
        background: linear-gradient(to right, transparent, #bdc3c7, transparent);
        margin: 0 auto 2mm;
    }
    .footer {
        text-align: center;
        font-size: 8px;
        margin-top: 3mm;
        color: #7f8c8d;
        font-style: italic;
    }
    .print-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 10px;
        margin: 0 5px;
        transition: background-color 0.3s;
    }
    .print-btn:hover {
        background-color: #2980b9;
    }
    .reprint-notice {
        background-color: #fff3cd;
        color: #856404;
        padding: 5px;
        text-align: center;
        margin-bottom: 5px;
        border: 1px solid #ffeeba;
        border-radius: 3px;
        font-size: 9px;
    }
    .example-note {
        color: #6c757d;
        font-style: italic;
        font-size: 0.85em;
    }
    @media print {
        body {
            padding: 0;
        }
        .no-print {
            display: none;
        }
    }
     @page {
        size: A5;
        margin: 5mm;
        @bottom-center {
            content: counter(page);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            color: #7f8c8d;
        }
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 10px;
        margin: 0;
        padding: 5mm;
        color: #333;
        background-color: #fff;
        position: relative;
        min-height: 100%;
    }
    .page-number {
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #7f8c8d;
        font-style: italic;
    }
    .positive-balance {
        color: #28a745;
        font-weight: bold;
    }
    .negative-balance {
        color: #dc3545;
        font-weight: bold;
    }
    .totals-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: -1px;
    }
    .totals-table td {
        padding: 1mm;
        border: 1px solid #ccc;
    }
    .totals-label {
        background-color: #f5f5f5;
        font-weight: bold;
        text-align: left;
    }
    .totals-amount {
        background-color: #f5f5f5;
        font-weight: bold;
        text-align: right;
    }
    .negative-total {
        color: #dc3545;
    }
    .positive-total {
        color: #28a745;
    }
    </style>
</head>
<body>
<div class="container">
    <!-- Header Section -->
    <div class="header">
        <div class="logo-container">
            <img src="images/schoollogo.png" alt="School Logo" class="logo">
        </div>
        <div class="details-container">
            <div class="school-name"><?php echo htmlentities($school->schoolname); ?></div>
            <div class="school-motto">"<?php echo htmlentities($school->motto ?? 'Excellence in Education'); ?>"</div>
            <div class="school-details">
                <?php echo htmlentities($school->postaladdress); ?><br>
                Tel: <?php echo htmlentities($school->phonenumber); ?> | 
                Email: <?php echo htmlentities($school->emailaddress ?? 'info@school.edu'); ?>
            </div>
            <div class="payment-info">
                Paybill No: <strong>222111</strong> | 
                Account No: <strong>2045821#NameGrade</strong><span class="example-note"> &nbsp;eg. 2045821#Shirly8</span><br>
                Bank: <strong><?php echo htmlentities($school->bankname ?? 'Family BANK'); ?></strong> | 
                Name: <strong>ELGON HILLS SCHOOLS LTD</strong>
            </div>
        </div>
    </div>

    <!-- Receipt Details -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div class="receipt-number">RECEIPT #<?php echo htmlentities($payment->receiptno ?? ''); ?></div>
        <div class="receipt-title">OFFICIAL FEE PAYMENT RECEIPT (Bulk)</div>
    </div>

    <table class="details-table" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td class="label" style="font-weight: bold;">Receipt Date:</td>
            <td><?php echo htmlentities($payment->paymentdate ?? 'N/A'); ?></td>
            
            <td class="label" style="font-weight: bold;">Bank Payment Date:</td>
            <td><?php echo htmlentities($payment->bankpaymentdate ?? 'N/A'); ?></td>
            
            <td style="font-weight: bold;"><?php echo htmlentities($payment->bank ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td class="label" style="font-weight: bold;">Payer:</td>
            <td colspan="4"><?php echo htmlentities($payment->payer ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td class="label" style="font-weight: bold;">Description:</td>
            <td colspan="4"><?php echo htmlentities($payment->details ?? 'Bulk payment for multiple Learners'); ?></td>
        </tr>
    </table>

    <div class="section-title">List of Beneficiaries</div>
    <table class="votehead-table">
        <thead>
            <tr>
                <th colspan="5"></th>
                <th colspan="4" style="text-align:center; background-color:#f0f0f0; font-weight:bold;">
                    Current Fee Balance
                </th>
            </tr>
            <tr>
                <th>#</th>
                <th>AdmNo</th>
                <th>Name</th>
                <th>LatestGrade</th>
                <th class="amount-cell">Allocated</th>
                <th class="amount-cell">1st-Term</th>
                <th class="amount-cell">2nd-Term</th>
                <th class="amount-cell">3rd-Term</th>
                <th class="amount-cell">YearlyBal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($payments)) {
                $counter = 1;
                $totalAllocated = 0;
                $totalFirstTermBal = 0;
                $totalSecondTermBal = 0;
                $totalThirdTermBal = 0;
                $totalYearlyBal = 0;

                foreach ($payments as $p) {
                    // Fetch latest balances
                    $sql = "SELECT firsttermbal, secondtermbal, thirdtermbal, yearlybal 
                            FROM feebalances 
                            WHERE studentadmno = :admno 
                            ORDER BY feebalancecode DESC 
                            LIMIT 1";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(':admno', $p->studentadmno, PDO::PARAM_STR);
                    $stmt->execute();
                    $balance = $stmt->fetch(PDO::FETCH_OBJ);

                    // Assign defaults
                    $firstTermBal = $secondTermBal = $thirdTermBal = $yearlyBal = 0;
                    $balanceClass = '';
                    $displayYearly = 'N/A';

                    if ($balance) {
                        $firstTermBal = $balance->firsttermbal ?? 0;
                        $secondTermBal = $balance->secondtermbal ?? 0;
                        $thirdTermBal = $balance->thirdtermbal ?? 0;
                        $yearlyBal = $balance->yearlybal ?? 0;
                        $displayYearly = number_format($yearlyBal,);
                        $balanceClass = ($yearlyBal > 0) ? 'negative-balance' : 'positive-balance';
                    }

                    // Aggregate totals
                    $totalAllocated += $p->cash;
                    $totalFirstTermBal += $firstTermBal;
                    $totalSecondTermBal += $secondTermBal;
                    $totalThirdTermBal += $thirdTermBal;
                    $totalYearlyBal += $yearlyBal;

                    echo "<tr>
                            <td>{$counter}</td>
                            <td style='text-align:center;'>" . htmlentities($p->studentadmno ?? 'N/A') . "</td>
                            <td>" . htmlentities($p->studentname ?? 'N/A') . "</td>
                            <td>" . htmlentities($p->gradefullname ?? 'N/A') . "</td>
                            <td class='amount-cell'>" . number_format($p->cash) . "</td>
                            <td class='amount-cell'>" . number_format($firstTermBal) . "</td>
                            <td class='amount-cell'>" . number_format($secondTermBal) . "</td>
                            <td class='amount-cell'>" . number_format($thirdTermBal) . "</td>
                            <td class='amount-cell {$balanceClass}'>{$displayYearly}</td>
                          </tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='9' style='text-align:center;'>No payment records found for this receipt</td></tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right; font-weight: bold; padding-right: 10px;">TOTALS:</td>
                <td class="amount-cell"><?= number_format($totalAllocated) ?></td>
                <td class="amount-cell"><?= number_format($totalFirstTermBal) ?></td>
                <td class="amount-cell"><?= number_format($totalSecondTermBal) ?></td>
                <td class="amount-cell"><?= number_format($totalThirdTermBal) ?></td>
                <td class="amount-cell <?= ($totalYearlyBal > 0) ? 'negative-balance' : 'positive-balance' ?>">
                    <?= number_format($totalYearlyBal) ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="details-table" style="width:100%; margin-top: 1rem; border-collapse: collapse;">
        <tr class="highlight" style="background-color: #f5f5f5;">
            <td class="label" style="padding: 8px; font-weight: bold; width: 20%;">SUM Paid:</td>
            <td colspan="3" style="padding: 8px;"> 
                <strong>KSh <?= number_format($total); ?></strong>
                <em>(<?= strtoupper(numberToWords($total)); ?> only.)</em>
            </td>
        </tr>
    </table>

    <div class="signature">Authorized Signature</div>

    <div class="footer">This is a system-generated receipt and does not require a physical signature.</div>
     
    <!-- Page number container (visible in print) -->
    <div class="page-number no-print" style="display: none;">
        Page <span class="page-number-display"></span>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 10px;">
        <button class="print-btn" onclick="window.print()">Print Receipt</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pageNumberDisplay = document.querySelector('.page-number-display');
    if (pageNumberDisplay) {
        pageNumberDisplay.textContent = '1';
    }
});
</script>
</body>
</html>