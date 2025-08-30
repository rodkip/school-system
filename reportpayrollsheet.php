<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'])==0) {
    header('location:logout.php');
} else {
    $payrollserialno=$_GET['payrollserialno'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payroll Sheet</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
    
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
        .bold-row {
            font-weight: bold !important;
            background-color: #f1f1f1 !important;
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
        margin-bottom: 20px;
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
        text-align: left;
    }
    
    .school-details {
        font-size: 12px;
        color: #7f8c8d;
        line-height: 1.3;
        text-align: left;
    }
    
    .report-title {
        text-align: center;
        margin: 15px 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-bottom: 20px;
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
        vertical-align: middle;
    }
    
    .text-left {
        text-align: left;
    }
    
    .text-center {
        text-align: center;
    }
    
    .bold-row {
        font-weight: bold;
        background-color: #f1f1f1;
    }
    
    .signature-section {
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
    }
    
    .signature-box {
        width: 45%;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
    }
    
    .footer {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e0e0e0;
        font-size: 11px;
        color: #7f8c8d;
        text-align: center;
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
    
</style>
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
                <div class="school-info">
                    <div class="school-name"><?php echo $rlt->schoolname; ?></div>
                    <div class="school-details">
                        Tel: <?php echo htmlentities($rlt->phonenumber); ?><br>
                        <?php echo htmlentities($rlt->postaladdress); ?><br>
                        Email: <?php echo htmlentities($rlt->emailaddress); ?>
                    </div>
                </div>
                <?php $cnt=$cnt+1;}} ?>
            </div>
            <div class="report-title">
                Payroll Sheet
            </div>
        </div>

        <?php
        $sql="SELECT payrollserialno, payrollmonth, payrollyear, chequeno, bank
        FROM payrolldetails WHERE payrollserialno= '$payrollserialno'";
        $query = $dbh -> prepare($sql);
        $query->execute();
        $results=$query->fetchAll(PDO::FETCH_OBJ);
        if($query->rowCount() > 0) {
            foreach($results as $row) {      
        ?>
    <div style="margin-bottom: 20px;">
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <div><strong>Serial No:</strong> <?php echo htmlentities($row->payrollserialno); ?></div>
        <div><strong>Year:</strong> <?php echo htmlentities($row->payrollyear); ?></div>
        <div><strong>Month:</strong> <?php echo htmlentities($row->payrollmonth); ?></div>
        <div><strong>Bank:</strong> <?php echo htmlentities($row->bank); ?></div>
        <div><strong>Cheque No:</strong> <?php echo htmlentities($row->chequeno); ?></div>
    </div>
</div>

        <?php }} ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-left">Name</th>
                        <th class="text-center">Id No</th>
                        <th class="text-center">Bank Acc No</th>
                        <th class="text-center">Basic</th>
                        <th class="text-center">House All</th>
                        <th class="text-center">Resp All</th>
                        <th class="text-center">Gross Pay</th>
                        <th class="text-center">NHIF</th>
                        <th class="text-center">NSSF</th>
                        <th class="text-center">Teachers' Welfare</th>
                        <th class="text-center">Staff Welfare</th>
                        <th class="text-center">Fees Ded</th>
                        <th class="text-center">Advance</th>
                        <th class="text-center">Others</th>
                        <th class="text-center">Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql="SELECT payrollentriesdetails.id, payrollentriesdetails.staffidno, payrollentriesdetails.payrollserialno, 
       payrollentriesdetails.basicpay, payrollentriesdetails.houseallowance, payrollentriesdetails.respallowance, 
       (basicpay + houseallowance + respallowance) AS grosspay, 
       payrollentriesdetails.nhifdeduction, payrollentriesdetails.nssfdeduction, payrollentriesdetails.teacherswelfarededuction,
       payrollentriesdetails.staffwelfarededuction, payrollentriesdetails.feesdeduction, 
       payrollentriesdetails.advancededuction, payrollentriesdetails.othersdeduction, 
       staffdetails.staffname, staffdetails.bankaccno, staffdetails.stafftitle, 
       -- Calculate netpay with COALESCE to handle NULL values
       (basicpay + houseallowance + respallowance 
        - COALESCE(advancededuction, 0) 
        - COALESCE(feesdeduction, 0) 
        - COALESCE(nhifdeduction, 0) 
        - COALESCE(nssfdeduction, 0) 
        - COALESCE(othersdeduction, 0) 
        - COALESCE(teacherswelfarededuction, 0) 
        - COALESCE(staffwelfarededuction, 0)) AS netpay, 
       payrolldetails.payrollmonth, payrolldetails.payrollyear, payrolldetails.chequeno, 
       payrolldetails.bank, staffdetails.bank, staffdetails.staffname
FROM payrolldetails 
INNER JOIN (staffdetails 
            INNER JOIN payrollentriesdetails ON staffdetails.staffidno = payrollentriesdetails.staffidno) 
ON payrolldetails.payrollserialno = payrollentriesdetails.payrollserialno  
WHERE payrollentriesdetails.payrollserialno = '$payrollserialno' 
ORDER BY staffdetails.staffname ASC";
                    $query = $dbh -> prepare($sql);
                    $query->execute();
                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    $sumbasicpay = $sumgrosspay = $sumnetpay = $sumhouseallowance = $sumrespallowance = $sumnhifdeduction = 
                    $sumnssfdeduction = $sumteacherswelfarededuction = $sumstaffwelfarededuction = $sumfeesdeduction = 
                    $sumadvancededuction = $sumothersdeduction = 0;
                    
                    if($query->rowCount() > 0) {
                        foreach($results as $row) {      
                            $sumbasicpay += $row->basicpay;
                            $sumgrosspay += $row->grosspay;
                            $sumnetpay += $row->netpay;
                            $sumhouseallowance += $row->houseallowance;
                            $sumrespallowance += $row->respallowance;
                            $sumnhifdeduction += $row->nhifdeduction;
                            $sumnssfdeduction += $row->nssfdeduction;
                            $sumteacherswelfarededuction += $row->teacherswelfarededuction;
                            $sumstaffwelfarededuction += $row->staffwelfarededuction;
                            $sumfeesdeduction += $row->feesdeduction;
                            $sumadvancededuction += $row->advancededuction;
                            $sumothersdeduction += $row->othersdeduction;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $cnt; ?></td>
                        <td class="text-left"><?php echo htmlentities($row->staffname); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->staffidno); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->bankaccno); ?></td>
                        <td class="text-center"><?php echo number_format($row->basicpay); ?></td>
                        <td class="text-center"><?php echo number_format($row->houseallowance); ?></td>
                        <td class="text-center"><?php echo number_format($row->respallowance); ?></td>
                        <td class="text-center"><?php echo number_format($row->grosspay); ?></td>
                        <td class="text-center"><?php echo number_format($row->nhifdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($row->nssfdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($row->teacherswelfarededuction); ?></td>
                        <td class="text-center"><?php echo number_format($row->staffwelfarededuction); ?></td>
                        <td class="text-center"><?php echo number_format($row->feesdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($row->advancededuction); ?></td>
                        <td class="text-center"><?php echo number_format($row->othersdeduction); ?></td>
                        <td class="text-center"><b><?php echo number_format($row->netpay); ?></b></td>
                    </tr>
                    <?php $cnt++; }} ?>
                    
                    <!-- Summary Row -->
                    <tr class="bold-row">
                        <td colspan="4" class="text-left">Total (Ksh)</td>
                        <td class="text-center"><?php echo number_format($sumbasicpay); ?></td>
                        <td class="text-center"><?php echo number_format($sumhouseallowance); ?></td>
                        <td class="text-center"><?php echo number_format($sumrespallowance); ?></td>
                        <td class="text-center"><?php echo number_format($sumgrosspay); ?></td>
                        <td class="text-center"><?php echo number_format($sumnhifdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($sumnssfdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($sumteacherswelfarededuction); ?></td>
                        <td class="text-center"><?php echo number_format($sumstaffwelfarededuction); ?></td>
                        <td class="text-center"><?php echo number_format($sumfeesdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($sumadvancededuction); ?></td>
                        <td class="text-center"><?php echo number_format($sumothersdeduction); ?></td>
                        <td class="text-center"><?php echo number_format($sumnetpay); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <!-- Accounts Clerk Card -->
    <div style="flex: 1 1 25%; min-width: 280px; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); padding: 15px; border: 2px solid #004B6E;">

            <i class="bi bi-pencil" style="font-size: 24px; color: #004B6E;"></i> Accounts Clerk:<br>
           <p>Date: .......................................Sign:..................................................................................</p> 

    </div>

    <!-- Director/HeadTeacher Card -->
    <div style="flex: 1 1 25%; min-width: 280px; background: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); padding: 15px; border: 2px solid #004B6E;">
       
            <i class="bi bi-building" style="font-size: 24px; color: #004B6E;"></i> Director/HeadTeacher: <br>
            <p>Date: .......................................Sign:..................................................................................</p> 
    </div>
</div>


        <div class="footer">
            Generated on <?php echo date('F j, Y, g:i a'); ?> by <b><?php echo $user  ?></b>
        </div>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>

</body>
</html>