<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else {   

  }
// Check if form data is posted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Loop through the permissions submitted via the form
    if (isset($_POST['permissions'])) {
        // Begin a transaction to ensure data integrity
        $dbh->beginTransaction();

        try {
            // Loop through each permission selected in the form
            foreach ($_POST['permissions'] as $permission_name => $accounttypes) {
                // Loop through each account type for the given permission
                foreach ($accounttypes as $accounttype => $value) {
                    // Check if the permission is being granted (checkbox checked)
                    $isGranted = ($value == 1) ? true : false;

                    // Check if the permission already exists in the role_permissions table
                    $checkQuery = "SELECT * FROM role_permissions WHERE permission_id = 
                                   (SELECT permission_id FROM permissions WHERE permission_name = :permission_name)
                                   AND accounttype = :accounttype";

                    $checkStmt = $dbh->prepare($checkQuery);
                    $checkStmt->execute([':permission_name' => $permission_name, ':accounttype' => $accounttype]);
                    $existingPermission = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    // If the permission is being granted and doesn't exist, insert a new record
                    if ($isGranted && !$existingPermission) {
                        $insertQuery = "INSERT INTO role_permissions (permission_id, accounttype) 
                                        SELECT permission_id, :accounttype FROM permissions WHERE permission_name = :permission_name";
                        $insertStmt = $dbh->prepare($insertQuery);
                        $insertStmt->execute([':permission_name' => $permission_name, ':accounttype' => $accounttype]);

                    // If the permission is being revoked (unchecked) and exists, delete the record
                    } elseif (!$isGranted && $existingPermission) {
                        $deleteQuery = "DELETE FROM role_permissions WHERE permission_id = 
                                        (SELECT permission_id FROM permissions WHERE permission_name = :permission_name) 
                                        AND accounttype = :accounttype";
                        $deleteStmt = $dbh->prepare($deleteQuery);
                        $deleteStmt->execute([':permission_name' => $permission_name, ':accounttype' => $accounttype]);
                    }
                }
            }

            // Commit the transaction if all updates are successful
            $dbh->commit();    
            $messagestate = 'added';
            $mess = "Permissions Records updated....";

        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $dbh->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }
}

// Handle CSV export request
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=user_permissions_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    $headers = array('#', 'Category', 'Permission');
    $accountTypeQuery = "SELECT DISTINCT accounttype FROM accounttypes"; 
    $accountTypeResult = $dbh->query($accountTypeQuery);
    $accountTypes = $accountTypeResult->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($accountTypes as $accountType) {
        $headers[] = $accountType;
    }
    fputcsv($output, $headers);
    
    // Write data rows
    $permissionQuery = "SELECT DISTINCT permission_name, permission_category 
                        FROM permissions 
                        ORDER BY permission_category, permission_name ASC";
    $permissionResult = $dbh->query($permissionQuery);
    $permissions = $permissionResult->fetchAll(PDO::FETCH_ASSOC);
    
    $cnt = 1;
    foreach ($permissions as $permission) {
        $row = array(
            $cnt++,
            $permission['permission_category'],
            $permission['permission_name']
        );
        
        foreach ($accountTypes as $accountType) {
            $checkQuery = "SELECT 1 
                            FROM role_permissions 
                            JOIN permissions ON role_permissions.permission_id = permissions.permission_id
                            WHERE permissions.permission_name = :permission 
                            AND role_permissions.accounttype = :accounttype";
            $checkStmt = $dbh->prepare($checkQuery);
            $checkStmt->execute([
                ':permission' => $permission['permission_name'], 
                ':accounttype' => $accountType
            ]);
            $exists = $checkStmt->fetchColumn();
            
            $row[] = $exists ? 'Yes' : 'No';
        }
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|Users Permissions </title>
    <link rel="icon" href="images/tabpic.png">
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script>
    <style>
        .customstaff-modal-xl {
    max-width: 90%; /* Adjust to your desired width, e.g., 90% of the viewport width */
    width: 100%; /* Ensure it uses the full width within its container */
}

    </style>
  </head>
  <body>
    <!-- Wrapper -->
    <div id="wrapper">
        <!-- Navbar Top -->
        <?php include_once('includes/header.php'); ?>
        <!-- End Navbar Top -->

        <!-- Navbar Side -->
        <?php include_once('includes/sidebar.php'); ?>
        <!-- End Navbar Side -->

        <!-- Page Wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- Page Header -->
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                            <h1 class="page-header">
                                Users' Permissions <i class="fa fa-cogs" aria-hidden="true"></i>
                            </h1>

                            </td>
                            <td>
                                <!-- Button that will trigger the modal -->
                                <?php include('edit-userpermissionspopup.php'); ?>
                                <a href="#edituserpermissions" data-toggle="modal">
                                    <button class="btn btn-success">
                                        <i class="fa fa-edit" aria-hidden="true"></i>&nbsp;&nbsp;Edit/Update
                                    </button>
                                </a>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <a href="?export=csv" class="btn btn-primary">
                                    <i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;Export to CSV
                                </a>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
                <!-- End Page Header -->
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Panel -->
                    <div class="panel panel-primary">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- Advanced Tables -->
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                            <div id="table-wrapper">
                                                <!-- Table Loading Animation -->
                                                <!-- Table Loading Animation End -->
                                                <div id="table-container">
                                                <table class="table table-striped table-bordered table-hover" id="dataTable1">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Category</th>
                                                        <th>Permission</th>
                                                        <?php
                                                        // Fetch all account types from the accounttypes table
                                                        $accountTypeQuery = "SELECT DISTINCT accounttype FROM accounttypes"; 
                                                        $accountTypeResult = $dbh->query($accountTypeQuery);
                                                        $accountTypes = $accountTypeResult->fetchAll(PDO::FETCH_COLUMN);

                                                        // Display account type headers
                                                        foreach ($accountTypes as $accountType) {
                                                            echo "<th>" . htmlentities($accountType) . "</th>";
                                                        }
                                                        ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // Fetch all permissions with their categories
                                                    $permissionQuery = "SELECT DISTINCT permission_name, permission_category 
                                                                        FROM permissions 
                                                                        ORDER BY permission_category, permission_name ASC";
                                                    $permissionResult = $dbh->query($permissionQuery);
                                                    $permissions = $permissionResult->fetchAll(PDO::FETCH_ASSOC);

                                                    $cnt = 1;

                                                    foreach ($permissions as $permission) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlentities($cnt) . "</td>";
                                                        echo "<td>" . htmlentities($permission['permission_category']) . "</td>";
                                                        echo "<td>" . htmlentities($permission['permission_name']) . "</td>";

                                                        // Check each account type for the current permission
                                                        foreach ($accountTypes as $accountType) {
                                                            $checkQuery = "SELECT 1 
                                                                        FROM role_permissions 
                                                                        JOIN permissions ON role_permissions.permission_id = permissions.permission_id
                                                                        WHERE permissions.permission_name = :permission 
                                                                        AND role_permissions.accounttype = :accounttype";
                                                            $checkStmt = $dbh->prepare($checkQuery);
                                                            $checkStmt->execute([
                                                                ':permission' => $permission['permission_name'], 
                                                                ':accounttype' => $accountType
                                                            ]);
                                                            $exists = $checkStmt->fetchColumn();

                                                            // Set color based on permission existence
                                                            $color = $exists ? "green" : "red";
                                                            $text = $exists ? "Yes" : "No";
                                                            echo "<td style='color: $color; font-weight: bold;'>$text</td>";
                                                        }

                                                        echo "</tr>";
                                                        $cnt++;
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Advanced Tables -->
                            </div>
                        </div>
                    </div>
                    <!-- End Panel -->
                </div>
            </div>
        </div>
        <!-- End Page Wrapper -->
    </div>
    <!-- End Wrapper -->

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
        <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
        <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
        <script src="assets/plugins/pace/pace.js"></script>
        <script src="assets/scripts/siminta.js"></script>
        <!-- Page-Level Plugin Scripts-->
        <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Initialize DataTable on page load
        $(document).ready(function() {
            $('#dataTable1').dataTable();
            $('#dataTable2').dataTable();
        });
    </script>

    <?php
    if ($messagestate == 'added' || $messagestate == 'deleted') {
        echo '<script type="text/javascript">
        function hideMsg() {
            document.getElementById("popup").style.visibility = "hidden";
        }
        document.getElementById("popup").style.visibility = "visible";
        window.setTimeout("hideMsg()", 5000);
        </script>';
    }
    ?>
</body>
</html>