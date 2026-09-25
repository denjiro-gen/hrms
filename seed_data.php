<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/config/database.php";

$db = getDB();

try {
    $db->beginTransaction();

    // Ensure we have a school admin department if needed, but let us just use 1 (IT) or insert an Executive Dept
    $stmt = $db->query("SELECT id FROM departments WHERE name = 'Executive Management' LIMIT 1");
    $deptId = $stmt->fetchColumn();
    if (!$deptId) {
        $db->exec("INSERT INTO departments (name, code, description) VALUES ('Executive Management', 'EXEC', 'Top Level Management')");
        $deptId = $db->lastInsertId();
    }

    // Insert School Admin Employee
    $stmt = $db->prepare("SELECT id FROM employees WHERE email = 'school@bestlink.edu.ph' LIMIT 1");
    $empId = $stmt->fetchColumn();
    if (!$empId) {
        $stmt = $db->prepare("INSERT INTO employees (employee_code, department_id, position, first_name, last_name, email, employment_type, employment_status, basic_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['EMP-2026-004', $deptId, 'School Administrator', 'School', 'Admin', 'school@bestlink.edu.ph', 'Regular', 'Active', 80000.00]);
        $empId = $db->lastInsertId();
    }

    // Insert School Admin User
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'school@bestlink.edu.ph' LIMIT 1");
    $userId = $stmt->fetchColumn();
    if (!$userId) {
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (role_id, employee_id, username, email, password_hash, first_name, last_name, status) VALUES (4, ?, 'school', 'school@bestlink.edu.ph', ?, 'School', 'Admin', 'Active')");
        $stmt->execute([$empId, $hash]);
    }

    // Insert sample audit logs to make the system look active
    $logs = [
        ['Login', 'Auth', 'User HR Officer logged in successfully.'],
        ['Create Employee', 'Employees', 'Created new employee record for EMP-2026-004'],
        ['Update Payroll', 'Payroll', 'Generated payroll for September 2026'],
        ['Approve Leave', 'Leave', 'Approved Vacation Leave for EMP-2026-001'],
        ['System Config', 'Settings', 'Updated company holiday calendar'],
        ['Logout', 'Auth', 'User HR Officer logged out.']
    ];

    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, username, role, action, module, description) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($logs as $log) {
        $stmt->execute([2, 'hr', 'HR Officer', $log[0], $log[1], $log[2]]);
    }

    $db->commit();
    echo "Sample data inserted successfully.\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
