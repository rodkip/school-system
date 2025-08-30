<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else {
    // defaults setting
    $update = false;
    $quotes = '';
 

}

// saving a new record
if (isset($_POST['submit'])) {
    $quote = $_POST['quote'];
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "INSERT INTO ceoquotes (quote) VALUES(:quote)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':quote', $quote, PDO::PARAM_STR);
    $stmt->execute();

    $messagestate = 'added';
    $mess = "New record created";
    $update = false;
    $quote = '';


}

// End adding new record...

///deleting a record
if (isset($_GET['delete']))
try {
$id=$_GET['delete'];
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$sql="DELETE FROM ceoquotes WHERE id=$id";
$dbh->exec($sql);
$messagestate='deleted';
$mess="Record Deleted!!";
}
catch (PDOException $e)
{
echo $sql."<br>".$e->getmessage();
}  
//End deleting....


//Retrieving records for Editing
if (isset($_GET['edit'])){
$id=$_GET['edit'];
$update=true;
$sql="SELECT * from  ceoquotes where id=$id";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
if($query->rowCount() > 0) 
{
foreach($results as $rw)
{              
$quote=$rw->quote;
$id=$rw->id;
$messagestate='added';
$mess="Record on EDIT mode!!";
}}}
//End retrieving records for Editing

// Updating a record
if (isset($_POST['update'])) {
  $id = $_POST['id'];
  $quote = $_POST['quote'];

  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $sql = "UPDATE ceoquotes SET quote = '$quote' WHERE id = $id";
  $dbh->exec($sql);
  $update = false;
  $messagestate = 'added';
  $mess = "Record updated!!";
  $quote = '';


}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Field-team CEO Quotes
    </title>
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
            <!-- messanger -->
            <?php include_once('updatemessagepopup.php');?>
            <!-- end messanger -->
            <br>
            <h1 class="page-header">CEO Quotes:
            </h1>
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
                    <form method="post" enctype="multipart/form-data" action="manage-ceoquote.php"> 
                      <input type="hidden" name="id" value="<?php echo $id; ?>">
                      <table  class="table" width="70%">
                        <tr>
                          <td>
                            <label for="quote">Quote:
                            </label>
                          </td>
                          <td>
                            <input type="text" name="quote"  class="form-control" required='true' value="<?php echo $quote; ?>">
                          </td>
                      
                          <td colspan="2">  
                            <?php
                            if($update==true):
                            ?>
                            <button type="submit" name="update" class="btn btn-success" action="manage-ceoquotes.php">Update
                            </button>
                            <?php else:?>
                            <button type="submit" class="btn btn-primary" name="submit" id="submit" action="manage-ceoquotes.php" autofocus>Submit
                            </button> 
                            <?php endif;?>
                          </td>
                        </tr>
                      </table>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Form Elements -->
            <span style='color:green; font-size:20px;'>Quotes
            </span>
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
                                <th>#
                                </th>
                                <th>Quote
                                </th>
                                <th>Action
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
$sql="SELECT * From ceoquotes";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
                              <tr>
                                <td>
                                  <?php echo htmlentities($cnt);?>
                                </td>
                                <td>
                                  <?php echo htmlentities($row->quote);?>
                                </td>
                                <td style="padding: 5px">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-default pull-right" role="menu">
                             <li>
                                        <a href="manage-ceoquote.php?edit=<?php echo htmlentities ($row->id);?>">
                                            <i class="fa  fa-pencil"></i>&nbsp;&nbsp;Edit
                                        </a>
                                    </li>
                                    <li class='divider'></li>
                                    <li>
                                        <a href="manage-ceoquote.php?delete=<?php echo htmlentities ($row->id);?> " onclick="return confirm('You want to delete the record?!!')" name="delete">
                                            <i class="fa  fa-trash-o"></i>&nbsp;&nbsp;Delete
                                        </a>
                                    </li>
                               
                            </ul>
                        </div>
                    </td>

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
          </div>
        </div>
      </div>
      <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->
    <!-- Core Scripts - Include with every page -->
    <script src="assets/plugins/jquery-1.10.2.js">
    </script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js">
    </script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js">
    </script>
    <script src="assets/plugins/pace/pace.js">
    </script>
    <script src="assets/scripts/siminta.js">
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
