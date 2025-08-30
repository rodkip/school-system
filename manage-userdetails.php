<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else {
    // defaults setting
    $update = false;
    $newfullnames = '';
    $newaccounttype = '';
    $newusername = '';
    $mobilenumber = '';
    $emailaddress = '';
    $password = '';
}

// saving a new record
if (isset($_POST['submit'])) {
    $newfullnames = $_POST['newfullnames'];
    $newaccounttype = $_POST['newaccounttype'];
    $newusername = $_POST['newusername'];
    $mobilenumber = $_POST['mobilenumber'];
    $emailaddress = $_POST['emailaddress'];
    $password = password_hash($_POST['newpassword'], PASSWORD_DEFAULT); // Using bcrypt for password hashing

    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "INSERT INTO tbladmin (fullnames,accounttype,username,mobilenumber,emailaddress,password) VALUES(:newfullnames,:newaccounttype,:newusername,:mobilenumber,:emailaddress,:password)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':newfullnames', $newfullnames, PDO::PARAM_STR);
    $stmt->bindParam(':newaccounttype', $newaccounttype, PDO::PARAM_STR);
    $stmt->bindParam(':newusername', $newusername, PDO::PARAM_STR);
    $stmt->bindParam(':mobilenumber', $mobilenumber, PDO::PARAM_STR);
    $stmt->bindParam(':emailaddress', $emailaddress, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();


    $_SESSION['messagestate'] = 'added';
    $_SESSION['mess'] = "Record CREATED Successfully";
    
    $update = false;
    $fullnames = '';
    $newaccounttype = '';
    $newusername = '';
    $mobilenumber = '';
    $emailaddress = '';
    $password = '';
}

// rest of the code...

// deleting a record
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']); // ensure it's an integer
  $loggedInUserId = $_SESSION['cpmsaid']; // current user id

  if ($id == $loggedInUserId) {
     
          $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "You cannot delete your own account while logged in.";
  } else {
      try {
          $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $sql = "DELETE FROM tbladmin WHERE id = :id";
          $stmt = $dbh->prepare($sql);
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
          $stmt->execute();

      
          $_SESSION['messagestate'] = 'deleted';
          $_SESSION['mess'] = "Record DELETED Successfully";
          
      } catch (PDOException $e) {
         
          $_SESSION['messagestate'] = 'deleted';
          $_SESSION['mess'] = "Error deleting record: " . $e->getMessage();
      }
  }
}

// Updating a record
if (isset($_POST['update'])) {
  $id = $_POST['id'];
  $newfullnames = $_POST['newfullnames'];
  $newaccounttype = $_POST['newaccounttype'];
  $newusername = $_POST['newusername'];
  $mobilenumber = $_POST['mobilenumber'];
  $emailaddress = $_POST['emailaddress']; 


  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $sql = "UPDATE tbladmin SET fullnames = '$newfullnames', accounttype = '$newaccounttype', username = '$newusername', mobilenumber = '$mobilenumber', emailaddress = '$emailaddress' WHERE id = $id";
  $dbh->exec($sql);

  $update = false;
 
  $_SESSION['messagestate'] = 'added';
  $_SESSION['mess'] = "Record updated!!";
  $newfullnames = '';
  $newaccounttype = '';
  $newusername = '';
  $mobilenumber = '';
  $emailaddress = '';
 
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|User Profile
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
            <br>
            <table>
              <tr>
                <td width="100%">
                  <h1 class="page-header">Manage System User Details <i class="fa fa-users" aria-hidden="true"></i></h1>
                </td>
                  <td>
                    <!-- Button that will trigger the modal -->
                    <?php include('new-systemuserpopup.php'); ?>
                                                              <a href="#new-systemuserentry" data-toggle="modal">
                                                                <button class="btn btn-success">
                                                                    <i class="fa fa-bars" aria-hidden="true"></i>&nbsp;&nbsp;New User Entry
                                                                </button> 
                                                              </a>  
                </td>
                <td> &nbsp;&nbsp;&nbsp;&nbsp; </td> 
                <td>  
                  <a href="manage-permissions.php">
                    <button class="btn btn-primary">
                        <i class="fa fa-bars" aria-hidden="true"></i>&nbsp;&nbsp;Manage Permissions
                    </button> 
                  </a>  
                </td> 
                <td> <?php include_once('updatemessagepopup.php');?> </td>
              </tr>
            </table>
          </div>
          <!--end page header -->
        </div>
          
        <div class="row">
  <div class="col-lg-12">
    <div class="panel panel-primary">
      <div class="panel-heading">System Users</div>
      <div class="panel-body">
        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
          <table class="table table-striped table-bordered table-hover" id="dataTable1">
            <thead>
              <tr>
                <th>#</th>
                <th>Full Names</th>
                <th>Account Type</th>
                <th>User Name</th>
                <th>Mobile Number</th>
                <th>Email Address</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $username = $_SESSION['username']; // assuming username is stored in session

            $sql = "SELECT * FROM tbladmin WHERE username != :username ORDER BY id DESC";
            $query = $dbh->prepare($sql);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $cnt = 1;

            if ($query->rowCount() > 0):
              foreach ($results as $row): ?>

                  <tr>
                    <td><?= $cnt; ?></td>
                    <td><?= htmlentities($row->fullnames); ?></td>
                    <td><?= htmlentities($row->accounttype); ?></td>
                    <td><?= htmlentities($row->username); ?></td>
                    <td><?= htmlentities($row->mobilenumber); ?></td>
                    <td><?= htmlentities($row->emailaddress); ?></td>
                    <td>
                      <div class="btn-group dropup">
                        <button class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                          Action <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu pull-right">
                          <li>
                            <a href="edit-systemusers.php?edit=<?= urlencode($row->id); ?>">
                              <i class="fa fa-pencil"></i> Edit
                            </a>
                          </li>
                          <li class="divider"></li>
                          <li>
                            <a href="manage-userdetails.php?delete=<?= urlencode($row->id); ?>"
                               onclick="return confirm('Are you sure you want to delete this user?');">
                              <i class="fa fa-trash-o"></i> Delete
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php
                $cnt++;
                endforeach;
              endif;
              ?>
            </tbody>
          </table>
          <h4>Total System Users: <strong><span style="color:green"><?= $cnt - 1; ?></span></strong></h4>
        </div>
      </div>
    </div>
  </div>
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
    <script>
      if (window.history.replaceState){
        window.history.replaceState(null,null,window.location.href);
      }
    </script>
     <script>
// Initialize DataTable on page load
$(document).ready(function() {
    $('#dataTable1').dataTable();
});

  </body>
</html>
