<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'])==0) {
    header('location:logout.php');
} else {
    $academicyear=$_GET['academicyear'];
    if(isset($_POST['submit'])) {
        $financialyear=$_POST['financialyear'];         
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Balance Whole School</title>
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
            <div class="report-title">
            Per Votehead Expenditures Summary Per Year   
        <div class="report-subtitle">
            Financial Year: <b><?php echo $financialyear; ?></b>
        </div>
        </div>
        </div>
            

        <div class="table-container">
        <?php
$sql = "SELECT votehead, financialyear, SUM(amount) AS sumofamount FROM Expendituresdetails GROUP BY votehead, financialyear HAVING financialyear='$financialyear'";
$query = $dbh->prepare($sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;
$sumexpenditure = 0;
if ($query->rowCount() > 0) {
?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Votehead</th>
                <th>Amount (Ksh)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($results as $row) {
            ?>
                <tr>
                    <td><?php echo number_format($cnt); ?></td>
                    <td><?php echo htmlentities($row->votehead); ?></td>
                    <td><?php echo number_format($row->sumofamount); ?></td>
                </tr>
                <?php
                $sumexpenditure += ($row->sumofamount);
                $cnt++;
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><b>Sum Total (Ksh)</b></td>
                <td><b><?php echo number_format($sumexpenditure); ?></b></td>
            </tr>
        </tfoot>
    </table>
<?php
}
?>

        </div>
        <?php
include('reportfooter.php');
?>
    </div>

    <button class="print-button no-print" onclick="window.print()">Print Report</button>
    <a href="javascript:history.back()" class="back-button no-print">Back</a>
</body>
</html>