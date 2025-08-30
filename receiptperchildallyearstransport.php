<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
    header('location:logout.php');
} else {
    $studentadmno = $_GET['studentadmno'] ?? '';
    if (!preg_match('/^[a-zA-Z0-9]+$/', $studentadmno)) {
        header('location:error-page.php');
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transport Payments Breakdown</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
    @page {
        size: A4 landscape;
        margin: 5mm;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 10px;
        color: #333;
        background-color: #f5f7fa;
    }
    
    @media print {
        body {
            background-color: white;
            padding: 0;
            margin: 0;
        }
        .no-print {
            display: none !important;
        }
        .container {
            box-shadow: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        /* Print color settings */
        th {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #34495e !important;
            color: white !important;
        }
        tr:nth-child(even) {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #f8f9fa !important;
        }
        .student-info {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #f8f9fa !important;
        }
        /* Prevent page breaks */
        table {
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        /* Ensure no blank space at the end of the page */
        .footer {
            page-break-after: avoid;
        }
        /* Remove any extra margins */
        html, body {
            height: auto;
        }
    }
    
    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px;
        background-color: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        box-sizing: border-box;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .logo img {
        height: 70px;
        width: auto;
        object-fit: contain;
    }
    
    .school-info {
        text-align: right;
    }
    
    .school-name {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 3px;
    }
    
    .school-details {
        font-size: 12px;
        color: #7f8c8d;
        line-height: 1.3;
    }
    
    .report-title {
        text-align: center;
        margin: 15px 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .student-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding: 12px;
        background-color: #f8f9fa;
        border-radius: 4px;
        font-size: 13px;
    }
    
    .info-item {
        padding: 0 8px;
    }
    
    .info-item strong {
        color: #2c3e50;
        font-weight: 500;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-bottom: 15px;
        -webkit-overflow-scrolling: touch;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        table-layout: auto;
    }
    
    th {
        background-color: #34495e;
        color: white;
        padding: 8px 5px;
        text-align: center;
        font-weight: 500;
    }
    
    td {
        padding: 8px 5px;
        border-bottom: 1px solid #e0e0e0;
        text-align: center;
        vertical-align: middle;
    }
    
    tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .text-left {
        text-align: left;
    }
    
    .text-center {
        text-align: center;
    }
    
    .highlight {
        font-weight: 600;
        color: #e74c3c;
    }
    
    .positive {
        color: #27ae60;
        font-weight: 500;
    }
    
    .footer {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e0e0e0;
        font-size: 11px;
        color: #7f8c8d;
        text-align: center;
    }
    
    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        margin: 15px 0 10px 0;
    }
    
    .print-button {
        position: fixed;
        bottom: 15px;
        right: 15px;
        padding: 10px 20px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        z-index: 1000;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 500;
    }
    
    .back-button {
        position: fixed;
        bottom: 15px;
        left: 15px;
        padding: 10px 20px;
        background-color: #7f8c8d;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        z-index: 1000;
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }
    
    .print-button:hover {
        background-color: #2980b9;
    }
    
    .back-button:hover {
        background-color: #6c757d;
    }
    
    .balance-negative {
        color: #e74c3c;
        font-weight: 600;
    }
    
    .balance-positive {
        color: #27ae60;
        font-weight: 600;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <?php      
                $searchquery = "SELECT * FROM schooldetails";
                $qry = $dbh->prepare($searchquery);
                $qry->execute();
                $row = $qry->fetchAll(PDO::FETCH_OBJ);
                $cnt = 1;
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
            <div class="report-title">
                Per Child Transport Payments BreakDown
            </div>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <?php       
            $searchquery = "SELECT * FROM studentdetails WHERE studentadmno = :studentadmno";
            $qry = $dbh->prepare($searchquery);
            $qry->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
            $qry->execute();
            $row = $qry->fetchAll(PDO::FETCH_OBJ);
            $cnt = 1;
            if($qry->rowCount() > 0) {
                foreach($row as $rlt) {   
            ?>
            <div class="info-item">
                <strong>Student Name:</strong> <?php echo htmlentities($rlt->studentname); ?> |
                <strong>Admission No:</strong> <?php echo htmlentities($rlt->studentadmno); ?> |
                <strong>Gender:</strong> <?php echo htmlentities($rlt->gender); ?>
            </div>
            <?php $cnt=$cnt+1;}} ?>
        </div>

        <!-- Transport Payments Summary -->
        <div class="section-title">Transport Payments Summary</div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Academic Year</th>
                        <th>Stage</th>
                        <th>Charge/Month</th>
                        <th>Total Months</th>
                        <th>Child Treatment</th>
                        <th>Total Charges</th>
                        <th>Sum Paid</th>
                        <th>Installments</th>
                        <th>Yearly Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT transportentries.studentadmno, transportentries.childtreatment, 
                            transportentries.childtreatmentrate, transportstructure.chargespermonth, 
                            transportstructure.academicyear, transportentries.stagefullname, 
                            transportentries.totmonthsperyear, SUM(transportpayments.cash) as sumpaid, 
                            COUNT(transportpayments.cash) as installments,
                            (transportentries.childtreatmentrate * transportstructure.chargespermonth * transportentries.totmonthsperyear) as totalcharges 
                            FROM transportentries 
                            INNER JOIN transportpayments ON transportpayments.studentadmno = transportentries.studentadmno
                            INNER JOIN transportstructure ON transportentries.stagefullname = transportstructure.stagefullname
                            WHERE transportentries.studentadmno = :studentadmno
                            GROUP BY transportentries.studentadmno, transportentries.childtreatment, 
                            transportentries.childtreatmentrate, transportstructure.chargespermonth, 
                            transportstructure.academicyear, transportentries.stagefullname, 
                            transportentries.totmonthsperyear
                            ORDER BY transportstructure.academicyear DESC";
                    
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;
                    $arr = 0;
                    
                    if($query->rowCount() > 0) {
                        foreach($results as $row) {      
                    ?>      
                    <tr>
                        <td><?php echo htmlentities($row->academicyear); ?></td>
                        <td><?php echo htmlentities($row->stagefullname); ?></td>
                        <td>Ksh <?php echo number_format($row->chargespermonth); ?></td>
                        <td><?php echo number_format($row->totmonthsperyear); ?></td>
                        <td><?php echo htmlentities($row->childtreatment); ?></td>
                        <td>Ksh <?php echo number_format($row->totalcharges); ?></td>
                        <td class="positive">Ksh <?php echo number_format($row->sumpaid); ?></td>
                        <td><?php echo number_format($row->installments); ?></td>
                        <td class="<?php echo (($row->totalcharges - $row->sumpaid) > 0) ? 'balance-negative' : 'balance-positive'; ?>">
                            Ksh <?php echo number_format(($row->totalcharges) - ($row->sumpaid)); ?>
                        </td>
                    </tr>
                    <?php $cnt=$cnt+1;}} else { ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No transport payment records found for this student</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            Generated on <?php echo date('F j, Y, g:i a'); ?> | Fee Management System
        </div>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>
<?php } ?>