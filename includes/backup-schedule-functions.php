<?php
function getBackupSchedules($dbh) {
    $stmt = $dbh->query("SELECT * FROM backup_schedules ORDER BY is_active DESC, next_run ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getScheduleById($dbh, $id) {
    $stmt = $dbh->prepare("SELECT * FROM backup_schedules WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveBackupSchedule($dbh, $data) {
    // Calculate next run time
    $nextRun = calculateNextRunTime($data);
    
    if (isset($data['id'])) {
        // Update existing schedule
        $stmt = $dbh->prepare("UPDATE backup_schedules SET 
            frequency = ?, time = ?, day_of_week = ?, day_of_month = ?, custom_cron = ?,
            is_active = ?, next_run = ?, updated_at = NOW()
            WHERE id = ?");
        return $stmt->execute([
            $data['frequency'],
            $data['time'],
            $data['day_of_week'] ?? null,
            $data['day_of_month'] ?? null,
            $data['custom_cron'] ?? null,
            $data['is_active'],
            $nextRun,
            $data['id']
        ]);
    } else {
        // Create new schedule
        $stmt = $dbh->prepare("INSERT INTO backup_schedules 
            (frequency, time, day_of_week, day_of_month, custom_cron, is_active, next_run)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['frequency'],
            $data['time'],
            $data['day_of_week'] ?? null,
            $data['day_of_month'] ?? null,
            $data['custom_cron'] ?? null,
            $data['is_active'],
            $nextRun
        ]);
    }
}

function calculateNextRunTime($schedule) {
    $now = new DateTime();
    $nextRun = new DateTime();
    
    // Set the time
    $timeParts = explode(':', $schedule['time']);
    $nextRun->setTime($timeParts[0], $timeParts[1], 0);
    
    switch ($schedule['frequency']) {
        case 'daily':
            if ($nextRun <= $now) {
                $nextRun->modify('+1 day');
            }
            break;
            
        case 'weekly':
            $dayOfWeek = $schedule['day_of_week'] ?? $now->format('N');
            $nextRun->modify('next ' . ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$dayOfWeek-1]);
            break;
            
        case 'monthly':
            $dayOfMonth = $schedule['day_of_month'] ?? $now->format('j');
            $nextRun->setDate($now->format('Y'), $now->format('m'), $dayOfMonth);
            if ($nextRun <= $now) {
                $nextRun->modify('+1 month');
            }
            break;
            
        case 'custom':
            // For custom cron expressions, we'll just set to now + 1 day as placeholder
            // Actual cron would handle this on the server
            $nextRun->modify('+1 day');
            break;
    }
    
    return $nextRun->format('Y-m-d H:i:s');
}

function deleteSchedule($dbh, $id) {
    $stmt = $dbh->prepare("DELETE FROM backup_schedules WHERE id = ?");
    return $stmt->execute([$id]);
}