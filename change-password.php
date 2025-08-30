<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
error_reporting(0);
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
} else {
    if (isset($_POST['submit'])) {
        $adminid = $_SESSION['cpmsaid'];
        $cpassword = $_POST['currentpassword'];
        $newpassword = $_POST['newpassword'];

        // Verify current password using bcrypt
        $sql = "SELECT Password FROM tbladmin WHERE id=:adminid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':adminid', $adminid, PDO::PARAM_STR);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $hash = $row['Password'];

        if (password_verify($cpassword, $hash)) {
            // Hash the new password using bcrypt
            $newHash = password_hash($newpassword, PASSWORD_DEFAULT);

            $con = "UPDATE tbladmin SET Password=:newpassword WHERE id=:adminid";
            $chngpwd1 = $dbh->prepare($con);
            $chngpwd1->bindParam(':adminid', $adminid, PDO::PARAM_STR);
            $chngpwd1->bindParam(':newpassword', $newHash, PDO::PARAM_STR);
            $chngpwd1->execute();

            echo '<script>alert("Your password was successfully changed")</script>';
            echo "<script>window.location.href ='logout.php?username=<?php echo htmlentities ($username);?>'</script>";
        } else {
            echo '<script>alert("Your current password is wrong")</script>';
        }
    }

?>
<!DOCTYPE html>
<html>

<head>
    
    <title>Kipmetz-SMS|Change Password</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
   <link href="assets/css/style.css" rel="stylesheet" />
      <link href="assets/css/main-style.css" rel="stylesheet" />

<script type="text/javascript">
function checkpass()
{
if(document.changepassword.newpassword.value!=document.changepassword.confirmpassword.value)
{
alert('New Password and Confirm Password field does not match');
document.changepassword.confirmpassword.focus();
return false;
}
return true;
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
                 <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Change Password</h1>
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
  <form name="changepassword" method="post" onsubmit="return checkpass();" action=""> 
                  
<div class="form-group"> 
    <label for="password">Current Password</label>
    <input type="password" name="currentpassword" id="currentpassword" class="form-control" required="true"> 
</div>

<div class="form-group">
    <label for="newpassword">New Password</label>
    <input type="password" name="newpassword"  class="form-control" required="true"> 
</div>

<div class="form-group"> 
    <label for="confirmpassword">Confirm Password</label>
    <input type="password" name="confirmpassword" id="confirmpassword" value=""  class="form-control" required="true">
</div>
   
     <p style="padding-left: 450px"><button type="submit" class="btn btn-primary" name="submit" id="submit">Change</button></p> </form>
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

</body>

</html>
<?php }  ?>