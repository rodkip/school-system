<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else {
    // Fetch all parent records
    $smt = $dbh->prepare('SELECT * FROM parentdetails ORDER BY parentname DESC');
    $smt->execute();
    $data = $smt->fetchAll(PDO::FETCH_ASSOC);
    $parents = [];
    foreach ($data as $rw) {
        $parents[$rw['parentno']] = $rw['parentname'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS|Update S-Details</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .blue-text {
            color: #fff;
            background-color: #007bff;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        #ageMessage {
            color: red;
            font-size: 12px;
        }
        #ageMessage.valid {
            color: green;      
        }
        .profile-image-cell {
            text-align: center;
            vertical-align: middle;
            width: 40%;
            height: 610px;
            background-size: cover;
            background-position: center;
            border-left: 2px solid #ddd;
            background-color: #f9f9f9;
        }
        .form-column {
            width: 30%;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        select.form-control {
            appearance: none;
            background-color: #fff;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='5'%3E%3Cpath fill='%23333' d='M0 0l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .panel-primary {
            border: 1px solid #007bff;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
        }
        .panel-body {
            padding: 20px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group:last-child {
            margin-bottom: 0;
        }
        .form-control {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            height: 40px;
            border-color:rgb(200, 188, 209);
            font-weight: bold;
            color: chocolate;
        }
        .radio-option {
            display: flex;
            align-items: center;
            margin-right: 20px;
        }
        .radio-option small {
            margin-left: 5px;
        }
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
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
                    <h1 class="page-header">Update Student Details:  </h1>
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
                                <?php
                                    $eid = $_GET['editid'] ?? '';

                                    if ($eid) {
                                        $sql = "SELECT * FROM studentdetails WHERE id = :eid";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $row) {
                                                // Get existing parent numbers for this student
                                                $motherParentNo = $row->motherparentno ?? '';
                                                $fatherParentNo = $row->fatherparentno ?? '';
                                                $guardianParentNo = $row->guardianparentno ?? '';
                                ?>
                                    <form method="POST" enctype="multipart/form-data" action="manage-studentdetails.php">
                                    <input type="hidden" name="id" value="<?php echo $eid ?>">
                                        <table class="table">
                                            <tr class="blue-text">
                                                <td colspan="3"><strong style="font-size: larger;">Personal Details</strong></td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="admdate">Admission Date:</label>
                                                    <input type="text" class="form-control" name="admdate" id="admdate" required value="<?= htmlentities($row->admdate); ?>" readonly>
                                                </td>
                                                <td class="form-column">
                                                    <label for="studentadmno">AdmNo:</label>
                                                    <input type="text" name="studentadmno" id="studentadmno" required value="<?= htmlentities($row->studentadmno); ?>" class="form-control" onBlur="admnoAvailability()" readonly>
                                                    <span id="user-availability-status1" style="font-size:12px;"></span>
                                                </td>
                                                <td rowspan="20" class="profile-image-cell" id="profileImageCell">
                                                    <!-- Profile image preview area -->
                                                    <?php if (!empty($row->pfpicname)): ?>
                                                        <img src="studentimages/<?= htmlentities($row->pfpicname); ?>" style="max-width: 100%; max-height: 100%;" alt="Student Photo">
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="studentname">Full Names:</label>
                                                    <input type="text" class="form-control" name="studentname" id="studentname" required value="<?= htmlentities($row->studentname); ?>">
                                                </td>
                                                <td class="form-column"><label for="assessmentno">Assessment:</label>
                                                    <input type="text" class="form-control" name="assessmentno" id="assessmentno" value="<?= htmlentities($row->assessmentno); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="birthcertno">Birth Cert No:</label>
                                                    <input type="text" class="form-control" name="birthcertno" id="birthcertno" value="<?= htmlentities($row->birthcertno); ?>">
                                                </td>
                                                <td class="form-column"><label for="upicode">UPI Code:</label>
                                                    <input type="text" class="form-control" name="upicode" id="upicode" value="<?= htmlentities($row->upicode); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="dateofbirth">Date of Birth:</label>
                                                    <input type="date" class="form-control" name="dateofbirth" id="dateofbirth" required value="<?= htmlentities($row->dateofbirth); ?>">
                                                </td>
                                                <td class="form-column"><label for="gender">Gender:</label>
                                                    <select name="gender" class="form-control">
                                                        <option value="">--select gender--</option>
                                                        <option value="Male" <?= $row->gender == "Male" ? "selected" : "" ?>>Male</option>
                                                        <option value="Female" <?= $row->gender == "Female" ? "selected" : "" ?>>Female</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="previousschool">Previous School:</label>
                                                    <input type="text" class="form-control" name="previousschool" id="previousschool" value="<?= htmlentities($row->previousschool); ?>">
                                                </td>
                                                <td class="form-column"><label for="entryperformancelevel">Interview Points:</label>                                                   
                                                    <select class="form-control" name="entryperformancelevel" id="entryperformancelevel">
                                                                <option value="<?= htmlentities($row->entryperformancelevel); ?>"><?= htmlentities($row->entryperformancelevel); ?></option>
                                                                <option value="1 - Below Expectations">1 - Below Expectations</option>
                                                                <option value="2 - Approaching Expectations">2 - Approaching Expectations</option>
                                                                <option value="3 - Meets Expectations">3 - Meets Expectations</option>
                                                                <option value="4 - Exceeds Expectations">4 - Exceeds Expectations</option>
                                                                <option value="5 - Outstanding">5 - Outstanding</option>
                                                            </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column">
                                                    <label for="motherparentno"><i class="bi bi-person-vcard"></i> Mother ParentNo:</label>
                                                    <input type="text" class="form-control" name="motherparentno" id="motherparentno" 
                                                        placeholder="Enter ID No or Parent Name" 
                                                        list="motherparentno-list" autocomplete="off" 
                                                        onBlur="validateParentID('motherparentno', 'displaymothername'); updateRadioAvailability();"
                                                        value="<?= $motherParentNo ?>">
                                                    <datalist id="motherparentno-list">
                                                        <?php foreach ($data as $rw): ?>
                                                            <option value="<?= $rw["parentno"] ?>">
                                                                <?= $rw["parentno"] ?> - <?= $rw["parentname"] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </datalist>
                                                </td>
                                                <td class="form-column">
                                                    <label for="mothername"><i class="bi bi-person-circle"></i> Mother Name:</label>
                                                    <span id="displaymothername" style="font-size:18px; color:blue;">
                                                        <?= !empty($motherParentNo) && isset($parents[$motherParentNo]) ? $parents[$motherParentNo] : '' ?>
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="form-column">
                                                    <label for="fatherparentno"><i class="bi bi-person-vcard"></i> Father ParentNo:</label>
                                                    <input type="text" class="form-control" name="fatherparentno" id="fatherparentno" 
                                                        placeholder="Enter ID No or Parent Name" 
                                                        list="fatherparentno-list" autocomplete="off" 
                                                        onBlur="validateParentID('fatherparentno', 'displayfathername'); updateRadioAvailability();"
                                                        value="<?= $fatherParentNo ?>">
                                                    <datalist id="fatherparentno-list">
                                                        <?php foreach ($data as $rw): ?>
                                                            <option value="<?= $rw["parentno"] ?>">
                                                                <?= $rw["parentno"] ?> - <?= $rw["parentname"] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </datalist>
                                                </td>
                                                <td class="form-column">
                                                    <label for="fathername"><i class="bi bi-person-circle"></i> Father Name:</label>
                                                    <span id="displayfathername" style="font-size:18px; color:blue;">
                                                        <?= !empty($fatherParentNo) && isset($parents[$fatherParentNo]) ? $parents[$fatherParentNo] : '' ?>
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="form-column">
                                                    <label for="guardianparentno"><i class="bi bi-person-vcard"></i> Guardian ParentNo:</label>
                                                    <input type="text" class="form-control" name="guardianparentno" id="guardianparentno" 
                                                        placeholder="Enter ID No or Parent Name" 
                                                        list="guardianparentno-list" autocomplete="off" 
                                                        onBlur="validateParentID('guardianparentno', 'displayguardianname'); updateRadioAvailability();"
                                                        value="<?= $guardianParentNo ?>">
                                                    <datalist id="guardianparentno-list">
                                                        <?php foreach ($data as $rw): ?>
                                                            <option value="<?= $rw["parentno"] ?>">
                                                                <?= $rw["parentno"] ?> - <?= $rw["parentname"] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </datalist>
                                                </td>
                                                <td class="form-column">
                                                    <label for="guardianname"><i class="bi bi-person-circle"></i> Guardian Name:</label>
                                                    <span id="displayguardianname" style="font-size:18px; color:blue;">
                                                        <?= !empty($guardianParentNo) && isset($parents[$guardianParentNo]) ? $parents[$guardianParentNo] : '' ?>
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="form-column">
                                                    <label><strong>Who Pays Fees?</strong></label>
                                                    <div class="radio-group">
                                                        <?php
                                                        $payers = ["Father", "Mother", "Both Parents", "Guardian"];
                                                        foreach ($payers as $payer):
                                                            $canSelect = false;
                                                            if ($payer == "Father") {
                                                                $canSelect = !empty($fatherParentNo);
                                                            } elseif ($payer == "Mother") {
                                                                $canSelect = !empty($motherParentNo);
                                                            } elseif ($payer == "Guardian") {
                                                                $canSelect = !empty($guardianParentNo);
                                                            } elseif ($payer == "Both Parents") {
                                                                $canSelect = !empty($fatherParentNo) && !empty($motherParentNo);
                                                            }
                                                        ?>
                                                        <div class="radio-option">
                                                            <input type="radio" name="feepayer" value="<?= $payer ?>" 
                                                                <?= $row->feepayer == $payer ? "checked" : "" ?>
                                                                <?= !$canSelect ? "disabled" : "" ?>
                                                                id="feepayer_<?= str_replace(' ', '_', strtolower($payer)) ?>">
                                                            <label for="feepayer_<?= str_replace(' ', '_', strtolower($payer)) ?>">
                                                                <?= $payer ?>
                                                                <?php if (!$canSelect): ?>
                                                                    <small style="color:red;">(No data)</small>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                                <td class="form-column">
                                                    <label><strong>Who Receives FeeBalanceReminder SMS?</strong></label>
                                                    <div class="radio-group">
                                                        <?php
                                                        $feebalancereminderOptions = ["Father", "Mother", "Both Parents", "Guardian"];
                                                        foreach ($feebalancereminderOptions as $option):
                                                            $canSelect = false;
                                                            if ($option == "Father") {
                                                                $canSelect = !empty($fatherParentNo);
                                                            } elseif ($option == "Mother") {
                                                                $canSelect = !empty($motherParentNo);
                                                            } elseif ($option == "Guardian") {
                                                                $canSelect = !empty($guardianParentNo);
                                                            } elseif ($option == "Both Parents") {
                                                                $canSelect = !empty($fatherParentNo) && !empty($motherParentNo);
                                                            }
                                                        ?>
                                                        <div class="radio-option">
                                                            <input type="radio" name="feebalancereminder" value="<?= $option ?>" 
                                                                <?= $row->feebalancereminder == $option ? "checked" : "" ?>
                                                                <?= !$canSelect ? "disabled" : "" ?>
                                                                id="smsreminder_<?= str_replace(' ', '_', strtolower($option)) ?>">
                                                            <label for="smsreminder_<?= str_replace(' ', '_', strtolower($option)) ?>">
                                                                <?= $option ?>
                                                                <?php if (!$canSelect): ?>
                                                                    <small style="color:red;">(No data)</small>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="emergencyname">Emergency Name:</label>
                                                    <input type="text" class="form-control" name="emergencyname" id="emergencyname" value="<?= htmlentities($row->emergencyname); ?>">
                                                </td>
                                                <td class="form-column"><label for="emergencycontact">Emergency Contact:</label>
                                                    <input type="text" class="form-control" name="emergencycontact" id="emergencycontact" value="<?= htmlentities($row->emergencycontact); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="form-column"><label for="homecounty">Home County:</label>
                                                    <input type="text" class="form-control" name="homecounty" id="homecounty" value="<?= htmlentities($row->homecounty); ?>">
                                                </td>
                                                <td class="form-column"><label for="homesubcounty">Home Subcounty:</label>
                                                    <input type="text" class="form-control" name="homesubcounty" id="homesubcounty" value="<?= htmlentities($row->homesubcounty); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="form-column">
                                                    <label for="healthissue">Any Health Issue (Explain Clearly):</label>
                                                    <textarea class="form-control" name="healthissue" id="healthissue" rows="3"><?= htmlentities($row->healthissue); ?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="form-column">
                                                    <label for="allergy">Any Allergy:</label>
                                                    <textarea class="form-control" name="allergy" id="allergy" rows="3"><?= htmlentities($row->allergy); ?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="form-column">
                                                    <label for="insurancecover">Any Insurance Cover:</label>
                                                    <textarea class="form-control" name="insurancecover" id="insurancecover" rows="3"><?= htmlentities($row->insurancecover); ?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="form-column">
                                                    <label><strong>Does the child have a special doctor?</strong></label>
                                                    <div style="margin-top: 10px;">
                                                        <label style="margin-right: 20px;">
                                                            <input type="radio" name="special_doctor" value="Yes" id="special_doctor_yes" onclick="toggleDoctorDetails()" <?= $row->special_doctor == 'Yes' ? 'checked' : '' ?>> Yes
                                                        </label>
                                                        <label>
                                                            <input type="radio" name="special_doctor" value="No" id="special_doctor_no" onclick="toggleDoctorDetails()" <?= $row->special_doctor == 'No' ? 'checked' : '' ?>> No
                                                        </label>
                                                    </div>
                                                    <div id="doctor_details" style="margin-top: 10px; display: <?= $row->special_doctor == 'Yes' ? 'block' : 'none' ?>;">
                                                        <label for="doctor_name">Doctor's Name:</label>
                                                        <input type="text" class="form-control" name="doctor_name" id="doctor_name" value="<?= htmlentities($row->doctor_name); ?>">
                                                        <label for="doctor_contact">Doctor's Contact:</label>
                                                        <input type="text" class="form-control" name="doctor_contact" id="doctor_contact" value="<?= htmlentities($row->doctor_contact); ?>">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="form-column">
                                                    <b>Profile Picture: <input type="file" name="pfpicname" id="pfpicname" class="form-control"></b>
                                                </td>
                                            </tr>
                                        </table>

                                        <div class="form-group mt-3">
                                            <button type="submit" name="update" class="btn btn-primary">Update</button>
                                        </div>

                                        <?php if (isset($_SESSION['message'])): ?>
                                            <div class="alert alert-info mt-2"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                                        <?php endif; ?>
                                    </form>
                                <?php
                                            } // end foreach
                                        } else {
                                            echo "<p>No student found with the provided ID.</p>";
                                        }
                                    } else {
                                        echo "<p>Invalid request: edit ID missing.</p>";
                                    }
                                ?>
                                </div>
                            </div>
                        </div>
                    </div>
                     <!-- End Form Elements -->
                </div>
            </div>
        </div>
        <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->

    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('admdate').value = today;

            // Initialize profile image preview
            var profileImage = document.getElementById('profileImageCell');
            var currentImage = profileImage.querySelector('img');
            if (currentImage) {
                profileImage.style.backgroundImage = 'url(' + currentImage.src + ')';
                currentImage.style.display = 'none';
            }

            document.getElementById('pfpicname').addEventListener('change', function(event) {
                var file = event.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profileImageCell').style.backgroundImage = `url('${e.target.result}')`;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Initialize radio button availability
            updateRadioAvailability();
        });

        function checkAge() {
            var dobInput = document.getElementById('dob').value;
            var dob = new Date(dobInput);
            var currentDate = new Date();
            var age = currentDate.getFullYear() - dob.getFullYear();
            var ageMessage = document.getElementById('ageMessage');

            if (age < 18) {
                ageMessage.innerHTML = 'The staff must be at least 18 years old. Check the DOB again';
                ageMessage.classList.remove('valid');
            } else {
                ageMessage.innerHTML = 'Valid age!';
                ageMessage.classList.add('valid');
            }
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

        // Store parent details from PHP to JavaScript
        var parentDetails = <?php echo json_encode($parents); ?>;
        
        function validateParentID(inputId, displayId) {
            let idInput = document.getElementById(inputId);
            let parentNameDisplay = document.getElementById(displayId);

            if (parentDetails[idInput.value]) {
                parentNameDisplay.textContent = parentDetails[idInput.value];
                parentNameDisplay.style.color = "blue";
            } else {
                parentNameDisplay.textContent = "Error: ID Number not found!";
                parentNameDisplay.style.color = "red";
                idInput.value = "";
            }
        }

        function toggleDoctorDetails() {
            var doctorDetailsDiv = document.getElementById('doctor_details');
            var specialDoctorYes = document.getElementById('special_doctor_yes');

            if (specialDoctorYes.checked) {
                doctorDetailsDiv.style.display = 'block';
            } else {
                doctorDetailsDiv.style.display = 'none';
            }
        }

        // Function to update radio button availability
        function updateRadioAvailability() {
            // Get current parent values
            const motherParentNo = document.getElementById('motherparentno').value;
            const fatherParentNo = document.getElementById('fatherparentno').value;
            const guardianParentNo = document.getElementById('guardianparentno').value;
            
            // Update Fee Payer options
            updateRadioOption('feepayer_father', fatherParentNo);
            updateRadioOption('feepayer_mother', motherParentNo);
            updateRadioOption('feepayer_guardian', guardianParentNo);
            updateRadioOption('feepayer_both_parents', fatherParentNo && motherParentNo);
            
            // Update SMS Reminder options
            updateRadioOption('smsreminder_father', fatherParentNo);
            updateRadioOption('smsreminder_mother', motherParentNo);
            updateRadioOption('smsreminder_guardian', guardianParentNo);
            updateRadioOption('smsreminder_both_parents', fatherParentNo && motherParentNo);
        }
        
        function updateRadioOption(radioId, hasData) {
            const radio = document.getElementById(radioId);
            if (radio) {
                radio.disabled = !hasData;
                // Find the parent label and update the "(No data)" indicator
                const label = radio.closest('.radio-option').querySelector('label');
                if (label) {
                    let indicator = label.querySelector('small');
                    if (!indicator && !hasData) {
                        indicator = document.createElement('small');
                        indicator.style.color = 'red';
                        indicator.textContent = '(No data)';
                        label.appendChild(indicator);
                    } else if (indicator && hasData) {
                        label.removeChild(indicator);
                    }
                }
            }
        }
    </script>
</body>
</html>