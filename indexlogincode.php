<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Handle login code verification
if (isset($_POST['verifyCode'])) {
    $enteredCode = $_POST['loginCode'];

    if (isset($_SESSION['loginCode']) && $_SESSION['loginCodeExpires'] > time()) {
        if ($enteredCode == $_SESSION['loginCode']) {
            // Successful login, reset session
            unset($_SESSION['loginCode']);
            unset($_SESSION['loginCodeExpires']);
            unset($_SESSION['notificationMethod']);
            
            // Show custom alert
            $_SESSION['customAlert'] = [
                'title' => 'Success!',
                'message' => 'Login successful!',
                'type' => 'success'
            ];
            header("Location: dashboard.php");
            exit();
        } else {
            // Invalid login code
            $_SESSION['messagestate'] = 'deleted';
            $_SESSION['mess'] = "Invalid login code";
            
            // Show custom alert
            $_SESSION['customAlert'] = [
                'title' => 'Error!',
                'message' => 'Invalid login code',
                'type' => 'error'
            ];
        }
    } else {
        // Expired code
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Login code expired or not generated";
        
        // Show custom alert
        $_SESSION['customAlert'] = [
            'title' => 'Error!',
            'message' => 'Login code expired or not generated',
            'type' => 'error'
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Field-Team Database System | Login-Code Page</title>
    <link rel="icon" href="images/tabpic.png">
    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>

body {
    background-color: #1E1E1E; /* Dark Gray */
    color: #F5F5F5; /* Light Gray */
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

label {
    display: grid;
    letter-spacing: 4px;
    padding-top: 25px;
    position: relative;
}

label .label-text {
    position: absolute;
    top: 5px;
    left: 20px;
    color: #9b9b9b; /* Gray for placeholder text */
    cursor: text;
    font-size: 15px;
    line-height: 20px;
    transition: all 0.3s ease-in-out;
}

label input {
    background-color: transparent;
    border: none;
    border-bottom: 2px solid #4a4a4a; /* Gray border */
    color: #F5F5F5; /* Light Gray */
    font-size: 20px;
    letter-spacing: 1px;
    outline: none;
    padding: 10px 20px;
    width: 220px;
    transition: all 0.3s ease-in-out;
}

label input:focus + .label-text,
label input:valid + .label-text {
    font-size: 13px;
    color: #00CED1; /* Teal for focused label */
    transform: translateY(-30px);
}

label input:hover {
    border-bottom: 2px solid #FF6F61; /* Coral on hover */
}

button {
    background: linear-gradient(135deg, #00CED1, #9370DB); /* Teal to Soft Purple */
    border: none;
    color: #F5F5F5; /* Light Gray */
    font-size: 15px;
    letter-spacing: 2px;
    padding: 15px 50px;
    cursor: pointer;
    margin: 20px;
    border-radius: 10px;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 4px 10px rgba(0, 206, 209, 0.5); /* Teal shadow */
}

button:hover {
    background: linear-gradient(135deg, #9370DB, #00CED1); /* Reverse gradient on hover */
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 206, 209, 0.7); /* Stronger shadow on hover */
}

/* Updated .gif-container with timer effects */
.gif-container {
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #1E1E1E, #333); /* Dark gradient background */
    color: #00CED1; /* Teal color for the text */
    text-align: center;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 206, 209, 0.3); /* Teal shadow for depth */
    position: relative;
    overflow: hidden;
    animation: fadeIn 1s ease-in-out;
}
.gif-container {
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #1E1E1E, #333); /* Dark gradient background */
    color: #00CED1; /* Teal color for the text */
    text-align: center;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 206, 209, 0.3); /* Teal shadow for depth */
    position: relative;
    overflow: hidden;
    animation: fadeIn 1s ease-in-out;
    z-index: 1; /* Ensure the container has a lower z-index */
}

.gif-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 15px;
    border: 2px solid transparent;
    background: linear-gradient(135deg, #00CED1, #9370DB) border-box;
    mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: destination-out;
    mask-composite: exclude;
    animation: glow 2s infinite alternate ease-in-out;
    z-index: -1; /* Ensure the glow is behind the input field */
}


/* Timer-specific styles */
#timer {
    font-size: 4rem; /* Large font size for visibility */
    font-weight: bold;
    color: #00CED1; /* Teal color for the text */
    margin: 0;
}

/* Keyframes for glowing effect */
@keyframes glow {
    0% {
        box-shadow: 0 0 10px rgba(0, 206, 209, 0.5), 0 0 20px rgba(0, 206, 209, 0.3);
    }
    100% {
        box-shadow: 0 0 20px rgba(0, 206, 209, 0.8), 0 0 40px rgba(0, 206, 209, 0.5);
    }
}

/* Keyframes for fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
        /* Custom Alert Styles */
        .alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .alert-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .alert-box {
            background: #2a2a2a;
            border-radius: 10px;
            padding: 25px;
            width: 350px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            transform: translateY(-20px);
            transition: all 0.3s ease;
            border-left: 5px solid;
        }
        
        .alert-overlay.active .alert-box {
            transform: translateY(0);
        }
        
        .alert-box.success {
            border-left-color: #00CED1;
        }
        
        .alert-box.error {
            border-left-color: #FF6F61;
        }
        
        .alert-box.warning {
            border-left-color: #FFD700;
        }
        
        .alert-title {
            font-size: 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .alert-title i {
            margin-right: 10px;
        }
        
        .alert-message {
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .alert-close {
            background: linear-gradient(135deg, #00CED1, #9370DB);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            float: right;
            transition: all 0.3s ease;
        }
        
        .alert-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,206,209,0.4);
        }
    </style>
</head>
<body class="body-Login-back" style="background-image: url('images/researchplusbackground.jpg'); background-position: center; background-size: cover;">
    <!-- Custom Alert Overlay -->
    <div class="alert-overlay" id="alertOverlay">
        <div class="alert-box" id="alertBox">
            <div class="alert-title" id="alertTitle">
                <i id="alertIcon"></i>
                <span id="alertTitleText"></span>
            </div>
            <div class="alert-message" id="alertMessage"></div>
            <button class="alert-close" onclick="hideAlert()">OK</button>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center logo-margin">
                <strong style="color:darkgreen;font-size: 20px;font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; text-shadow: 1px 1px 2px black, 0 0 25px blue, 0 0 5px darkblue">
                    <br><br>
                </strong>
            </div>
            <?php include_once('updatemessagepopup.php');?>
            <div class="col-md-4 col-md-offset-4">
                <div class="gif-container">                
                    <div class="panel-body">
                        <?php
                        $methodText = "Check login code sent to you";
                        $methodIcon = "";
                        
                        if (isset($_SESSION['notificationMethod'])) {
                            switch ($_SESSION['notificationMethod']) {
                                case 'email':
                                    $methodText = "Check login code sent to your email";
                                    $methodIcon = "<i class='fa fa-envelope' style='margin-right: 8px; color: #00CED1;'></i>";
                                    break;
                                case 'sms':
                                    $methodText = "Check login code sent via SMS";
                                    $methodIcon = "<i class='fa fa-mobile' style='margin-right: 8px; color: #00CED1;'></i>";
                                    break;
                                case 'both':
                                    $methodText = "Check login code sent to your email and via SMS";
                                    $methodIcon = "<i class='fa fa-envelope' style='margin-right: 8px; color: #00CED1;'></i><i class='fa fa-mobile' style='margin-right: 8px; color: #00CED1;'></i>";
                                    break;
                                default:
                                    $methodText = "Check login code";
                            }
                        }
                        ?>
                        <h4><?php echo $methodIcon . htmlspecialchars($methodText); ?></h4>
                        <div id="timer">15:00</div>
                        <form role="form" method="post" name="verifyCode">
                            <label for="loginCode">Login Code:</label>
                            <input type="text" class="form-control" id="loginCode" name="loginCode" required>
                            <button type="submit" class="btn btn-primary" name="verifyCode">Verify</button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Back to Index Page</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    
    <script>
        // Timer functionality remains the same...
        let time = 900;
        const timerElement = document.getElementById('timer');
        const loginCodeInput = document.getElementById('loginCode');
        const verifyButton = document.querySelector('button[name="verifyCode"]');

        const updateTimer = () => {
            let minutes = Math.floor(time / 60);
            let seconds = time % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            timerElement.innerHTML = `${minutes}:${seconds}`;
            time--;

            if (time < 0) {
                clearInterval(timerInterval);
                timerElement.innerHTML = 'Code expired';
                loginCodeInput.disabled = true;
                verifyButton.disabled = true;
            }
        };

        const timerInterval = setInterval(updateTimer, 1000);

        // Custom Alert System
        function showAlert(title, message, type) {
            const overlay = document.getElementById('alertOverlay');
            const box = document.getElementById('alertBox');
            const titleText = document.getElementById('alertTitleText');
            const messageText = document.getElementById('alertMessage');
            const icon = document.getElementById('alertIcon');
            
            // Set content
            titleText.textContent = title;
            messageText.textContent = message;
            
            // Set styling based on type
            box.className = 'alert-box ' + type;
            
            // Set icon
            if (type === 'success') {
                icon.className = 'fa fa-check-circle';
            } else if (type === 'error') {
                icon.className = 'fa fa-times-circle';
            } else {
                icon.className = 'fa fa-info-circle';
            }
            
            // Show alert
            overlay.classList.add('active');
        }
        
        function hideAlert() {
            document.getElementById('alertOverlay').classList.remove('active');
        }
        
        // Check for alert in session
        <?php if (isset($_SESSION['customAlert'])): ?>
            showAlert(
                '<?php echo $_SESSION['customAlert']['title']; ?>',
                '<?php echo $_SESSION['customAlert']['message']; ?>',
                '<?php echo $_SESSION['customAlert']['type']; ?>'
            );
            <?php unset($_SESSION['customAlert']); ?>
        <?php endif; ?>
    </script>
</body>
</html>