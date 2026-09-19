<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$role = $_SESSION['role_slug'] ?? '';
$isEmployee = ($role === 'employee');
if (!in_array($role, ['admin', 'hr', 'dept_head', 'employee'])) {
    http_response_code(403); die('Access denied.');
}

$pageTitle = $isEmployee ? 'My Attendance' : 'Attendance Records';
$db = getDB();

// For employees, find their employee_id by email
$empId = null;
if ($isEmployee) {
    $s = $db->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
    $s->execute([$_SESSION['email'] ?? '']);
    $empId = $s->fetchColumn() ?: 0;
}

$search = $_GET['search'] ?? '';
$date   = $_GET['date'] ?? date('Y-m-d');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ["a.attendance_date = ?"];
$params = [$date];

if ($isEmployee) {
    // Employee sees only their own records
    $where[] = "a.employee_id = ?";
    $params[] = $empId;
} else {
    if ($search) {
        $where[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }
    if (isDeptHead()) {
        $where[] = "e.department_id = ?";
        $params[] = $_SESSION['dept_id'];
    }
}

$whereClause = implode(' AND ', $where);

$stmt = $db->prepare("SELECT COUNT(*) FROM attendance a JOIN employees e ON a.employee_id = e.id WHERE $whereClause");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$pag = paginate($total, $perPage, $page);

$sql = "SELECT a.*, e.employee_code, e.first_name, e.last_name, d.code as dept_code 
        FROM attendance a 
        JOIN employees e ON a.employee_id = e.id 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE $whereClause 
        ORDER BY a.attendance_date DESC, e.last_name ASC 
        LIMIT {$pag['per_page']} OFFSET {$pag['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <?php if ($isEmployee): ?>
    <div class="d-flex align-items-center gap-3">
      <a href="<?= BASE_URL ?>/portal.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
      <h1 class="page-title mb-0"><i class="fas fa-clock me-2"></i>My Attendance</h1>
    </div>
    <?php else: ?>
    <h1 class="page-title mb-0"><i class="fas fa-calendar-check me-2"></i>Daily Attendance</h1>
    <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body p-3">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label">Search Employee</label>
          <input type="text" name="search" class="form-control" placeholder="Name or Code..." value="<?= e($search) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="<?= e($date) ?>">
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-outline w-100"><i class="fas fa-filter"></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Dept</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Total Hrs</th>
            <th>Late (Mins)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr class="empty-row"><td colspan="7">No attendance records found for this date.</td></tr>
          <?php else: ?>
            <?php foreach ($records as $r): ?>
              <tr>
                <td>
                  <div class="fw-semibold text-dark"><?= e($r['last_name'] . ', ' . $r['first_name']) ?></div>
                  <div class="small text-muted"><?= e($r['employee_code']) ?></div>
                </td>
                <td><?= e($r['dept_code']) ?></td>
                <td><?= $r['time_in'] ? date('h:i A', strtotime($r['time_in'])) : '--:--' ?></td>
                <td><?= $r['time_out'] ? date('h:i A', strtotime($r['time_out'])) : '--:--' ?></td>
                <td><?= number_format((float)$r['total_hours'], 2) ?></td>
                <td>
                  <?php if($r['late_minutes'] > 0): ?>
                    <span class="text-danger fw-bold"><?= $r['late_minutes'] ?></span>
                  <?php else: ?>
                    <span class="text-muted">0</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?= badgeClass($r['status']) ?>"><?= e($r['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <?php if ($pag['total_pages'] > 1): ?>
    <div class="card-body border-top">
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
            <li class="page-item <?= $i === $pag['page'] ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&date=<?= urlencode($date) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
