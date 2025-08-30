<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
  header('location:logout.php');
} else {
  $currentfinancialyear = date("Y");
  $messagestate = '';
  $mess = '';
  // To search teamlist
  if (isset($_POST['submit_search'])) {
    $searchprojectfullname = $_POST['projectfullname'];
  }
  // End team search
}


// To pick projectfull name for randomly generated teamlist
if (isset($_POST['generateteamlist'])) {
  $randomlygeneratedteamlistprojectfullname = $_POST['projectfullname'];
}
// End team search



//generate teamsize template
if (isset($_POST['generate_teamsizetemplate'])) {
  // Prefilled data
  $csvData .= "workregion,RAsCount\n";
  $csvData .= "Nairobi,0\n";
  $csvData .= "Lower-Eastern,0\n";
  $csvData .= "Upper-Eastern,0\n";
  $csvData .= "Coast,0\n";
  $csvData .= "North-Eastern,0\n";
  $csvData .= "Central,0\n";
  $csvData .= "Lower-Rift,0\n";
  $csvData .= "Upper-Rift,0\n";
  $csvData .= "Western,0\n";
  $csvData .= "Nyanza,0\n";
  // Set headers for download
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="teamsizetemplate.csv"');
  // Output the CSV data
  echo $csvData;
  exit();
}
//End generating teamsize template
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Staff Details Download </title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link rel="icon" href="images/tabpic.png">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
      #myTable.loading-overlay {
        position: relative;
      }

      #myTable.loading-overlay:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
      }

      #myTable.loading-overlay:after {
        content: "Loading...";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 1.2em;
        z-index: 10000;
      }

      @keyframes tickAnimation {
        0% {
          transform: scale(0);
          opacity: 0;
        }

        50% {
          transform: scale(1.2);
          opacity: 1;
        }

        100% {
          transform: scale(1);
          opacity: 1;
        }
      }

      .ticking-icon {
        animation: tickAnimation 0.5s ease-in-out;
      }
      /* Add margin between rows on small screens */
@media (max-width: 767.98px) {
    .row > div {
        margin-bottom: 15px;
    }
}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  </head>
  <body>
    <!--  wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php'); ?>
        <!-- end navbar top -->
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php'); ?>
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
                                <h1 class="page-header">Staff Details DOWNLOAD </h1>
                            </td>
                            <td>
                            <?php if (has_permission($accounttype, 'download_reports')): ?>  
                                <button onclick="downloadCSV()" class="primary">Download Filtered CSV</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <!--end page header -->
            </div>
            <div class="panel panel-primary">
                <div class="row">
                    <div class="col-lg-12">
                        <!-- Advanced Tables -->
                        <div class="panel panel-default">
                            <div class="panel-body">
                            <div class="form-group">
                              <div class="container-fluid">
                                  <div class="row align-items-center">
                                  <div class="col-12 mb-3">
                                  <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            const filters = [
                                                { id: "staffactivenessFilter", label: "Activeness", color: "#FF5733" },
                                                { id: "staffTitleFilter", label: "Staff Title", color: "#33FF57" },
                                                { id: "staffTierFilter", label: "Staff Tier", color: "#3357FF" },
                                                { id: "staffflagFilter", label: "Flag", color: "#FF33A1" },
                                                { id: "workregionFilter", label: "Work Region", color: "#A133FF" },
                                                { id: "projectAvailabilityFilter", label: "Project Availability", color: "#33FFF5" },
                                                { id: "countryFilter", label: "Country", color: "#FFC733" }
                                            ];

                                            function updateSelectedFilters() {
                                                let selectedFilters = [];

                                                filters.forEach(filter => {
                                                    const selectElement = document.getElementById(filter.id);
                                                    if (selectElement && selectElement.value !== "") {
                                                        const filterText = `${filter.label}: ${selectElement.options[selectElement.selectedIndex].text}`;
                                                        selectedFilters.push(`<span style="color: ${filter.color};">${filterText}</span>`);
                                                    }
                                                });

                                                document.getElementById("selectedFilters").innerHTML = selectedFilters.length > 0 ? selectedFilters.join(" | ") : "None";
                                            }

                                            // Attach event listener to all filter dropdowns
                                            filters.forEach(filter => {
                                                const selectElement = document.getElementById(filter.id);
                                                if (selectElement) {
                                                    selectElement.addEventListener("change", updateSelectedFilters);
                                                }
                                            });

                                            // Call once to initialize if filters are pre-selected
                                            updateSelectedFilters();
                                        });
                                        </script>

                                        <div>
                                            <label style="font-size: 16px; font-weight: bold;">Selected Filters:</label>
                                            <span id="selectedFilters" style="font-size: 16px; color: #004B6E; font-weight: bold;">None</span>
                                        </div>
                                      <!-- Filter by Activeness -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="staffactivenessFilter" style="font-size: 12px;">Filter by Activeness:</label>
                                            <select id="staffactivenessFilter" class="form-control" style="font-size: 12px; background-color: #d5c7dd;">
                                                <option value="">All</option>
                                                <option value="active">Active</option>
                                                <option value="dormant">Dormant</option>
                                            </select>
                                        </div>

                                        <!-- Filter by Staff Title -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="staffTitleFilter" style="font-size: 12px;">Filter by Staff Title:</label>
                                            <select id="staffTitleFilter" class="form-control" style="font-size: 12px; background-color: #cdddc7;">
                                                <option value="">All Titles</option>
                                                <?php
                                                // Custom query to prioritize "Research Assistant" and "Team Leader"
                                                $titlesQuery = "
                                                    SELECT DISTINCT stafftitle 
                                                    FROM staffdetails 
                                                    WHERE stafftitle IS NOT NULL AND stafftitle != '' 
                                                    ORDER BY 
                                                        CASE 
                                                            WHEN stafftitle = 'Research Assistant' THEN 1
                                                            WHEN stafftitle = 'Team Leader' THEN 2
                                                            ELSE 3 
                                                        END, 
                                                        stafftitle
                                                ";
                                                $titlesStmt = $dbh->prepare($titlesQuery);
                                                $titlesStmt->execute();
                                                $titles = $titlesStmt->fetchAll(PDO::FETCH_OBJ);

                                                foreach ($titles as $title) {
                                                    echo '<option value="' . htmlentities($title->stafftitle) . '">' . htmlentities($title->stafftitle) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Filter by Staff Tier -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="staffTierFilter" style="font-size: 12px;">Filter by Staff Tier:</label>
                                            <select id="staffTierFilter" class="form-control" style="font-size: 12px; background-color: #e0d5f0;">
                                                <option value="">All Tiers</option>
                                                <?php
                                                $tiersQuery = "SELECT DISTINCT stafftier FROM staffdetails WHERE stafftier IS NOT NULL AND stafftier != '' ORDER BY stafftier";
                                                $tiersStmt = $dbh->prepare($tiersQuery);
                                                $tiersStmt->execute();
                                                $tiers = $tiersStmt->fetchAll(PDO::FETCH_OBJ);
                                                foreach ($tiers as $tier) {
                                                    echo '<option value="' . htmlentities($tier->stafftier) . '">' . htmlentities($tier->stafftier) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Filter by Flag -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="staffflagFilter" style="font-size: 12px;">Filter by Flag:</label>
                                            <select id="staffflagFilter" class="form-control" style="font-size: 12px; background-color: #f4d1f0;">
                                                <option value="">All Flags</option>
                                                <?php
                                                $flagseverityQuery = "SELECT DISTINCT flagseverity FROM staffdetails WHERE flagseverity IS NOT NULL AND flagseverity != '' ORDER BY flagseverity";
                                                $flagseverityStmt = $dbh->prepare($flagseverityQuery);
                                                $flagseverityStmt->execute();
                                                $flagseverities = $flagseverityStmt->fetchAll(PDO::FETCH_OBJ);
                                                foreach ($flagseverities as $flagseverity) {
                                                    echo '<option value="' . htmlentities($flagseverity->flagseverity) . '">' . htmlentities($flagseverity->flagseverity) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Filter by Work Region -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="workregionFilter" style="font-size: 12px;">Filter by Work Region:</label>
                                            <select id="workregionFilter" class="form-control" style="font-size: 12px; background-color: #c7d7dd;">
                                                <option value="">All Work Regions</option>
                                                <?php
                                                $workregionsQuery = "SELECT DISTINCT workregion FROM staffdetails WHERE workregion IS NOT NULL AND workregion != '' ORDER BY workregion";
                                                $workregionsStmt = $dbh->prepare($workregionsQuery);
                                                $workregionsStmt->execute();
                                                $workregions = $workregionsStmt->fetchAll(PDO::FETCH_OBJ);
                                                foreach ($workregions as $workregion) {
                                                    echo '<option value="' . htmlentities($workregion->workregion) . '">' . htmlentities($workregion->workregion) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Filter by Project Availability -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="projectAvailabilityFilter" style="font-size: 12px;">Filter by Project Availability:</label>
                                            <select id="projectAvailabilityFilter" class="form-control" style="font-size: 12px; background-color: #e3e7d3;">
                                                <option value="">All</option>
                                                <?php
                                                $availabilityQuery = "SELECT DISTINCT projectavailability FROM staffdetails WHERE projectavailability IS NOT NULL AND projectavailability != '' ORDER BY projectavailability";
                                                $availabilityStmt = $dbh->prepare($availabilityQuery);
                                                $availabilityStmt->execute();
                                                $availabilities = $availabilityStmt->fetchAll(PDO::FETCH_OBJ);
                                                foreach ($availabilities as $availability) {
                                                    echo '<option value="' . htmlentities($availability->projectavailability) . '">' . htmlentities($availability->projectavailability) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Filter by Country -->
                                        <div class="col-12 col-sm-3 col-md-4 col-lg-2 mb-3">
                                            <label for="countryFilter" style="font-size: 12px;">Filter by Country:</label>
                                            <select id="countryFilter" class="form-control" style="font-size: 12px; background-color: rgb(200, 162, 201);">
                                                <option value="">All Countries</option>
                                                <?php
                                                $countriesQuery = "SELECT DISTINCT country FROM staffdetails WHERE country IS NOT NULL AND country != '' ORDER BY country";
                                                $countriesStmt = $dbh->prepare($countriesQuery);
                                                $countriesStmt->execute();
                                                $countries = $countriesStmt->fetchAll(PDO::FETCH_OBJ);
                                                foreach ($countries as $country) {
                                                    echo '<option value="' . htmlentities($country->country) . '">' . htmlentities($country->country) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>


                                <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                    <br>
                                    <?php
                                    $sql = "SELECT staffdetails.staffname,staffdetails.dob,staffdetails.educationlevel,staffdetails.speciality,staffdetails.country,staffdetails.contact,staffdetails.projectavailability,staffdetails.emergencycontact,staffdetails.emailaddress,staffdetails.flagseverity,staffdetails.gender,staffdetails.idno,staffdetails.stafftitle,staffdetails.stafftier,staffdetails.workregion,staffdetails.homecounty,staffdetails.residencecounty,staffdetails.googleform, latest_project.projectenddate AS latest_projectenddate 
                                    FROM staffdetails LEFT JOIN (
                                    SELECT 
                                    ple.idno,
                                    MAX(pd.projectenddate) AS projectenddate
                                    FROM projectlistentries ple
                                    INNER JOIN projectdetails pd ON ple.projectfullname = pd.projectfullname
                                    GROUP BY ple.idno
                                    ) AS latest_project ON staffdetails.idno = latest_project.idno
                                    ORDER BY staffdetails.id DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    if ($query->rowCount() > 0) {
                                    ?>
                                        <table class="table table-striped table-bordered table-hover" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th></th>
                                                    <th>Flag</th>
                                                    <th>Availability</th>
                                                    <th>Country</th>
                                                    <th>Name</th>
                                                    <th>Contact</th>
                                                    <th>Email</th>
                                                    <th>Gender</th>
                                                    <th>IdNo</th>
                                                    <th>Title</th>
                                                    <th>Tier</th>
                                                    <th>Home</th>
                                                    <th>Residence</th>
                                                    <th>Workregion</th>
                                                    <th>Form?</th>
                                                    <th>LastProject</th>
                                                    <th>Age</th>
                                                    <th>EduLevel</th>
                                                    <th>Speciality</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                foreach ($results as $row) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt); ?></td>
                                                        <td style="background-color: 
                                                            <?php
                                                            if (!empty($row->latest_projectenddate)) {
                                                                $latestProjectEndDate = new DateTime($row->latest_projectenddate);
                                                                $currentDate = new DateTime();
                                                                $interval = $currentDate->diff($latestProjectEndDate);
                                                                $yearsDifference = $interval->y;
                                                                if ($yearsDifference >= 2) {
                                                                    echo "brown";
                                                                } else {
                                                                    echo "green";
                                                                }
                                                            } else {
                                                                echo "brown";
                                                            }
                                                            ?>">
                                                            <?php
                                                            if (!empty($row->latest_projectenddate)) {
                                                                echo ($yearsDifference >= 2) ? "Dormant" : "Active";
                                                            } else {
                                                                echo "Dormant";
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlentities($row->flagseverity); ?></td>
                                                        <td><?php echo htmlentities($row->projectavailability); ?></td>
                                                        <td><?php echo htmlentities($row->country); ?></td>
                                                        <td><?php echo htmlentities($row->staffname); ?></td>
                                                        <td>
                                                            <?php 
                                                                if (!empty($row->dob) && $row->dob != "0000-00-00") {
                                                                    $dob = DateTime::createFromFormat('Y-m-d', $row->dob);
                                                                    if ($dob) { // Ensure $dob is a valid date
                                                                        $today = new DateTime();
                                                                        $age = $today->diff($dob)->y;
                                                                        echo ($age == 2025) ? "0" : htmlentities($age);
                                                                    }
                                                                } elseif ($row->dob == "0000-00-00") {
                                                                    echo "0";
                                                                }
                                                            ?>
                                                        </td>


                                                        <td><?php echo htmlentities($row->emailaddress); ?></td>
                                                        <td><?php echo htmlentities($row->gender); ?></td>
                                                        <td><?php echo htmlentities($row->idno); ?></td>
                                                        <td><?php echo htmlentities($row->stafftitle); ?></td>
                                                        <td><?php echo htmlentities($row->stafftier); ?></td>
                                                        <td><?php echo htmlentities($row->homecounty); ?></td>
                                                        <td><?php echo htmlentities($row->residencecounty); ?></td>
                                                        <td><?php echo htmlentities($row->workregion); ?></td>
                                                        <td><?php echo htmlentities($row->googleform); ?></td>
                                                        <td><?php echo htmlentities($row->latest_projectenddate); ?></td>
                                                        <td>
                                                            <?php 
                                                                if (!empty($row->dob)) {
                                                                    $dob = new DateTime($row->dob);
                                                                    $today = new DateTime();
                                                                    $age = $today->diff($dob)->y;
                                                                    echo htmlentities($age);
                                                                }
                                                            ?>
                                                        </td>

                                                        <td><?php echo htmlentities($row->educationlevel); ?></td>
                                                        <td><?php echo htmlentities($row->speciality); ?></td>
                                                    </tr>
                                                <?php
                                                    $cnt = $cnt + 1;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php } else {
                                        echo "No records found.";
                                    }
                                    ?>
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
            var table = $('#dataTable').DataTable();

            // Apply staff activeness filter
            $('#staffactivenessFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(1).search(filterValue).draw();
            });
          
            // Apply country filter
            $('#countryFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(4).search(filterValue).draw();
            });

            // Apply staff title filter
            $('#staffTitleFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(10).search(filterValue).draw();
            });

              // Apply flag filter
              $('#staffflagFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(2).search(filterValue).draw();
            });


             // Apply staff tier filter
             $('#staffTierFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(11).search(filterValue).draw();
            });

            // Apply work region filter
            $('#workregionFilter').on('change', function() {
                var filterValue = $(this).val();
                table.column(14).search(filterValue).draw();
            });
                $(document).ready(function() {
            var table = $('#dataTable').DataTable();
            
              // Apply project availability filter
              $('#projectAvailabilityFilter').on('change', function() {
                  var filterValue = $(this).val();
                  table.column(3).search(filterValue).draw();
              });
          });
        });
    </script>
            <script>
          $(document).ready(function() {
            $('#dataTable').dataTable();
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
          // Get today's date
          var today = new Date().toISOString().split('T')[0];
          // Set the value of the input field to today's date
          document.getElementById('dob').value = today;
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
      link.download = 'stafflistactive/dormant.csv';
      link.click();
    }
  </script>
  <script>
function downloadCSV() {
    // Use DataTables API to get the filtered rows and column headers
    var table = $('#dataTable').DataTable();
    var header = table.columns().header().toArray().map(col => col.innerText);
    var rows = table.rows({ search: 'applied' }).data().toArray(); // Get only filtered rows
    var csvData = [];

    // Include column headers as the first row
    csvData.push(header);

    // Loop through filtered rows
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
    link.download = 'filtered_stafflist.csv'; // Adjust the filename as needed
    link.click();
}


  </script>
</body>
</html>