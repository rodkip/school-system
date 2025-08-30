<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
header('location:logout.php');
} else{
$messagestate=false;
$mess="";
$dobestimate=date('Y-m-d',strtotime('-5 year'));
// Include the functions file
$prefix = "11111"; // The desired prefix
require_once 'randomidno.php';
//Post the new records
if (isset($_POST['submit'])) {
try {
$idno = $_POST['idno'];
if (empty($idno) || isIdNoDuplicate($idno)) {
// Generate a random unique idno with prefix
$idno = generateUniqueRandomIdNo($prefix);
// Check if the generated idno already exists in the database
while (isIdNoDuplicate($idno)) {
// If the generated idno is a duplicate, generate a new random number with prefix
$idno = generateUniqueRandomIdNo($prefix);
}
}
// Process the remaining form data
$staffname = $_POST['staffname'];
$gender = $_POST['gender'];
$contact = $_POST['contact'];
$mpesano = $_POST['mpesano'];
$mpesaname = $_POST['mpesaname'];
$emailaddress = $_POST['emailaddress'];
$postaladdress = $_POST['postaladdress'];
$maritalstatus = $_POST['maritalstatus'];
$krapin = $_POST['krapin'];
$residencecounty = $_POST['residencecounty'];
$homecounty = $_POST['homecounty'];
$educationlevel = $_POST['educationlevel'];
$speciality = $_POST['speciality'];
$projectavailability = $_POST['projectavailability'];
$projecttype = $_POST['projecttype'];
$othercomments = $_POST['othercomments'];
$flagged = "No";
$pfpicname = $idno . $staffname;
// Working on the profile picture
if (isset($_FILES['pfpicname']) && $_FILES['pfpicname']['name'] != "") {
// Code to handle profile picture upload
}
// Prepare and execute the SQL statement
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = "INSERT INTO staffdetails (idno, staffname, gender, contact, mpesano, mpesaname, emailaddress, postaladdress, maritalstatus, krapin, residencecounty, homecounty, educationlevel, speciality, projectavailability, projecttype, othercomments, pfpicname, flagged) VALUES (:idno, :staffname, :gender, :contact, :mpesano, :mpesaname, :emailaddress, :postaladdress, :maritalstatus, :krapin, :residencecounty, :homecounty, :educationlevel, :speciality, :projectavailability, :projecttype, :othercomments, :pfpicname, :flagged)";
$query = $dbh->prepare($sql);
$query->bindParam(':idno', $idno, PDO::PARAM_STR);
$query->bindParam(':staffname', $staffname, PDO::PARAM_STR);
$query->bindParam(':gender', $gender, PDO::PARAM_STR);
$query->bindParam(':contact', $contact, PDO::PARAM_STR);
$query->bindParam(':mpesano', $mpesano, PDO::PARAM_STR);
$query->bindParam(':mpesaname', $mpesaname, PDO::PARAM_STR);
$query->bindParam(':emailaddress', $emailaddress, PDO::PARAM_STR);
$query->bindParam(':postaladdress', $postaladdress, PDO::PARAM_STR);
$query->bindParam(':maritalstatus', $maritalstatus, PDO::PARAM_STR);
$query->bindParam(':krapin', $krapin, PDO::PARAM_STR);
$query->bindParam(':residencecounty', $residencecounty, PDO::PARAM_STR);
$query->bindParam(':homecounty', $homecounty, PDO::PARAM_STR);
$query->bindParam(':educationlevel', $educationlevel, PDO::PARAM_STR);
$query->bindParam(':speciality', $speciality, PDO::PARAM_STR);
$query->bindParam(':projectavailability', $projectavailability, PDO::PARAM_STR);
$query->bindParam(':projecttype', $projecttype, PDO::PARAM_STR);
$query->bindParam(':othercomments', $othercomments, PDO::PARAM_STR);
$query->bindParam(':pfpicname', $pfpicname, PDO::PARAM_STR);
$query->bindParam(':flagged', $flagged, PDO::PARAM_STR);
$query->execute();
$messagestate = 'added';
$mess = "New record created";
} catch (PDOException $e) {
echo $row->sql . "<br>" . $e->getmessage();
}
}
}
// ...
//updating a record
if (isset($_POST['update_submit']))
try {
$editedidno=$_POST['editedidno'];
$id=$_POST['id'];
$idno=$_POST['idno'];
$staffname=$_POST['staffname'];
$gender=$_POST['gender']; 
$contact=$_POST['contact'];
$mpesano=$_POST['mpesano'];
$mpesaname=$_POST['mpesaname'];
$emailaddress=$_POST['emailaddress'];
$postaladdress=$_POST['postaladdress']; 
$maritalstatus=$_POST['maritalstatus'];
$krapin=$_POST['krapin'];
$residencecounty=$_POST['residencecounty'];
$homecounty=$_POST['homecounty'];
$educationlevel=$_POST['educationlevel'];
$speciality=$_POST['speciality'];
$projectavailability=$_POST['projectavailability'];
$projecttype=$_POST['projecttype'];
$othercomments=$_POST['othercomments'];
$pfpicname=$idNo.$staffname;
//working on profile pic
if (($_FILES['pfpicname']['name']!="")){
// Where the file is going to be stored
$target_dir = "pfpics/";
$file = $_FILES['pfpicname']['name'];
$path = pathinfo($file);
$filename = $path['filename'];
$ext = $path['extension'];
$temp_name = $_FILES['pfpicname']['tmp_name'];
$path_filename_ext = $target_dir.$pfpicname.".".$ext;
// Check if file already exists
$allowed = array('gif', 'png', 'jpg', 'jpeg');  
if (!in_array($ext, $allowed)) {
}
else{
if (file_exists($path_filename_ext)) {
unlink($path_filename_ext);
move_uploaded_file($temp_name,$path_filename_ext);
}else{
move_uploaded_file($temp_name,$path_filename_ext);
echo "Congratulations! File Uploaded Successfully.";
}
}
}
//End working on profile pic
//Updating registration records
$dbh->query("UPDATE staffdetails SET idno='$idno',staffname='$staffname',gender='$gender',contact='$contact',mpesano='$mpesano',mpesaname='$mpesaname',emailaddress='$emailaddress',postaladdress='$postaladdress',maritalstatus='$maritalstatus',krapin='$krapin',residencecounty='$residencecounty',homecounty='$homecounty',educationlevel='$educationlevel',speciality='$speciality',projectavailability='$projectavailability',projecttype='$projecttype',othercomments='$othercomments', pfpicname='$path_filename_ext' WHERE id=$id");
$_SESSION['message']="Record has been UPDATED!!";
$messagestate='added';
$mess="Record updated!!";
}
catch (PDOException $e)
{
echo $sql."<br>".$e->getmessage();
}
//end updating a record
//delete a record
if (isset($_GET['delete']))
try {
$id=$_GET['delete'];
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$sql="DELETE FROM staffdetails WHERE id=$id";
$dbh->exec($sql);
$messagestate='deleted';
$mess="Record Deleted!!";
}
catch (PDOException $e)
{
echo $sql."<br>".$e->getmessage();
}
//end delete a record
//Flag staff
if (isset($_POST['submit-flag']))
try {
$idno=$_POST['idno'];
$flagged=$_POST['flagged'];
$flaggedtype=$_POST['flaggedtype'];
$flagreason=$_POST['flagreason'];
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$dbh->query("UPDATE staffdetails SET flagged='$flagged',flaggedtype='$flaggedtype',flaggedreason='$flagreason' WHERE idno=$idno");
$messagestate='deleted';
$mess="The field-staff Flagged!!";
}
catch (PDOException $e)
{
echo $sql."<br>".$e->getmessage();
}
//end flag staff
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Staff Details
    </title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js">
    </script>
    <script type="text/javascript">
      $(document).ready(function(){
        $('#dataTables-example td.y_n').each(function(){
          if ($(this).text() == 'Yes') {
            $(this).css('background-color','#f00');
          }
        }
                                            );
      }
                       );
    </script>
    <style>
      .y_n[data-title]:hover::after {
        content: attr(data-title);
        position: absolute;
        background-color: #f9f9f9;
        color: #f00;
        /* Change color to red */
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        word-wrap: break-word;
        /* Add word-wrap property */
      }
   
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js">
    </script>
    <script>
      $(document).ready(function() {
        $('#dataTables-example td.y_n').each(function() {
          var text = $(this).text().trim().toLowerCase();
          console.log(text);
          // Output the trimmed and lowercase text for debugging
          if (text === 'no') {
            $(this).addClass('yes');
          }
        }
                                            );
      }
                       );
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
          <!-- page header -->
          <div class="col-lg-12">
            <br>
            <table>
              <tr> 
                <td width="100%">
                  <h1 class="page-header">View Staff-Details 
                  </h1>
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
            </table>
          </div>
          <!--end page header -->
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="row">
              <div class="col-lg-12">
                <!-- Advanced Tables -->
                <div class="panel panel-default">
                  <div class="panel-body">
                    <?php include('newflag-staffpopup.php');?>      
                    <a href="#myModal1" data-toggle="modal" class="btn btn-danger">
                      <i class="fa  fa-ban">
                      </i>  FLAG 
                    </a>
                    <div class="table-responsive" style="overflow-x: auto; width: 100%">
                      <form>
                        <br>
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example" style="font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; white-space: nowrap">
                          <thead>
                            <tr>
                              <th>#
                              </th>                                            
                              <th>Name
                              </th>                                            
                              <th>Flagged
                              </th>                                                                                                                                
                              <th>Contact
                              </th>                           
                              <th>ResidenceCounty
                              </th>
                              <th>HomeCounty
                              </th>
                              <th>Speciality
                              </th>
                              <th>IdNo
                              </th>
                              <th>
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
$sql="SELECT * FROM staffdetails order by staffdetails.id desc";
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
                              <td width="15%">
                                <?php echo htmlentities($row->staffname);?>
                              </td>
                              <td class="y_n" data-title="<?php echo htmlentities($row->flaggedtype);?>: <?php echo htmlentities($row->flaggedreason);?>">
                                <?php echo htmlentities($row->flagged);?>
                              </td>                                                                        
                              <td>
                                <?php echo htmlentities($row->contact);?>
                              </td>
                              <td>
                                <?php echo htmlentities($row->residencecounty);?>
                              </td>                    
                              <td>
                                <?php echo htmlentities($row->homecounty);?>
                              </td>
                              <td>
                                <?php include('viewotherstaffdetailspopup.php');?>
                                <?php echo htmlentities($row->speciality);?>
                              </td>
                              <td>
                                <?php echo htmlentities($row->idno);?>
                              </td>
                              <td>
                                <a href="#otherstaffdetails<?php echo $cnt; ?>" data-toggle="modal">
                                  <i class="fa fa-bars" aria-hidden="true">
                                  </i>&nbsp;&nbsp;Other-Details
                                </a>
                              </td>
                            </tr>
                            <?php $cnt=$cnt+1;}} ?>  
                          </tbody>
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
            $('#dataTables-example').dataTable();
          }
                           );
        </script>
        <script>
          if (window.history.replaceState){
            window.history.replaceState(null,null,window.location.href);
          }
        </script>
        <script>
          function admnoprojectavailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
              url: "checkadmnoforreg.php",
              data:'idNo='+$("#idNo").val(),
              type: "POST",
              success:function(data){
                $("#user-projectavailability-status1").html(data);
                $("#loaderIcon").hide();
              }
              ,
              error:function (){
              }
            }
                       );
          }
        </script>	
        
<script>
  // Function to toggle the Flag Explanation textarea based on the Flag select option
  function toggleFlagReason() {
    var flaggedSelect = document.getElementById("flaggedSelect");
    var flagReasonTextarea = document.getElementById("flagreason");

    if (flaggedSelect.value === "Yes") {
      flagReasonTextarea.disabled = false;
    } else {
      flagReasonTextarea.disabled = true;
    }
  }

  // Function to fetch the flagged reason from the database based on the selected IdNo
  function fetchFlagReason(idno) {
    // Perform an AJAX request to fetch the flag reason based on the IdNo
    // Replace the code below with your actual AJAX request

    // Example code
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var flagReason = xhr.responseText; // Fetched flag reason

        // Update the Flag Explanation textarea with the fetched flag reason
        document.getElementById("flagreason").value = flaggedReason;
      }
    };

    xhr.open("GET", "flaggedreason.php?idno=" + idno, true); // Replace with the actual PHP file to fetch the flag reason
    xhr.send();
  }

  // Event listener for the "IdNo" input
  document.getElementById("idno").addEventListener("change", function() {
    var idno = this.value; // Selected IdNo

    // Fetch the flagged reason from the database based on the selected IdNo
    fetchFlagReason(idno);
  });
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
