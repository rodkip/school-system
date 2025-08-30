<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if(isset($_POST['submit']))
  {
      
$email=$_POST['email'];
$mobile=$_POST['mobile'];
$newpassword=md5($_POST['newpassword']);

$sql ="SELECT id,Email FROM tbladmin WHERE Email=:email and MobileNumber=:mobile";

$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)

{
$dbh->query("UPDATE tbladmin SET password='$newpassword' WHERE email=$email") or die($dbh->error);

$messagestate='added';
$mess="Record updated!!";


echo "<script>alert('Your Password succesfully changed');</script>";
}else 

{
echo "<script>alert('Email id or Mobile no is invalid');</script>"; 
}
}

?>
<!DOCTYPE html>
<html>

<head>
 
    <title>Kipmets SMS System|Forgot Password Page</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
   <link href="assets/css/style.css" rel="stylesheet" />
      <link href="assets/css/main-style.css" rel="stylesheet" />
<script type="text/javascript">
function valid()
{
if(document.chngpwd.newpassword.value!= document.chngpwd.confirmpassword.value)
{
alert("New Password and Confirm Password Field do not match  !!");
document.chngpwd.confirmpassword.focus();
return false;
}
return true;
}
</script>

</head>

<body class="body-Login-back">

    <div class="container">
    <?php 
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
        <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center logo-margin ">
              <strong style="color: white;font-size: 25px">Kipmets SMS System</strong>
                </div>
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">                  
                    <div class="panel-heading">
                        <h3 class="panel-title">Reset Your Password</h3>
                    </div>
                    <div class="panel-body">
                        <form role="form" method="post" name="chngpwd" onSubmit="return valid();">
                            <table class="table">
                                <tr>
                                    <td><label>Email:</label></td>
                                    <td><input class="form-control" placeholder="E-Mail" name="email" required="true" type="email" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></td>
                                </tr>
                                <tr>
                                    <td><label>Mobile No:</label></td>
                                    <td><input class="form-control" placeholder="Mobile Number" name="mobile" maxlength="10" pattern="[0-9]+" required="true" type="text" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></td> 
                                </tr>
                                    
                                <tr>
                                    <td><label>New Password:</label></td>
                                    <td><input class="form-control" type="password" placeholder="New Password"  name="newpassword" required="true" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></td>
                                </tr>
                                <tr>
                                    <td><label>Confirm Password:</label></td>
                                    <td><input class="form-control" type="password" placeholder="Confirm Password"  name="confirmpassword" required="true" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"></td>
                                </tr>
                                <tr>
                                   <td>
                                   <div class="checkbox">
                                    <label><a href="index.php">Already have an account</a></label>
                                    </div>
                                   </td> 
                                    <td><input type="submit" value="submit" class="btn btn-lg btn-success btn-block" name="submit" ></td>
                                </tr>                               

                                <!-- Change this to a button or input when using this as a form -->
                                                               
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
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
