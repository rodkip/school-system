<?php
   session_start();
   error_reporting(0);
   include('includes/dbconnection.php');
   if (strlen($_SESSION['cpmsaid']==0)) {
     header('location:logout.php');
     } else{
      $payrollserialno= $_GET['viewid'];
      //adding new record
   if(isset($_POST['submit']))
   {
      $staffidno=$_POST['staffidno'];
      $payrollserialno=$_POST['payrollserialno'];
      $basicpay=$_POST['basicpay'];
      $houseallowance=$_POST['houseallowance'];
      $respallowance=$_POST['respallowance'];
      $nhifdeduction=$_POST['nhifdeduction'];
      $nssfdeduction=$_POST['nssfdeduction'];
      $teacherswelfarededuction=$_POST['teacherswelfarededuction'];
      $staffwelfarededuction=$_POST['staffwelfarededuction'];
      $feesdeduction=$_POST['feesdeduction'];
      $advancededuction=$_POST['advancededuction'];
      $othersdeduction=$_POST['othersdeduction'];
      
      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="INSERT INTO payrollentriesdetails (staffidno,payrollserialno,basicpay,houseallowance,respallowance,nhifdeduction,nssfdeduction,teacherswelfarededuction,staffwelfarededuction,feesdeduction,advancededuction,othersdeduction) VALUES('$staffidno','$payrollserialno','$basicpay','$houseallowance','$respallowance','$nhifdeduction','$nssfdeduction','$teacherswelfarededuction','$staffwelfarededuction','$feesdeduction','$advancededuction','$othersdeduction')";     

      $dbh->exec($sql);
   
      $_SESSION['messagestate'] = 'added';
      $_SESSION['mess'] = "Records CREATED successfully.";
      
   } 
  
   //searching a record          
   else if(isset($_POST['searchsubmit']))
   {
        $payrollserialno= $_POST['payrollserialno'];
   }
   //updating a record
   else if(isset($_POST['update_submit']))
   {
     $id=$_POST['id']; 
     $staffidno=$_POST['editstaffidno'];
     $payrollserialno=$_POST['editpayrollserialno'];
     $basicpay=$_POST['editbasicpay'];
     $houseallowance=$_POST['edithouseallowance'];
     $respallowance=$_POST['editrespallowance'];
     $nhifdeduction=$_POST['editnhifdeduction'];
     $nssfdeduction=$_POST['editnssfdeduction'];
     $teacherswelfarededuction=$_POST['editteacherswelfarededuction'];
     $staffwelfarededuction=$_POST['editstaffwelfarededuction'];
     $feesdeduction=$_POST['editfeesdeduction'];
     $advancededuction=$_POST['editadvancededuction'];
     $othersdeduction=$_POST['editothersdeduction'];
 
     $dbh->query("UPDATE payrollentriesdetails SET staffidno='$staffidno',payrollserialno='$payrollserialno',basicpay='$basicpay',houseallowance='$houseallowance',respallowance='$respallowance',nhifdeduction='$nhifdeduction',nssfdeduction='$nssfdeduction',teacherswelfarededuction='$teacherswelfarededuction',staffwelfarededuction='$staffwelfarededuction',feesdeduction='$feesdeduction',advancededuction='$advancededuction',othersdeduction='$othersdeduction' WHERE id=$id") or die($dbh->error);

     $_SESSION['messagestate'] = 'added';
     $_SESSION['mess'] = "Records UPDATED successfully.";
   } 
   else
   {}
  //deleting a record
   if (isset($_GET['delete']))
    {
           $id=$_GET['delete'];
   
           $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
           $sql="DELETE FROM payrollentriesdetails WHERE id=$id";
           $dbh->exec($sql);
             
     $_SESSION['messagestate'] = 'deleted';
     $_SESSION['mess'] = "Records DELETED successfully.";
     }
     
     // CSV Download functionality
     if(isset($_POST['download_csv'])) {
      $payrollserialno= $_POST['payrollserialno'];
         if(!empty($payrollserialno)) {
             header('Content-Type: text/csv; charset=utf-8');
             header('Content-Disposition: attachment; filename=payroll_entries_'.$payrollserialno.'.csv');
             
             $output = fopen('php://output', 'w');
             
             // CSV headers
             fputcsv($output, array(
                 '#', 'Name', 'IDNo', 'Title', 'BasicPay', 'HouseAll', 'RespAll', 'GrossPay', 
                 'SHA', 'NSSF', 'TeachersWelfare', 'StaffWelfare', 'Fees', 'Advance', 'Others', 'NetPay'
             ));
             
             // Get data from database
             $sql = "SELECT payrollentriesdetails.id, payrollentriesdetails.entrydate, payrollentriesdetails.staffidno,
                     payrollentriesdetails.payrollserialno, payrollentriesdetails.basicpay, payrollentriesdetails.houseallowance,
                     payrollentriesdetails.respallowance, (basicpay + houseallowance + respallowance) AS grosspay,
                     payrollentriesdetails.nhifdeduction, payrollentriesdetails.nssfdeduction, payrollentriesdetails.teacherswelfarededuction,
                     payrollentriesdetails.staffwelfarededuction, payrollentriesdetails.feesdeduction, payrollentriesdetails.advancededuction,
                     payrollentriesdetails.othersdeduction, staffdetails.staffname, staffdetails.bankaccno, staffdetails.stafftitle,
                     (basicpay + houseallowance + respallowance - advancededuction - feesdeduction - nhifdeduction - nssfdeduction - othersdeduction - teacherswelfarededuction + staffwelfarededuction) AS netpay,
                     payrolldetails.payrollmonth, payrolldetails.payrollyear, payrolldetails.chequeno, payrolldetails.bank, staffdetails.bank
                     FROM payrolldetails
                     INNER JOIN (staffdetails INNER JOIN payrollentriesdetails ON staffdetails.staffidno = payrollentriesdetails.staffidno)
                     ON payrolldetails.payrollserialno = payrollentriesdetails.payrollserialno
                     WHERE payrollentriesdetails.payrollserialno = '$payrollserialno'
                     ORDER BY entrydate DESC";
             
             $query = $dbh->prepare($sql);
             $query->execute();
             $results = $query->fetchAll(PDO::FETCH_OBJ);
             
             $cnt = 1;
             foreach ($results as $row) {
                 fputcsv($output, array(
                     $cnt++,
                     $row->staffname,
                     $row->staffidno,
                     $row->stafftitle,
                     number_format($row->basicpay),
                     number_format($row->houseallowance),
                     number_format($row->respallowance),
                     number_format($row->grosspay),
                     number_format($row->nhifdeduction),
                     number_format($row->nssfdeduction),
                     number_format($row->teacherswelfarededuction),
                     number_format($row->staffwelfarededuction),
                     number_format($row->feesdeduction),
                     number_format($row->advancededuction),
                     number_format($row->othersdeduction),
                     number_format($row->netpay)
                 ));
             }
             
             fclose($output);
             exit();
         }
     }
     ?>
<!DOCTYPE html>
<html>
   <head>
      <title>Kipmetz-SMS|Payroll Entries</title>
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
<script type="text/javascript">     
    function PrintDiv() {    
       var divToPrint = document.getElementById('divToPrint');
       var popupWin = window.open('', '_blank', 'width=500,height=500');
       popupWin.document.open();
       popupWin.document.write('<html><body onload="window.print()">' + divToPrint.innerHTML + '</html>');
        popupWin.document.close();
            }
 </script>
<style>
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            white-space: nowrap;
           border: 1px solid #ddd;
        }
        .table th {
            background-color:rgb(60, 97, 134);
        }
        .dropdown-menu {
            min-width: 120px;
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
               <h2 class="page-header">
                  Payroll Entry Details <i class="fa fa-file-invoice-dollar"></i>
               </h2>

               <table class="table">
                     <tr>
                        <td colspan="4">
                           <form action="manage-payrollentries.php" method="POST" class="form-inline">
                                 <label for="gradefullname" class="mr-2"><strong>Select Payroll Serial No:</strong></label>
                                 <select name="payrollserialno" class="form-control mr-2" required>
                                    <option value="">-- Select Payroll Serial No --</option>
                                    <?php
                                    $smt = $dbh->prepare('SELECT * FROM payrolldetails ORDER BY payrollyear DESC');
                                    $smt->execute();
                                    $data = $smt->fetchAll();
                                    foreach ($data as $row):
                                    ?>
                                       <option value="<?= $row['payrollserialno'] ?>"><?= $row['payrollserialno'] ?></option>
                                    <?php endforeach; ?>
                                 </select>
                                 <button type="submit" name="searchsubmit" class="btn btn-primary">
                                    <i class="fa fa-search"></i> Search
                                 </button>
                           </form>
                        </td>                    
                        <td colspan="4">
                           <form action="manage-payrollentries.php" method="POST">
                                 <?php include('newpayrollentrypopup.php'); ?>
                                 <a href="#myModal" data-toggle="modal" class="btn btn-success mt-2">
                                    <i class="fa fa-plus"></i> New Payroll Entry
                                 </a>
                           </form>
                        </td>
                     </tr>

                     <tr>
                        <td colspan="4">
                           <?php include_once('updatemessagepopup.php'); ?>
                        </td>
                     </tr>
               </table>
            </div>
         </div>

   
   <div class="panel panel-primary">
    <!-- Panel Heading -->
    <div class="panel-heading">
        <h3 class="panel-title">Payroll Entries</h3>
    </div>

    <div class="panel-body">
        <div class="table-responsive" style="overflow-x: auto; width: 100%;">     
        <?php if (!empty($payrollserialno)): ?>
         <h3>Payroll SerialNo:  <span style="color:green"><?php echo $payrollserialno; ?></span></h3>

         <div class="btn-group" style="display: flex; gap: 10px;">
            <!-- Print View Button -->
            <a href="reportpayrollsheet.php?payrollserialno=<?php echo htmlentities($payrollserialno); ?>" 
               data-rel="dialog" data-transition="flip" data-role="button" 
               class="btn btn-primary" style="vertical-align: middle;">
               <i class="fa fa-print"></i> Print View
            </a>
            
            <!-- CSV Download Button -->
            <form method="POST" action="">
               <input type="hidden" name="payrollserialno" value="<?= htmlspecialchars($payrollserialno) ?>">
               <button type="submit" name="download_csv" class="btn btn-success">
                  <i class="fa fa-download"></i> Download CSV
               </button>
            </form>

         </div>
      <?php endif; ?>
      <br>
                  
            <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>IDNo</th>
                        <th>Title</th>
                        <th>BasicPay</th>
                        <th>HouseAll</th>
                        <th>RespAll</th>
                        <th>GrossPay</th>
                        <th>SHA</th>
                        <th>NSSF</th>
                        <th>TeachersWelfare</th>  
                        <th>StaffWelfare</th> 
                        <th>Fees</th>
                        <th>Advance</th>   
                        <th>Others</th>
                        <th>NetPay</th>  
                        <th>Action</th>                              
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT payrollentriesdetails.id, payrollentriesdetails.entrydate, payrollentriesdetails.staffidno,
                            payrollentriesdetails.payrollserialno, payrollentriesdetails.basicpay, payrollentriesdetails.houseallowance,
                            payrollentriesdetails.respallowance, (basicpay + houseallowance + respallowance) AS grosspay,
                            payrollentriesdetails.nhifdeduction, payrollentriesdetails.nssfdeduction, payrollentriesdetails.teacherswelfarededuction,
                            payrollentriesdetails.staffwelfarededuction, payrollentriesdetails.feesdeduction, payrollentriesdetails.advancededuction,
                            payrollentriesdetails.othersdeduction, staffdetails.staffname, staffdetails.bankaccno, staffdetails.stafftitle,
                            (basicpay + houseallowance + respallowance - advancededuction - feesdeduction - nhifdeduction - nssfdeduction - othersdeduction - teacherswelfarededuction + staffwelfarededuction) AS netpay,
                            payrolldetails.payrollmonth, payrolldetails.payrollyear, payrolldetails.chequeno, payrolldetails.bank, staffdetails.bank
                            FROM payrolldetails
                            INNER JOIN (staffdetails INNER JOIN payrollentriesdetails ON staffdetails.staffidno = payrollentriesdetails.staffidno)
                            ON payrolldetails.payrollserialno = payrollentriesdetails.payrollserialno
                            WHERE payrollentriesdetails.payrollserialno = '$payrollserialno'
                            ORDER BY entrydate DESC";
                    
                    $query = $dbh->prepare($sql);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    $cnt = 1;
                    if ($query->rowCount() > 0) {
                        foreach ($results as $row) {
                    ?>
                    <tr>
                        <td><?php echo htmlentities($cnt); ?></td>
                        <td><?php echo htmlentities($row->staffname); ?></td>
                        <td><?php echo htmlentities($row->staffidno); ?></td>
                        <td><?php echo htmlentities($row->stafftitle); ?></td>
                        <td><?php echo number_format($row->basicpay); ?></td>
                        <td><?php echo number_format($row->houseallowance); ?></td>
                        <td><?php echo number_format($row->respallowance); ?></td>
                        <td><b><?php echo number_format($row->grosspay); ?></b></td>
                        <td><?php echo number_format($row->nhifdeduction); ?></td>
                        <td><?php echo number_format($row->nssfdeduction); ?></td>
                        <td><?php echo number_format($row->teacherswelfarededuction); ?></td>
                        <td><?php echo number_format($row->staffwelfarededuction); ?></td>
                        <td><?php echo number_format($row->feesdeduction); ?></td>
                        <td><?php echo number_format($row->advancededuction); ?></td>
                        <td><?php echo number_format($row->othersdeduction); ?></td>
                        <td><b><?php echo number_format($row->netpay); ?></b></td>
                        <td style="padding: 5px">
                            <form method="POST" enctype="multipart/form-data" action="manage-payrollentries.php">
                                <?php include('editpayrollentrypopup.php'); ?>
                            </form>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                    Action <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                                    <li><a href="#myModal<?php echo ($row->id); ?>" data-toggle="modal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Edit</a></li>
                                    <li class="divider"></li>
                                    <li><a href="manage-payslip.php?payslip=<?php echo htmlentities($row->id); ?>" name="payslip"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;Payslip</a></li>
                                    <li class="divider"></li>
                                    <li><a href="manage-payrollentries.php?delete=<?php echo htmlentities($row->id); ?>" onclick="return confirm('You want to delete the record?!!')" name="delete"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        $cnt++;
                        } 
                    } 
                    ?>
                  
                </tbody>
            </table>
        </div>
    </div>
</div>


 </div>
      <!-- end wrapper -->
      <!-- Core Scripts - Include with every page -->
      <script src="assets/plugins/jquery-1.10.2.js"></script>
      <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
      <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
      <script src="assets/plugins/pace/pace.js"></script>
      <script src="assets/scripts/siminta.js"></script>
      <!-- Page-Level Plugin Scripts-->
      <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
      <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
      <script>
         $(document).ready(function () {
             $('#dataTables-example1').dataTable();
             $('#dataTables-example2').dataTable();
             $('#dataTables-example3').dataTable();
         });
      </script>
      <script>
         if (window.history.replaceState){
           window.history.replaceState(null,null,window.location.href);
         }
      </script>
      <script>
         document.getElementById("defaultOpen").click();
      </script>
          <script>
function staffidnoAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "checkstaffidno.php",
data:'staffidno='+$("#staffidno").val(),
type: "POST",
success:function(data){
$("#user-availability-status1").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}
</script>
<?php
      if ($messagestate=='added' or $messagestate=='deleted'){
        echo '<script type="text/javascript">
        function hideMsg()
        {
          document.getElementById("popup").style.visibility="hidden";
        }
        document.getElementById("popup").style.visibility="visible";
        window.setTimeout("hideMsg()",5000);
        </script>';
      }
      ?>	
   </body>
</html>
<?php }?>