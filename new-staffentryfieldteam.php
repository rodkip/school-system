<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|New Staff Details</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
      .blue-text {
        color: blue;
        background: lightskyblue;
      }
      #ageMessage {
      color: red;
    }

    #ageMessage.valid {
      color: green;      
    }
    </style>
  </head>
  <body>

            <h1>Research Plus Africa</h1>           
            <h3>New Staff Entry Form</h3>
      
                 
                    <div background-color="green"> <?php echo $_SESSION['message']; 
       unset($_SESSION['message'])
    ?> 
    <form method="post" enctype="multipart/form-data" action="manage-staffdetails.php">
    <div class="form-group">
      
        <div class="panel panel-primary">
          <!-- Your form content for Basic Information -->
          <table class="table" style="font-size: large;">
            <tr class="blue-text">
            <td><strong style="font-size: larger;">Personal Details</strong></td>
            </tr>
            <tr>
              <td>
              <label for="staffname">Staff Full Names:*</label> <input type="text" class="form-control" name="staffname" id="staffname" required="required" placeholder="Enter Staff name" value="" autofocus required="required">
              </td>
            </tr>
            <tr>
              <td>
              <label for="idno">Id No:*</label><input type="text" name="idno" id="idno" placeholder="Enter Idno here" value="" class="form-control" required="required" onBlur="idnoregavailability()">
                <span id="idnoavailabilitystatus" style="font-size:12px;"></span>
              </td>
            </tr>
            <tr>
              <td>
              <label for="stafftitle">Staff-Title:*</label><input type="text" class="form-control" name="stafftitle" id="stafftitle" placeholder="Select Staff Title here" value="" list="projectdesignation-list">
                <datalist id="projectdesignation-list"> <?php
                        $smt=$dbh->prepare('SELECT * from projectdesignation order by projectdesignation asc');
                        $smt->execute();
                        $data=$smt->fetchAll();
                        ?> <?php foreach ($data as $rw):?> <option value="<?=$rw["projectdesignation"]?>"> <?=$rw["projectdesignation"]?> </option> <?php endforeach ?> </datalist>
              </td>
            </tr>
            <tr>
              <td>
              <label for="emailaddress">Email Address:</label><input type="email" class="form-control" name="emailaddress" id="emailaddress" placeholder="Enter emailaddress here" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="krapin">KRA Pin:*</label><input type="text" class="form-control" name="krapin" id="krapin" placeholder="Enter krapin No" value="" required="required">
              </td>
            </tr>
            <tr>
              <td>
              <label for="gender">Gender:</label><select name="gender" value="" class="form-control">
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="maritalstatus">Marital Status:</label><select name="maritalstatus" value="" class="form-control">
                  <option value="Single">Single</option>
                  <option value="Married">Married/Domestic Patnership</option>
                  <option value="Separated/Divorced">Separated/Divorced</option>
                  <option value="Widowed">Widowed</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="dob">DOB:</label><input type="date" class="form-control" name="dob" id="dob" placeholder="Enter DOB here" oninput="checkAge()">
<div id="ageMessage"></div>
</td>
            </tr>
            <tr>
              <td>
              <label for="Disability">Disability?:</label><input type="radio" name="disability" value="No" id="disability_no" checked>
                <label for="disability_no">No</label>
                <input type="radio" name="disability" value="Yes" id="disability_yes">
                <label for="disability_yes">Yes</label>
                <br>
                <label for="disabilitytype">Select disability type:</label> <?php
$smt=$dbh->prepare('SELECT * from disabilities order by id asc');
$smt->execute();
$data=$smt->fetchAll();
?> <select name="disabilitytype" id="disabilitytype" value="" class="form-control" disabled>
                  <option value="" disabled selected>Select DisabilityType </option> <?php foreach ($data as $rw):?> <option value="
                                        
                                      <?=$rw["disabilitytype"]?>"> <?=$rw["disabilitytype"]?> </option> <?php endforeach ?>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="image">Profile Picture:</label><input type="file" name="pfpicname" title="Profile Image" class="form-control">
              </td>
            </tr>
            <tr>
              <td>
              <label for="contact">Phone Number:</label><input type="tel" class="form-control" name="contact" id="contact" placeholder="Enter contact here" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="emergencycontact">Alternative Contact:</label><input type="text" class="form-control" name="emergencycontact" id="emergencycontact" placeholder="Enter alternate-contact here" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="postaladdress">Postal Address:</label><input type="text" name="postaladdress" id="postaladdress" placeholder="Enter postaladdress here" value="" class="form-control">
                <span id="user-availability-status1" style="font-size:12px;"></span>
              </td>
            </tr>
            <tr class="blue-text">
            <td><strong style="font-size: larger;">Projects Availabilty:</strong></td>
            </tr>
            <tr>
              <td>
              <label for="projectavailability">Availability:</label><select name="projectavailability" value="" class="form-control">
                  <option value="Fully">Fully</option>
                  <option value="Partially">Partially</option>
                  <option value="NotAvailable">Not-Available</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="projecttype">Project Type:</label><select name="projecttype" value="" class="form-control">
                  <option value="Quant">Quantitative Only</option>
                  <option value="Qual">Qualitative Only</option>
                  <option value="Both">Both Quant and Qual</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="projectmode">Project Mode:</label><select name="projectmode" value="" class="form-control">
                  <option value="Field">Field Only</option>
                  <option value="Telephonic">Telephonic Only</option>
                  <option value="Both">Both Field and Telephonic</option>
                </select>
              </td>
            </tr>
            <tr class="blue-text">
              <td>Mpesa Details:</td>
            </tr>
            <tr>
              <td>
              <label for="mpesano">MpesaNO:</label><input type="text" class="form-control" name="mpesano" id="mpesano" placeholder="Enter mpesano here" value="">
              </td>
            <tr>
              <td>
              <label for="mpesaname">MpesaName:</label><input type="text" class="form-control" name="mpesaname" id="mpesaname" placeholder="Enter mpesaname here" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="mpesaidno">MpesaIdNo:</label><input type="text" class="form-control" name="mpesaidno" id="mpesaidno" placeholder="Enter mpesaidno here" value="" onBlur="checkIdnoMpesaidnoMatch()">
<span id="mpesaidnoMatchStatus" style="font-size:12px;"></span>
</td>
            </tr>
            <tr class="blue-text">
            <td><strong style="font-size: larger;">Emergency Contacts:</strong></td>
            </tr>
            <tr>
              <td>
              <label for="nextofkinname">Contact 1-Name:</label><input type="text" class="form-control" name="nextofkinname" id="nextofkinname" placeholder="Emergency Contact 1-Name" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="nextofkincontact">Contact 1-Phone No.:</label><input type="tel" class="form-control" name="nextofkincontact" id="nextofkincontact" placeholder="Emergency Contact 1 Phone No." value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="nextofkinrelation">Contact 1-Relationship:</label><input type="text" class="form-control" name="nextofkinrelation" id="nextofkinrelation" placeholder="Emergency Contact 1-Relationship" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="secondnextofkinname">Contact 2-Name:</label><input type="text" class="form-control" name="secondnextofkinname" id="secondnextofkinname" placeholder="Emergency Contact 2-Name" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="secondnextofkincontact">Contact 2-Phone No.:</label><input type="tel" class="form-control" name="secondnextofkincontact" id="secondnextofkincontact" placeholder="Emergency Contact 2-Phone No." value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="secondnextofkinrelation">Contact 2-Relationship:</label><input type="text" class="form-control" name="secondnextofkinrelation" id="secondnextofkinrelation" placeholder="Emergency Contact 2-Relationship" value="">
              </td>
            </tr>
            <tr class="blue-text">
            <td><strong style="font-size: larger;">Beneficiaries </strong></td>
                        </tr>
                        <tr>
                          <td>
                          <label for="beneficiaryname">First-Beneficiary Name:</label><input type="text" class="form-control" name="beneficiaryname" id="beneficiaryname" placeholder="First Beneficiary Name" value="">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="beneficiarycontact">First-Beneficiary Phone No.:</label><input type="tel" class="form-control" name="beneficiarycontact" id="beneficiarycontact" placeholder="First Beneficiary Phone No." value="">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="beneficiaryrelation">First-Beneficiary Relationship:</label><input type="text" class="form-control" name="beneficiaryrelation" id="beneficiaryrelation" placeholder="First Beneficiary Relation" value="">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="beneficiarypercentage">First-Beneficiary Percentage:</label><input type="text" class="form-control" name="beneficiarypercentage" id="beneficiarypercentage" placeholder="First Beneficiary Percentage" value="0">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="secondbeneficiaryname">Second-Beneficiary Name:</label> <input type="text" class="form-control" name="secondbeneficiaryname" id="secondbeneficiaryname" placeholder="Second Beneficiary Name" value="">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="secondbeneficiarycontact">Second-Beneficiary Phone No.:</label><input type="tel" class="form-control" name="secondbeneficiarycontact" id="secondbeneficiarycontact" placeholder="Second Beneficiary Phone No." value="">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="secondbeneficiaryrelation">Second-Beneficiary Relationship:</label><input type="text" class="form-control" name="secondbeneficiaryrelation" id="secondbeneficiaryrelation" placeholder="Second Beneficiary Relation" value="">
                          </td>
                        </tr>
                        <tr>
                          <td>
                          <label for="secondbeneficiarypercentage">Second-Beneficiary Percentage:</label><input type="text" class="form-control" name="secondbeneficiarypercentage" id="secondbeneficiarypercentage" placeholder="Second Beneficiary Percentage" value="0">
                          </td>
                        </tr>
            <tr class="blue-text">
            <td><strong style="font-size: larger;">Work Regions</strong></td>
            </tr>
            <tr>
              <td> 
              <label for="workregion">Work-Region:</label><?php
        $smt = $dbh->prepare('SELECT * from workregion order by workregion asc');
        $smt->execute();
        $data = $smt->fetchAll();
        ?> <select name="workregion" id="workregion" value="" class="form-control" required>
                  <option value="" disabled selected>Select Work-Region</option> <?php foreach ($data as $rw): ?> <option value="<?= $rw["workregion"] ?>"> <?= $rw["workregion"] ?> </option> <?php endforeach ?>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="residencecounty">ResidenceCounty:</label><input type="text" class="form-control" name="residencecounty" id="residencecounty" placeholder="Enter residencecounty" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="residencesublocation">Residence Sublocation:</label><input type="text" class="form-control" name="residencesublocation" id="residencesublocation" placeholder="Enter residencesublocation" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="homecounty">HomeCounty:</label><input type="text" class="form-control" name="homecounty" id="homecounty" placeholder="Enter homecounty" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="homesublocation">Home Sublocation:</label><input type="text" class="form-control" name="homesublocation" id="homesublocation" placeholder="Enter homesublocation" value="">
              </td>
            </tr>
            <tr class="blue-text">
            <td><strong style="font-size: larger;">Education</strong></td>
            </tr>
            <tr>
              <td>
              <label for="educationlevel">Education Level:</label><select name="educationlevel" value="" class="form-control">
                  <option value="Primary">Primary</option>
                  <option value="Secondary">Secondary</option>
                  <option value="Certificate">Certificate</option>
                  <option value="Diploma">Diploma</option>
                  <option value="Degree">Degree</option>
                  <option value="Masters">Masters</option>
                  <option value="PHD">Phd</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="speciality">Course/Speciality:</label> <input type="text" class="form-control" name="speciality" id="speciality" placeholder="Enter speciality" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="languages">Spoken Language(s):</label><input type="text" class="form-control" name="languages" id="languages" placeholder="Enter languages spoken" value="">
              </td>
            </tr>
            <!-- Add a new row for language proficiency -->
            <tr>
              <td>
              <label for="languagepspokenproficiency">Spoken Languages Proficiency:</label><select class="form-control" name="languagespokenproficiency" id="languagespokenproficiency">
                  <option value="Beginner">Beginner</option>
                  <option value="Intermediate">Intermediate</option>
                  <option value="Advanced">Advanced</option>
                  <option value="Native">Native</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="writtenlanguages">Written Language(s):</label><input type="text" class="form-control" name="writtenlanguages" id="writtenlanguages" placeholder="Enter written languages" value="">
              </td>
            </tr>
            <!-- Add a new row for language proficiency -->
            <tr>
              <td>
              <label for="writtenlanguagesproficiency">Written Languages Proficiency:</label><select class="form-control" name="writtenlanguagesproficiency" id="writtenlanguagesproficiency">
                  <option value="Beginner">Beginner</option>
                  <option value="Intermediate">Intermediate</option>
                  <option value="Advanced">Advanced</option>
                  <option value="Native">Native</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
              <label for="keycompetencies">Key Competencies:</label><input type="text" class="form-control" name="keycompetencies" id="keycompetencies" placeholder="Enter key competencies/hobbies/skills" value="">
              </td>
            </tr>
            <tr>
              <td>
              <label for="othercomments">Other Comments:</label><input type="text" class="form-control" name="othercomments" id="othercomments" placeholder="Enter othercomments" value="">
              </td>
            </tr>
          </table>
          <p style="padding-left: 450px">
            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
          </p>
  </form>
       </div>
                  </div>
                  
                </div>
              </div>
              <!-- End Form Elements -->
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
    function checkAge() {
      // Get the input value
      var dobInput = document.getElementById('dob').value;

      // Convert the input value to a Date object
      var dob = new Date(dobInput);

      // Get the current date
      var currentDate = new Date();

      // Calculate the age
      var age = currentDate.getFullYear() - dob.getFullYear();

      // Get the message element
      var ageMessage = document.getElementById('ageMessage');

      // Check if the age is below 18
      if (age < 18) {
        ageMessage.innerHTML = 'The staff must be at least 18 years old. Check the DOB again';
        ageMessage.classList.remove('valid'); // Remove the 'valid' class
      } else {
        ageMessage.innerHTML = 'Valid age!';
        ageMessage.classList.add('valid'); // Add the 'valid' class
        // You can add additional logic here for further processing
      }
    }
  </script>
   <script>
          $(document).ready(function() {
            $('#dataTables-example').dataTable();
          });
        </script>
        <script>
          if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
          }
        </script>
        <script>
          function idnoregavailability() {
            $("#loaderIcon").show();
            // Show a loading icon or animation
            jQuery.ajax({
              url: "checkstaffidno.php", // URL of the PHP script to be called
              data: 'idno=' + $("#idno").val(), // Send the value of an element with the ID "idNo" as the 'idNo' parameter
              type: "POST", // Use the HTTP POST method
              success: function(data) {
                // Function to handle the response on success
                $("#idnoavailabilitystatus").html(data);
                // Update the HTML content of an element with the ID "idnoavailabilitystatus" with the response data
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
          // Add matches Idno with MpesaIdno
  function checkIdnoMpesaidnoMatch() {
    var idnoValue = document.getElementById('idno').value;
    var mpesaidnoValue = document.getElementById('mpesaidno').value;
    var matchStatusElement = document.getElementById('mpesaidnoMatchStatus');

    if (idnoValue === mpesaidnoValue) {
      matchStatusElement.innerHTML = 'This MpesaIdNo is matching the staff IdNo!';
      matchStatusElement.style.color = 'green';
    } else {
      matchStatusElement.innerHTML = 'This MpesaIdNo is not matching the staff IdNo!';
      matchStatusElement.style.color = 'red';
    }
  }
</script>
        <script>
          // Get today's date
          var today = new Date().toISOString().split('T')[0];
          // Set the value of the input field to today's date
          document.getElementById('dob').value = today;
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
    function checkAge() {
      // Get the input value
      var dobInput = document.getElementById('dob').value;

      // Convert the input value to a Date object
      var dob = new Date(dobInput);

      // Get the current date
      var currentDate = new Date();

      // Calculate the age
      var age = currentDate.getFullYear() - dob.getFullYear();

      // Get the message element
      var ageMessage = document.getElementById('ageMessage');

      // Check if the age is below 18
      if (age < 18) {
        ageMessage.innerHTML = 'The staff must be at least 18 years old. Check the DOB again';
        ageMessage.classList.remove('valid'); // Remove the 'valid' class
      } else {
        ageMessage.innerHTML = 'Valid age!';
        ageMessage.classList.add('valid'); // Add the 'valid' class
        // You can add additional logic here for further processing
      }
    }
  </script>
  </body>
</html>