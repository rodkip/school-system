<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else{  
      $academicyear=date("Y");
      $messagestate='';
      $mess="";

    if(isset($_POST['submit']))
            {
  $subjectname=$_POST['subjectname'];
  $grades=implode(',',$_POST['grades']);


      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="INSERT INTO subjects (subjectname,grades) VALUES('$subjectname','$grades')";

      $dbh->exec($sql);
      $messagestate='added';
      $mess="Record created...";
        }

 else if(isset($_POST['update_submit']))
        {
            $id=$_POST['id'];
            $subjectname=$_POST['subjectname'];
           
          
          $dbh->query("UPDATE subjects SET subjectname='$subjectname',grades='$grades' WHERE id=$id") or die($dbh->error);
          $dbh->exec($sql);
          $messagestate='added';
          $mess="Record updated...";
        }
    }   
  
  if (isset($_GET['delete']))
  try {
      $id=$_GET['delete'];
      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="DELETE FROM subjects WHERE id=$id";
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
    
    <title>Kipmetz-SMS|Subjects</title>
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
      
    <h1 class="page-header">Manage Subjects:</h1></td>
                    
                </div>
                <!--end page header -->
            </div>
                     <!-- End Form Elements -->
                     <div class="panel panel-primary">
                     <div class="row">
                <div class="col-lg-12">                  
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                      
                        <div class="panel-body">
                             
                <form method="POST" enctype="multipart/form-data"  action="manage-subjects.php">
                <div class="form-group"> 
                <input type="hidden" name="id" value="<?php echo $row->id ?>">               
                  <table  class="table" >
                  <tr >
                      <td colspan="3"> 
                      <?php
             $smt=$dbh->prepare('SELECT grade from grades order by id asc');
              $smt->execute();
              $data=$smt->fetchAll();
        ?>  
        <Label for="grades"> Check Grades for the Subject</Label>  
         <div>
              <?php foreach ($data as $row):?> 
                 
                <label class="btn btn-primary text-center" >
                  <input type="checkbox" name="grades[]" value="<?=$row["grade"]?>"> <?=$row["grade"]?>  
                </label>      
               
              <?php endforeach ?>
            </div>
                    </td>
                    </tr>
                    <tr >
                      <td style="width: 10%;">
                        <label for="subjectname">Subject Name:</label>
                      </td>
                      <td>
                        <input type="text" class="form-control" name="subjectname" id="subjectname" required="required" placeholder="subjectname" value="" style="width:300px"> 
                      </td> 
                      <td>  
                  <button type="submit" name="submit" class="btn btn-success">Submit</button>
                </td> 
                    </tr>
   
                    </table>

        </div>
    </form> 
                            <div class="table-responsive">
                             
                              
                               <br>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example" style="font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;border-style:double;">
                                    <thead>
                                        <tr>
                                            <th style="width:3%">#</th>
                                            <th style="width:20%">Subject Name</th>
                                            <th>Grades</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                   
                                        <?php
$sql="SELECT * FROM subjects order by id desc" ;
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>

                <tr> 
                  <td><?php echo $cnt;?></td>           
                    <td><?php echo htmlentities($row->subjectname);?>  </td> 
                    <td><?php echo htmlentities($row->grades);?>  </td>                          
                    <td style="width: 10%;">
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
                          <a href="manage-subjects.php?delete=<?php echo htmlentities ($row->id);?>" onclick="return confirm('You want to delete the record?!!')" name="delete"><i class="fa  fa-trash-o"></i>&nbsp;&nbsp;Delete</a>
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
