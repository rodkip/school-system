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
  $studentadmno=$_POST['studentadmno'];
  $examfullname=$_POST['examfullname'];
  $maths=$_POST['maths'];
  $english=$_POST['english'];
  $kiswahili=$_POST['kiswahili'];
  $science=$_POST['science'];
  $sstudiescre=$_POST['sstudiescre'];
  $total=$maths+$english+$kiswahili+$science+$sstudiescre;
  $average=$total/5;

      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="INSERT INTO examsupperprimary (examfullname,studentadmno,maths,english,kiswahili,science,sstudiescre,total,average) VALUES('$examfullname','$studentadmno','$maths','$english','$kiswahili','$science','$sstudiescre','$total','$average')";

      $dbh->exec($sql);
      $messagestate='added';
      $mess="Record created...";

      $search_examfullname= $_POST['examfullname'];  
        }

 else if(isset($_POST['update_submit']))
        {
            $id=$_POST['id'];
            $examfullname=$_POST['examfullname'];
           
          
          $dbh->query("UPDATE examsupperprimary SET examfullname='$examfullname',studentadmno='$studentadmno' WHERE id=$id") or die($dbh->error);
          $dbh->exec($sql);
          $messagestate='added';
          $mess="Record updated...";
        }
    }   
  
    //search by examfullname
    if(isset($_POST['search_submit']))
    {
       $search_examfullname= $_POST['search_examfullname'];           

      }
//end-search by examfullname

//delete a record
  if (isset($_GET['delete']))
  try {
      $id=$_GET['delete'];
      $sql ="SELECT examfullname FROM examsupperprimary WHERE id='$id'";
      $query = $dbh -> prepare($sql);
      $query->execute();
      $results=$query->fetchAll(PDO::FETCH_OBJ);
      $cnt=1;
      if($query->rowCount() > 0)
      {
  foreach($results as $row)
      {
    $search_examfullname=$row->examfullname;
      }}
   
      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="DELETE FROM examsupperprimary WHERE id=$id";
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
    
    <title>Kipmetz-SMS|Upper Primary Exams</title>
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
      
    <h1 class="page-header">Upper Primary Exams: <span style='color:blue;font-size:30px;'> <?php echo $search_examfullname; ?></h1></td>
                    
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
              <div class="table-responsive">
              <table  width="40%">
                     <tr>
                        <td width="40%"><label for="search_examfullname">Select Exam Fullname to View:</label></td>
                        <form action="manage-examsupperprimary.php" method="POST" >
                        <td width="40%">
                        <?php
                        $smt=$dbh->prepare('SELECT examfullname,id from examdetails order by examfullname asc');
                        $smt->execute();
                        $data=$smt->fetchAll();?>
                              <select name="search_examfullname"  value="<?php echo $examfullname; ?>" class="form-control" required="required"> 
                                 <option value="">--select examfullname--</option>
                                    <?php foreach ($data as $rw):?>
                                 <option value="<?=$rw["examfullname"]?>"><?=$rw["examfullname"]?></option> 
                                    <?php endforeach ?>
                              </select>
                        </td>
                        <td>&nbsp;&nbsp;<button type="submit" name="search_submit" class="btn btn-primary"><i class="fa  fa-search"></i> Search</button></>
                        </form>

                        <td> </td>
      
                     </tr>
                  </table>
                  <br>
                  <?php include('newexamupperprimaryentrypopup.php');?>
      <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa  fa-plus-circle"></i> Exam Details Entry </a>
                   
                               <br><br>
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example" style="font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;border-style:double;">
                                    <thead>
                                        <tr>
                                            <th style="width:4%">Pos</th>
                                            <th>AdmNo</th>
                                            <th>StudentName</th>
                                            <th>Mat</th>
                                            <th>Eng</th>
                                            <th>Kisw</th>
                                            <th>Sci</th>
                                            <th>SsCRE</th>  
                                            <th>Total</th>
                                            <th>Average</th>                                                 
                                            <th>Action</th>                                    
                                        </tr>
                                    </thead>
                                    <tbody>
                                   
                                        <?php
$sql="SELECT examsupperprimary.examfullname,examsupperprimary.studentadmno,examsupperprimary.maths,examsupperprimary.english,examsupperprimary.kiswahili,examsupperprimary.science,examsupperprimary.sstudiescre,examsupperprimary.total,examsupperprimary.average,examsupperprimary.id,studentdetails.studentname FROM examsupperprimary INNER JOIN studentdetails ON examsupperprimary.studentadmno = studentdetails.studentadmno
 WHERE examfullname='$search_examfullname' ORDER BY average DESC" ;
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$rowcount=$query->rowCount($results);
$rank=0;
$rows=0;
$last_score-false;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>

                <tr> 
                  <td><?php 
                  $rows++;
                  if ($last_score!=($row->average)){
                    $last_score=($row->average);
                    $rank=$rows;
                  }
                  echo $rank;?></td>
                    <td><?php echo htmlentities($row->studentadmno);?>  </td> 
                    <td><?php echo htmlentities($row->studentname);?>  </td>
                    <td><?php echo htmlentities($row->maths);?>  </td>     
                    <td><?php echo htmlentities($row->english);?>  </td>   
                    <td><?php echo htmlentities($row->kiswahili);?>  </td>   
                    <td><?php echo htmlentities($row->science);?>  </td>   
                    <td><?php echo htmlentities($row->sstudiescre);?> </td>   
                    <td><?php echo htmlentities($row->total);?> </td> 
                    <td><?php echo htmlentities($row->average);?> </td>                       
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
                          <a href="manage-examsupperprimary.php?delete=<?php echo htmlentities ($row->id);?>" onclick="return confirm('You want to delete the record?!!')" name="delete"><i class="fa  fa-trash-o"></i>&nbsp;&nbsp;Delete</a>
                          </li>
                        </ul>
                      </div>
                </td>
                </tr>

 <!-- update position and outof fields -->
               <?php 
    
                    $dbh->query("UPDATE examsupperprimary SET position='$rank',outof='$rowcount' WHERE id=$row->id") or die($dbh->error);

              }} ?>  
<!-- end of updating -->    

                                       Total Entries: <?php echo $rowcount;?>
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
function admnoAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "checkadmno.php",
data:'studentadmno='+$("#studentadmno").val(),
type: "POST",
success:function(data){
$("#user-availability-status1").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}
</script>	
    <script>
    if (window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }
    </script>

<!--maximum and minimum limit-->
    <script type="text/javascript">
    function fnc(value,min,max)
    {
      if(parseInt(value)<0 || isNaN(value))
      return 0;
      else if(parseInt(value)>100)
      return false;
      else return value;
    }    
    </script>
    <!--end maximum and minimum limit-->

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
