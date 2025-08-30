<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
} else {
    $studentadmno = $_GET['studentadmno'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Payments Breakdown</title>
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
            Email: <?php echo htmlentities($rlt->emailaddress); ?>.
        </div>
                </div>
                <?php $cnt=$cnt+1;}} ?>
            </div>
       
        </div>
        Report: Learner Fee Balance/Grades
        <?php       
        $searchadmno= $_POST['searchbyadmno'];
        $searchquery="SELECT * from studentdetails WHERE studentadmno= '$studentadmno'";
        $qry = $dbh -> prepare($searchquery);
        $qry->execute();
        $row=$qry->fetchAll(PDO::FETCH_OBJ);
        $cnt=1;
        if($qry->rowCount() > 0) {
            foreach($row as $rlt) {   
        ?>
        <div class="student-info">
            <div class="info-item">
            <strong>Learner Name:</strong> <?php echo $rlt->studentname; ?><br>
            <strong>Admission No:</strong> <?php echo $rlt->studentadmno; ?><br>
            <strong>Gender:</strong> <?php echo $rlt->gender; ?></div>
        </div>
        <?php $cnt=$cnt+1;}} ?>

        <div class="table-container">
    
            <table>
                <thead>
                    <tr>
                        <th class="text-left">Grade</th>
                        <th>Bal BF</th>
                        <th>Other-Fee</th>
                        <th>1st Term</th>
                        <th>2nd Term</th>
                        <th>3rd Term</th>                      
                        <th>Total Fee</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql="SELECT * FROM feebalances WHERE studentadmno='$studentadmno' ORDER BY gradefullname DESC";
                    $query = $dbh -> prepare($sql);
                    $query->execute();
                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    $arr=0;
                    if($query->rowCount() > 0) {
                        foreach($results as $row) {      
                    ?>      
                    <tr>
                        <td class="text-left"><?php echo htmlentities($row->gradefullname);?></td>
                        <td style="
                            color: <?php 
                                if ($row->arrears == 0) {
                                    echo 'black';
                                } elseif ($row->arrears > 0) {
                                    echo 'red';
                                } else {
                                    echo 'green';
                                }
                            ?>;
                        ">
                            <?php echo number_format($row->arrears); ?>
                        </td>
                        <td><?php echo number_format($row->othersfee);?></td>
                        <td><?php echo number_format($row->firsttermfee);?></td>
                        <td><?php echo number_format($row->secondtermfee);?></td>
                        <td><?php echo number_format($row->thirdtermfee);?></td>                     
                        <td><?php echo number_format(($row->totalfee)+($row->arrears));?></td>
                        <td class="positive"><?php echo number_format($row->totalpaid);?></td>
                        <td style="
                            color: <?php 
                                if ($row->yearlybal == 0) {
                                    echo 'black';
                                } elseif ($row->yearlybal > 0) {
                                    echo 'red';
                                } else {
                                    echo 'green';
                                }
                            ?>;
                        ">
                            <b><?php echo number_format($row->yearlybal); ?></b>
                        </td>

                    </tr>
                    <?php $cnt=$cnt+1;}} ?>
                </tbody>
            </table>
        </div>
        <i>Note: The FEEs Amount may include charges for using school transport.</i><br>
        <?php
        include('reportfooter.php');
        ?>
    </div>
    
    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>
<?php } ?>