<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
header('location:logout.php');
} else{
$messagestate=false;
$mess="";
// Your SQL update logic
if (isset($_POST['update_amountsent'])) {
  try {
      // Sanitize inputs
      $welfare_id = htmlspecialchars($_POST['welfare_id']);
      $amount_sent = htmlspecialchars($_POST['amount_sent']);   
      $date_sent = htmlspecialchars($_POST['date_sent']); 
      $mpesano = htmlspecialchars($_POST['mpesano']); 
      $mpesaname = htmlspecialchars($_POST['mpesaname']);
      $mpesa_code = htmlspecialchars($_POST['mpesa_code']);
      $username = htmlspecialchars($_POST['username']);
      $welfare_status = htmlspecialchars($_POST['welfare_status']);  
      $total_contributed = htmlspecialchars($_POST['total_contributed']);  

      // Check if the welfare_id exists
      $sqlCheck = "SELECT 1 FROM welfaredetails WHERE welfare_id = :welfare_id";
      $queryCheck = $dbh->prepare($sqlCheck);
      $queryCheck->bindParam(':welfare_id', $welfare_id, PDO::PARAM_STR);
      $queryCheck->execute();

      if ($queryCheck->rowCount() > 0) {
          // Update record
          $sql = "UPDATE welfaredetails 
                  SET 
                      amount_sent = :amount_sent,                 
                      date_sent = :date_sent,
                       mpesano = :mpesano,
                        mpesaname = :mpesaname,  
                      mpesa_code = :mpesa_code,  
                      username = :username,
                      welfare_status = :welfare_status,  
                      total_contributed = :total_contributed  
                  WHERE welfare_id = :welfare_id";  

          // Prepare and bind parameters
          $query = $dbh->prepare($sql);
          $query->bindParam(':amount_sent', $amount_sent, PDO::PARAM_STR);
          $query->bindParam(':date_sent', $date_sent, PDO::PARAM_STR);
          $query->bindParam(':mpesano', $mpesano, PDO::PARAM_STR); 
          $query->bindParam(':mpesaname', $mpesaname, PDO::PARAM_STR); 
          $query->bindParam(':mpesa_code', $mpesa_code, PDO::PARAM_STR);  
          $query->bindParam(':welfare_id', $welfare_id, PDO::PARAM_STR);
          $query->bindParam(':username', $username, PDO::PARAM_STR);
          $query->bindParam(':welfare_status', $welfare_status, PDO::PARAM_STR);  
          $query->bindParam(':total_contributed', $total_contributed, PDO::PARAM_STR);  

          // Execute query
          $query->execute();

          // Check the number of rows affected
          echo "Rows affected: " . $query->rowCount();

      } else {
          echo "Record with welfare_id $welfare_id not found.";
      }
  } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
  }
}
      // Log the activity
      $activitydescription = "Welfare record for IdNo: $idno has been updated.";
      $username = htmlspecialchars($_POST['username']);
      $sql_log = "INSERT INTO logstable (activitydescription, username) VALUES('$activitydescription', '$username')";
      $dbh->exec($sql_log);

      // Success message
      $messagestate = 'updated';
      $mess = "Welfare record updated successfully.";


 }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz FTD|Welfare Contributions Allocation </title>
    <link rel="icon" href="images/tabpic.png">
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script>
  </head>
  <body>
    <!--  wrapper -->
    <div id="wrapper">
 <!-- navbar top -->
 <?php include_once('includes/header.php');?>
      <!-- end navbar top -->
      <!-- navbar side --> <?php include_once('includes/sidebar.php');?>
      <!-- end navbar side -->
      <!--  page-wrapper -->
      <div id="page-wrapper">
        <div class="row">
          <!-- page header -->
          <div class="col-lg-12">
            <br>
            <table>
              <tr>
                <td width="100%">
                  <h1 class="page-header">Contributions Allocation <i class="fa fa-users" aria-hidden="true"></i></h1>
                </td>
                <td>
                  <button onclick="downloadCSV()">Download CSV</button>
                </td> 
                <td> &nbsp;&nbsp;&nbsp;&nbsp; </td>               
                <td> <?php include_once('updatemessagepopup.php');?> </td>
              </tr>
            </table>
          </div>
          <!--end page header -->
        </div>
     <!-- Project Teamlist View -->
            <div class="panel panel-primary">
              <div class="row">
                <div class="col-lg-12">
                  <!-- Advanced Tables -->
                  <div class="panel panel-default">
                    <div class="panel-body">          
                <div class="table-responsive" style="overflow-x: auto; width: 100%">
                <div id="table-wrapper">
                    <!-- Table loading animation -->
              
  <!-- Table loading animation end-->
  <div id="table-container" >
    <?php
    // Database query
    $sql = "
    SELECT i.*, m.membername,i.mpesano,i.mpesaname
    FROM welfaredetails i
    LEFT JOIN memberdetails m ON i.idno = m.idno
    ORDER BY i.welfare_id DESC
";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) { 
    ?>
   <table class="table table-striped table-bordered table-hover" id="dataTable1">
  <thead>
    <tr>
      <th>#</th>
      <th>Welfare ID</th>
      <th>Description</th>
      <th>MemIdNo</th>
      <th>MemName</th>      
      <th>Target Amount</th>  
      <th>Contributed</th>   
      <th>Admin Fee-(1500)</th>  
      <th>ToBeSent</th>
      <th>Sent</th>
      <th>MpesaNo</th>
      <th>MpesaName</th>
      <th></th>
     
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $cnt = 1;
    foreach ($results as $row) { ?>
      <tr>
        <td><?php echo htmlentities($cnt); ?></td>
        <td><?php echo htmlentities($row->welfare_id); ?></td>
        <td style="white-space: normal; word-wrap: break-word;">
          <?php echo htmlentities($row->welfare_description); ?>
        </td>
        <td><?php echo htmlentities($row->idno); ?></td>
        <td><?php echo htmlentities($row->membername); ?></td>      
        <td><?php echo htmlentities(number_format($row->amount_target)); ?></td>    
        <td>
          <?php
          try {
              // Query the total contributions
              $sql = "SELECT SUM(amount_contributed) as totalContributions FROM contributionsdetails where welfare_id='$row->welfare_id'";
              $query = $dbh->prepare($sql);
              $query->execute();
              $result = $query->fetch(PDO::FETCH_OBJ);
              $totalContributions = $result->totalContributions ?? 0; // Default to 0 if no result
          } catch (Exception $e) {
              $totalContributions = 'Error';
          }
          ?>
          <?php echo htmlentities(number_format($totalContributions)); ?>     
        </td>
        <td><?php echo ($totalContributions <= 1500) ? "0" : htmlentities(number_format(1500)); ?></td>    
        <td><?php echo ($totalContributions <= 1500) ? "0" : htmlentities(number_format($totalContributions - 1500)); ?></td>
        <td><?php echo htmlentities($row->amount_sent);?></td>  
        <td><?php echo htmlentities($row->mpesano); ?></td>
        <td><?php echo htmlentities($row->mpesaname); ?></td>
        <td><?php echo htmlentities($row->welfare_status);?></td>   
                
        <td>
        <a href="perwelfarecontributionsallocation.php?welfare_id=<?php echo htmlentities($row->welfare_id); ?>">
  <button class="btn btn-primary">
    Sent Contribution
  </button>
</a>

        </a>
        </td> 
      </tr>
    <?php 
    $cnt++;
    } ?>
  </tbody>
</table>

    <?php } else {
        echo "No records found.";
    } ?>
  </div>

                      
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
          $(document).ready(function() {
            $('#dataTable1').dataTable();
          });
        </script>
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            var form = document.getElementById("yourFormId"); // Replace with your form ID
            var submitButton = form.querySelector("button[type='submit']"); // Find the submit button

            form.addEventListener("submit", function() {
              // Disable the submit button to prevent double submission
              submitButton.disabled = true;
            });
          });
        </script>
<script>
function downloadCSV() {
    var table = $('#dataTable1').DataTable();

    // Get all column headers, excluding the last two columns
    var header = table.columns().header().toArray().map(function(col, index) {
        return index < table.columns().header().length - 2 ? col.innerText : null;
    }).filter(Boolean);

    // Get all rows data, excluding the last two columns
    var rows = table.rows().data().toArray();
    var csvData = [];

    // Include column headers as the first row
    csvData.push(header);

    // Loop through all rows and create CSV rows, excluding the last two columns
    for (var i = 0; i < rows.length; i++) {
        var rowData = Object.values(rows[i]).slice(0, -2).map(function(value) {
            return `"${String(value).replace(/"/g, '""')}"`; // Properly escape quotes in the data
        });
        csvData.push(rowData);
    }

    // Use the memberName and memberId passed from PHP as the CSV file name
    var csvFileName = `welfarecontributionsbreakdown.csv`;

    // Convert the CSV data to a blob
    var csvContent = csvData.map(function(row) {
        return row.join(',');
    }).join('\n');

    var blob = new Blob([csvContent], { type: 'text/csv' });

    // Create a link element and trigger a click event to download the CSV file
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = csvFileName; // Use the dynamically generated filename
    link.click();
}



          function displaymembername(){
            $("#loaderIcon").show();
            // Show a loading icon or animation
            jQuery.ajax({
              url: "displaymembernamefound.php", // URL of the PHP script to be called
              data: 'idno=' + $("#idno").val(), // Send the value of an element with the ID "idNo" as the 'idNo' parameter
              type: "POST", // Use the HTTP POST method
              success: function(data) {
                // Function to handle the response on success
                $("#displaymembername").html(data);
                // Update the HTML content of an element with the ID "displaymembername" with the response data
                $("#loaderIcon").hide();
                // Hide the loading icon
              },
              error: function() {
                // Function to handle errors (currently empty)
                // You can add error handling code here if needed
              }
            });
          }
        </script>
        <script>
          // Add autofocus to the cancel button and trigger Cancel on Enter key press
          document.addEventListener('DOMContentLoaded', function() {
            var cancelButton = document.getElementsByName('delete')[0];
            cancelButton.focus();
            document.addEventListener('keydown', function(event) {
              if (event.key === 'Enter') {
                event.preventDefault();
                cancelButton.click();
              }
            });
          });
        </script>
      
        <script>
          // Get today's date
          var today = new Date().toISOString().split('T')[0];
          // Set the value of the input field to today's date
          document.getElementById('joineddate').value = today;
        </script>
        <script>
          $(document).ready(function() {
            $("input[name='disabilitytype']").change(function() {
              if ($(this).val() === "Yes") {
                $("select[name='disabilitytype']").prop("disabled", false);
              } else {
                $("select[name='disabilitytype']").prop("disabled", true);
              }
            });
          });
        </script>
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            var disabilityYes = document.getElementById("disability_yes");
            var disabilityTypeSelect = document.getElementById("disabilitytype");
            // Initial check on page load
            toggleDisabilityType(disabilityYes.checked);
            // Add event listener for radio button change
            disabilityYes.addEventListener("change", function() {
              toggleDisabilityType(disabilityYes.checked);
            });

            function toggleDisabilityType(isYesChecked) {
              disabilityTypeSelect.disabled = !isYesChecked;
            }
          });
        </script>

        <script>
          $(document).ready(function() {
            $('#myTabs a').click(function(e) {
              e.preventDefault()
              $(this).tab('show')
            })
          });
        </script> 

  <script>
    // Simulate table loading
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        document.getElementById("spinner").style.display = "none"; // Hide spinner
        document.getElementById("table-container").style.display = "block"; // Show table
      }, 3000); // Adjust delay as per actual loading time
    });
  </script>


        <?php
if ($messagestate=='added' or $messagestate=='deleted'){
echo '
									<script type="text/javascript">
function hideMsg()
{
document.getElementById("popupmessage").style.visibility="hidden";
}
document.getElementById("popupmessage").style.visibility="visible";
window.setTimeout("hideMsg()",5000);
</script>';
}
?>
  </body>
</html>