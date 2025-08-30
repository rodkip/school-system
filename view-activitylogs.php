<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
header('location:logout.php');
} else{  



}   

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Activities Logs
    </title>
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
          <!-- page header -->
          <div class="col-lg-12">
            <!---messanger-->              
            <table>
              <tr> 
                <td width="100%">
                    <br>
                  <h1 class="page-header">System Activities Logs:
                  </h1>
                </td>
                
              </tr>
            </table>
            <!-- end messanger --> 
          </div>
          <!-- End Form Elements -->
          <div class="panel panel-primary">
            <div class="row">
              <div class="col-lg-12">
                <!-- Advanced Tables -->
                <div class="panel panel-default">
                  <div class="panel-body">
                  <div class="table-responsive" style="overflow-x: auto; width: 100%">
                      <br>
                      <table class="table table-striped table-bordered table-hover" id="dataTable">
                        <thead>
                          <tr>
                            <th>S.NO
                            </th>
                            <th>Entry Date
                            </th>
                            <th> Activity
                            </th> 
                            <th> UserName
</th>                       
                          </tr>
                        </thead>
                        <tbody>

                          <?php
$sql="SELECT * from logstable order by entryid desc" ;
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
                          <tr>
                            <td>
                              <?php echo htmlentities($cnt);?>
                            </td>
                            <td>
                              <?php echo htmlentities($row->entrydate);?>
                            </td>
                            <td>
                              <?php echo htmlentities($row->activitydescription);?>
                            </td>  
                            <td>
                              <?php echo htmlentities($row->username);?>
                            </td>                       
                          </tr>                 
                          <?php $cnt=$cnt+1;}} ?>                          
                        </tbody>
                      </table>
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
        <script src="assets/plugins/jquery-1.10.2.js">
        </script>
        <script src="assets/plugins/bootstrap/bootstrap.min.js">
        </script>
        <script src="assets/plugins/metisMenu/jquery.metisMenu.js">
        </script>
        <script src="assets/plugins/pace/pace.js">
        </script>
        <script src="assets/scripts/siminta.js">
        </script>
        <!-- Page-Level Plugin Scripts-->
        <script src="assets/plugins/dataTables/jquery.dataTables.js">
        </script>
        <script src="assets/plugins/dataTables/dataTables.bootstrap.js">
        </script>
        <script>
          $(document).ready(function () {
            $('#dataTable').dataTable();
          }
                           );
        </script>
        <script>
          if (window.history.replaceState){
            window.history.replaceState(null,null,window.location.href);
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
<script src="tableExport/tableExport.js"></script>
<script type="text/javascript" src="tableExport/jquery.base64.js"></script>
<script src="js/export.js"></script>
        </body>
      </html>