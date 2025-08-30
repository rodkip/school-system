<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
header('location:logout.php');
} else{  
$currentfinancialyear=date("Y");
$messagestate='';
$mess="";
if(isset($_GET['viewprojectfullname']))
{
$viewprojectfullname=$_GET['viewprojectfullname'];
}

}   

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Projects TeamList
    </title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    
  </head>
  <body>
  <div id="wrapper">
        <!-- navbar top --> <?php include_once('includes/header.php');?>
        <!-- end navbar top -->
        <!-- navbar side --> <?php include_once('includes/sidebar.php');?>
        <!-- end navbar side -->
        <!--  page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12"> <br>
                <table>
              <tr> 
                <td width="100%">
                    <br>
                  <h3 class="page-header">Project-TeamList: <span style="color:blue"><?php echo $viewprojectfullname;?></span>
                  </h3>
                </td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;<div class="btn-group pull-right">
                <button type="button" class="btn btn-success" onclick="downloadCSV()">TeamList-Download CSV</button>
                 </td>
                <td>
                  <?php 
echo  $projectphase;
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
                </td>
              </tr>
            </table></div>
                <!--end page header -->
            </div> 
                   
            
       
          <!-- End Form Elements -->
          <div class="panel panel-primary">
            <div class="row">
              <div class="col-lg-12">
                <!-- Advanced Tables -->
                <div class="panel panel-default">
                  <div class="panel-body">
                  <div class="table-responsive" style="overflow-x: auto; width: 100%">
                  <div id="table-wrapper">
                      <br>
                      <table class="table table-striped table-bordered table-hover" id="dataTable">
                        <thead>
                          <tr>
                            <th>#
                            </th>
                            <th>Idno
                            </th>
                            <th>KRAPin
                            </th>
                            <th>MpesaNo
                            </th>
                            <th>Name
                            </th>
                            <th>Contact
                            </th>
                            <th>Title
                            </th>   
                            <th>WorkRegion
                            </th> 
                            <th>ResidenceCounty
                            </th> 
                            <th>AssignedRegion(s)
                            </th>  
                            <th>Ratings
                            </th>                                 
                          </tr>
                        </thead>
                        <tbody>

                          <?php
$sql="SELECT projectlistentries.id,projectlistentries.idno,staffdetails.krapin,staffdetails.workregion,staffdetails.residencecounty,staffdetails.mpesano,projectlistentries.projectfullname,projectlistentries.projectdesignation,projectlistentries.projectcompletion,projectlistentries.region,projectlistentries.ratings,projectlistentries.comments,projectlistentries.id,staffdetails.staffname,staffdetails.contact FROM projectlistentries JOIN staffdetails ON projectlistentries.idno = staffdetails.idno  where projectlistentries.projectfullname='$viewprojectfullname' AND (projectlistentries.projectlistentrystatus IS NULL OR projectlistentries.projectlistentrystatus != 'Replaced') order by staffdetails.staffname asc" ;
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
                            <?php
if ($accounttype == "Supervisor" or $accounttype == "Admin") {
    // Show the menu item for "QC Rating"
    echo "<td>" . htmlentities($row->idno) . "</td>";
} else {
    // Display first two characters and replace the rest with asterisks
    $idnoToShow = substr($row->idno, 0, 2) . str_repeat('*', strlen($row->idno) - 2);
    echo "<td>" . htmlentities($idnoToShow) . "</td>";
}
?>

<?php
if ($accounttype == "Supervisor" or $accounttype == "Admin") {
    // Show the menu item for "QC Rating"
    $krapinToShow = empty($row->krapin) ? "" : htmlentities($row->krapin);
    echo "<td>" . $krapinToShow . "</td>";
} else {
    // Display first two characters and replace the rest with asterisks
    $krapinToShow = empty($row->krapin) ? "" : substr($row->krapin, 0, 2) . str_repeat('*', strlen($row->krapin) - 2);
    echo "<td>" . htmlentities($krapinToShow) . "</td>";
}
?>
                             <?php
if ($accounttype == "Supervisor" or $accounttype == "Admin") {
    // Show the menu item to hide sensitive data
    $mpesanoToShow = empty($row->mpesano) ? "" : htmlentities($row->mpesano);
    echo "<td>" . $mpesanoToShow . "</td>";
} else {
    // Display first two characters and replace the rest with asterisks
    $mpesanoToShow = empty($row->mpesano) ? "" : substr($row->mpesano, 0, 2) . str_repeat('*', strlen($row->mpesano) - 2);
    echo "<td>" . htmlentities($mpesanoToShow) . "</td>";
}
?>

                            <td>
                              <?php echo htmlentities($row->staffname);?>
                            </td>
                            <td>
                              <?php echo htmlentities($row->contact);?>
                            </td>
                            <td>
                              <?php echo htmlentities($row->projectdesignation);?>
                            </td> 
                            <td>
                              <?php echo htmlentities($row->workregion);?>
                            </td>   
                            <td>
                              <?php echo htmlentities($row->residencecounty);?>
                            </td>    
                            <td>
                              <?php echo htmlentities($row->region);?>
                            </td>    
                            <td>
                              <?php echo htmlentities($row->ratings);?>
                            </td>
                            
                          </tr>                 
                          <?php $cnt=$cnt+1;}} ?> 
                          <b>Team Summary: </b> 
                          <span style="color:blue">
                          <?php
$designations = []; // Initialize an array to store the designations

// Assuming you have executed the SQL query and fetched the results into $results

if ($query->rowCount() > 0) {
    foreach ($results as $row) {
        $designation = htmlentities($row->projectdesignation);
        // Add the designation to the array
        $designations[] = $designation;
    }
}

// Count the number of occurrences of each designation
$designationCounts = array_count_values($designations);

// Display the count and designation information
foreach ($designationCounts as $designation => $count) {
    echo $count . "-" . $designation . ", ";
}
?> 
</span>
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

<script>
    // DataTables initialization
    $(document).ready(function() {
      $('#dataTable').DataTable();
    });

    function downloadCSV() {
      // Use DataTables API to get all rows and column headers
      var table = $('#dataTable').DataTable();
      var header = table.columns().header().toArray().map(col => col.innerText);
      var rows = table.rows().data().toArray();
      var csvData = [];

      // Include column headers as the first row
      csvData.push(header);

      // Loop through all rows
      for (var i = 0; i < rows.length; i++) {
        var rowData = Object.values(rows[i]);
        csvData.push(rowData);
      }

      // Convert the CSV data to a blob
      var csvContent = csvData.map(row => row.join(',')).join('\n');
      var blob = new Blob([csvContent], { type: 'text/csv' });

      // Create a link element and trigger a click event to download the CSV file
      var link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'projectteamlist.csv';
      link.click();
    }
  </script>
        </body>
      </html>