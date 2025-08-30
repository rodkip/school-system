<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else{

//Editing a record
if (isset($_GET['editid'])){
  $id=$_GET['editid'];
  $update=true;
  $sql="SELECT * from  classentries where id=$id";
  $query = $dbh -> prepare($sql);
  $query->execute();
  $results=$query->fetchAll(PDO::FETCH_OBJ);
  $cnt=1;
  if($query->rowCount() > 0) 
  {
      foreach($results as $row)
  {                   
      $gradefullname=$row->gradefullname;
      $feetreatment=$row->feetreatment;
      $childtreatment=$row->childtreatment;
      $studentadmno=$row->studentadmno;
      $entryterm=$row->entryterm;
      $stream=$row->stream;
      $boarding=$row->boarding;
      $feewaiver=$row->feewaiver;
      $childstatus=$row->childstatus;
      $dorm=$row->dorm;
      $id=$row->id;
      $_SESSION['messagestate'] = 'added';
      $_SESSION['mess'] = "Record on EDIT mode!!";
  $cnt=$cnt+1;
  }}}
}
 
     

?>
<!DOCTYPE html>
<html>

<head>
    
    <title>Edit-Grade Entry</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
   <link href="assets/css/style.css" rel="stylesheet" />
      <link href="assets/css/main-style.css" rel="stylesheet" />



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
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Edit Class Entry: <i class="fa fa-file-alt" aria-hidden="true"></i></h2>
                            </td>                           
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
      
                     <div class="panel panel-primary">
                     <div class="row">
                <div class="col-lg-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">                   
                        <div class="panel-body">

                            <div class="table-responsive">                                                           
                               <br>
                              <form method="post" enctype="multipart/form-data" action="manage-classentries.php">         
                                 <div class="form-group">         
                                 <input type="hidden" name="id" value="<?php echo $id; ?>">                       
                                 <table class="table" style="width:100%;">
                                    <tr>
                                       <td style="width:50%; vertical-align:top;">
                                             <label for="studentadmno">Student AdmNo:</label>
                                             <input type="text" class="form-control" name="studentadmno" id="studentadmno" required="required" placeholder="Enter studentadmno here" value="<?php echo $studentadmno; ?>" onBlur="admnoAvailability()" readonly>
                                             <span id="user-availability-status1" style="font-size:12px;"></span>
                                             
                                             <br>
                                             
                                             <label for="gradefullname">Gradefull Name:</label>
                                             <?php
                                             $smt=$dbh->prepare('SELECT gradefullname from classdetails order by gradefullname desc');
                                             $smt->execute();
                                             $data=$smt->fetchAll();
                                             ?>
                                             <select name="gradefullname" value="<?php echo $gradefullname; ?>" class="form-control" required="required"> 
                                                <option value="<?php echo $gradefullname; ?>"><?php echo $gradefullname; ?></option>
                                                <?php foreach ($data as $rw):?>
                                                <option value="<?=$rw["gradefullname"]?>"><?=$rw["gradefullname"]?></option> 
                                                <?php endforeach ?>
                                             </select>
                                             
                                             <br>
                                             
                                             <label for="entryterm">Entry Term:</label>
                                             <select name="entryterm" value="<?php echo $entryterm; ?>" class="form-control"> 
                                                <option value="<?php echo $entryterm; ?>"><?php echo $entryterm; ?></option> 
                                                <option value="Firstterm">1st Term</option> 
                                                <option value="Secondterm">2nd Term</option>
                                                <option value="Thirdterm">3rd Term</option>
                                             </select>
                                             
                                             <br>
                                             
                                             <label for="feetreatment">Fee Treatment:</label>
                                             <select name="feetreatment" value="<?php echo $feetreatment; ?>" class="form-control"> 
                                                <option value="<?php echo $feetreatment; ?>"><?php echo $feetreatment; ?></option>
                                                <option value="Normal">Normal</option>
                                                <option value="Staff">Staff</option>
                                                <option value="Director">Director</option>
                                             </select>
                                             <br>
                                             
                                             <label>Child Status:</label>
                                             <div style="margin-top:5px;">
                                                <label style="margin-right:15px;">
                                                   <input type="radio" name="childstatus" value="Present" checked> Present
                                                </label>
                                                <label style="margin-right:15px;">
                                                   <input type="radio" name="childstatus" value="Gone"> Gone
                                                </label>
                                                <label>
                                                   <input type="radio" name="childstatus" value="Suspended"> Suspended
                                                </label>
                                             </div>

                                       </td>
                                       
                                       <td style="width:50%; vertical-align:top;">
                                             <label for="childtreatment">Child Treatment:</label>
                                             <select name="childtreatment" value="<?php echo $childtreatment; ?>" class="form-control"> 
                                                <option value="<?php echo $childtreatment; ?>"><?php echo $childtreatment; ?></option> 
                                                <option value="1stChild">1st Child</option> 
                                                <option value="2ndChild">2nd Child</option>
                                                <option value="3rdChild">3rd Child</option>
                                                <option value="4thChild">4th Child</option> 
                                                <option value="5thChild">5th Child</option>
                                                <option value="6thChild">6th Child</option>
                                                <option value="7thChild">7th Child</option> 
                                                <option value="8thChild">8th Child</option>
                                                <option value="9thChild">9th Child</option>
                                                <option value="10thChild">10th Child</option>
                                             </select>
                                             
                                             <br>
                                             
                                             <label for="stream">Class Stream:</label>
                                             <?php
                                             $smt=$dbh->prepare('SELECT streamname from streams order by streamname asc');
                                             $smt->execute();
                                             $data=$smt->fetchAll();
                                             ?>
                                             <select name="stream" value="<?php echo $stream; ?>" class="form-control"> 
                                                <option value="<?php echo $stream; ?>"><?php echo $stream; ?></option>
                                                <?php foreach ($data as $rw):?>
                                                <option value="<?=$rw["streamname"]?>"><?=$rw["streamname"]?></option> 
                                                <?php endforeach ?>
                                             </select>
                                             
                                             <br>
                                             
                                             <label for="boarding">Boarding?:</label>
                                             <select id="boarding" name="boarding" value="<?php echo $boarding; ?>" class="form-control" onchange="showdiv()"> 
                                                <option value="<?php echo $boarding; ?>"><?php echo $boarding; ?></option> 
                                                <option value="Day">Day</option> 
                                                <option value="Border">Border</option>
                                             </select>
                                             
                                             <div id="boardinghide" style="display: none;">
                                                <label for="dorm">Dormitory:</label>
                                                <?php
                                                $smt=$dbh->prepare('SELECT dormitoryname from dormitoriesdetails order by dormitoryname desc');
                                                $smt->execute();
                                                $data=$smt->fetchAll();
                                                ?>
                                                <select name="dorm" value="<?php echo $dorm; ?>" class="form-control"> 
                                                   <option value="<?php echo $dorm; ?>"><?php echo $dorm; ?></option>
                                                   <?php foreach ($data as $rw):?>
                                                   <option value="<?=$rw["dormitoryname"]?>"><?=$rw["dormitoryname"]?></option> 
                                                   <?php endforeach ?>
                                                </select>
                                             </div>
                                             
                                             <br>
                                             
                                             <label for="feewaiver">Fee Waiver?:</label>
                                             <select id="feewaiver" name="feewaiver" value="<?php echo $feewaiver; ?>" class="form-control"> 
                                                <option value="<?php echo $feewaiver; ?>"><?php echo $feewaiver; ?></option> 
                                                <option value="No">No</option> 
                                                <option value="Yes">Yes</option>
                                             </select>

                                             <br>
                                             <div>
                                               <style>
                                                   .wide-btn {
                                                      width: 300px;
                                                      padding: 8px 0; /* Optional: Adjust padding for better proportions */
                                                   }
                                                </style>
                                                   <button type="submit" name="update" class="btn btn-primary wide-btn">Update</button>                                               
                                             </div>
                                       </td>
                                    </tr>
                                 </table>                          
                              </form>                                
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page-wrapper -->

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
            $('#dataTables-example').dataTable();
        });
    </script>
    <script>
    if (window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }
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
<!--toggle dorm select-->
<script type="text/javascript">
function showdiv(){
  getselectvalue=document.getElementById("boarding").value;
  if(getselectvalue=="Boarding"){
    document.getElementById('boardinghide').style.display="block";
  }else{
    document.getElementById('boardinghide').style.display="none";
  }
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
