<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'])==0) {
    header('location:logout.php');
} else {
    $gradefullname=$_GET['gradefullname'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grade Student-List</title>
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
    }
    
    .school-details {
        font-size: 12px;
        color: #7f8c8d;
        line-height: 1.3;
    }
    
    .report-title {
        text-align: center;
        margin: 15px 0;
        font-size: 20px;
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
                Class List
            </div>
            <div class="report-title">
            Grade: <b><?php echo htmlentities($gradefullname); ?></b>
        </div>
        </div>

     

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-left">Learner Name</th>
                        <th class="text-center">Adm No</th>
                        <th class="text-center">Entry Term</th>
                        <th class="text-center">Child Treatment</th>
                        <th class="text-center">Fee Treatment</th>
                        <th class="text-center">Boarding</th>
                        <th class="text-center">Stream</th>
                        <th class="text-center">Home County</th>
                        <th class="text-center">Birth Cert No</th>
                        <th class="text-center">UPI Code</th>
                        <th class="text-center">AssessmentNo</th>
                        <th class="text-center">TransStage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql="SELECT studentdetails.studentname, 
       classentries.studentadmno, 
       classentries.gradefullname, 
       classentries.feetreatment, 
       classentries.childtreatment, 
       classentries.entryterm, 
       classentries.boarding, 
        classentries.stream, 
       studentdetails.homecounty, 
       studentdetails.birthcertno, 
       studentdetails.upicode, 
        studentdetails.assessmentno, 
       transportentries.stagefullname
FROM classentries 
INNER JOIN studentdetails 
    ON classentries.studentadmno = studentdetails.studentadmno
LEFT JOIN transportentries 
    ON classentries.classentryfullname = transportentries.classentryfullname
WHERE classentries.gradefullname = :gradefullname
GROUP BY studentdetails.studentname, 
         classentries.studentadmno, 
         classentries.gradefullname, 
         classentries.feetreatment, 
         classentries.childtreatment, 
         classentries.entryterm, 
         classentries.boarding, 
          classentries.stream, 
         studentdetails.homecounty, 
         studentdetails.birthcertno, 
         studentdetails.upicode,
         studentdetails.assessmentno, 
         transportentries.stagefullname";
                    
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':gradefullname', $gradefullname, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;
                    $totalStudents = 0;
                    
                    if ($query->rowCount() > 0) {
                        foreach ($results as $row) {
                            $totalStudents++;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $cnt; ?></td>
                        <td class="text-left"><?php echo htmlentities($row->studentname); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->studentadmno); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->entryterm); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->childtreatment); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->feetreatment); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->boarding); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->stream); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->homecounty); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->birthcertno); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->upicode); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->assessmentno); ?></td>
                        <td class="text-center"><?php echo htmlentities($row->stagefullname); ?></td>
                    </tr>
                    <?php $cnt++; }} ?>
                    
                    <!-- Summary Row -->
                    <tr class="bold-row">
                        <td colspan="2" class="text-left">Total Learners: <?php echo $totalStudents; ?></td>
                        <td colspan="11" class="text-left">Grade: <?php echo htmlentities($gradefullname); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
     <?php echo date('F j, Y, g:i a'); ?> 
        </div>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>