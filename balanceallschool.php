<?php
   session_start();
   error_reporting(0);
   include('includes/dbconnection.php');
   if (strlen($_SESSION['cpmsaid']==0)) {
     header('location:logout.php');
     } else{
             
      if (isset($_POST['submit']))
      {
        $academicyear= $_POST['academicyear'];
        
      }
  
        
     ?>
<!DOCTYPE html>
<html>
   <head>
      <title>Kipmetz-SMS|Per Class Fee Balance</title>
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
         <!--  page header -->
         <div class="col-lg-12">
         <form method="POST">
            <h2 class="page-header">All school Fee payment Analysis: <span style='color:green'><?php echo $academicyear; ?></span><a href="receiptbalanceallschool.php?academicyear=<?php echo htmlentities ($academicyear);?>" data-rel="dialog" data-transition="flip" data-role='button'>Print View</a></h2>
         </form>
         </div>
         <!-- end  page header -->
      </div>
      <div class="panel panel-primary">
      <div class="panel-body">
         <div class="row">
            <div class="col-lg-12">
                              <form action="#" method="POST" >
                  <table class="tr" width="50%">
                     <tr>
                        <td><label for="academicyear">Type AcademicYear and press SEARCH to DISPLAY:  </label></td>
                        <td><input type="text" class="form-control" name="academicyear" placeholder="Academic year" autofocus></td>
                        <td><button type="submit" name="submit" class="btn btn-primary">SEARCH</button></td>
                     </tr>
                  </table>
               </form>

               </div>
            </div>
         </div>
      
<!-- Tab links -->

   <div class="tab" style="border-radius:10px">
      <span style='color:darkblue; font-size:20px'>
      <button class="tablinks" onclick="opentab(event,'feebalance' )" id="defaultOpen" >Balance PerChild</button>
      <button class="tablinks" onclick="opentab(event, 'feepayments')">corona</button>
      <button class="tablinks" onclick="opentab(event, 'feestructure')">corona</button>
      </span>
   </div>

<!-- Tab content -->
<!-- First tab -->        
<div id="feebalance" class="tabcontent">
<div class="row">
               <div class="col-lg-15">
                  <!-- Advanced Tables -->
                  <div class="panel panel-default">
                     
                        <div class="table-responsive">
                           <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                           <thead>
                                 <tr>
                                    <th>#</th>
                                    <th>Year</th> 
                                    <th>Grade</th>                                   
                                    <th>Student Count</th>
                                    <th>Total Arrears</th>
                                    <th>Total Fee</th>
                                    <th>Sum Paid</th>
                                    <th>Total Balance</th>
                                 </tr>
                              </thead>
                              <tbody style="height: 5px;">

                              <?php
            $sql="SELECT classdetails.academicyear, classdetails.gradefullname, Count(feebalances.studentAdmNo) AS countadmno, Sum(feebalances.arrears) AS sumarrears, Sum(feebalances.totalfee) AS sumfee, Sum(feebalances.totalpaid) AS sumpaid, Sum(feebalances.yearlybal) AS sumbal
            FROM feebalances INNER JOIN classdetails ON feebalances.gradefullname = classdetails.gradefullName WHERE academicyear= '$academicyear' GROUP BY classdetails.academicyear, classdetails.gradefullName;"
            ;
            $query = $dbh -> prepare($sql);
            $query->execute();
            $results=$query->fetchAll(PDO::FETCH_OBJ);
            $cnt=1;
            $arr=0;
            if($query->rowCount() > 0)
            {
            foreach($results as $row)
            {      
         ?>   
                                 <tr>
                                    <td><?php echo htmlentities($cnt);?></td>
                                    <td><?php echo htmlentities($row->academicyear);?></td>
                                    <td><?php echo htmlentities($row->gradefullname);?></td>
                                    <td><?php echo htmlentities($row->countadmno);?></td>
                                    <td><?php echo number_format($row->sumarrears);?></td>
                                    <td><?php echo number_format($row->sumfee);?></td>
                                    <td><?php echo number_format($row->sumpaid);?></td>
                                    <td><?php echo number_format($row->sumbal);?></td>
                                 </tr>
                                 <?php $classfeebalance+=($row->yearlybal); ?>
                                 <?php $cnt=$cnt+1;}}?> 
<h5>Overall Grade-Fee Balance: <b><span style='color:green'><?php echo number_format($classfeebalance); ?></span></b></h5>                                </tbody>
                               
                           </table>

                        </div>
                       </div>
  
                  </div>
                  <!--End Advanced Tables -->
               </div>
            <!-- end page-wrapper -->
         </div>
<!-- End of First tab -->


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
   </body>
</html>
<?php }?>