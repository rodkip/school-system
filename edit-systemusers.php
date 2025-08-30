<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else
  
  {
    if (isset($_GET['edit'])){
      $id=$_GET['edit'];
      $update=true;
      $sql="SELECT * from  tbladmin where id=$id";
      $query = $dbh -> prepare($sql);
      $query->execute();
      $results=$query->fetchAll(PDO::FETCH_OBJ);
      $cnt=1;
      if($query->rowCount() > 0) 
      {
      foreach($results as $rw)
      {              
      $newfullnames=$rw->fullnames;
      $newaccounttype=$rw->accounttype;
      $newusername=$rw->username;
      $mobilenumber=$rw->mobilenumber;
      $emailaddress=$rw->emailaddress;
      $password=$rw->password;
      $id=$rw->id;
      $messagestate='added';
      $mess="Record on EDIT mode!!";
      $cnt=$cnt+1;
      }}}
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Update System Users Details</title>
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
            <h1 class="page-header">Update System User Details:</h1>
          </div>
          <!--end page header -->
        </div>
        <div class="row">
          <div class="col-lg-12">
            <!-- Form Elements -->
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                  <div class="col-lg-12">
                    <div background-color="green"> <?php echo $_SESSION['message']; 
                            unset($_SESSION['message'])
                          ?>  <form name="changepassword" method="post" onsubmit="return checkpass();" enctype="multipart/form-data" action="manage-userdetails.php"> 
                          <input type="hidden" name="id" value="<?php echo $id; ?>">
                          <table  class="table" width="70%">
                            <tr>
                              <td>
                                <label for="newfullnames">Full Names:
                                </label>
                              </td>
                              <td>
                                <input type="text" name="newfullnames"  class="form-control" required='true' value="<?php echo $newfullnames; ?>">
                              </td>
                              </tr>
                              <tr>
                              <td>
                                <label for="newusername">User Name
                                </label>
                              </td>
                              <td>
                                <input type="text" name="newusername"  class="form-control" required='true' value="<?php echo $newusername; ?>"> 
                              </td>
                              </tr>
                              <tr>
                              <td>
                                <label for="newaccounttype">Account Type:
                                </label>
                              </td>
                              <td>
                                <input type="text" class="form-control" name="newaccounttype" id="newaccounttype" value="<?php echo $newaccounttype; ?>" list="accounttypelist" required='true'>
                                  <datalist id="accounttypelist">                              
                                     <?php
                                        $smt=$dbh->prepare('SELECT * from accounttypes order by accounttype asc');
                                        $smt->execute();
                                        $data=$smt->fetchAll();
                                        ?> 
                                        
                                     <?php foreach ($data as $rw):?> 
                                      
                                        <option value="<?=$rw["accounttype"]?>"> <?=$rw["accounttype"]?> </option> 
                                        <?php endforeach ?> 
                                  </datalist>    
    
                              </td>
                              </tr>
                              <tr>
                              <td>
                                <label for="mobilenumber">Mobile No:
                                </label>
                              </td>
                              <td>
                                <input type="text" name="mobilenumber" value="<?php echo $mobilenumber; ?>"  class="form-control" maxlength='10'  pattern="[0-9]+"> 
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <label for="emailaddress">Email Address:
                                </label>
                              </td>
                              <td>
                                <input type="email" name="emailaddress" value="<?php echo $emailaddress; ?>" class="form-control" required>
                              </td>
                              </tr>
                              <tr>
                              <td>
                                <label for="profilepic">Profile Pic:
                                </label>
                              </td>
                              <td>
                                <input type="file" name="profilepic" value="" class="form-control" >
                              </td>
                              </tr>
                                <tr></tr>
                              <td colspan="2">                               
                                <button type="submit" name="update" class="btn btn-success" action="manage-userdetails.php">Update
                                </button>                              
                              </td>
                            </tr>
                          </table>
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
        document.addEventListener("DOMContentLoaded", function() {
            var currentTimestamp = new Date().toISOString();
            document.getElementById("lastupdatedate").value = currentTimestamp;
        });
    </script>
  </body>
</html>