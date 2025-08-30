<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else{
    $mess="";
    $update=false;
    if(isset($_POST['submit']))
 try {
    
  $vehiclenoplate=$_POST['vehiclenoplate'];
  $make=$_POST['make'];
  $capacity=$_POST['capacity'];
  $fueltype=$_POST['fueltype'];
  $vehicletype=$_POST['vehicletype'];
 
      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="INSERT INTO vehiclesdetails (vehiclenoplate,make,capacity,fueltype,vehicletype) VALUES('$vehiclenoplate','$make','$capacity','$fueltype','$vehicletype')";

      $dbh->exec($sql);
      $vehiclenoplate='';
      $make='';
      $capacity='';
      $fueltype='';
      $vehicletype='';
      $messagestate='added';
      $mess="Record created...";
      $update=false;
        }
        catch (PDOException $e)
        {
          echo $sql."<br>".$e->getmessage();
        }
      
  }
//Deleting a record
if (isset($_GET['delete']))
{
      $id=$_GET['delete'];
      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="DELETE FROM vehiclesdetails WHERE id=$id";
      $dbh->exec($sql);
      $messagestate='deleted';
      $mess="Record Deleted!!";
      $update=false;
      
}
//Editing a record
if (isset($_GET['edit'])){
$id=$_GET['edit'];
$update=true;
$sql="SELECT * from  vehiclesdetails where id=$id";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0) 
{
    foreach($results as $row)
{              
    $vehiclenoplate=$row->vehiclenoplate;
    $make=$row->make;
    $capacity=$row->capacity;
    $id=$row->id;
    $fueltype=$row->fueltype;
    $vehicletype=$row->vehicletype;

    $messagestate='added';
    $mess="Record on EDIT mode!!";
$cnt=$cnt+1;
}}}
//Updating a record
if(isset($_POST['update']))
 {
    $id=$_POST['id'];
    $vehiclenoplate=$_POST['vehiclenoplate'];
    $make=$_POST['make'];
    $capacity=$_POST['capacity'];
    $fueltype=$_POST['fueltype'];
    $vehicletype=$_POST['vehicletype'];
       
    $dbh->query("UPDATE vehiclesdetails SET vehiclenoplate='$vehiclenoplate',make='$make',capacity='$capacity',fueltype='$fueltype',vehicletype='$vehicletype' WHERE id=$id") or die($dbh->error);
    $vehiclenoplate='';
    $make='';
    $capacity='';
    $fueltype='';
    $vehicletype='';
    $messagestate='added';
    $mess="Record updated!!";
    $update=false;
 }

?>
<!DOCTYPE html>
<html>

<head>
    
    <title>Kipmetz-SMS|School Vehicles</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />



</head>

<body onload="startTime()">
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
                                       <!---messanger-->
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
                          <!-- end messanger -->
                    <h2 class="page-header">Manage School Vehicles:</h2>
                </div>
                <!--end page header -->
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-primary">
                       
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
   
    <form method="POST" enctype="multipart/form-data">
    <div class="form-group"> 
    <input type="hidden" name="id" value="<?php echo $id; ?>">

      <br>
      <table  class="table" width="70%">
      <tr>
      <td>
        <label for="vehicletype">Vehicle Type:</label></td><td>
    <select id="vehicletype" name="vehicletype"  value="<?php echo $vehicletype; ?>" class="form-control"> 
        <option value="<?php echo $vehicletype; ?>"><?php echo $vehicletype; ?></option>
        <option value="Bus">Bus</option> 
        <option value="Lorry">Lorry</option>
        <option value="Van">Van</option>
    </select></td>
    <td>
        <label for="vehiclenoplate">Number Plate:</label>
        </td><td>
        <input type="text" class="form-control" name="vehiclenoplate" id="vehiclenoplate" required="required" placeholder="vehiclenoplate" value="<?php echo $vehiclenoplate; ?>">
        </td> 
        <td>
        <label for="make">Make:</label></td><td>
        <input type="text" class="form-control" name="make" id="make" placeholder="make" value="<?php echo $make; ?>"></td>
        <td>
        <label for="capacity">Capacity:</label></td><td>
        <input type="text" class="form-control" name="capacity" id="capacity" placeholder="capacity" value="<?php echo $capacity; ?>"></td><td>
        <label for="fueltype">FuelType:</label></td><td>
    <select id="fueltype" name="fueltype"  value="<?php echo $fueltype; ?>" class="form-control"> 
        <option value="<?php echo $fueltype; ?>"><?php echo $fueltype; ?></option>
        <option value="Diesel">Diesel</option> 
        <option value="Petrol">Petrol</option>
    </select></td>
        <td>    
        <?php
        if($update==true):
        ?>
        <button type="submit" name="update" class="btn btn-primary" action="manage-vehiclesdetails.php">Update</button>
        <?php else:?>
        <button type="submit" name="submit" class="btn btn-success" action="manage-vehiclesdetails.php">Submit</button>
        <?php endif;?>
        </td></tr>
        </table>
        </div>
    </form> 
                                </div>
                                
                            </div>
                        </div>
                    </div>
                     <!-- End Form Elements -->
                     <div class="panel panel-primary">
                     <div class="row">
                <div class="col-lg-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                      
                        <div class="panel-body">
                            <div class="table-responsive">
                               <form>
                              
                               <br>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>S.NO</th>
                                            <th>Vehicle Type</th>
                                            <th>Number Plate</th>
                                            <th>Make</th> 
                                            <th>Capacity</th>  
                                            <th>Fuel Type</th>           
                                        </tr>
                                    </thead>
                                    <tbody>                                   
                                        <?php
$sql="SELECT * from vehiclesdetails";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>

                <tr>
                    <td><?php echo htmlentities($cnt);?></td>
                    <td><?php echo htmlentities($row->vehicletype);?></td>
                    <td><?php echo htmlentities($row->vehiclenoplate);?></td>
                    <td><?php echo htmlentities($row->make);?></td>
                    <td><?php echo htmlentities($row->capacity);?></td>
                    <td><?php echo htmlentities($row->fueltype);?></td>               
                    <td><a href="manage-vehiclesdetails.php?edit=<?php echo htmlentities ($row->id);?>">Edit</a>  ||  <a href="manage-vehiclesdetails.php?delete=<?php echo htmlentities ($row->id);?>" onclick="return confirm('You want to delete the record?!!')" name="delete">Delete</a> </td>
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
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
     <!-- Page-Level Plugin Scripts-->
     <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });
    </script>
    <script>
    if (window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }
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
