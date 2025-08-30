<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else
  
  {
    $welfare_id=$_GET['welfare_id']; 
    
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Welfare Contributions Allocate</title>
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
    <!--  wrapper -->
    <div id="wrapper">
      <!-- navbar top --> <?php include_once('includes/header.php');?>
      <!-- end navbar top -->
      <!-- navbar side --> <?php include_once('includes/sidebar.php');?>
      <!-- end navbar side -->
      <!--  page-wrapper -->
      <div id="page-wrapper">
        <div class="row">
          <!-- page header -->
          <div class="col-lg-12">
            <br>
            <h1 class="page-header">Allocate/Sent Contributions:</h1>
          </div>
          <!--end page header -->
        </div>
        <div class="row">
    <div class="col-lg-6">
        <!-- Form Elements -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">

                    <!-- Session message -->
                    <div style="background-color: green;">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                        ?>
                    </div>

                    <form method="post" enctype="multipart/form-data" action="manage-welfareallocation.php">
                        <div class="form-group">

                            <?php
                            $sql = "
                                SELECT i.*, m.membername, m.mpesano, m.mpesaname
                                FROM welfaredetails i
                                LEFT JOIN memberdetails m ON i.idno = m.idno
                                WHERE i.welfare_id = '$welfare_id'
                            ";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                            ?>
                                    <input type="hidden" name="username" value="<?php echo $username ?>">
                                    <input type="hidden" name="welfare_status" value="Closed">

                                    <table class="table">
                                        <!-- Welfare ID -->
                                        <tr>
                                            <td><label for="welfare_id">Welfare_Id:</label></td>
                                            <td><input type="text" class="form-control" name="welfare_id" id="welfare_id" value="<?php echo htmlentities($row->welfare_id); ?>" readonly></td>
                                        </tr>

                                        <!-- Welfare Description -->
                                        <tr>
                                            <td><label for="welfare_description">Welfare Description:</label></td>
                                            <td style="white-space: normal; word-wrap: break-word;">
                                                <input type="text" class="form-control" name="welfare_description" id="welfare_description" value="<?php echo htmlentities($row->welfare_description); ?>" readonly>
                                            </td>
                                        </tr>

                                        <!-- Total Contributions -->
                                        <tr>
                                            <td><label for="totalcontributions">Total Contributions:</label></td>
                                            <td>
                                                <?php
                                                try {
                                                    // Query the total contributions
                                                    $sql = "SELECT SUM(amount_contributed) as totalContributions FROM contributionsdetails WHERE welfare_id = '$welfare_id'";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $result = $query->fetch(PDO::FETCH_OBJ);
                                                    $totalContributions = $result->totalContributions ?? 0; // Default to 0 if no result
                                                } catch (Exception $e) {
                                                    $totalContributions = 'Error';
                                                }
                                                ?>
                                                <input type="text" class="form-control" name="total_contributed" id="total_contributed" value="Ksh <?php echo htmlentities(number_format($totalContributions)); ?>" readonly>
                                            </td>
                                        </tr>

                                        <!-- Admin Fee (10%) -->
                                        <tr>
                                            <td><label for="adminfee">Admin Fee (1500):</label></td>
                                            <td>
                                                <input type="text" class="form-control" name="admin_fee" id="admin_fee" 
                                                value="Ksh <?php echo htmlentities(($totalContributions <= 1500) ? '0' : number_format(1500)); ?>" readonly>
                                            </td>
                                        </tr>
                                        <!-- To Be Sent -->
                                        <tr>
                                            <td><label for="tobesent">To Be Sent:</label></td>
                                            <td>
                                                <input type="text" class="form-control" name="to_be_sent" id="to_be_sent" 
                                                      value="Ksh <?php echo htmlentities(($totalContributions <= 1500) ? '0' : number_format($totalContributions - 1500)); ?>" 
                                                      readonly>
                                            </td>
                                        </tr>

                                        <!-- Amount Sent -->
                                        <tr>
                                            <td><label for="amount_sent">Amount Sent:</label></td>
                                            <td>
                                                <input type="text" class="form-control" name="amount_sent" id="amount_sent" 
                                                      value="<?php echo htmlentities(($totalContributions <= 1500) ? '0' : number_format($totalContributions - 1500)); ?>">
                                            </td>
                                        </tr>

                                        
                                        <!-- Mpesa No -->
                                        <tr>
                                            <td><label for="mpesano">Mpesa No:</label></td>
                                            <td><input type="text" class="form-control" name="mpesano" id="mpesano" value="<?php echo htmlentities($row->mpesano); ?>" required></td>
                                        </tr>

                                        <!-- Mpesa Name -->
                                        <tr>
                                            <td><label for="mpesaname">Mpesa Name:</label></td>
                                            <td><input type="text" class="form-control" name="mpesaname" id="mpesaname" value="<?php echo htmlentities($row->mpesaname); ?>" required></td>
                                        </tr>

                                        <!-- Mpesa Code -->
                                        <tr>
                                            <td><label for="mpesa_code">Reference(Mpesa Code):*</label></td>
                                            <td><input type="text" name="mpesa_code" id="mpesa_code" class="form-control"></td>
                                        </tr>

                                        <!-- Date Sent -->
                                        <tr>
                                            <td><label for="date_sent">Date Sent:</label></td>
                                            <td>
                                                <input type="date" name="date_sent" id="date_sent" class="form-control">
                                                <script>
                                                    // Set today's date in yyyy-mm-dd format
                                                    const today = new Date().toISOString().split('T')[0];
                                                    document.getElementById('date_sent').value = today;
                                                </script>
                                            </td>
                                        </tr>
                                    </table>

                                    <?php
                                    $cnt++;
                                }
                            }
                            ?>

                            <!-- Submit Button -->
                            <p style="padding-left: 450px">
                                <button type="submit" class="btn btn-primary" name="update_amountsent" id="submit">Update</button>
                            </p>
                        </div>
                    </form>
                </div>
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
        document.addEventListener("DOMContentLoaded", function() {
            var currentTimestamp = new Date().toISOString();
            document.getElementById("lastupdatedate").value = currentTimestamp;
        });
    </script>
  </body>
</html>