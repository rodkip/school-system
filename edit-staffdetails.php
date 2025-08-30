<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else
  
  {
    $eid=$_GET['editid']; 
    if(isset($_POST['submit']))
    {
      $id=$_POST['id'];
      $staffidno=$_POST['staffidno'];
      $staffname=$_POST['staffname'];
      $gender=$_POST['gender'];
      $bank=$_POST['bank'];
      $bankaccno=$_POST['bankaccno'];
      $stafftitle=$_POST['stafftitle'];
      $staffcontact=$_POST['staffcontact'];
      $nssfaccno=$_POST['nssfaccno'];
      $nhifaccno=$_POST['nhifaccno'];
    
    $dbh->query("UPDATE staffdetails SET staffidno='$staffidno',staffname='$staffname',gender='$gender',bank='$bank',bankaccno='$bankaccno',stafftitle='$stafftitle',staffcontact='$staffcontact',nssfaccno='$nssfaccno',nhifaccno='$nhifaccno' WHERE id=$id") or die($dbh->error);
    $_SESSION['message']="Record has been updated!!";
     $staffidno='';
        $staffname='';
        $gender='';
        $bank='';
        $bankaccno='';
        $stafftitle='';
        $staffcontact='';
        $nssfaccno='';
        $nhifaccno='';
        $eid='';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    
    <title>Kipmetz-SMS|Update Staff Details</title>
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
                    <h1 class="page-header">Update Staff Details:</h1>
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
                                <div background-color="green">
      <?php echo $_SESSION['message']; 
       unset($_SESSION['message'])
    ?>
                                    <form method="post" enctype="multipart/form-data"> 
                                      <?php

$sql="SELECT * from  staffdetails where staffidno=$eid";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>


    </div>

    <div class="form-group">  
   
    <input type="hidden" name="id" value="<?php echo $row->id ?>">
    
    <label for="staffidno">Adm No:</label>
          <input type="text" name="staffidno" id="staffidno" required="required" placeholder="Enter AdmNo here" value="<?php echo $row->staffidno; ?>" class="form-control">
 
        <label for="staffname">Staff Names:</label>

          <input type="text" class="form-control" name="staffname" id="staffname" required="required" placeholder="Enter Student name" value="<?php echo $row->staffname; ?>">

        <label for="gender">Gender:</label>

            <select name="gender"  value="<?php echo $row->gender; ?>" class="form-control"> 
            <option value="<?php echo $row->gender; ?>"><?php echo $row->gender; ?></option>
              <option value="Male">Male</option> 
              <option value="Female">Female</option>
            </select>

          <label for="bank">Bank:</label>
          <?php
     $smt=$dbh->prepare('SELECT bankname from bankdetails');
     $smt->execute();
     $data=$smt->fetchAll();
     ?>   <select name="bank"  value="<?php echo $row->bank; ?>" class="form-control"> 
          <option value="<?php echo $row->bank; ?>"><?php echo $row->bank; ?></option>
          <?php foreach ($data as $rw):?>
          <option value="<?=$rw["bankname"]?>"><?=$rw["bankname"]?></option> 
          <?php endforeach ?>
          </select>

          <label for="bankaccno">Bank AccNo:</label>
          <input type="text" class="form-control" name="bankaccno" id="bankaccno"  placeholder="Enter bankaccno here" value="<?php echo $row->bankaccno; ?>">

          <label for="stafftitle">Staff Title:</label>
          <input type="text" class="form-control" name="stafftitle" id="stafftitle" placeholder="staff title" value="<?php echo $row->stafftitle; ?>">

          <label for="staffcontact">Staff Contact:</label>
          <input type="text" class="form-control" name="staffcontact" id="staffcontact" placeholder="staffcontact" value="<?php echo $row->staffcontact; ?>">

          <label for="nssfaccno">Nssf AccNo:</label>
          <input type="text" class="form-control" name="nssfaccno" id="nssfaccno" placeholder="nssfno" value="<?php echo $row->nssfaccno; ?>">
          </div>

          <label for="nssfaccno">Nhif AccNo:</label>
          <input type="text" class="form-control" name="nhifaccno" id="nhifaccno" placeholder="nhifno" value="<?php echo $row->nhifaccno; ?>">
          </div>
     <?php $cnt=$cnt+1;}} ?> 
     <p style="padding-left: 450px"><button type="submit" class="btn btn-primary" name="submit" id="submit">Update</button></p> </form>
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
