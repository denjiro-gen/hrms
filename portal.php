<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$role   = $_SESSION['role_slug'] ?? 'employee';
$firstName = $_SESSION['first_name'] ?? 'User';
$lastName  = $_SESSION['last_name']  ?? '';

// Try to find an employee record by matching email address to session email
$userEmail = $_SESSION['email'] ?? '';
$emp = false;
if ($userEmail) {
    $stmt = $db->prepare("SELECT e.*, d.name as dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.email = ? LIMIT 1");
    $stmt->execute([$userEmail]);
    $emp = $stmt->fetch();
}

$empId   = $emp ? $emp['id'] : 0;
$empPos  = $emp ? ($emp['position']  ?? 'N/A') : 'System User';
$empDept = $emp ? ($emp['dept_name'] ?? 'N/A') : 'N/A';
$empCode = $emp ? ($emp['employee_code'] ?? 'N/A') : 'N/A';

// Leave balance
$leaveBalance  = 15; // default placeholder
if ($empId) {
    $lb = $db->query("SELECT SUM(balance) FROM leave_balances WHERE employee_id = $empId AND year = YEAR(NOW())")->fetchColumn();
    if ($lb !== null) $leaveBalance = (int)$lb;
}

// Pending leave requests
$pendingLeaves = $empId
    ? $db->query("SELECT COUNT(*) FROM leave_requests WHERE employee_id = $empId AND status = 'Pending'")->fetchColumn()
    : 0;

// Recent payroll records
$recentPayroll = $empId
    ? $db->query("SELECT pp.period_name, pr.net_pay FROM payroll pr JOIN payroll_periods pp ON pr.period_id = pp.id WHERE pr.employee_id = $empId ORDER BY pr.created_at DESC LIMIT 3")->fetchAll()
    : [];

$pageTitle = 'My Portal';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="hrms-main">
<div class="main-content">

  <!-- Welcome Header -->
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;">
    <div style="width:56px;height:56px;border-radius:50%;background:#2F7BEE;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;flex-shrink:0;">
      <?= strtoupper(substr($firstName,0,1) . substr($lastName,0,1)) ?>
    </div>
    <div>
      <h1 style="font-size:22px;font-weight:700;color:#111;margin:0 0 4px;">Welcome back, <?= e($firstName) ?>!</h1>
      <p style="font-size:13px;color:#6B7280;margin:0;"><?= e($empPos) ?> &bull; <?= e($empDept) ?> &bull; <?= e($empCode) ?></p>
    </div>
  </div>

  <!-- KPI Cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:28px;">

    <!-- Leave Balance -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:22px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:0.06em;">Leave Balance</span>
        <span style="background:#FEF3C7;color:#D97706;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">
          <i class="fas fa-calendar-minus"></i>
        </span>
      </div>
      <div style="font-size:38px;font-weight:800;color:#111;line-height:1;"><?= $leaveBalance ?><span style="font-size:15px;font-weight:500;color:#9CA3AF;margin-left:4px;">days</span></div>
      <div style="font-size:12px;color:#6B7280;margin:6px 0 16px;"><?= $pendingLeaves ?> requests pending</div>
      <a href="<?= BASE_URL ?>/modules/leave/apply.php" class="btn btn-primary" style="width:100%;padding:10px;text-align:center;display:block;">
        <i class="fas fa-plus me-2"></i> File for Leave
      </a>
    </div>

    <!-- Recent Payslips -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:22px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:0.06em;">Recent Payroll</span>
        <span style="background:#D1FAE5;color:#059669;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">
          <i class="fas fa-wallet"></i>
        </span>
      </div>
      <?php if (empty($recentPayroll)): ?>
        <div style="font-size:13px;color:#9CA3AF;text-align:center;padding:20px 0;">
          <i class="fas fa-file-invoice-dollar" style="font-size:28px;margin-bottom:8px;display:block;color:#E5E7EB;"></i>
          No payslips yet.
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <?php foreach ($recentPayroll as $pr): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#F9FAFB;border-radius:8px;">
              <span style="font-size:13px;font-weight:600;color:#374151;"><?= e($pr['period_name']) ?></span>
              <span style="font-size:13px;font-weight:700;color:#059669;">₱<?= number_format($pr['net_pay'], 2) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:22px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:0.06em;">Quick Actions</span>
        <span style="background:#EDE9FE;color:#7C3AED;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;">
          <i class="fas fa-bolt"></i>
        </span>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <a href="<?= BASE_URL ?>/modules/attendance/index.php" style="text-decoration:none;display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;padding:10px 12px;background:#F9FAFB;border-radius:8px;font-weight:500;transition:background .2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='#F9FAFB'">
          <i class="fas fa-clock" style="color:#2F7BEE;width:16px;text-align:center;"></i> My Attendance Log
        </a>
        <a href="<?= BASE_URL ?>/modules/benefits/index.php" style="text-decoration:none;display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;padding:10px 12px;background:#F9FAFB;border-radius:8px;font-weight:500;transition:background .2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='#F9FAFB'">
          <i class="fas fa-hand-holding-medical" style="color:#10B981;width:16px;text-align:center;"></i> My Benefits
        </a>
        <a href="<?= BASE_URL ?>/modules/leave/index.php" style="text-decoration:none;display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;padding:10px 12px;background:#F9FAFB;border-radius:8px;font-weight:500;transition:background .2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='#F9FAFB'">
          <i class="fas fa-calendar-alt" style="color:#F59E0B;width:16px;text-align:center;"></i> My Leave History
        </a>
      </div>
    </div>

  </div>

</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
