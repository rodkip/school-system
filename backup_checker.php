<?php
// Backup checker script - run via cron job
require_once("includes/dbconnection.php");
// Include the file containing your backup functions
// require_once("backup_functions.php");

// Check for scheduled backups that need to run
$current_time = date("Y-m-d H:i:s");
$current_day = date("N"); // 1 (Monday) to 7 (Sunday)
$current_date = date("j"); // Day of the month

try {
    $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get active schedules
    $stmt = $dbh->prepare("SELECT * FROM backup_schedules WHERE status = 'active' AND next_run <= :current_time");
    $stmt->bindParam(":current_time", $current_time);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($schedules as $schedule) {
        // Execute the backup (you need to implement createBackup function)
        // $backup_path = createBackup();
        
        if ($backup_path) {
            // Send email notification (you need to implement sendBackupEmail function)
            // sendBackupEmail($backup_path, "System Scheduler");
            
            // Update next run time
            // $next_run = calculateNextRun($schedule["frequency"], $schedule["time"], 
            //                            $schedule["day"] ?? null, $schedule["date"] ?? null);
            
            // $update_stmt = $dbh->prepare("UPDATE backup_schedules SET last_run = :current_time, 
            //                             next_run = :next_run WHERE id = :id");
            // $update_stmt->bindParam(":current_time", $current_time);
            // $update_stmt->bindParam(":next_run", $next_run);
            // $update_stmt->bindParam(":id", $schedule["id"]);
            // $update_stmt->execute();
            
            // Log the successful backup
            error_log("Scheduled backup executed: " . $schedule["schedule_name"]);
        }
    }
} catch (PDOException $e) {
    error_log("Backup scheduler error: " . $e->getMessage());
}
?>                                    