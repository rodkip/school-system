<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else
  $eid=$_GET['editid'];
  $mess='';
  $update=false;
  {
    
    if(isset($_POST['submit']))
    {
      $id=$_POST['id'];
      $gradename=$_POST['gradename'];
      $academicyear=$_POST['academicyear'];
      $classcapacity=$_POST['classcapacity'];
      $gradefullname=$academicyear.$gradename;
     
    
    $dbh->query("UPDATE classdetails SET gradename='$gradename',academicyear='$academicyear',classcapacity='$classcapacity',gradefullname='$gradefullname' WHERE id=$id") or die($dbh->error);
    $eid='';
    $mess='Record Updated!!!';
    $update=true;
 
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    
    <title>School Management System | Update Student Details</title>
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
                    <h1 class="page-header">Update Class Details:</h1>
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
                                    <form method="post" enctype="multipart/form-data"> 
                                      <?php

$sql="SELECT * from  classdetails where id=$eid";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
      <div background-color="green">
      <?php echo $_SESSION['message']; 
       unset($_SESSION['message'])
       
      ?>

    </div>

    <div class="form-group">  
   <br>
    <input type="hidden" name="id" value="<?php echo $row->id ?>">
    
    <label for="gradename">Adm No:</label>
 
          <select name="gradename"  value="" width="10px" class="form-control"> 
          <option value="<?php echo $row->gradename; ?>"><?php echo $row->gradename; ?></option>
              <option value="Baby">Baby</option> 
              <option value="Middle">Middle</option>
              <option value="Preunit">Pre-Unit</option> 
              <option value="Grade1">Grade 1</option>
              <option value="Grade2">Grade 2</option>
              <option value="Grade3">Grade 3</option>
              <option value="Grade4">Grade 4</option>
              <option value="Grade5">Grade 5</option>
              <option value="Grade6">Grade 6</option>
              <option value="Grade7">Grade 7</option>
              <option value="Grade8">Grade 8</option>
            </select>
 
        <label for="academicyear">Academic Year:</label>

          <input type="text" class="form-control" name="academicyear" id="academicyear" required="required" placeholder="Enter academicyear name" value="<?php echo $row->academicyear; ?>">

        <label for="classcapacity">Class Capacity:</label>
        <input type="text" class="form-control" name="classcapacity" id="classcapacity" required="required" placeholder="Enter classcapacity name" value="<?php echo $row->classcapacity; ?>">

          <label for="gradefullname">Gradefull Name:</label>
          <input type="text" class="form-control" name="gradefullname" id="gradefullname" placeholder="Enter gradefullname here" value="<?php echo $row->gradefullname; ?>" readonly>

          </div>
     <?php $cnt=$cnt+1;}} ?>
     <?php
      if($update==false):
      ?> 
     <p style="padding-left: 450px"><button type="submit" class="btn btn-primary" name="submit" id="submit">Update</button></p>
     <?php else:?>
      <h3><?php echo $mess ?></h3>
    <?php endif;?>
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
    if (window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }
    </script>
</body>

</html>
