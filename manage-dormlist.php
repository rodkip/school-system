<?php
   session_start();
   error_reporting(0);
   include('includes/dbconnection.php');
   if (strlen($_SESSION['cpmsaid']==0)) {
     header('location:logout.php');
     } else{
      $dormid=$_GET['dormid'];

?>
<!DOCTYPE html>
<html>
   <head>
      <title>Dorm List/Grade</title>
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
    <!-- end messanger -->
            <h2 class="page-header">Per Dorm/Entries per Grade.   </h2> 
               DORM:
               <span style="color: green;">
            <?php
            // Assuming $dormid is already defined
            $sql_dorm = "SELECT dormitoryname FROM dormitoriesdetails WHERE dormid = :dormid";
            $query_dorm = $dbh->prepare($sql_dorm);
            $query_dorm->bindParam(':dormid', $dormid, PDO::PARAM_INT);
            $query_dorm->execute();
            $dorm = $query_dorm->fetch(PDO::FETCH_OBJ);

            if ($dorm) {
               $dormitoryname = $dorm->dormitoryname;
            } else {
               $dormitoryname = "Unknown"; // Default in case the dormid doesn't exist
            }
            ?>
            <?php echo $dormitoryname; ?>

               </span>        
   
         </div>
         <!-- end  page header -->
      </div>
      <div class="panel panel-primary">
      <div class="panel-body">
         <div class="row">
            <div class="col-lg-12">
                  
               </div>
            </div>
         </div>

            <div class="row">
               <div class="col-lg-12">
                  <!-- Advanced Tables -->
                  
                  <div class="panel panel-default">
                     <div class="panel-body">
                        <div class="table-responsive">
                           <table class="table table-striped table-bordered table-hover" id="dataTables-example1" style="font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif">
                           <thead>
                                 <tr>
                                    <th style="width:3%">#</th>
                                    <th>Grade</th>
                                    <th>Count</th>                                   
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php
                                    $sql="SELECT gradefullname,dorm,Count(studentadmno)as dormentrycount FROM classentries  WHERE dorm='$dormid'  GROUP BY gradefullname ORDER BY gradefullname DESC";
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt=1;
                                    if($query->rowCount() > 0)
                                    {
                                    foreach($results as $row)
                                    {               
                                    ?>
                                 <tr>
                                    <td><?php echo htmlentities($cnt);?></td>
                                    <td><?php echo htmlentities($row->gradefullname);?></td>
                                    <td><?php echo number_format($row->dormentrycount);?></td>
                                 </tr>
                                 <?php $cnt=$cnt+1;}}?> 
                                 </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
                  <!--End Advanced Tables -->
               </div>
            </div>
            <!-- end page-wrapper -->
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