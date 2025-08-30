<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['submit_all'])) {
    if (!empty($_POST['notifications'])) {
        foreach ($_POST['notifications'] as $id => $data) {
            $notificationname = $data['notificationname'];
            $notificationcomments = $data['notificationcomments'];
            $notificationemails = $data['notificationemails'];
            $notificationstatus = isset($data['notificationstatus']) ? 'Active' : 'Inactive';

            $sql = "UPDATE notificationssettings 
                    SET notificationname = :notificationname, 
                        notificationcomments = :notificationcomments, 
                        notificationstatus = :notificationstatus, 
                        notificationemails = :notificationemails 
                    WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':notificationname', $notificationname, PDO::PARAM_STR);
            $stmt->bindParam(':notificationcomments', $notificationcomments, PDO::PARAM_STR);
            $stmt->bindParam(':notificationstatus', $notificationstatus, PDO::PARAM_STR);
            $stmt->bindParam(':notificationemails', $notificationemails, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
       
        $_SESSION['messagestate'] = 'added';
        $_SESSION['mess'] = "All changes saved successfully.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS|Notifications Settings</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
      <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .form-control {
            border-radius: 8px;
        }
        .form-check-input {
            width: 2.5em;
            height: 1.5em;
        }
        .btn-success {
            border-radius: 25px;
            padding: 10px 25px;
        }
        th {
            background-color: #0d6efd;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
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
                    <h1 class="page-header">Notifications settings</h1>
                </div>
                <!--end page header -->
            </div>
            
            <div class="panel panel-primary">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-body">          
                                <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                    <div id="table-wrapper">
                                        <div id="table-container">
                                            <span style='color:green; font-size:20px;'>System Notifications Settings</span>
                                              <form method="POST">
                                                  <div class="table-responsive">
                                                      <table class="table table-bordered align-middle">
                                                          <thead>
                                                              <tr>
                                                                  <th>#</th>
                                                                  <th>Notification Name</th>
                                                                  <th>Status</th>
                                                                  <th>Comments</th>
                                                                  <th>Emails</th>
                                                              </tr>
                                                          </thead>
                                                          <tbody>
                                                            <?php
                                                            $sql = "SELECT * FROM notificationssettings";
                                                            $query = $dbh->prepare($sql);
                                                            $query->execute();
                                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                            $cnt = 1;
                                                            foreach ($results as $row) {
                                                                echo "<tr>
                                                                    <td>{$cnt}</td>
                                                                    <td>
                                                                        <input type='text' name='notifications[{$row->id}][notificationname]' class='form-control' readonly value='" . htmlspecialchars($row->notificationname, ENT_QUOTES) . "'>
                                                                    </td>
                                                                    <td class='text-center'>
                                                                        <div class='form-check form-switch'>
                                                                            <input class='form-check-input' type='checkbox' 
                                                                                name='notifications[{$row->id}][notificationstatus]' 
                                                                                value='Active' " . ($row->notificationstatus == 'Active' ? 'checked' : '') . ">
                                                                            <input type='hidden' name='notifications[{$row->id}][notificationstatus_hidden]' value='Inactive'>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <input type='text' name='notifications[{$row->id}][notificationcomments]' readonly value='" . htmlentities($row->notificationcomments, ENT_QUOTES) . "' class='form-control'>
                                                                    </td>
                                                                    <td>
                                                                        <input type='text' name='notifications[{$row->id}][notificationemails]' value='" . htmlentities($row->notificationemails, ENT_QUOTES) . "' class='form-control'>
                                                                    </td>
                                                                </tr>";
                                                                $cnt++;
                                                            }
                                                            ?>
                                                          </tbody>
                                                      </table>
                                                  </div>
                                                  <div class="text-center mt-4">
                                                      <button type="submit" name="submit_all" class="btn btn-success shadow-sm">
                                                          <i class="fa fa-save me-2"></i>Save All Changes
                                                      </button>
                                                  </div>
                                              </form>
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
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>