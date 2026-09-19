<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

if (($_SESSION['role_slug'] ?? '') !== 'employee') {
    http_response_code(403);
    die('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    header("Location: portal.php");
    exit;
}

verifyCsrf();

$db = getDB();
$email = $_SESSION['email'] ?? '';
$action = $_POST['action'];

// Get employee ID
$stmt = $db->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$empId = $stmt->fetchColumn();

if (!$empId) {
    flash('error', 'Employee record not found.');
    header("Location: portal.php");
    exit;
}

$today = date('Y-m-d');
$currentTime = date('H:i:s');

// Check for existing attendance record today
$stmt = $db->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ? LIMIT 1");
$stmt->execute([$empId, $today]);
$attendance = $stmt->fetch();

try {
    if ($action === 'time_in') {
        if ($attendance) {
            flash('warning', 'You have already timed in today.');
        } else {
            // Calculate late minutes (Threshold: 08:00 AM)
            $lateMinutes = 0;
            $threshold = strtotime("$today 08:00:00");
            $now = time();
            
            if ($now > $threshold) {
                $lateMinutes = floor(($now - $threshold) / 60);
            }
            
            $status = $lateMinutes > 0 ? 'Late' : 'Present';

            $insert = $db->prepare("INSERT INTO attendance (employee_id, attendance_date, time_in, late_minutes, status, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$empId, $today, $currentTime, $lateMinutes, $status, $_SESSION['user_id']]);
            
            flash('success', 'Successfully timed in at ' . date('g:i A'));
        }
    } elseif ($action === 'time_out') {
        if (!$attendance) {
            flash('error', 'You must time in first.');
        } elseif ($attendance['time_out']) {
            flash('warning', 'You have already timed out today.');
        } else {
            // Calculate total hours
            $timeInStr = $today . ' ' . $attendance['time_in'];
            $timeInTs = strtotime($timeInStr);
            $now = time();
            
            $totalSeconds = $now - $timeInTs;
            // Subtract 1 hour for lunch if they worked more than 5 hours (basic rule)
            if ($totalSeconds > 5 * 3600) {
                $totalSeconds -= 3600;
            }
            $totalHours = max(0, $totalSeconds / 3600);

            $update = $db->prepare("UPDATE attendance SET time_out = ?, total_hours = ? WHERE id = ?");
            $update->execute([$currentTime, $totalHours, $attendance['id']]);
            
            flash('success', 'Successfully timed out at ' . date('g:i A') . '. Total hours logged: ' . number_format($totalHours, 2));
        }
    }
} catch (Exception $e) {
    error_log("Attendance error: " . $e->getMessage());
    flash('error', 'An error occurred while logging your time.');
}

header("Location: portal.php");
exit;
