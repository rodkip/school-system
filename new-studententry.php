<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid'])==0) {
  header('location:logout.php');
} else {
    $eid=$_GET['editid'] ?? '';    
    $dobestimate = date('Y-m-d', strtotime('-4 years'));
}

// Handle AJAX request for checking student name
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_name'])) {
    $studentname = trim($_POST['studentname']);
    $response = ['exists' => false, 'error' => null];
    
    if (!empty($studentname)) {
        try {
            // Case-insensitive check for student name
            $stmt = $dbh->prepare("SELECT COUNT(*) FROM studentdetails WHERE LOWER(studentname) = LOWER(?)");
            $stmt->execute([$studentname]);
            $count = $stmt->fetchColumn();
            $response['exists'] = ($count > 0);
        } catch (PDOException $e) {
            $response['error'] = $e->getMessage();
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | New Student Details</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --border-radius: 6px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .page-header {
            color: var(--dark-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .panel-primary {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background-color: white;
        }
        
        .panel-body {
            padding: 25px;
        }
        
        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .form-section {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .profile-section {
            flex: 0 0 300px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-image-container {
            width: 100%;
            height: 300px;
            background-color: #f1f5f9;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            overflow: hidden;
            border: 2px dashed #ccc;
            transition: var(--transition);
        }
        
        .profile-image-container:hover {
            border-color: var(--primary-color);
        }
        
        .profile-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .profile-image-placeholder {
            text-align: center;
            color: #7a7a7a;
        }
        
        .profile-image-placeholder i {
            font-size: 50px;
            margin-bottom: 10px;
            color: #b8c2cc;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            background-color: #f8fafc;
            color: initial; /* Default color */
        }

        /* Change text color to green when input is filled */
        .form-control:not(:placeholder-shown) {
            color: green;
        }

        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
            background-color: white;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-primary i {
            font-size: 16px;
        }
        
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
        }
        
        .radio-option input {
            margin-right: 8px;
        }
        
        .status-message {
            padding: 8px 12px;
            border-radius: var(--border-radius);
            margin-top: 5px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-checking {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            font-size: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        @media (max-width: 1200px) {
            .form-container {
                flex-direction: column;
            }
            
            .profile-section {
                order: -1;
                flex-direction: row;
                align-items: flex-start;
            }
            
            .profile-image-container {
                width: 150px;
                height: 150px;
                margin-right: 20px;
                margin-bottom: 0;
            }
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .profile-section {
                flex-direction: column;
                align-items: center;
            }
            
            .profile-image-container {
                width: 100%;
                height: 200px;
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
        
        /* Animation classes */
        .shake {
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
        }
        
    
       
        
        /* Floating labels */
        .floating-label-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .floating-label {
            position: absolute;
            pointer-events: none;
            left: 15px;
            top: 12px;
            transition: var(--transition);
            background: white;
            padding: 0 5px;
            color: #7a7a7a;
            font-size: 14px;
        }
        
        .form-control:focus ~ .floating-label,
        .form-control:not(:placeholder-shown) ~ .floating-label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: var(--primary-color);
            background: white;
        }
        
        /* Required field indicator */
        .required:after {
            content: " *";
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php');?>
        <?php include_once('includes/sidebar.php');?>
        <div id="page-wrapper" class="animate__animated animate__fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">
                        <i class="bi bi-person-plus-fill"></i> New Learner Admission
                        <small class="text-muted">Register a new student</small>
                    </h1>
                    <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <form method="post" enctype="multipart/form-data" action="manage-studentdetails.php" id="studentForm" class="form-container">
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-card-heading"></i> Basic Information</h3>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="admdate" class="required"><i class="bi bi-calendar2-week"></i> Admission Date</label>
                                            <input type="date" class="form-control" name="admdate" id="admdate" required>
                                        </div>
                                        <?php 
                                            $stmt = $dbh->prepare("SELECT MAX(CAST(studentadmno AS UNSIGNED)) AS max_admno FROM studentdetails");
                                            $stmt->execute();
                                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $lastAdmno = $row['max_admno'];
                                            $newAdmno = $lastAdmno ? $lastAdmno + 1 : 1;
                                        ?>
                                        <div class="form-group">
                                            <label for="studentadmno" class="required"><i class="bi bi-person-badge"></i> Admission Number</label>
                                            <input type="text" name="studentadmno" id="studentadmno" required
                                                placeholder="Enter AdmNo here" value="<?php echo htmlspecialchars($newAdmno); ?>" 
                                                class="form-control" onBlur="admnoAvailability()">
                                            <span id="user-availability-status1" class="status-message"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="studentname" class="required"><i class="bi bi-person-fill"></i> Full Name</label>
                                        <input type="text" class="form-control" name="studentname" id="studentname" required 
                                            placeholder="Enter learner's full name" onblur="checkStudentName()" oninput="startNameCheckTimer()">
                                        <span id="nameStatus" class="status-message"></span>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="birthcertno"><i class="bi bi-file-earmark-text"></i> Birth Certificate No.</label>
                                            <input type="text" class="form-control" name="birthcertno" id="birthcertno" placeholder="Enter birth certificate number">
                                        </div>
                                        <div class="form-group">
                                            <label for="upicode"><i class="bi bi-code-square"></i> UPI Code</label>
                                            <input type="text" class="form-control" name="upicode" id="upicode" placeholder="Enter UPI code">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="dateofbirth" class="required"><i class="bi bi-calendar-heart"></i> Date of Birth</label>
                                            <input type="date" class="form-control" name="dateofbirth" id="dateofbirth" 
                                                placeholder="Enter DOB here" value="<?php echo $dobestimate; ?>" required>
                                            <span id="ageMessage" class="status-message"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="gender" class="required"><i class="bi bi-gender-ambiguous"></i> Gender</label>
                                            <select name="gender" class="form-control" required> 
                                                <option value="">-- Select gender --</option>
                                                <option value="Male">Male</option> 
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="previousschool"><i class="bi bi-building"></i> Previous School</label>
                                        <input type="text" class="form-control" name="previousschool" id="previousschool" placeholder="Enter previous school name">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="entryperformancelevel"><i class="bi bi-bar-chart-line"></i> Entry Performance Level</label>
                                        <select class="form-control" name="entryperformancelevel" id="entryperformancelevel">
                                            <option value="">-- Select performance level --</option>
                                            <option value="1 - Below Expectations">1 - Below Expectations</option>
                                            <option value="2 - Approaching Expectations">2 - Approaching Expectations</option>
                                            <option value="3 - Meets Expectations">3 - Meets Expectations</option>
                                            <option value="4 - Exceeds Expectations">4 - Exceeds Expectations</option>
                                            <option value="5 - Outstanding">5 - Outstanding</option>
                                        </select>
                                    </div>
                               
                                        <h3 class="section-title"><i class="bi bi-heart-pulse"></i> Health Information</h3>
                                        
                                        <div class="form-group">
                                            <label for="healthissue"><i class="bi bi-heart-pulse"></i> Health Issues</label>
                                            <textarea class="form-control" name="healthissue" id="healthissue" rows="2" placeholder="Any health conditions?"></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="allergy"><i class="bi bi-exclamation-triangle"></i> Allergies</label>
                                            <textarea class="form-control" name="allergy" id="allergy" rows="2" placeholder="Any known allergies?"></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="insurancecover"><i class="bi bi-shield-plus"></i> Insurance Cover</label>
                                            <textarea class="form-control" name="insurancecover" id="insurancecover" rows="2" placeholder="Insurance details if any"></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label><i class="bi bi-person-vcard-fill"></i> Special Doctor?</label>
                                            <div class="radio-group">
                                                <label class="custom-radio">
                                                    <input type="radio" name="special_doctor" value="Yes" id="special_doctor_yes" onclick="toggleDoctorDetails()">
                                                    <span class="radio-checkmark"></span>
                                                    <span>Yes</span>
                                                </label>
                                                <label class="custom-radio">
                                                    <input type="radio" name="special_doctor" value="No" id="special_doctor_no" onclick="toggleDoctorDetails()" checked>
                                                    <span class="radio-checkmark"></span>
                                                    <span>No</span>
                                                </label>
                                            </div>
                                            <div id="doctor_details" style="margin-top: 10px; display: none;">
                                                <div class="form-group">
                                                    <label for="doctor_name"><i class="bi bi-person-fill-check"></i> Doctor's Name</label>
                                                    <input type="text" class="form-control" name="doctor_name" id="doctor_name" placeholder="Doctor's name">
                                                </div>
                                                <div class="form-group">
                                                    <label for="doctor_contact"><i class="bi bi-telephone-forward"></i> Doctor's Contact</label>
                                                    <input type="text" class="form-control" name="doctor_contact" id="doctor_contact" placeholder="Doctor's phone number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-people-fill"></i> Parent & Guardian Information</h3>
                                    
                                    <?php
                                    // Fetch all parent records
                                    $smt = $dbh->prepare('SELECT * FROM parentdetails ORDER BY parentname DESC');
                                    $smt->execute();
                                    $data = $smt->fetchAll(PDO::FETCH_ASSOC);

                                    // Build a map of parentno => parentname
                                    $parents = [];
                                    foreach ($data as $rw) {
                                        $parents[$rw['parentno']] = $rw['parentname'];
                                    }
                                    ?>

                                    <?php function generateFormRow($label, $id, $name, $value) { ?>
                                        <div class="form-group">
                                            <label for="<?= $id ?>"><i class="bi bi-person-vcard"></i> <?= $label ?> Parent/Guardian ID</label>
                                            <input type="text" class="form-control" name="<?= $id ?>" id="<?= $id ?>" 
                                                placeholder="Enter ID No or Parent Name" 
                                                list="parentdetails-list" autocomplete="off" 
                                                onBlur="validateParentID('<?= $id ?>', 'display<?= $name ?>name')" 
                                                value="<?= $value ?>">
                                            <datalist id="parentdetails-list">
                                                <?php foreach ($GLOBALS['data'] as $rw): ?>
                                                    <option value="<?= $rw["parentno"] ?>">
                                                        <?= $rw["parentno"] ?> - <?= $rw["parentname"] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </datalist>
                                            <div id="display<?= $name ?>name" class="status-message"></div>
                                        </div>
                                    <?php } ?>

                                    <?php generateFormRow('Mother', 'motherparentno', 'mother', $rlt->parentno); ?>
                                    <?php generateFormRow('Father', 'fatherparentno', 'father', $rlt->parentno); ?>
                                    <?php generateFormRow('Guardian', 'guardianparentno', 'guardian', $rlt->parentno); ?>
                                    
                                    <div class="form-group">
                                        <label ><i class="bi bi-cash-coin"></i> Who Pays Fees?</label>
                                        <div class="radio-group">
                                            <?php
                                            $feepayerOptions = ["Father", "Mother", "Both Parents", "Guardian"];
                                            foreach ($feepayerOptions as $option):
                                            ?>
                                            <label class="custom-radio">
                                                <input type="radio" name="feepayer" value="<?= $option ?>" <?= $row->feepayer == $option ? "checked" : "" ?>>
                                                <span class="radio-checkmark"></span>
                                                <span><?= $option ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><i class="bi bi-chat-dots"></i> Who Receives Fee Balance SMS?</label>
                                        <div class="radio-group">
                                            <?php
                                            $feebalancereminderOptions = ["Father", "Mother", "Both Parents", "Guardian"];
                                            foreach ($feebalancereminderOptions as $option):
                                            ?>
                                            <label class="custom-radio">
                                                <input type="radio" name="feebalancereminder" value="<?= $option ?>" <?= $row->feebalancereminder == $option ? "checked" : "" ?>>
                                                <span class="radio-checkmark"></span>
                                                <span><?= $option ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="emergencyname"><i class="bi bi-person-lines-fill"></i> Emergency Contact Name</label>
                                            <input type="text" class="form-control" name="emergencyname" id="emergencyname" placeholder="Emergency contact name">
                                        </div>
                                        <div class="form-group">
                                            <label for="emergencycontact"><i class="bi bi-telephone-fill"></i> Emergency Contact Number</label>
                                            <input type="text" class="form-control" name="emergencycontact" id="emergencycontact" placeholder="Emergency phone number">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="homecounty"><i class="bi bi-geo-alt-fill"></i> Home County</label>
                                            <input type="text" class="form-control" name="homecounty" id="homecounty" placeholder="Enter home county">
                                        </div>
                                        <div class="form-group">
                                            <label for="homesubcounty"><i class="bi bi-geo"></i> Home Subcounty</label>
                                            <input type="text" class="form-control" name="homesubcounty" id="homesubcounty" placeholder="Enter home subcounty">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="profile-section">
                                    <h3 class="section-title"><i class="bi bi-image"></i> Student Photo</h3>
                                    
                                    <div class="profile-image-container" id="profileImageContainer">
                                        <div class="profile-image-placeholder">
                                            <i class="bi bi-person-square"></i>
                                            <p>No photo selected</p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group" style="width: 100%;">
                                        <label for="pfpicname"><i class="bi bi-upload"></i> Upload Profile Picture</label>
                                        <input type="file" name="pfpicname" id="pfpicname" class="form-control" 
                                            accept="image/*" capture="camera">
                                        <small class="text-muted">Max size: 2MB (Recommended: 300x300px)</small>
                                    </div>
                                   
                                    
                                   <button type="submit" name="submit" class="btn btn-primary" id="submitBtn" style="height: 100px; width: 100%; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-save"></i> Save Learner's Record
                                    </button>

                                </div>
                            </form>
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
    <script>
        // Global variables
        let isNameAvailable = false;
        let nameCheckTimer = null;
        const submitButton = document.getElementById('submitBtn');
        const studentForm = document.getElementById('studentForm');
        const studentNameInput = document.getElementById('studentname');
        const nameStatus = document.getElementById('nameStatus');
        const profileImageContainer = document.getElementById('profileImageContainer');

        // Initialize form
        document.addEventListener("DOMContentLoaded", function() {
            // Set admission date to today
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('admdate').value = today;

            // Profile picture preview
            document.getElementById('pfpicname').addEventListener('change', function(event) {
                var file = event.target.files[0];
                if (file) {
                    // Validate file size (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size exceeds 2MB limit. Please choose a smaller file.');
                        this.value = '';
                        return;
                    }
                    
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        // Remove placeholder
                        const placeholder = profileImageContainer.querySelector('.profile-image-placeholder');
                        if (placeholder) {
                            placeholder.remove();
                        }
                        
                        // Create or update image
                        let img = profileImageContainer.querySelector('img');
                        if (!img) {
                            img = document.createElement('img');
                            profileImageContainer.appendChild(img);
                        }
                        img.src = e.target.result;
                        img.alt = "Student Photo Preview";
                        
                        // Add animation
                        profileImageContainer.classList.add('animate__animated', 'animate__pulse');
                        setTimeout(() => {
                            profileImageContainer.classList.remove('animate__animated', 'animate__pulse');
                        }, 1000);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Initialize submit button state
            updateSubmitButton();
        });

        // Start timer for name checking (debounce)
        function startNameCheckTimer() {
            clearTimeout(nameCheckTimer);
            nameCheckTimer = setTimeout(checkStudentName, 500);
        }

        // Check if student name exists
        function checkStudentName() {
            const studentName = studentNameInput.value.trim();
            
            // Clear previous status
            nameStatus.innerHTML = '';
            nameStatus.className = '';
            
            if (studentName === '') {
                isNameAvailable = false;
                updateSubmitButton();
                return;
            }
            
            // Show loading indicator
            nameStatus.innerHTML = '<i class="bi bi-hourglass"></i> Checking availability...';
            nameStatus.className = 'status-message status-checking';
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'check_name=true&studentname=' + encodeURIComponent(studentName)
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    showNameStatus('<i class="bi bi-exclamation-triangle-fill"></i> Error checking name', 'status-error');
                    isNameAvailable = false;
                } else if (data.exists) {
                    showNameStatus('<i class="bi bi-x-circle-fill"></i> Student name already exists!', 'status-error');
                    isNameAvailable = false;
                    studentNameInput.classList.add('animate__animated', 'animate__shakeX');
                    setTimeout(() => {
                        studentNameInput.classList.remove('animate__animated', 'animate__shakeX');
                    }, 1000);
                } else {
                    showNameStatus('<i class="bi bi-check-circle-fill"></i> Name available', 'status-success');
                    isNameAvailable = true;
                }
                updateSubmitButton();
            })
            .catch(error => {
                showNameStatus('<i class="bi bi-exclamation-triangle-fill"></i> Error checking name', 'status-error');
                isNameAvailable = false;
                updateSubmitButton();
                console.error('Error:', error);
            });
        }

        // Helper function to show name status
        function showNameStatus(message, className) {
            nameStatus.innerHTML = message;
            nameStatus.className = 'status-message ' + className;
        }

        // Update submit button state
        function updateSubmitButton() {
            if (submitButton) {
                const formValid = isNameAvailable && studentForm.checkValidity();
                submitButton.disabled = !formValid;
                
                if (formValid) {
                    submitButton.classList.remove('btn-secondary');
                    submitButton.classList.add('btn-primary');
                    submitButton.title = "";
                } else {
                    submitButton.classList.remove('btn-primary');
                    submitButton.classList.add('btn-secondary');
                    submitButton.title = "Please complete all required fields correctly";
                }
                
                // Initialize tooltips if Bootstrap is available
                if (typeof $ !== 'undefined' && $.fn.tooltip) {
                    $('[title]').tooltip();
                }
            }
        }

        // ADM Number availability check
        function admnoAvailability() {
            const statusElement = document.getElementById('user-availability-status1');
            const admnoInput = document.getElementById('studentadmno');
            
            statusElement.innerHTML = '<i class="bi bi-hourglass"></i> Checking...';
            statusElement.className = 'status-message status-checking';
            
            jQuery.ajax({
                url: "checkadmnoforreg.php",
                data: 'studentadmno=' + admnoInput.value,
                type: "POST",
                success: function (data) {
                    if (data.includes('TAKEN')) {
                        statusElement.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + data;
                        statusElement.className = 'status-message status-error';
                        admnoInput.value = '';
                        admnoInput.classList.add('animate__animated', 'animate__shakeX');
                        setTimeout(() => {
                            admnoInput.classList.remove('animate__animated', 'animate__shakeX');
                        }, 1000);
                    } else {
                        statusElement.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + data;
                        statusElement.className = 'status-message status-success';
                    }
                },
                error: function () {
                    statusElement.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Error checking ADM number';
                    statusElement.className = 'status-message status-error';
                }
            });
        }

        // Parent validation functions
        var parentDetails = <?php echo json_encode($parents); ?>;

        function validateParentID(inputId, displayId) {
            const idInput = document.getElementById(inputId);
            const parentNameDisplay = document.getElementById(displayId);
            const enteredParentNo = idInput.value.trim();

            if (enteredParentNo in parentDetails) {
                parentNameDisplay.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + parentDetails[enteredParentNo];
                parentNameDisplay.className = 'status-message status-success';
            } else {
                parentNameDisplay.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Parent not registered!';
                parentNameDisplay.className = 'status-message status-error';
                idInput.value = "";
            }
        }

        // Toggle doctor details
        function toggleDoctorDetails() {
            var doctorDetailsDiv = document.getElementById('doctor_details');
            var specialDoctorYes = document.getElementById('special_doctor_yes');

            if (specialDoctorYes.checked) {
                doctorDetailsDiv.style.display = 'block';
                doctorDetailsDiv.classList.add('animate__animated', 'animate__fadeIn');
            } else {
                doctorDetailsDiv.classList.add('animate__animated', 'animate__fadeOut');
                setTimeout(() => {
                    doctorDetailsDiv.style.display = 'none';
                    doctorDetailsDiv.classList.remove('animate__animated', 'animate__fadeOut');
                }, 500);
            }
        }

        // Age validation
        function checkAge() {
            var dobInput = document.getElementById('dateofbirth').value;
            var dob = new Date(dobInput);
            var currentDate = new Date();
            var age = currentDate.getFullYear() - dob.getFullYear();
            var ageMessage = document.getElementById('ageMessage');

            if (age < 2) {
                ageMessage.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Learner must be at least 2 years old';
                ageMessage.className = 'status-message status-error';
            } else {
                ageMessage.innerHTML = '<i class="bi bi-check-circle-fill"></i> Valid age';
                ageMessage.className = 'status-message status-success';
            }
        }

        // Add event listener for DOB change
        document.getElementById('dateofbirth').addEventListener('change', checkAge);
        
        // Form validation on input
        studentForm.addEventListener('input', function() {
            updateSubmitButton();
        });
    </script>
</body>
</html>