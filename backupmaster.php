<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
ini_set('display_errors', 1);
// Set PHP timezone to match your local system
date_default_timezone_set('Africa/Nairobi');
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

// Updated backup locations that work for both local and online
$backup_locations = [
    __DIR__ . '/backups/',  // Creates a backups folder in your script's directory
    sys_get_temp_dir() . '/',  // System temp directory
];

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = '';
$backup_path = '';

// Check if backup_schedules table exists, if not create it
try {
    $dbh->query("SELECT 1 FROM backup_schedules LIMIT 1");
} catch (PDOException $e) {
    // Table doesn't exist, create it
    $createTableSQL = "CREATE TABLE backup_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        schedule_name VARCHAR(255) NOT NULL,
        frequency ENUM('daily', 'weekly', 'monthly') NOT NULL,
        time TIME NOT NULL,
        day TINYINT NULL,
        date TINYINT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        next_run DATETIME NOT NULL,
        last_run DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $dbh->exec($createTableSQL);
}

function rotateBackups($directory, $currentBackup, $maxBackups = 5) {
    $files = array_filter(glob($directory . 'backup-' . DB_NAME . '*.sql'), function($file) use ($currentBackup) {
        return $file !== $currentBackup;
    });

    if (count($files) >= $maxBackups) {
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        $toDelete = (count($files) - $maxBackups) + 1;

        for ($i = 0; $i < $toDelete; $i++) {
            if (isset($files[$i]) && file_exists($files[$i]) && filesize($files[$i]) > 0) {
                @unlink($files[$i]);
                error_log("Deleted old backup: " . $files[$i]);
            }
        }
    }
}

function createBackup($backup_path) {
    try {
        $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get all tables
        $tables = $dbh->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        
        $output = "-- Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Host: " . DB_HOST . "\n";
        $output .= "-- Database: " . DB_NAME . "\n\n";
        
        foreach ($tables as $table) {
            $output .= "--\n-- Table structure for table `$table`\n--\n\n";
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            
            $createTable = $dbh->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $output .= $createTable['Create Table'] . ";\n\n";
            
            $output .= "--\n-- Dumping data for table `$table`\n--\n\n";
            
            $rows = $dbh->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows)) {
                $columns = array_keys($rows[0]);
                $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES \n";
                
                $values = array();
                foreach ($rows as $row) {
                    $rowValues = array();
                    foreach ($row as $value) {
                        $rowValues[] = is_null($value) ? 'NULL' : $dbh->quote($value);
                    }
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                }
                $output .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        if (file_put_contents($backup_path, $output) !== false) {
            return true;
        }
        return false;
        
    } catch (PDOException $e) {
        error_log("Backup failed: " . $e->getMessage());
        return false;
    }
}

function calculateNextRun($frequency, $time, $day = null, $date = null) {
    $now = time();
    $timeParts = explode(':', $time);
    $hour = (int)$timeParts[0];
    $minute = (int)$timeParts[1];
    
    if ($frequency === 'daily') {
        $nextRun = mktime($hour, $minute, 0, date('n', $now), date('j', $now) + 1, date('Y', $now));
    } elseif ($frequency === 'weekly') {
        $currentDayOfWeek = date('w', $now);
        $daysToAdd = (7 + $day - $currentDayOfWeek) % 7;
        if ($daysToAdd === 0) $daysToAdd = 7; // Next week
        $nextRun = mktime($hour, $minute, 0, date('n', $now), date('j', $now) + $daysToAdd, date('Y', $now));
    } elseif ($frequency === 'monthly') {
        $currentDay = date('j', $now);
        $currentMonth = date('n', $now);
        $currentYear = date('Y', $now);
        
        if ($currentDay < $date) {
            // This month
            $nextRun = mktime($hour, $minute, 0, $currentMonth, $date, $currentYear);
        } else {
            // Next month
            if ($currentMonth == 12) {
                $nextRun = mktime($hour, $minute, 0, 1, $date, $currentYear + 1);
            } else {
                $nextRun = mktime($hour, $minute, 0, $currentMonth + 1, $date, $currentYear);
            }
        }
    }
    
    return date('Y-m-d H:i:s', $nextRun);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_backup'])) {
        $username = trim($_POST['username']);

        if (empty($username)) {
            $message = "<div style='color:red;'><strong>Error:</strong> Username cannot be empty.</div>";
        } else {
            foreach ($backup_locations as $location) {
                if (!file_exists($location)) {
                    @mkdir($location, 0755, true);
                }

                if (is_writable($location)) {
                    $backup_file = 'backup-' . DB_NAME . '-' . date("Y-m-d-H-i-s") . '.sql';
                    $backup_path = $location . $backup_file;
                    break;
                }
            }

            if (empty($backup_path)) {
                $message = "<div style='color:red;'><strong>Error:</strong> Could not find a writable backup location.</div>";
            } else {
                if (createBackup($backup_path)) {
                    $sql = 'SELECT notificationemails FROM notificationssettings WHERE notificationname = "Email BackUp" AND notificationstatus = "Active"';
                    $stmt = $dbh->prepare($sql);

                    if (!$stmt->execute()) {
                        error_log("Error checking notification settings: " . implode(" ", $stmt->errorInfo()));
                    }

                    if ($stmt->rowCount() > 0) {
                        $notificationData = $stmt->fetch(PDO::FETCH_ASSOC);
                        $recipientEmails = $notificationData['notificationemails'];

                        // Sanitize and split emails, remove spaces
                        $recipientEmails = preg_replace('/\s+/', '', $recipientEmails);
                        $emailList = explode(',', $recipientEmails);

                        // Validate emails
                        $validEmails = array_filter($emailList, function($email) {
                            return filter_var($email, FILTER_VALIDATE_EMAIL);
                        });

                        if (!empty($validEmails)) {
                            try {
                                $mail = new PHPMailer(true);

                                $mail->SMTPDebug = 0;
                                $mail->isSMTP();
                                $mail->Host       = 'mail.kipmetzsolutions.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'rplusfieldteamdatabase@kipmetzsolutions.com';
                                $mail->Password   = 'ynU(0HCq?TDt';
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                $mail->setFrom('rplusfieldteamdatabase@kipmetzsolutions.com', 'Database Backup System');
                                foreach ($validEmails as $email) {
                                    $mail->addAddress($email);
                                }
                                $mail->addReplyTo('info@kipmetzsolutions.com', 'Information');

                                $mail->isHTML(true);
                                $mail->Subject = 'Elgon Hills Schools Backup - ' . date("Y-m-d H:i:s");
                                $mail->Body    = 'Please find attached the latest database backup.<br>'
                                               . 'Backup file: ' . htmlspecialchars(basename($backup_path)) . '<br>'
                                               . 'Size: ' . round(filesize($backup_path) / 1024, 2) . ' KB<br>'
                                               . 'Generated by: ' . htmlspecialchars($username) . '<br>'
                                               . 'Generated on: ' . date("F j, Y, g:i a");
                                $mail->AltBody = 'Database backup attached. File: ' . basename($backup_path);

                                $mail->addAttachment($backup_path, basename($backup_path));
                                $mail->send();

                                $message = "<div style='color:green;'><strong>Backup and email sent successfully!</strong><br>";
                                $message .= "Sent to: " . htmlspecialchars(implode(', ', $validEmails)) . "<br>";
                            } catch (Exception $e) {
                                $message = "<div style='color:red;'><strong>Backup created but email failed:</strong> " 
                                         . htmlspecialchars($e->getMessage()) . "</div>";
                                $message .= "<div style='color:orange;'>Backup file kept at: " . htmlspecialchars($backup_path) . "</div>";
                            }
                        } else {
                            $message = "<div style='color:blue;'><strong>Backup created successfully!</strong> No valid email addresses found in notification settings.</div>";
                        }
                    } else {
                        $message = "<div style='color:blue;'><strong>Backup created successfully!</strong> Email notification was not sent (disabled in settings).</div>";
                    }

                    $updateSql = "UPDATE schooldetails SET latestbackupusername = :username, latestbackupdate = NOW()";
                    $updateQuery = $dbh->prepare($updateSql);
                    $updateQuery->bindParam(':username', $username, PDO::PARAM_STR);

                    if (!$updateQuery->execute()) {
                        error_log("Error updating backup record: " . implode(" ", $updateQuery->errorInfo()));
                        $message .= "<div style='color:orange;'>Warning: Could not update backup record in database.</div>";
                    }

                    $message .= "File: " . htmlspecialchars($backup_path) . "<br>";
                    $message .= "Size: " . round(filesize($backup_path) / 1024, 2) . " KB</div>";

                    // Perform rotation after everything else is done
                    rotateBackups(dirname($backup_path) . DIRECTORY_SEPARATOR, $backup_path);
                } else {
                    $message = "<div style='color:red;'><strong>Backup failed.</strong> Could not create backup file.</div>";
                    if (file_exists($backup_path)) {
                        @unlink($backup_path);
                    }
                }
            }
        }
    } elseif (isset($_POST['action'])) {
        // Handle schedule actions
        if ($_POST['action'] === 'add_schedule') {
            $schedule_name = trim($_POST['schedule_name']);
            $frequency = $_POST['frequency'];
            $time = $_POST['time'];
            $day = isset($_POST['day']) ? (int)$_POST['day'] : null;
            $date = isset($_POST['date']) ? (int)$_POST['date'] : null;
            $status = $_POST['status'];
            
            // Calculate next run time
            $next_run = calculateNextRun($frequency, $time, $day, $date);
            
            try {
                $stmt = $dbh->prepare("INSERT INTO backup_schedules (schedule_name, frequency, time, day, date, status, next_run) 
                                      VALUES (:schedule_name, :frequency, :time, :day, :date, :status, :next_run)");
                $stmt->bindParam(':schedule_name', $schedule_name);
                $stmt->bindParam(':frequency', $frequency);
                $stmt->bindParam(':time', $time);
                $stmt->bindParam(':day', $day);
                $stmt->bindParam(':date', $date);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':next_run', $next_run);
                
                if ($stmt->execute()) {
                    $message = "<div style='color:green;'><strong>Schedule added successfully!</strong></div>";
                } else {
                    $message = "<div style='color:red;'><strong>Error adding schedule.</strong></div>";
                }
            } catch (PDOException $e) {
                $message = "<div style='color:red;'><strong>Error adding schedule:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } elseif ($_POST['action'] === 'toggle_schedule') {
            $schedule_id = (int)$_POST['schedule_id'];
            
            try {
                // Get current status
                $stmt = $dbh->prepare("SELECT status FROM backup_schedules WHERE id = :id");
                $stmt->bindParam(':id', $schedule_id);
                $stmt->execute();
                $current_status = $stmt->fetchColumn();
                
                $new_status = ($current_status === 'active') ? 'inactive' : 'active';
                
                $update_stmt = $dbh->prepare("UPDATE backup_schedules SET status = :status WHERE id = :id");
                $update_stmt->bindParam(':status', $new_status);
                $update_stmt->bindParam(':id', $schedule_id);
                
                if ($update_stmt->execute()) {
                    $message = "<div style='color:green;'><strong>Schedule updated successfully!</strong></div>";
                } else {
                    $message = "<div style='color:red;'><strong>Error updating schedule.</strong></div>";
                }
            } catch (PDOException $e) {
                $message = "<div style='color:red;'><strong>Error updating schedule:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } elseif ($_POST['action'] === 'delete_schedule') {
            $schedule_id = (int)$_POST['schedule_id'];
            
            try {
                $stmt = $dbh->prepare("DELETE FROM backup_schedules WHERE id = :id");
                $stmt->bindParam(':id', $schedule_id);
                
                if ($stmt->execute()) {
                    $message = "<div style='color:green;'><strong>Schedule deleted successfully!</strong></div>";
                } else {
                    $message = "<div style='color:red;'><strong>Error deleting schedule.</strong></div>";
                }
            } catch (PDOException $e) {
                $message = "<div style='color:red;'><strong>Error deleting schedule:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

// Fetch existing schedules
$schedules = [];
try {
    $stmt = $dbh->query("SELECT * FROM backup_schedules ORDER BY created_at DESC");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table might not exist yet, error will be handled in display
    error_log("Error fetching schedules: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
   <head>
      <title>Kipmetz-SMS|BACKUP SCHEDULER</title>
      <!-- Core CSS - Include with every page -->
      <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
      <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
      <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
      <link href="assets/css/style.css" rel="stylesheet" />
      <link href="assets/css/main-style.css" rel="stylesheet" />
      <!-- Page-Level CSS -->
      <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            .success { color: green; padding: 10px; background: #e8f5e9; border: 1px solid #c8e6c9; }
            .error { color: #d32f2f; padding: 10px; background: #ffebee; border: 1px solid #ef9a9a; }
            .warning { color: #ff8f00; padding: 10px; background: #fff3e0; border: 1px solid #ffcc80; }
            button { 
                padding: 10px 20px; 
                background: #4CAF50; 
                color: white; 
                border: none; 
                cursor: pointer; 
                font-size: 16px;
                border-radius: 4px;
                transition: background 0.3s;
                margin: 5px;
            }
            button:hover { background: #388e3c; }
            button.secondary {
                background: #2196F3;
            }
            button.secondary:hover {
                background: #0b7dda;
            }
            button.danger {
                background: #f44336;
            }
            button.danger:hover {
                background: #d32f2f;
            }
            pre { 
                background: #f5f5f5; 
                padding: 15px; 
                border-radius: 4px; 
                overflow-x: auto;
                border: 1px solid #e0e0e0;
            }
            .container { max-width: 1000px; margin: 0 auto; }
            .backup-info { margin-top: 20px; padding: 15px; background: #e3f2fd; border: 1px solid #bbdefb; }
            .schedule-form { 
                background: #f9f9f9; 
                padding: 20px; 
                border-radius: 5px; 
                margin: 20px 0; 
                border: 1px solid #ddd;
            }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], select, input[type="number"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            .schedules-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .schedules-table th, .schedules-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            .schedules-table th { background-color: #f2f2f2; }
            .status-active { color: green; font-weight: bold; }
            .status-inactive { color: #888; }
            .action-buttons { display: flex; gap: 5px; }
        </style>
   </head>
   <body>
    <!-- wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php include_once('includes/header.php');?>
        <!-- end navbar top -->
        <!-- navbar side -->
        <?php include_once('includes/sidebar.php');?>
        <!-- end navbar side -->
        <!-- page-wrapper -->
        <div id="page-wrapper">
            <div class="panel panel-primary">    
                <div id="page-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <br>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="page-header">
                                    Database Backup & Scheduler
                                </h2>
                                <?php include_once('updatemessagepopup.php'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Panel -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                                <?php if ($message) echo $message; ?>
                                <form method="post">
                                    <input type="hidden" name="username" value="<?php echo $_SESSION['cpmsaid'] ?>">
                                    <button type="submit" name="create_backup">
                                        <i class="fa fa-database"></i> Create Backup and Send Email
                                    </button>
                                </form>
                                
                                <div class="backup-info">
                                    <h3>Backup Information:</h3>
                                    <p>This system will:</p>
                                    <ul>
                                        <li>Create a complete backup of the <?= htmlspecialchars(DB_NAME) ?> database</li>
                                        <li>Automatically keep only the 5 most recent backups</li>
                                        <li>Email the latest backup to only registered emails on Notification settings</li>
                                        <li>Clean up temporary files after sending</li>
                                    </ul>
                                </div>
                                
                                <hr>
                                
                                <h3>Automatic Backup Scheduler</h3>
                                <p>Set up automatic backups to run at specified intervals:</p>
                                
                                <!-- Schedule Form -->
                                <div class="schedule-form">
                                    <h4>Add New Schedule</h4>
                                    <form method="post" id="scheduleForm">
                                        <input type="hidden" name="action" value="add_schedule">
                                        
                                        <div class="form-group">
                                            <label for="schedule_name">Schedule Name:</label>
                                            <input type="text" id="schedule_name" name="schedule_name" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="frequency">Frequency:</label>
                                            <select id="frequency" name="frequency" required>
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" id="timeGroup">
                                            <label for="time">Time of Day:</label>
                                            <input type="text" id="time" name="time" placeholder="HH:MM (24-hour format)" required 
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]">
                                        </div>
                                        
                                        <div class="form-group" id="dayGroup" style="display: none;">
                                            <label for="day">Day of Week:</label>
                                            <select id="day" name="day">
                                                <option value="1">Monday</option>
                                                <option value="2">Tuesday</option>
                                                <option value="3">Wednesday</option>
                                                <option value="4">Thursday</option>
                                                <option value="5">Friday</option>
                                                <option value="6">Saturday</option>
                                                <option value="0">Sunday</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" id="dateGroup" style="display: none;">
                                            <label for="date">Day of Month:</label>
                                            <input type="number" id="date" name="date" min="1" max="31" value="1">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="status">Status:</label>
                                            <select id="status" name="status" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" class="secondary">
                                            <i class="fa fa-plus"></i> Add Schedule
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Schedules Table -->
                                <h4>Current Backup Schedules</h4>
                                <table class="schedules-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Frequency</th>
                                            <th>Next Run</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($schedules)) {
                                            echo '<tr><td colspan="5" style="text-align: center;">No backup schedules found.</td></tr>';
                                        } else {
                                            foreach ($schedules as $schedule) {
                                                $statusClass = $schedule['status'] === 'active' ? 'status-active' : 'status-inactive';
                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($schedule['schedule_name']) . '</td>';
                                                echo '<td>' . htmlspecialchars($schedule['frequency']) . '</td>';
                                                echo '<td>' . htmlspecialchars($schedule['next_run']) . '</td>';
                                                echo '<td class="' . $statusClass . '">' . htmlspecialchars($schedule['status']) . '</td>';
                                                echo '<td class="action-buttons">';
                                                echo '<form method="post" style="display:inline;">';
                                                echo '<input type="hidden" name="action" value="toggle_schedule">';
                                                echo '<input type="hidden" name="schedule_id" value="' . $schedule['id'] . '">';
                                                echo '<button type="submit" class="secondary">' . 
                                                     ($schedule['status'] === 'active' ? 'Deactivate' : 'Activate') . 
                                                     '</button>';
                                                echo '</form>';
                                                echo '<form method="post" style="display:inline;">';
                                                echo '<input type="hidden" name="action" value="delete_schedule">';
                                                echo '<input type="hidden" name="schedule_id" value="' . $schedule['id'] . '">';
                                                echo '<button type="submit" class="danger" onclick="return confirm(\'Are you sure you want to delete this schedule?\')">Delete</button>';
                                                echo '</form>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                
                                <div class="backup-info" style="margin-top: 30px;">
                                    <h3>How the Automatic Backup Works:</h3>
                                    <p>The system uses a cron job to check for scheduled backups. To set up automatic backups:</p>
                                    <ol>
                                        <li>Add a backup schedule using the form above</li>
                                        <li>Set up a cron job on your server to run the backup checker regularly</li>
                                        <li>Example cron job (runs every hour):<br>
                                            <pre>0 * * * * php /path/to/your/backup_checker.php</pre>
                                        </li>
                                    </ol>                                
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            // Handle frequency change to show/hide appropriate fields
            $('#frequency').change(function() {
                var frequency = $(this).val();
                $('#dayGroup').hide();
                $('#dateGroup').hide();
                
                if (frequency === 'weekly') {
                    $('#dayGroup').show();
                } else if (frequency === 'monthly') {
                    $('#dateGroup').show();
                }
            });
            
            // Initialize the form based on current selection
            $('#frequency').trigger('change');
            
            // Initialize data tables if needed
            $('.schedules-table').DataTable({
                "pageLength": 10,
                "ordering": true
            });
        });
    </script>
</body>
</html>