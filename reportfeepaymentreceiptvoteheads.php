<?php 
include('includes/dbconnection.php');

// Update print status when page loads
if (isset($_GET['id'])) {
    $paymentId = $_GET['id'];
    
    // Update the printed status in the database
    $updateSql = "UPDATE feepayments SET printed = TRUE, print_date = NOW() WHERE id = :id";
    $updateQuery = $dbh->prepare($updateSql);
    $updateQuery->bindParam(':id', $paymentId, PDO::PARAM_INT);
    $updateQuery->execute();
}

function numberToWords($number) {
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'forty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
    
    if (!is_numeric($number)) {
        return false;
    }
    
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        trigger_error(
            'numberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }
    
    if ($number < 0) {
        return $negative . numberToWords(abs($number));
    }
    
    $string = $fraction = null;
    
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
    
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . numberToWords($remainder);
            }
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
    
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
    
    return $string;
}

if (isset($_GET['id'])) {
    $paymentId = $_GET['id'];

    try {
        $sql = "SELECT fp.*, s.studentname
                FROM feepayments fp 
                JOIN studentdetails s ON fp.studentadmno = s.studentadmno 
                WHERE fp.id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $paymentId, PDO::PARAM_INT);
        $query->execute();
        $payment = $query->fetch(PDO::FETCH_OBJ);

        // Check if this is a reprint
        $isReprint = ($payment->printed == 1);
        $originalPrintDate = $payment->print_date;

        $voteheadpaymentsql = "
            SELECT fv.*, v.votehead 
            FROM feepayment_voteheads fv
            JOIN voteheads v ON fv.votehead_id = v.id
            WHERE fv.payment_id = :id
        ";
        $voteheadquery = $dbh->prepare($voteheadpaymentsql);
        $voteheadquery->bindParam(':id', $paymentId, PDO::PARAM_INT);
        $voteheadquery->execute();
        $voteheadpayments = $voteheadquery->fetchAll(PDO::FETCH_OBJ);

        $schoolquery = "SELECT * FROM schooldetails LIMIT 1";
        $schoolstmt = $dbh->prepare($schoolquery);
        $schoolstmt->execute();
        $school = $schoolstmt->fetch(PDO::FETCH_OBJ);

        $gradeSql = "SELECT ce.gradefullname 
                     FROM classentries ce 
                     JOIN classdetails cd ON ce.gradefullname = cd.gradefullname 
                     WHERE ce.studentadmno = :admno AND cd.academicyear = :year 
                     LIMIT 1";
        $gradeQuery = $dbh->prepare($gradeSql);
        $gradeQuery->bindParam(':admno', $payment->studentadmno, PDO::PARAM_STR);
        $gradeQuery->bindParam(':year', $payment->academicyear, PDO::PARAM_STR);
        $gradeQuery->execute();
        $gradeRow = $gradeQuery->fetch(PDO::FETCH_OBJ);

        $displayGrade = $gradeRow ? $gradeRow->gradefullname : 'N/A';
        $yearlybalSql = "SELECT firsttermbal, secondtermbal, thirdtermbal, yearlybal FROM feebalances 
        WHERE studentadmno = :admno 
        ORDER BY feebalancecode DESC 
        LIMIT 1";
$yearlybalQuery = $dbh->prepare($yearlybalSql);
$yearlybalQuery->bindParam(':admno', $payment->studentadmno, PDO::PARAM_STR);
$yearlybalQuery->execute();
$yearlybalRow = $yearlybalQuery->fetch(PDO::FETCH_OBJ);

// Assign term balances and yearly balance
$firstTermBal = $yearlybalRow ? $yearlybalRow->firsttermbal : 'N/A';
$secondTermBal = $yearlybalRow ? $yearlybalRow->secondtermbal : 'N/A';
$thirdTermBal = $yearlybalRow ? $yearlybalRow->thirdtermbal : 'N/A';
$yearlyBal = $yearlybalRow ? $yearlybalRow->yearlybal : 'N/A';


        if ($payment && $school) {
            $amountInWords = numberToWords($payment->cash);
            // Construct the combined value
$classEntryFullname = $displayGrade . $payment->studentadmno;

// Check transportentries for a match
$transportSql = "SELECT * FROM transportentries WHERE classentryfullname = :classentryfullname LIMIT 1";
$transportQuery = $dbh->prepare($transportSql);
$transportQuery->bindParam(':classentryfullname', $classEntryFullname, PDO::PARAM_STR);
$transportQuery->execute();
$transportMatch = $transportQuery->fetch(PDO::FETCH_OBJ);

// Determine if transport exists
$hasTransport = $transportMatch ? true : false;

            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Payment Receipt</title>
                <style>
    @page {
        size: A5;
        margin: 5mm;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 10px;
        margin: 0;
        padding: 5mm;
        color: #333;
        background-color: #fff;
    }
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
        padding-bottom: 10px;
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

    /* Payment Info */
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

    /* Receipt Title & Number */
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

    /* Details Table */
    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 3mm;
        font-size: 12px;
    }
    .details-table td {
        padding: 1.5mm 0;
        vertical-align: top;
        border-bottom: 1px solid #ecf0f1;
    }
    .label {
        font-weight: bold;
        width: 25%;
        color: #7f8c8d;
    }

    /* Votehead Table */
    .votehead-table {
        width: 100%;
        border-collapse: collapse;
        margin: 2mm 0;
        font-size: 10px;
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
    .amount-cell {
        text-align: right;
        font-weight: bold;
    }
    .total-row {
        border-top: 1px solid #bdc3c7;
        font-weight: bold;
        background-color: #f8f9fa;
    }

    /* Signature */
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

    /* Footer */
    .footer {
        text-align: center;
        font-size: 8px;
        margin-top: 3mm;
        color: #7f8c8d;
        font-style: italic;
    }

    /* Buttons */
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

    /* Reprint Notice */
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
    color: #6c757d; /* Bootstrap's muted */
    font-style: italic;
    font-size: 0.85em;
}

    /* Utilities */
    .highlight {
        background-color: #f8f9fa;
    }
    .no-print {
        display: none;
    }
    @media print {
        body {
            padding: 0;
        }
        .no-print {
            display: none;
        }
    }
</style>

            </head>
            <body>
                <div class="container">
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
                                Account No: <strong>2045821#NameGrade</strong><span class="text-muted fst-italic small"> &nbsp;eg.2045821#Shirly8</span><br>
                                Bank: <strong><?php echo htmlentities($school->bankname ?? 'Family BANK'); ?></strong> | 
                                Name: <strong>ELGON HILLS SCHOOLS LTD</strong> 
                            </div>

                        </div>
                    </div>

                    
                    <div class="receipt-number">RECEIPT #<?php echo htmlentities($payment->receiptno); ?></div>
                    
                    <div class="receipt-title">OFFICIAL FEE PAYMENT RECEIPT</div>
                    <table class="details-table">
                        <tr>
                            <td class="label">Learner Name:</td>
                            <td><?php echo htmlentities($payment->studentname ?? 'N/A'); ?></td>
                            <td class="label">Admission No:</td>
                            <td><?php echo htmlentities($payment->studentadmno ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Grade/Class:</td>
                            <td><?php echo htmlentities($displayGrade ?? 'N/A'); ?></td>
                            <td class="label">Academic Year:</td>
                            <td><?php echo htmlentities($payment->academicyear ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                        <td class="label">Receipt Date:</td>
                        <td><?php echo htmlentities($payment->paymentdate ?? 'N/A'); ?></td>
                        <td class="label">Bank PaymentDate:</td>
                        <td><?php echo htmlentities($payment->bankpaymentdate ?? 'N/A'); ?></td>
                      
                        </tr>
                    </table>

                    <table class="votehead-table">
                        <tr>
                            <th>#</th>
                            <th>VOTEHEAD DETAILS</th>
                            <th class="amount-cell">AMOUNT (KSh)</th>
                        </tr>
                        <?php 
                        $total = 0;
                        $counter = 1;
                        if (!empty($voteheadpayments)) {
                            foreach ($voteheadpayments as $votehead) {
                                $total += $votehead->amount;
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?>.</td>
                                    <td><?php echo htmlentities($votehead->votehead); ?></td>
                                    <td class="amount-cell"><?php echo number_format($votehead->amount, 2); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No votehead details available</td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr class="total-row">
                            <td colspan="2">TOTAL PAID</td>
                            <td class="amount-cell"><?php echo number_format($total, 2); ?></td>
                        </tr>
                    </table>

                    <table class="details-table">
                        <tr class="highlight">
                            <td class="label">Amount in Words:</td>
                            <td colspan="3"><em><?php echo ucfirst($amountInWords ?? 'zero'); ?> shillings only</em></td>
                        </tr>
                        <tr>
                            <td class="label">Payment Method:</td>
                            <td><?php echo htmlentities($payment->bank ?$payment->bank : 'Cash'); ?></td>
                            <td class="label">Reference No:</td>
                            <td><?php echo htmlentities($payment->reference ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Details:</td>
                            <td colspan="3"><?php echo htmlentities($payment->details ? $payment->details : 'N/A'); ?></td>
                       
                        </tr>
                        <tr>
                            <td class="label">Term Balances:</td>
                            <td colspan="3">
                                 1st-Term: <span style="color: <?php echo ($firstTermBal == 0) ? 'green' : 'inherit'; ?>">
                                    <?php echo ($firstTermBal == 0) ? 'Cleared' : number_format($firstTermBal, ); ?>
                                </span>,
                                2nd-Term: <span style="color: <?php echo ($secondTermBal == 0) ? 'green' : 'inherit'; ?>">
                                    <?php echo ($secondTermBal == 0) ? 'Cleared' : number_format($secondTermBal, ); ?>
                                </span>,
                                3rd-Term: <span style="color: <?php echo ($thirdTermBal == 0) ? 'green' : 'inherit'; ?>">
                                    <?php echo ($thirdTermBal == 0) ? 'Cleared' : number_format($thirdTermBal, ); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Yearly Bal:</td>
                            <td>
                                <span style="color: <?php echo ($yearlyBal == 0) ? 'green' : 'inherit'; ?>">
                                    KSh <?php echo ($yearlyBal == 0) ? 'Cleared' : number_format($yearlyBal, 2); ?>
                                </span>
                            </td>
                            <td class="label">Received By:</td>
                            <td><?php echo htmlentities($payment->cashier ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><strong><i class="bi bi-truck"></i> Transport:</strong></td>
                            <td colspan="3">
                                <?php if ($hasTransport): ?>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle-fill"></i> Enrolled
                                    </span>
                                <?php else: ?>
                                    <span class="text-danger">
                                        <i class="bi bi-x-circle-fill"></i> Not Enrolled
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>


                    </table>

                    <div class="signature">
                        <div style="margin-top: 15px;">_________________________</div>
                        <div>Authorized Signature</div>
                    </div>
                    
                    <div class="footer">
                        This is an official computer-generated receipt. Please keep it safe.<br>
                        Printed on: <?php 
                            date_default_timezone_set('Africa/Nairobi');
                            echo date('d/m/Y H:i'); 
                        ?> EAT
                    </div>
                    
                    <div class="no-print" style="text-align: center; margin-top: 10px;">
                        <button class="print-btn" onclick="window.print()">Print Receipt</button>
                        <button class="print-btn" onclick="window.close()" style="background-color: #e74c3c;">Close Window</button>
                    </div>
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            
                            // Send confirmation back to parent window if this is in a popup
                            if (window.opener) {
                                window.opener.postMessage({
                                    type: 'receipt_printed',
                                    paymentId: <?php echo $paymentId; ?>,
                                    isReprint: <?php echo $isReprint ? 'true' : 'false'; ?>
                                }, '*');
                            }
                        }, 500);
                    };
                </script>
            </body>
            </html>
            <?php
        } else {
            echo "Payment or school record not found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>