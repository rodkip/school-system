<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else{
    $eid=$_GET['editid'];
    if(isset($_POST['submit']))
  {
  $adminid=$_SESSION['cpmsaid'];
  $fullnames=$_POST['fullnames'];
  $username=$_POST['username'];
  $accounttype=$_POST['accounttype'];
  $mobilenumber=$_POST['mobilenumber'];
  $emailaddress=$_POST['emailaddress'];
  $sql="update tbladmin set fullnames=:fullnames,accounttype=:accounttype,mobilenumber=:mobilenumber,emailaddress=:emailaddress where id=:aid";
     $query = $dbh->prepare($sql);
     $query->bindParam(':fullnames',$fullnames,PDO::PARAM_STR);
     $query->bindParam(':username',$username,PDO::PARAM_STR);
     $query->bindParam(':accounttype',$accounttype,PDO::PARAM_STR);
     $query->bindParam(':emailaddress',$emailaddress,PDO::PARAM_STR);
     $query->bindParam(':mobilenumber',$mobilenumber,PDO::PARAM_STR);
     $query->bindParam(':aid',$adminid,PDO::PARAM_STR);
     $query->execute();
     $mess='Record Updated';
         
      $eid="";
  }
  ?>
<!DOCTYPE html>
<html>

<head>
    
    <title>Curfew Pass Management System | Admin Profile</title>
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
                    <h1 class="page-header">Edit User Profile:</h1>
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
                                <h3><span style='color:green'><?php echo $mess?></span></h3>
                                    <form method="post"> 
                                    <?php

$sql="SELECT * from  tbladmin where id=$eid";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
    <div class="form-group"> 
    <label for="fullnames">Full Name</label>
    <input type="text" name="fullnames" value="<?php  echo $row->fullnames;?>" class="form-control" required='true'> 
    </div>

    <div class="form-group"> 
    <label for="username">User Name</label> 
    <input type="text" name="username" value="<?php  echo $row->username;?>" class="form-control">
    </div>

    <div class="form-group"> 
    <label for="accounttype">Account Type</label> 
    <select name="accounttype"  value="" width="10px" class="form-control" required='true'>
              <option value="<?php  echo $row->accounttype;?>"><?php  echo $row->accounttype;?></option>  
              <option value="admin">Admin</option> 
              <option value="accounts">Accounts</option>             
              <option value="teacher">Teacher</option>
              <option value="student">Student</option>  
              <option value="programmer">Programmer</option>        
            </select>
    </div>

    <div class="form-group"> 
    <label for="mobilenumber">Mobile Number</label>
    <input type="text" name="mobilenumber" value="<?php  echo $row->mobilenumber;?>"  class="form-control" maxlength='10' required='true' pattern="[0-9]+">
    </div>

    <div class="form-group">
    <label for="emailaddress">Email address</label> 
    <input type="email" name="email" value="<?php  echo $row->emailaddress;?>" class="form-control" required='true'> 
    </div> 
    
   
     <p style="padding-left: 450px"><button type="submit" class="btn btn-primary" name="submit" id="submit">Update</button></p> 
     <?php $cnt=$cnt+1;}} ?> </form>
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
    if (window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }
    </script>
</body>

</html>
<?php }  ?>