<?php 
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

if (isset($_POST['submit'])) {
    try {
        // Sanitize and retrieve inputs
        $parentno      = trim($_POST['parentno']);
        $idno      = trim($_POST['idno']);
        $parentname    = trim($_POST['parentname']);
        $parentcontact = trim($_POST['parentcontact']);
        $proffesion    = trim($_POST['proffesion']);
        $homearea      = trim($_POST['homearea']);

        // Ensure PDO throws exceptions
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if contact already exists
        $checkStmt = $dbh->prepare("SELECT 1 FROM parentdetails WHERE parentcontact = :parentcontact LIMIT 1");
        $checkStmt->execute([':parentcontact' => $parentcontact]);

        if ($checkStmt->fetch()) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "❌ Contact Number <strong>($parentcontact)</strong> already exists. Duplicate entries are prohibited.";
        } else {
            // Insert new parent record
            $insertSql = "INSERT INTO parentdetails 
                (parentno, idno, parentname, parentcontact, proffesion, homearea) 
                VALUES 
                (:parentno, :idno, :parentname, :parentcontact, :proffesion, :homearea)";

            $insertStmt = $dbh->prepare($insertSql);
            $insertStmt->execute([
                ':parentno'      => $parentno,
                ':idno'          => $idno,
                ':parentname'    => $parentname,
                ':parentcontact' => $parentcontact,
                ':proffesion'    => $proffesion,
                ':homearea'      => $homearea
            ]);

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "✅ Parent record created successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "❌ System error: " . htmlspecialchars($e->getMessage());
    }
}

// Update logic (same as before)

if (isset($_POST['update'])) {
    try {
        // Capture form data
        $id = $_POST['id']; // VERY important
        $parentno = $_POST['parentno'];
        $idno = $_POST['idno'];
        $parentname = $_POST['parentname'];
        $parentcontact = $_POST['parentcontact'];
        $proffesion = $_POST['proffesion'];
        $homearea = $_POST['homearea'];

        // Set PDO to exception mode for safe operations
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if record exists based on ID
        $checkSql = "SELECT id FROM parentdetails WHERE id = :id";
        $checkStmt = $dbh->prepare($checkSql);
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // Proceed with update
            $updateSql = "UPDATE parentdetails 
                          SET parentname = :parentname, 
                              parentcontact = :parentcontact, 
                              proffesion = :proffesion, 
                              homearea = :homearea,
                              idno = :idno
                          WHERE id = :id";
            $updateStmt = $dbh->prepare($updateSql);
            $updateStmt->bindParam(':parentname', $parentname, PDO::PARAM_STR);
            $updateStmt->bindParam(':parentcontact', $parentcontact, PDO::PARAM_STR);
            $updateStmt->bindParam(':proffesion', $proffesion, PDO::PARAM_STR);
            $updateStmt->bindParam(':homearea', $homearea, PDO::PARAM_STR);
            $updateStmt->bindParam(':idno', $idno, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $updateStmt->execute();

            $_SESSION['messagestate'] = 'added';
            $_SESSION['mess'] = "Parent record updated successfully.";
        } else {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Error: Parent ID not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Database Error: " . $e->getMessage();
    }
}


// Handle deletion logic (same as before)
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM parentdetails WHERE id=$id";
        $dbh->exec($sql);

        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Parent Records DELETED successfully.";
    } catch (PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
    }
}

// CSV export logic (same page)
if (isset($_GET['download_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="parent_details.csv"');
    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, array('Name','parentno','idno', 'Contact', 'Home Area', 'Profession', 'Kids- as Mother', 'Kids- as Father', 'Kids- as Guardian'));

    // Fetch data from the database
    $sql = "SELECT 
                                                                    parentdetails.*, 
                                                                    COUNT(DISTINCT CASE WHEN studentdetails.motherparentno = parentdetails.parentno THEN studentdetails.motherparentno END) AS mother_count,
                                                                    COUNT(DISTINCT CASE WHEN studentdetails.fatherparentno = parentdetails.parentno THEN studentdetails.fatherparentno END) AS father_count,
                                                                    COUNT(DISTINCT CASE WHEN studentdetails.guardianparentno = parentdetails.parentno THEN studentdetails.guardianparentno END) AS guardian_count
                                                                FROM parentdetails
                                                                LEFT JOIN studentdetails ON studentdetails.motherparentno = parentdetails.parentno
                                                                LEFT JOIN studentdetails AS father ON father.fatherparentno = parentdetails.parentno
                                                                LEFT JOIN studentdetails AS guardian ON guardian.guardianparentno = parentdetails.parentno
                                                                GROUP BY parentdetails.id
                                                                ORDER BY parentdetails.parentname ASC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // Add rows to CSV file
    foreach ($results as $row) {
        fputcsv($output, array(         
            
            $row->parentname,
            $row->parentno,
            $row->idno,
            $row->parentcontact,
            $row->homearea,
            $row->proffesion, 
            $row->mother_count,
            $row->father_count,
            $row->guardian_count
        ));
    }

    fclose($output);
    exit();
}

$mess = "";
$currentdate = date("Y");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Parent Details</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        .show-students {
            color: #337ab7;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .show-students:hover {
            color: #23527c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <br>
                    <table>
                        <tr>
                            <td width="100%">
                                <h2 class="page-header">Manage Parent Details <i class="fa fa-user-friends" aria-hidden="true"></i></h2>
                            </td>
                            <td>
                            <?php if (has_permission($accounttype, 'new_parent')): ?>
                                <?php include('newparentpopup.php'); ?>
                                <a href="#myModal" data-toggle="modal" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Register new Parent</a>
                              <?php endif; ?>          
                              <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>  
                            <td>
                              <!-- Add CSV download button here -->
                                <a href="?download_csv=true" class="btn btn-info"><i class="fa fa-download"></i> Download CSV</a>                           
                            </td>                           
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <i class="fa fa-user-friends" aria-hidden="true"></i> Parent Details
                </div>
                <div class="panel-body">
                        <div class="table-responsive" style="overflow-x: auto; width: 100%">
              
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ParentNo</th>
                                    <th>IdNo</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Home Area</th>
                                    <th>Profession</th>
                                    <th>Learners</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $sql = "SELECT 
                                    p.*, 
                                    COALESCE(total_students.count, 0) AS total_students,
                                    COALESCE(student_roles.details, '') AS student_names
                                FROM parentdetails p
                                LEFT JOIN (
                                    SELECT parentno,
                                        COUNT(*) AS count
                                    FROM (
                                        SELECT motherparentno AS parentno FROM studentdetails
                                        UNION ALL
                                        SELECT fatherparentno AS parentno FROM studentdetails
                                        UNION ALL
                                        SELECT guardianparentno AS parentno FROM studentdetails
                                    ) combined
                                    GROUP BY parentno
                                ) total_students ON total_students.parentno = p.parentno
                                LEFT JOIN (
                                    SELECT parentno,
                                        GROUP_CONCAT(CONCAT(studentadmno, ' - ', studentname, ' (', role, ')') SEPARATOR '\n') AS details
                                    FROM (
                                        SELECT motherparentno AS parentno, studentadmno, studentname, 'Mother' AS role FROM studentdetails
                                        UNION ALL
                                        SELECT fatherparentno AS parentno, studentadmno, studentname, 'Father' AS role FROM studentdetails
                                        UNION ALL
                                        SELECT guardianparentno AS parentno, studentadmno, studentname, 'Guardian' AS role FROM studentdetails
                                    ) roleinfo
                                    GROUP BY parentno
                                ) student_roles ON student_roles.parentno = p.parentno
                                ORDER BY p.id DESC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                $cnt = 1;
                                if ($query->rowCount() > 0) {
                                    foreach ($results as $row) {
                                ?>
                                    <tr>
                                        <td><?php echo htmlentities($cnt); ?></td>
                                        <td><?php echo htmlentities($row->parentno); ?></td>
                                        <td><?php echo htmlentities($row->idno); ?></td>
                                        <td><?php echo htmlentities($row->parentname); ?></td>
                                        <td><?php echo htmlentities($row->parentcontact); ?></td>
                                        <td><?php echo htmlentities($row->homearea); ?></td>
                                        <td><?php echo htmlentities($row->proffesion); ?></td>
                                        <td>
                                            <?php if ($row->total_students > 0): ?>
                                                <a class="show-students" data-toggle="modal" data-target="#studentModal<?php echo $cnt; ?>">
                                                    <?php echo htmlentities($row->total_students); ?> Learner(s)
                                                </a>

                                                <!-- Modal -->
                                                <div class="modal fade" id="studentModal<?php echo $cnt; ?>" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel<?php echo $cnt; ?>" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="studentModalLabel<?php echo $cnt; ?>">
                                                                    Learners linked to <?php echo htmlentities($row->parentname); ?>
                                                                </h5>
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php echo nl2br(htmlentities($row->student_names ?: 'No learners linked.')); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                No students
                                            <?php endif; ?>
                                        </td>


                                        <td style="padding: 5px">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                    Action <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu pull-right" role="menu">
                                                    <?php if (has_permission($accounttype, 'edit_parent')): ?>
                                                        <li>
                                                            <a href="edit-parentdetails.php?editid=<?php echo htmlentities($row->id); ?>">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li class="divider"></li>
                                                        <li>
                                                            <a href="manage-parentdetails.php?delete=<?php echo htmlentities($row->id); ?>"
                                                            onclick="return confirm('You want to delete the record?!!')"
                                                            name="delete">
                                                                <i class="fa fa-trash-o"></i> Delete
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                        $cnt++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
         
        </div>
    </div>


    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTables-example').dataTable();
            $('#table-container').show();
        });

        document.addEventListener("DOMContentLoaded", function() {
            var form = document.getElementById("yourFormId");
            var submitButton = form.querySelector("button[type='submit']");

            form.addEventListener("submit", function() {
                submitButton.disabled = true;
            });
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        function admnoAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "checkadmnoforreg.php",
                data: 'parentname=' + $("#parentname").val(),
                type: "POST",
                success: function(data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function() {}
            });
        }

        <?php
        if ($_SESSION['messagestate'] == 'added' || $_SESSION['messagestate'] == 'deleted') {
            echo 'document.getElementById("popup").style.visibility = "visible";
            window.setTimeout(function() {
                document.getElementById("popup").style.visibility = "hidden";
            }, 5000);';
        }
        ?>
    </script>
    <script>
$(document).ready(function() {
    $(document).on('click', '.show-students', function(e) {
        e.preventDefault();
        
        var parentno = $(this).data('parentno');
        var role = $(this).data('role');
        var parentName = $(this).data('name');
        var title = '';

        switch(role) {
            case 'mother':
                title = 'Learners with this Mother';
                break;
            case 'father':
                title = 'Learners with this Father';
                break;
            case 'guardian':
                title = 'Learners with this Guardian';
                break;
        }

        $('#studentModalLabel').text(title);
        $('#parentName').text(parentName); // Inject parent name here

        $('#studentListBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading students...</td></tr>');

        $.ajax({
            url: 'fetch_students_by_parent.php',
            type: 'GET',
            data: {
                parentno: parentno,
                role: role
            },
            success: function(response) {
    $('#studentListBody').html(response.html);
    $('#parentName').text(response.parentName);
},

            error: function() {
                $('#studentListBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading students</td></tr>');
            }
        });

        $('#studentModal').modal('show');
    });
});

</script>
</body>

</html>

