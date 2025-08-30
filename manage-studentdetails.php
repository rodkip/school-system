<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Redirect if user is not logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $messagestate = false;
    $mess = "";
    $dobestimate = date('Y-m-d', strtotime('-5 years'));

    // Handle new record submission
    if (isset($_POST['submit'])) {
        try {
            $studentadmno = $_POST['studentadmno'];
            $sql = "SELECT * FROM studentdetails WHERE studentadmno = :studentadmno";
            $query = $dbh->prepare($sql);
            $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            if ($query->rowCount() > 0) {
                $_SESSION['messagestate'] = 'deleted';
                $_SESSION['mess'] = "Record NOT saved - DUPLICATE AdmNo.";
            } else {
                // Retrieve all form fields
                $studentname = $_POST['studentname'];
                $gender = $_POST['gender'];
                $dateofbirth = $_POST['dateofbirth'];
                $birthcertno = $_POST['birthcertno'];
                $upicode = $_POST['upicode'];
                $assessmentno = $_POST['assessmentno']; 
                $previousschool = $_POST['previousschool'];
                $entryperformancelevel = $_POST['entryperformancelevel'];
                $motherparentno = $_POST['motherparentno'];
                $fatherparentno = $_POST['fatherparentno'];
                $guardianparentno = $_POST['guardianparentno'];
                $emergencyname = $_POST['emergencyname'];
                $emergencycontact = $_POST['emergencycontact'];
                $homecounty = $_POST['homecounty'];
                $homesubcounty = $_POST['homesubcounty'];
                $healthissue = $_POST['healthissue'];
                $allergy = $_POST['allergy'];
                $insurancecover = $_POST['insurancecover'];
                $feepayer = $_POST['feepayer'];
                $special_doctor = $_POST['special_doctor'];
                $doctor_name = $_POST['doctor_name'];
                $doctor_contact = $_POST['doctor_contact'];
                $admdate = $_POST['admdate'];
                $feebalancereminder = $_POST['feebalancereminder'];

                $pfpicname = $_FILES['pfpicname']['name'];
                if ($pfpicname) {
                    $target_dir = "pfpics/";
                    $file_extension = pathinfo($pfpicname, PATHINFO_EXTENSION);
                    $new_file_name = $studentadmno . '.' . $file_extension;
                    $target_file = $target_dir . $new_file_name;
                    move_uploaded_file($_FILES['pfpicname']['tmp_name'], $target_file);
                    $pfpicname = $new_file_name;
                }

                $sql = "INSERT INTO studentdetails (
                    studentadmno, studentname, gender, dateofbirth, birthcertno, upicode, 
                    previousschool, entryperformancelevel, motherparentno, fatherparentno, guardianparentno, 
                    emergencyname, emergencycontact, homecounty, homesubcounty, healthissue, 
                    allergy, insurancecover, pfpicname, feepayer, special_doctor, doctor_name, doctor_contact, 
                    assessmentno, admdate, feebalancereminder
                ) VALUES (
                    :studentadmno, :studentname, :gender, :dateofbirth, :birthcertno, :upicode, 
                    :previousschool, :entryperformancelevel, :motherparentno, :fatherparentno, :guardianparentno, 
                    :emergencyname, :emergencycontact, :homecounty, :homesubcounty, :healthissue, 
                    :allergy, :insurancecover, :pfpicname, :feepayer, :special_doctor, :doctor_name, :doctor_contact, 
                    :assessmentno, :admdate, :feebalancereminder
                )";

                $query = $dbh->prepare($sql);
                $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_INT);
                $query->bindParam(':studentname', $studentname);
                $query->bindParam(':gender', $gender);
                $query->bindParam(':dateofbirth', $dateofbirth);
                $query->bindParam(':birthcertno', $birthcertno);
                $query->bindParam(':upicode', $upicode);
                $query->bindParam(':assessmentno', $assessmentno);
                $query->bindParam(':previousschool', $previousschool);
                $query->bindParam(':entryperformancelevel', $entryperformancelevel);
                $query->bindParam(':motherparentno', $motherparentno);
                $query->bindParam(':fatherparentno', $fatherparentno);
                $query->bindParam(':guardianparentno', $guardianparentno);
                $query->bindParam(':emergencyname', $emergencyname);
                $query->bindParam(':emergencycontact', $emergencycontact);
                $query->bindParam(':homecounty', $homecounty);
                $query->bindParam(':homesubcounty', $homesubcounty);
                $query->bindParam(':healthissue', $healthissue);
                $query->bindParam(':allergy', $allergy);
                $query->bindParam(':insurancecover', $insurancecover);
                $query->bindParam(':pfpicname', $pfpicname);
                $query->bindParam(':feepayer', $feepayer);
                $query->bindParam(':special_doctor', $special_doctor);
                $query->bindParam(':doctor_name', $doctor_name);
                $query->bindParam(':doctor_contact', $doctor_contact);
                $query->bindParam(':admdate', $admdate);
                $query->bindParam(':feebalancereminder', $feebalancereminder);
                $query->execute();

                $_SESSION['insert_success'] = true;
                $_SESSION['studentname'] = $studentname;  
                $_SESSION['studentadmno'] = $studentadmno; 
                $_SESSION['admdate'] = $admdate;
                $_SESSION['messagestate'] = 'added';
                $_SESSION['mess'] = "Learner Records CREATED successfully.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

// Handle registration fee
if (isset($_POST['receiveregfee_submit'])) {
    $studentadmno = $_POST['studentadmno'] ?? '';
    $username = $_POST['username'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $bank = $_POST['bank'] ?? '';
    $admdate = $_POST['admdate'] ?? '';

    if (!empty($studentadmno) && !empty($amount) && !empty($bank)) {
        try {
            // Begin a transaction to ensure both queries execute together
            $dbh->beginTransaction();

            // Insert the registration fee into the regfeepayments table
            $stmt = $dbh->prepare("INSERT INTO regfeepayments (studentadmno, amount, bank, username,admdate) 
                                   VALUES (:studentadmno, :amount, :bank, :username,:admdate)");

            $stmt->bindParam(':studentadmno', $studentadmno);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':bank', $bank);
            $stmt->bindParam(':admdate', $admdate);
            $stmt->bindParam(':username', $username);

            if ($stmt->execute()) {
                // Update the studentdetails table to mark the registration fee as paid
                $updateStmt = $dbh->prepare("UPDATE studentdetails SET regfee = 'Paid' WHERE studentadmno = :studentadmno");
                $updateStmt->bindParam(':studentadmno', $studentadmno);
                $updateStmt->execute();

                // Commit the transaction
                $dbh->commit();

                $_SESSION['messagestate'] = 'added';
                $_SESSION['mess'] = "Registration fee recorded successfully and student status updated.";
            } else {
                // If insert fails, roll back the transaction
                $dbh->rollBack();
                $_SESSION['messagestate'] = 'deleted';
                $_SESSION['mess'] = "Failed to save payment. Please try again.";
            }
        } catch (Exception $e) {
            // In case of an exception, roll back the transaction
            $dbh->rollBack();
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "An error occurred: " . $e->getMessage();
        }
    } else {
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "All fields are required.";
    }

    header("Location: manage-studentdetails.php"); // change to your actual page
    exit();
}


    // Handle record update
    if (isset($_POST['update'])) {
        try {
            $id = $_POST['id'];

            $sql = "SELECT * FROM studentdetails WHERE id = :id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);

            if ($query->rowCount() == 0) {
                $_SESSION['messagestate'] = 'deleted';
                $_SESSION['mess'] = "Record NOT found - No such student with this Id.";
            } else {
                $studentadmno = $_POST['studentadmno'];
                $studentname = $_POST['studentname'];
                $gender = $_POST['gender'];
                $dateofbirth = $_POST['dateofbirth'];
                $birthcertno = $_POST['birthcertno'];
                $upicode = $_POST['upicode'];
                $assessmentno = $_POST['assessmentno'];
                $previousschool = $_POST['previousschool'];
                $entryperformancelevel = $_POST['entryperformancelevel'];
                $motherparentno = $_POST['motherparentno'];
                $fatherparentno = $_POST['fatherparentno'];
                $guardianparentno = $_POST['guardianparentno'];
                $emergencyname = $_POST['emergencyname'];
                $emergencycontact = $_POST['emergencycontact'];
                $homecounty = $_POST['homecounty'];
                $homesubcounty = $_POST['homesubcounty'];
                $healthissue = $_POST['healthissue'];
                $allergy = $_POST['allergy'];
                $insurancecover = $_POST['insurancecover'];
                $feepayer = $_POST['feepayer'];
                $special_doctor = $_POST['special_doctor'];
                $doctor_name = $_POST['doctor_name'];
                $doctor_contact = $_POST['doctor_contact'];
                $feebalancereminder = $_POST['feebalancereminder'];

                $pfpicname = $_FILES['pfpicname']['name'];
                $updatePhoto = false;

                if (!empty($pfpicname)) {
                    $target_dir = "pfpics/";
                    $file_extension = pathinfo($pfpicname, PATHINFO_EXTENSION);
                    $new_file_name = $studentadmno . '.' . $file_extension;
                    $target_file = $target_dir . $new_file_name;
                    move_uploaded_file($_FILES['pfpicname']['tmp_name'], $target_file);
                    $pfpicname = $new_file_name;
                    $updatePhoto = true;
                }

                $sql = "UPDATE studentdetails SET 
                    studentname = :studentname, 
                    gender = :gender, 
                    dateofbirth = :dateofbirth, 
                    birthcertno = :birthcertno, 
                    upicode = :upicode, 
                    assessmentno = :assessmentno,
                    previousschool = :previousschool, 
                    entryperformancelevel = :entryperformancelevel, 
                    motherparentno = :motherparentno, 
                    fatherparentno = :fatherparentno, 
                    guardianparentno = :guardianparentno, 
                    emergencyname = :emergencyname, 
                    emergencycontact = :emergencycontact, 
                    homecounty = :homecounty, 
                    homesubcounty = :homesubcounty, 
                    healthissue = :healthissue, 
                    allergy = :allergy, 
                    insurancecover = :insurancecover, 
                    feepayer = :feepayer, 
                    special_doctor = :special_doctor, 
                    doctor_name = :doctor_name, 
                    doctor_contact = :doctor_contact, 
                    feebalancereminder = :feebalancereminder";

                if ($updatePhoto) {
                    $sql .= ", pfpicname = :pfpicname";
                }

                $sql .= " WHERE studentadmno = :studentadmno";

                $query = $dbh->prepare($sql);
                $query->bindParam(':studentadmno', $studentadmno);
                $query->bindParam(':studentname', $studentname);
                $query->bindParam(':gender', $gender);
                $query->bindParam(':dateofbirth', $dateofbirth);
                $query->bindParam(':birthcertno', $birthcertno);
                $query->bindParam(':upicode', $upicode);
                $query->bindParam(':assessmentno', $assessmentno);
                $query->bindParam(':previousschool', $previousschool);
                $query->bindParam(':entryperformancelevel', $entryperformancelevel);
                $query->bindParam(':motherparentno', $motherparentno);
                $query->bindParam(':fatherparentno', $fatherparentno);
                $query->bindParam(':guardianparentno', $guardianparentno);
                $query->bindParam(':emergencyname', $emergencyname);
                $query->bindParam(':emergencycontact', $emergencycontact);
                $query->bindParam(':homecounty', $homecounty);
                $query->bindParam(':homesubcounty', $homesubcounty);
                $query->bindParam(':healthissue', $healthissue);
                $query->bindParam(':allergy', $allergy);
                $query->bindParam(':insurancecover', $insurancecover);
                $query->bindParam(':feepayer', $feepayer);
                $query->bindParam(':special_doctor', $special_doctor);
                $query->bindParam(':doctor_name', $doctor_name);
                $query->bindParam(':doctor_contact', $doctor_contact);
                $query->bindParam(':feebalancereminder', $feebalancereminder);

                if ($updatePhoto) {
                    $query->bindParam(':pfpicname', $pfpicname);
                }

                $query->execute();
                $_SESSION['messagestate'] = 'added';
                $_SESSION['mess'] = "Student record successfully updated.";
            }
        } catch (PDOException $e) {
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Error updating record: " . $e->getMessage();
        }
    }

    // Handle deletion block remains unchanged...
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Learners Details</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
  
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
                                <h2 class="page-header">Manage Learners Details <i class="fa fa-child"></i></h2>
                            </td>
                            <td>
                            <?php if (has_permission($accounttype, 'new_learner')): ?>
                                <a href='new-studententry.php' class='btn btn-primary'>
                                    <i class='fa fa-user-plus fa-fw'></i> Register new Student
                                </a>
                                <?php endif; ?>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><?php include_once('updatemessagepopup.php'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php if (!empty($_SESSION['insert_success'])) : ?>
                <script>
                    window.onload = function () {
                    $('#followUpModal').modal({ backdrop: 'static', keyboard: false });
                    };

                    $(document).ready(function () {
                    $('#followUpModal').on('shown.bs.modal', function () {
                        $(this).find('input[name="amount"]').trigger('focus');
                    });
                    });
                </script>
                <?php unset($_SESSION['insert_success']); ?>
                <?php endif; ?>

                <!-- Modal for follow-up actions -->
            <div class="modal fade" id="followUpModal" tabindex="-1" role="dialog" aria-labelledby="followUpLabel" aria-hidden="true"
                    data-backdrop="static" data-keyboard="false">

                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h3 class="modal-title" id="followUpLabel">Admission Fee Payment</h3>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    <div class="modal-body">
                        <!-- Header Info -->
                        <p><strong>Learner:</strong> <?= htmlspecialchars($_SESSION['studentname'] ?? '') ?></p>
                        <p><strong>Admission Number:</strong> <?= htmlspecialchars($_SESSION['studentadmno'] ?? '') ?></p>

                        <!-- Payment Form in Table -->
                        <form method="post" enctype="multipart/form-data" action="manage-studentdetails.php">
                        <input type="hidden" name="studentadmno" value="<?= htmlspecialchars($_SESSION['studentadmno'] ?? '') ?>">
                        <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                        <input type="hidden" name="admdate" value="<?= htmlspecialchars($_SESSION['admdate'] ?? '') ?>>">
                        <table class="table table-borderless align-middle">
                            <tr>
                            <td><label for="amount">Amount</label></td>
                            <td>
                                <input type="number" name="amount" class="form-control" required>
                            </td>
                            <td><label for="bank">Payment Method</label></td>
                            <td>
                                <select name="bank" class="form-control" required>
                                <option value="">-- Select Bank --</option>
                                <?php
                                $smt = $dbh->prepare('SELECT bankname FROM bankdetails');
                                $smt->execute();
                                $data = $smt->fetchAll();
                                foreach ($data as $rw): ?>
                                    <option value="<?= htmlspecialchars($rw["bankname"]) ?>"><?= htmlspecialchars($rw["bankname"]) ?></option>
                                <?php endforeach; ?>
                                </select>
                            </td>
                            </tr>
                            <tr>
                            <td colspan="4" class="text-right">
                                <button type="submit" name="receiveregfee_submit" class="btn btn-success mr-2">Submit Payment</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Pay Later</button>
                            </td>
                            </tr>
                        </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
           <!-- Show all records-->     
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><i class="fa fa-child"></i> Registered Learners</div>
                <div class="panel-body">
                    <div class="table-responsive" style="overflow-x: auto; width: 100%">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>AdmNo</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>DOB</th>
                                <th>Age</th>
                                <th>Home Area</th>
                                <th>Previous School</th>
                                <th>Reg Fee</th>
                                <th>CurrentGrade</th>
                                <th>ParentInfo</th> <!-- New column -->
                                <th>Other Details</th>
                                <th></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT 
                                sd.id, sd.studentadmno, sd.studentname, sd.gender, sd.dateofbirth, 
                                sd.birthcertno, sd.upicode, sd.assessmentno, sd.motherparentno, sd.fatherparentno, 
                                sd.guardianparentno, sd.emergencyname, sd.emergencycontact, 
                                sd.homecounty, sd.homesubcounty, sd.healthissue, sd.allergy, sd.insurancecover,
                                sd.pfpicname, sd.feepayer,sd.feebalancereminder, sd.special_doctor, sd.doctor_name, 
                                sd.doctor_contact, sd.previousschool, sd.regfee, sd.admdate, 
                                sd.entryperformancelevel, sd.entrydate, 
                                pd_mother.homearea AS motherhomearea,
                                pd_father.homearea AS fatherhomearea,
                                pd_guardian.homearea AS guardianhomearea,
                                pd_mother.parentname AS mothername, 
                                pd_father.parentname AS fathername, 
                                pd_guardian.parentname AS guardianname, 
                                pd_mother.parentcontact AS mothercontact, 
                                pd_father.parentcontact AS fathercontact, 
                                pd_guardian.parentcontact AS guardiancontact,
                                COALESCE(ce_current.gradefullname, 'N/A') AS current_year_grade,
                                CASE 
                                    WHEN pd_mother.parentno IS NOT NULL OR pd_father.parentno IS NOT NULL OR pd_guardian.parentno IS NOT NULL 
                                    THEN 'Yes' ELSE 'No' 
                                END AS has_parent_info
                            FROM studentdetails sd
                            LEFT JOIN parentdetails pd_mother ON sd.motherparentno = pd_mother.parentno
                            LEFT JOIN parentdetails pd_father ON sd.fatherparentno = pd_father.parentno
                            LEFT JOIN parentdetails pd_guardian ON sd.guardianparentno = pd_guardian.parentno
                            LEFT JOIN (
                                SELECT studentadmno, gradefullname
                                FROM classentries
                                WHERE LEFT(gradefullname, 4) = YEAR(CURDATE())
                                GROUP BY studentadmno
                            ) ce_current ON ce_current.studentadmno = sd.studentadmno
                            ORDER BY sd.id DESC";

                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                                    $photoPath = !empty($row->pfpicname) ? 'studentphotos/'.$row->pfpicname : 'assets/images/default-profile.png';
                            ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td><?php echo htmlentities($row->studentadmno); ?></td>
                                    <td><?php echo htmlentities($row->studentname); ?></td>
                                    <td><?php echo htmlentities($row->gender); ?></td>
                                    <td><?php echo htmlentities($row->dateofbirth); ?></td>
                                    <td><?php echo round((time() - strtotime($row->dateofbirth)) / (3600 * 24 * 365.25)); ?></td>
                                    <td><?php echo htmlentities($row->homearea); ?></td>
                                    <td><?php echo htmlentities($row->previousschool); ?></td>
                                    <td style="color: <?php echo ($row->regfee == 'Unpaid') ? 'red' : 'green'; ?>;">
                                        <?php echo ($row->regfee == 'Unpaid') ? '❌ ' : '✅ '; ?>
                                        <?php echo htmlentities($row->regfee); ?>
                                    </td>
                                    <td><?php echo htmlentities($row->current_year_grade); ?></td>
                                    <td>
                                        <?php echo ($row->has_parent_info === 'Yes') ? '✅ Yes' : '❌ No'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $popup_student_data = $row;
                                            include('viewstudentdetailspopup.php'); 
                                        ?>
                                        <a href="#otherstudentdetails<?php echo $cnt; ?>" data-toggle="modal">
                                            <i class="fa fa-bars" aria-hidden="true"></i> All RegDetails
                                        </a>
                                    </td>
                                    <td style="padding: 0; margin: 0;">
                                        <?php 
                                        $photoFile = $row->pfpicname ?? '';
                                        $photoPath = 'pfpics/' . htmlspecialchars($photoFile, ENT_QUOTES, 'UTF-8');

                                        if (!empty($photoFile) && file_exists($photoPath)) {
                                        ?>
                                            <a href="#" data-toggle="modal" data-target="#photoModal<?php echo $cnt; ?>">
                                                <img src="<?php echo $photoPath; ?>" 
                                                    alt="Profile Picture" 
                                                    style="width: 40px; height: 40px; object-fit: cover; cursor: pointer; padding: 0; margin: 0; border: none;">
                                            </a>

                                            <div class="modal fade" id="photoModal<?php echo $cnt; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" style="max-width: 80%;">
                                                    <div class="modal-content">
                                                        <div class="modal-body text-center p-0">
                                                            <img src="<?php echo $photoPath; ?>" 
                                                                alt="Profile Picture" 
                                                                style="width: 100%; height: auto; max-height: 80vh; object-fit: contain;">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <td style="padding: 5px">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                Action <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu pull-right" role="menu">
                                                <?php if (has_permission($accounttype, 'edit_learner')): ?>
                                                    <li>
                                                        <a href="edit-studentdetails.php?editid=<?php echo htmlentities($row->id); ?>">
                                                            <i class="fa fa-pencil"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li class="divider"></li>
                                                    <li>
                                                        <a href="manage-studentdetails.php?delete=<?php echo htmlentities($row->id); ?>"
                                                        onclick="return confirm('You want to delete the record?!!')" name="delete">
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
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        function admnoAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "checkadmnoforreg.php",
                data: 'studentadmno=' + $("#studentadmno").val(),
                type: "POST",
                success: function (data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function () {}
            });
        }

        <?php
        if ($messagestate == 'added' || $messagestate == 'deleted') {
            echo 'document.getElementById("popup").style.visibility = "visible";
                  window.setTimeout(function() {
                      document.getElementById("popup").style.visibility = "hidden";
                  }, 5000);';
        }
        ?>
    </script>

    <script>
        function fetchStudentDetails(studentadmno) {
            $.ajax({
                url: 'fetch-student-details.php',
                type: 'POST',
                data: { studentadmno: studentadmno },
                success: function(response) {
                    $('#studentDetailsContent').html(response);
                },
                error: function(xhr, status, error) {
                    $('#studentDetailsContent').html('<p class="text-danger">Error loading student details.</p>');
                }
            });
        }
    </script>
</body>

</html>