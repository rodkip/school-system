<?php
   session_start();
   error_reporting(0);
   include('includes/dbconnection.php');
   if (strlen($_SESSION['cpmsaid']==0)) {
     header('location:logout.php');
     } else{
      $mess="";
      $academicyear=date("Y");
      $currentdate=date("Y-m-d");
       $mess="";
       $messagestate='';
       $searchadmno=$_GET['viewstudentadmno'];
       if (!$_GET) $searchadmno="1";
  //delete a record     
       if (isset($_GET['delete']))
       try { 
          //delete a record
           $id=$_GET['delete'];
           $sql ="SELECT studentadmno FROM feepayments WHERE id='$id'";
           $query = $dbh -> prepare($sql);
           $query->execute();
           $results=$query->fetchAll(PDO::FETCH_OBJ);
           $cnt=1;
           if($query->rowCount() > 0)
           {
       foreach($results as $row)
           {
         $searchadmno=$row->studentadmno;
           }}
        
           $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
           $sql="DELETE FROM feepayments WHERE id=$id";
           $dbh->exec($sql);
           $messagestate='deleted';
           $mess="Record Deleted....";
     }
     catch (PDOException $e)
     {
       echo $sql."<br>".$e->getmessage();
     }
//end delete record

//search by admno
     if(isset($_POST['search_submit']))
      {
         $searchadmno= $_POST['searchbyadmno'];           

        }
//end-search by admno

//post a fee payment
        else if(isset($_POST['receivepay_submit']))
   try {
            $receiptno=$_POST['receiptno'];
            $sql="SELECT  * FROM feepayments where receiptno= $receiptno";
            $query = $dbh -> prepare($sql);
            $query->execute();
            $results=$query->fetchAll(PDO::FETCH_OBJ);
            if($query->rowCount() > 0)
         {
             $messagestate='deleted';
             $mess="Payment NOT Posted-DUPLICATE ReceiptNo";
         }else
         {
    
        $studentadmno=$_POST['studentadmno'];
        $receiptno=$_POST['receiptno'];
        $cash=$_POST['cash'];
        $bank=$_POST['bank'];
        $bankpaymentdate=$_POST['bankpaymentdate'];
        $paymentdate=$_POST['paymentdate'];
        $details=$_POST['details'];
        $academicyear=$_POST['academicyear'];
        $cashier=$_POST['username'];
        $receiptcode=$studentadmno.$academicyear;

      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="INSERT INTO feepayments (studentadmno,receiptno,cash,bank,bankpaymentdate,paymentdate,details,academicyear,cashier,receiptcode) VALUES('$studentadmno','$receiptno','$cash','$bank','$bankpaymentdate','$paymentdate','$details','$academicyear','$cashier','$receiptcode')";

      $dbh->exec($sql);
      $messagestate='added';
      $mess="New record created...";
      }
   }
      catch (PDOException $e)
   {
    echo $row->sql."<br>".$e->getmessage();
   }
      $sql ="SELECT studentname FROM studentdetails WHERE studentadmno='$studentadmno'";
      $query = $dbh -> prepare($sql);
      $query->execute();
      $results=$query->fetchAll(PDO::FETCH_OBJ);
      $cnt=1;
      if($query->rowCount() > 0)
      {
  foreach($results as $row)
      {
    $viewstudentname=$row->studentname;
      }
      $academicyear=date("Y");  
      $searchadmno=$studentadmno;    
        }
        else{         
        }

//end-post a fee payment

     ?>
<!DOCTYPE html>
<html>
   <head>
      <title>Kipmetz-SMS|Fee Payment</title>
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
         <?php 
if ($messagestate=='added')
{
    echo '<div class="popup" id="popup" style="background: green;"><i class="fa  fa-check-circle"></i>&nbsp;&nbsp;'; 
                  echo $mess;
                  echo '</div>';
}
else
{
  echo '<div class="popup" id="popup" style="background: rgb(206, 69, 133);"><i class="fa  fa-times"></i>&nbsp;&nbsp;'; 
                  echo $mess;
                  echo '</div>';
}
                 ?>
                          <!-- end messanger -->
            
            <h1 class="page-header">Student Registration Details</h1>
         </div>
         <!-- end  page header -->
         </div>
        <div class="row">
          <div class="col-lg-12">
            <!-- Form Elements -->
  
            <!-- End Form Elements -->
            <div class="panel panel-primary">
              <div class="row">
                <div class="col-lg-12">
                  <!-- Advanced Tables -->
                  <div class="panel panel-default">

                    <div class="panel-body">
                    <?php include('studentsearchpopup.php');?> 
                    <a href="#myModal1" data-toggle="modal" class="btn btn-primary"><i class="fa  fa-list"></i> Search Student Details</a>                        <a href="edit-studentdetails.php?editid=<?php echo htmlentities ($searchadmno);?>"  class="btn btn-success"><i class="fa  fa-edit"></i> Edit-The student Details </a>
                      <div class="table-responsive">
     
<?php
                    $searchquery="SELECT  studentdetails.id,studentdetails.studentadmno,studentdetails.studentname, studentdetails.gender, studentdetails.dateofbirth,studentdetails.parentname,studentdetails.previousschool,studentdetails.birthcertno,studentdetails.assessno,studentdetails.upino,studentdetails.entrydate,parentdetails.parentcontact,parentdetails.homearea,parentdetails.parentname2,studentdetails.admissiondate,studentdetails.pfpicname
                    FROM studentdetails LEFT JOIN parentdetails ON studentdetails.parentname = parentdetails.parentname
                    GROUP BY studentdetails.id,studentdetails.studentadmno,studentdetails.studentname, studentdetails.gender, studentdetails.dateofbirth,studentdetails.parentname,studentdetails.previousschool,studentdetails.birthcertno,studentdetails.assessno,studentdetails.upino,studentdetails.entrydate,parentdetails.parentcontact,parentdetails.homearea,parentdetails.parentname2,studentdetails.admissiondate,studentdetails.pfpicname HAVING studentdetails.studentadmno= $searchadmno";
                    $qry = $dbh -> prepare($searchquery);
                    $qry->execute();
                    $row=$qry->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    if($qry->rowCount() > 0)
                  {
                    foreach($row as $rlt)
                  {   
                
                ?>



            <!-- actual form --> 
            <h3><b>Name: </b><?php echo $rlt->studentname; ?>,&nbsp;&nbsp;<b> AdmNo: </b><?php echo $rlt->studentadmno; ?>, <b>&nbsp;&nbsp; Gender: </b><?php echo $rlt->gender; ?></h3>
            
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">      
                                <div class="table-responsive">            
                               
     
   <font size="3">
                    <table class="table table-striped table-bordered table-hover">
                     
                        <tr>
                           <td style="width:20%">Date Of Birth:</td><td style="width:30%"><b><?php echo $rlt->dateofbirth; ?></b></td>
                           <td rowspan="12" ><img src="<?php echo $rlt->pfpicname; ?>"  width="410" height="410"></td>
                        </tr>
                        <tr>
                           <td>Current Age:</td><td><b style="color:green"><?php $datestring=($rlt->dateofbirth);
                    $age=round((time()-strtotime($datestring))/(3600*24*365.25));
                    echo $age;?>-Years</b></td>
                        </tr>
                        <tr>
                           <td>Parent1 Names:</td><td><b><?php echo $rlt->parentname; ?></b></td>
                        </tr>
                        <tr>
                           <td>Parent2 Names:</td><td><b><?php echo $rlt->parentname2; ?></b></td>
                        </tr>
                        <tr>
                           <td>Parent Contacts:</td><td><b><?php echo $rlt->parentcontact; ?></b></td>
                        </tr>
                        <tr>
                           <td>Previous School:</td><td><b><?php echo $rlt->previousschool; ?></b></td>
                        </tr>
                        <tr>
                           <td>Home Area:</td><td><b><?php echo $rlt->homearea; ?></b></td>
                        </tr>
                        <tr>
                           <td>UPINo:</td><td><b><?php echo $rlt->upino; ?></b></td>
                        </tr>
                        <tr>
                           <td>Assessment No:</td><td><b><?php echo $rlt->assessno; ?></b></td>
                        </tr>
                        <tr>
                           <td>BirthCertNo:</td><td><b><?php echo $rlt->birthcertno; ?></b></td>
                        </tr>
                        <tr>
                           <td>Admission Date:</td><td><b><?php echo $rlt->admissiondate; ?></b></td>
                        </tr>

                        
                     </table>  
                     <b style="color:brown">Grades Attended:</b>
                     <table class="table table-striped table-bordered table-hover">
                           <tr>
                              <th scope="col"></th>
                              <th scope="col">Grade</th>
                              <th scope="col">Stream</th>
                              <th scope="col">EntryTerm</th>
                              <th scope="col">FeeTreatment</th>
                              <th scope="col">ChildTreatment</th>
                              <th scope="col">Boarding</th>
                           </tr>
                              <tbody>
                              <?php
                  $sql="SELECT classdetails.academicyear, classentries.gradefullname, classentries.studentadmno, classentries.feetreatment, classentries.childtreatment, classentries.entryterm,classentries.stream, classentries.boarding
                  FROM classentries INNER JOIN classdetails ON classentries.gradefullname = classdetails.gradefullName
                  GROUP BY classdetails.academicyear, classentries.gradefullname, classentries.studentadmno, classentries.feetreatment, classentries.childtreatment, classentries.entryterm,classentries.stream, classentries.boarding
                  HAVING (((classentries.studentadmno)='$searchadmno')) order by  academicyear desc";
                                    
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);                     
                                    $cnt=1;
                                    if($query->rowCount() > 0)
                                    {
                                    foreach($results as $rww)
                                    {      
                                          ?>  
                                 <tr>
                                    <td><?php echo htmlentities($cnt);?></td>                                    
                                    <td><?php echo htmlentities($rww->gradefullname);?></td>
                                    <td><?php echo htmlentities($rww->stream);?></td>
                                    <td><?php echo htmlentities($rww->entryterm);?></td>
                                    <td><?php echo htmlentities($rww->feetreatment);?></td>
                                    <td><?php echo htmlentities($rww->childtreatment);?></td>
                                    <td><?php echo htmlentities($rww->boarding);?></td>
                                 </tr>
                                 <?php $cnt=$cnt+1;}} ?> 
                              </tbody>
                     </table>
                                    </font>
   </div>
                                        </div> 
                                    </div> 
                                </div> 
                            </div>       
                        </div> 
                    </div>                
            </div> 
</div>
<?php $cnt=$cnt+1;}} ?>  


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
function admnoAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "checkadmno.php",
data:'studentadmno='+$("#studentadmno").val(),
type: "POST",
success:function(data){
$("#user-availability-status1").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}
</script>	
<script>
function receiptnoAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "checkreceiptno.php",
data:'receiptno='+$("#receiptno").val(),
type: "POST",
success:function(data){
$("#receiptno-availability-status1").html(data);
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