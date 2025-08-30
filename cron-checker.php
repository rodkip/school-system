<?php
require_once('includes/dbconnection.php');
require_once('includes/backup-schedule-functions.php');

// Get all active schedules that are due
$now = date('Y-m-d H:i:00');
$stmt = $dbh->prepare("SELECT * FROM backup_schedules 
                      WHERE is_active = 1 AND next_run <= ?");
$stmt->execute([$now]);
$dueSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($dueSchedules as $schedule) {
    try {
        // Execute the backup
        require('auto-backup.php'); // Your existing auto-backup script
        
        // Update schedule with last run time and calculate next run
        $nextRun = calculateNextRunTime($schedule);
        $updateStmt = $dbh->prepare("UPDATE backup_schedules 
                                    SET last_run = NOW(), next_run = ?
                                    WHERE id = ?");
        $updateStmt->execute([$nextRun, $schedule['id']]);
        
        error_log("Executed backup for schedule ID: " . $schedule['id']);
    } catch (Exception $e) {
        error_log("Error executing backup schedule ID " . $schedule['id'] . ": " . $e->getMessage());
    }
}