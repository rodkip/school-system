<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else{  
      $examyear=date("Y");
      $messagestate='';
      $mess="";

    if(isset($_POST['submit']))
            {
  $examname=$_POST['examname'];
  $examclass=$_POST['examclass'];
  $examyear=$_POST['examyear'];
  $examterm=$_POST['examterm'];
  $examfullname=$examclass.$examyear.$examterm.$examname;

  $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $sql="INSERT INTO examdetails (examname,examfullname,examclass,examyear,examterm) VALUES('$examname','$examfullname','$examclass','$examyear','$examterm')";

  $dbh->exec($sql);
  $messagestate='added';
  $mess="Record created...";
        }

 else if(isset($_POST['update_submit']))
        {
            $id=$_POST['id'];
            $examname=$_POST['editexamname'];
            $examclass=$_POST['editexamclass'];
            $examyear=$_POST['editexamyear'];
            $examterm=$_POST['editexamterm'];
            $stream=$_POST['editstream'];
            $examfullname=$examyear.$examclass;
           
          
          $dbh->query("UPDATE examdetails SET examname='$examname',examclass='$examclass',examyear='$examyear',examterm='$examterm',examfullname='$examfullname',stream='$stream' WHERE id=$id") or die($dbh->error);
          $dbh->exec($sql);
          $messagestate='added';
          $mess="Record updated...";
        }
    }   
  
  if (isset($_GET['delete']))
  try {
      $id=$_GET['delete'];
      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="DELETE FROM examdetails WHERE id=$id";
      $dbh->exec($sql);
      $messagestate='deleted';
      $mess="Record deleted!!!";

}
catch (PDOException $e)
{
  echo $sql."<br>".$e->getmessage();
}


?>
<!DOCTYPE html>
<html>

<head>
    
    <title>Kipmetz-SMS|Exam Details</title>
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
                    <!---messanger-->
                <?php 
                echo  $examclass;
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
                <table>
                    <tr> 
                        <td width="100%"><h1 class="page-header">Manage Exam Details:</h1></td>
                        <td><a href="manage-classentries.php" class="btn btn-primary"><i class="fa  fa-plus-circle"></i> Manage Class Session Entries </a>
                        </td>
                    </tr>
                </table>
                          
                    
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
   
    <form method="POST" enctype="multipart/form-data" action="manage-examdetails.php">
    <div class="form-group"> 
    <input type="hidden" name="id" value="<?php echo $row->id ?>">
      <br>
      <table  class="table" width="70%">
      <tr>
      <td><label for="examname">Exam Name:</label></td>
      <td><input type="text" name="examname" id="examname" required="required" placeholder="Exam Name" value="" class="form-control"></td>
      <td>
        <label for="examclass">Exam class:</label>
        </td><td>

        <?php
             $smt=$dbh->prepare('SELECT grade from grades order by id asc');
              $smt->execute();
              $data=$smt->fetchAll();
        ?>

          <select name="examclass"  value="<?php echo $examclass; ?>" class="form-control" required="required" > 
                  <option value="">--select grade--</option>
             <?php foreach ($data as $row):?>
                  <option value="<?=$row["grade"]?>"><?=$row["grade"]?></option> 
              <?php endforeach ?>
          </select>

            </td><td>
        <label for="examyear">Exam Year:</label>
        </td><td>
        <input type="text" class="form-control" name="examyear" id="examyear" required="required" placeholder="Enter examyear here" value="<?php echo $examyear; ?>">
        </td> 
        <td>
        <label for="examterm">Exam Term:</label></td><td>
        <select name="examterm"  value="" class="form-control" required="required" > 
                  <option value="">--select grade--</option>
                  <option value="firstterm">First Term</option>
                  <option value="secondterm">Second Term</option> 
                  <option value="thirdterm">Third Term</option>
              
          </select></td>
  
        <td>  
      <button type="submit" name="submit" class="btn btn-primary">Submit</button></td></tr>
        </table>

        </div>
        <div>

  
      </p>
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
                             
                              
                               <br>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>S.NO</th>
                                            <th>Exam Name</th>
                                            <th>Grade Name</th>
                                            <th>Academic Year</th>
                                            <th>Academic Term</th>                                   
                                            <th>Exam Fullname</th> 
                                            <th>Action</th>                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <span style='color:red'>Click on the Grade FullName to view the Class List</span>
                                        <?php
$sql="SELECT * FROM examdetails order by id desc" ;
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
                    <td><?php echo htmlentities($row->examname);?></td>
                    <td><?php echo htmlentities($row->examclass);?></td>
                    <td><?php echo htmlentities($row->examyear);?></td>
                    <td><?php echo htmlentities($row->examterm);?></td>
                    <td><?php echo htmlentities($row->examfullname);?></td>
                    <td style="padding: 5px">
                    <div class="btn-group" >
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                          Action <span class="caret"></span>
                        </button>                  
                        <ul class="dropdown-menu dropdown-default pull-right" role="menu" >
                         <li >
                          <a href="#myModal<?php echo ($row->id); ?>" data-toggle="modal" ><i class="fa  fa-pencil"></i>&nbsp;&nbsp;Edit</a>
                          </li>
                          <li class="divider"></li>
                          <li>                  
                          <a href="manage-examdetails.php?delete=<?php echo htmlentities ($row->id);?>" onclick="return confirm('You want to delete the record?!!')" name="delete"><i class="fa  fa-trash-o"></i>&nbsp;&nbsp;Delete</a>
                          </li>
                        </ul>
                      </div>
                </td>
                </tr>
               <?php $cnt=$cnt+1;}} ?>  
                                       
                                        
                                    </tbody>
                                </table>
                     
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
