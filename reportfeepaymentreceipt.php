<?php
include('includes/dbconnection.php');

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


        if ($payment && $school) {
            $amountInWords = numberToWords($payment->cash);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Payment Receipt</title>
                <style>
   @page {
    size: A5;
    margin: 10mm; /* Add margins around the content */
}

body {
    font-family: 'Courier New', monospace;
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    font-size: 12px;
    color: #000;
    background-color: white;
    display: flex;
    justify-content: center; /* Center content horizontally */
    align-items: center; /* Center content vertically */
    text-align: center; /* Center text */
}

.container {
    width: 90%; /* Ensure content fits within A5 size */
    margin: 0 auto;
    padding: 10mm;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.header {
    text-align: center;
    margin-bottom: 5px;
}

.logo-container {
    text-align: center;
    margin-bottom: 5px;
}

.logo {
    max-width: 60mm;
    max-height: 30mm;
}

.school-name {
    font-weight: bold;
    font-size: 19px;
    margin-bottom: 3px;
    text-transform: uppercase; /* Converts text to uppercase */
}


.school-details {
    font-size: 10px;
    line-height: 1.2;
}

.receipt-title {
    text-align: center;
    font-weight: bold;
    font-size: 12px;
    margin: 5px 0;
    text-decoration: underline;
}

.receipt-number {
    text-align: center;
    font-weight: bold;
    margin-bottom: 5px;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    text-align: left;
}

.details-table td {
    padding: 3px 0;
    vertical-align: top;
    font-size: 11px;
}

.label {
    font-weight: bold;
    width: 30%;
}

.amount-row td {
    font-weight: bold;
    border-top: 1px dashed #000;
    border-bottom: 1px dashed #000;
    padding: 5px 0;
}

.signature {
    margin-top: 15px;
    border-top: 1px dashed #000;
    width: 100%;
    text-align: center;
    padding-top: 5px;
    font-size: 11px;
}

.footer {
    text-align: center;
    margin-top: 10px;
    font-size: 10px;
    font-style: italic;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    padding: 5px 10px;
    border-radius: 3px;
    font-weight: bold;
    cursor: pointer;
    border: 1px solid #000;
    font-size: 11px;
}

@media print {
    body {
        padding: 0;
    }
    .no-print {
        display: none;
    }
    .container {
        padding: 0;
    }
}

</style>
            </head>
            <body>
                <div class="container">
                    <div class="logo-container">
                        <img src="images/schoollogo.png" alt="School Logo" class="logo">
                    </div>
                    
                    <div class="header">
                        <div class="school-name"><?php echo htmlentities($school->schoolname); ?></div>
                        <div class="school-details">
                            <?php echo htmlentities($school->postaladdress); ?><br>
                            Tel: <?php echo htmlentities($school->phonenumber); ?>
                        </div>
                    </div>
                    
                    <div class="receipt-number">Receipt #<?php echo htmlentities($payment->receiptno); ?></div>
                    
                    <div class="receipt-title">OFFICIAL RECEIPT</div>

                    <?php
// Assuming you've already fetched payment details in the $payment object

// Fetch the latest fee balance for the given studentadmno
$yearlybalSql = "SELECT yearlybal FROM feebalances 
                  WHERE studentadmno = :admno 
                  ORDER BY feebalancecode DESC 
                  LIMIT 1";
$yearlybalQuery = $dbh->prepare($yearlybalSql);
$yearlybalQuery->bindParam(':admno', $payment->studentadmno, PDO::PARAM_STR);
$yearlybalQuery->execute();
$yearlybalRow = $yearlybalQuery->fetch(PDO::FETCH_OBJ);

// Get the latest balance (if exists)
$yearlybal = $yearlybalRow ? $yearlybalRow->yearlybal : 'N/A';
?>

<table class="details-table">
    <tr>
        <td class="label">Name:</td>
        <td><?php echo htmlentities($payment->studentname ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Adm No:</td>
        <td><?php echo htmlentities($payment->studentadmno ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Grade:</td>
        <td><?php echo htmlentities($displayGrade ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Date:</td>
        <td><?php echo htmlentities($payment->paymentdate ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Year:</td>
        <td><?php echo htmlentities($payment->academicyear ?? 'N/A'); ?></td>
    </tr>
    <tr class="amount-row">
        <td class="label">Amount Paid:</td>
        <td>KSh <?php echo is_numeric($payment->cash) ? number_format($payment->cash, 2) : '0.00'; ?></td>
    </tr>
    <tr>
        <td class="label">In Words:</td>
        <td><?php echo ucfirst($amountInWords ?? 'zero'); ?> shillings only</td>
    </tr>
    <tr>
        <td class="label">Payment Method:</td>
        <td><?php echo htmlentities($payment->bank ? 'Bank (' . $payment->bank . ')' : 'Cash'); ?></td>
    </tr>
    <tr>
        <td class="label">Reference:</td>
        <td><?php echo htmlentities($payment->reference ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Details:</td>
        <td><?php echo htmlentities($payment->details ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Received By:</td>
        <td><?php echo htmlentities($payment->cashier ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td class="label">Item:</td>
        <td>Fee Payment</td>
    </tr>
    <tr>
        <td class="label">Fee Balance:</td>
        <td>KSh <?php echo is_numeric($yearlybal) ? number_format($yearlybal, 2) : '0.00'; ?></td>
    </tr>
</table>



                    <div class="signature">
                        Authorized Signature
                    </div>
                    <div class="footer">
                        This is an official receipt. Please keep it safe.<br>
                        <span style="font-size: 9px;">Printed on: <?php echo date('d/m/Y h:i A'); ?> EAT</span>
                    </div>


                    
                    <div class="action-buttons no-print">
                        <button class="btn" onclick="window.print()">Print Receipt</button>
                        <button class="btn" onclick="window.close()">Close</button>
                    </div>
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
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