<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// Non-employees shouldn't be here
$role = $_SESSION['role_slug'] ?? '';
if (!in_array($role, ['employee'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$db        = getDB();
$userId    = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? 'Employee';
$lastName  = $_SESSION['last_name']  ?? '';
$userEmail = $_SESSION['email'] ?? '';

// Load employee record
$emp = false;
if ($userEmail) {
    $stmt = $db->prepare("SELECT e.*, d.name as dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.email = ? LIMIT 1");
    $stmt->execute([$userEmail]);
    $emp = $stmt->fetch();
}

$empId      = $emp ? $emp['id']            : 0;
$empCode    = $emp ? ($emp['employee_code'] ?? 'N/A') : 'N/A';
$empPos     = $emp ? ($emp['position']      ?? 'N/A') : 'Employee';
$empDept    = $emp ? ($emp['dept_name']     ?? 'N/A') : 'N/A';
$empStatus  = $emp ? ($emp['status']        ?? 'Active') : 'Active';
$dateHired  = $emp ? ($emp['date_hired']    ?? null) : null;

// Leave balance
$leaveBalance = 0;
if ($empId) {
    $lb = $db->prepare("SELECT SUM(remaining_days) FROM leave_balances WHERE employee_id = ? AND year = YEAR(NOW())");
    $lb->execute([$empId]);
    $val = $lb->fetchColumn();
    if ($val !== null) $leaveBalance = (int)$val;
}

// Pending leave requests
$pendingLeaves = 0;
if ($empId) {
    $s = $db->prepare("SELECT COUNT(*) FROM leave_requests WHERE employee_id = ? AND status = 'Pending'");
    $s->execute([$empId]);
    $pendingLeaves = (int)$s->fetchColumn();
}

// Recent payslips
$recentPayroll = [];
if ($empId) {
    $s = $db->prepare("SELECT pp.period_name, pr.net_salary, pr.created_at FROM payroll pr JOIN payroll_periods pp ON pr.period_id = pp.id WHERE pr.employee_id = ? ORDER BY pr.created_at DESC LIMIT 3");
    $s->execute([$empId]);
    $recentPayroll = $s->fetchAll();
}

// Today's attendance
$todayAttendance = false;
if ($empId) {
    $s = $db->prepare("SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = CURRENT_DATE LIMIT 1");
    $s->execute([$empId]);
    $todayAttendance = $s->fetch();
}

// Recent leave requests
$leaveHistory = [];
if ($empId) {
    $s = $db->prepare("SELECT lr.*, lt.name as leave_type_name FROM leave_requests lr LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id WHERE lr.employee_id = ? ORDER BY lr.created_at DESC LIMIT 5");
    $s->execute([$empId]);
    $leaveHistory = $s->fetchAll();
}

// Years of service
$yearsOfService = '';
if ($dateHired) {
    $diff = (new DateTime())->diff(new DateTime($dateHired));
    $yearsOfService = $diff->y . 'y ' . $diff->m . 'm';
}

$h          = (int)date('G');
$greeting   = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
$pageTitle  = 'My Dashboard';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content hrms-main">

  <!-- ── Welcome Header ─────────────────────────────────────────── -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
    <div style="display:flex;align-items:center;gap:16px;">
      <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#2F7BEE,#7C3AED);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">
        <?= strtoupper(substr($firstName,0,1).substr($lastName,0,1)) ?>
      </div>
      <div>
        <h1 style="font-size:20px;font-weight:700;color:#111;margin:0 0 3px;"><?= e($greeting) ?>, <?= e($firstName) ?>!</h1>
        <p style="font-size:13px;color:#6B7280;margin:0;"><?= e($empPos) ?> &bull; <?= e($empDept) ?> &bull; <span class="badge badge-success" style="font-size:11px;"><?= e($empStatus) ?></span></p>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/employee_profile.php" class="btn btn-outline btn-sm"><i class="fas fa-user-circle me-1"></i>My Profile</a>
      <a href="<?= BASE_URL ?>/change_password.php" class="btn btn-outline btn-sm"><i class="fas fa-key me-1"></i>Change Password</a>
    </div>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = getFlash('warning')): ?>
    <div class="alert alert-warning"><i class="fas fa-info-circle me-2"></i><?= e($msg) ?></div>
  <?php endif; ?>

  <!-- ── KPI Cards ──────────────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:24px;">

    <!-- Employee Code -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:.06em;">Employee ID</span>
        <span style="background:#EFF8FF;color:#2F7BEE;width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="fas fa-id-badge"></i></span>
      </div>
      <div style="font-size:24px;font-weight:800;color:#111;font-family:monospace;"><?= e($empCode) ?></div>
      <div style="font-size:12px;color:#9CA3AF;margin-top:4px;">Hired <?= $dateHired ? date('M d, Y', strtotime($dateHired)) : 'N/A' ?></div>
    </div>

    <!-- Leave Balance -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:.06em;">Leave Balance</span>
        <span style="background:#FFFBEB;color:#D97706;width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="fas fa-calendar-minus"></i></span>
      </div>
      <div style="font-size:38px;font-weight:800;color:#111;line-height:1;"><?= $leaveBalance ?><span style="font-size:15px;font-weight:500;color:#9CA3AF;margin-left:4px;">days</span></div>
      <div style="font-size:12px;color:#9CA3AF;margin-top:4px;"><?= $pendingLeaves ?> request<?= $pendingLeaves !== 1 ? 's' : '' ?> pending</div>
    </div>

    <!-- Today Attendance -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:.06em;">Today's Log</span>
        <span style="background:#ECFDF5;color:#059669;width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="fas fa-clock"></i></span>
      </div>
      
      <form action="<?= BASE_URL ?>/time_log.php" method="POST">
        <?= csrfField() ?>
        <?php if (!$todayAttendance): ?>
          <input type="hidden" name="action" value="time_in">
          <div style="font-size:12px;color:#6B7280;margin-bottom:8px;">You haven't timed in yet today.</div>
          <button type="submit" class="btn btn-primary w-100" style="background:#10B981;border-color:#10B981;">
            <i class="fas fa-sign-in-alt me-2"></i> Time In Now
          </button>
        <?php elseif (!$todayAttendance['time_out']): ?>
          <input type="hidden" name="action" value="time_out">
          <div style="font-size:12px;color:#6B7280;margin-bottom:8px;">Timed in at <strong><?= date('g:i A', strtotime($todayAttendance['time_in'])) ?></strong></div>
          <button type="submit" class="btn btn-danger w-100">
            <i class="fas fa-sign-out-alt me-2"></i> Time Out
          </button>
        <?php else: ?>
          <div style="font-size:14px;font-weight:700;color:#059669;margin-bottom:4px;">
            <i class="fas fa-check-circle me-1"></i> Shift Completed
          </div>
          <div style="font-size:12px;color:#9CA3AF;">
            In: <?= date('g:i A', strtotime($todayAttendance['time_in'])) ?> &bull; 
            Out: <?= date('g:i A', strtotime($todayAttendance['time_out'])) ?>
          </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Service Duration -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:20px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;letter-spacing:.06em;">Service Duration</span>
        <span style="background:#F5F3FF;color:#7C3AED;width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;"><i class="fas fa-award"></i></span>
      </div>
      <div style="font-size:26px;font-weight:800;color:#111;"><?= $yearsOfService ?: 'N/A' ?></div>
      <div style="font-size:12px;color:#9CA3AF;margin-top:4px;">Years of service</div>
    </div>

  </div>

  <!-- ── Two Column ─────────────────────────────────────────────── -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

    <!-- Quick Actions -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;overflow:hidden;">
      <div style="padding:14px 18px;border-bottom:1px solid #F3F4F6;">
        <span style="font-size:13px;font-weight:700;color:#111;">Quick Actions</span>
      </div>
      <div style="padding:12px 14px;display:flex;flex-direction:column;gap:6px;">

        <?php
        $actions = [
          [BASE_URL.'/modules/attendance/index.php',  'fas fa-clock',              '#2F7BEE', 'My Attendance',   'View your attendance log'],
          [BASE_URL.'/modules/leave/apply.php',       'fas fa-calendar-plus',      '#10B981', 'File for Leave',  'Submit a leave request'],
          [BASE_URL.'/modules/leave/index.php',       'fas fa-calendar-alt',       '#F59E0B', 'Leave History',   'View your leave records'],
          [BASE_URL.'/modules/benefits/index.php',    'fas fa-hand-holding-heart', '#E11D48', 'My Benefits',     'View enrolled benefits'],
          [BASE_URL.'/employee_profile.php',          'fas fa-user-edit',          '#7C3AED', 'My Profile',      'View and update your info'],
          [BASE_URL.'/change_password.php',           'fas fa-key',               '#6B7280', 'Change Password', 'Update your login password'],
        ];
        foreach ($actions as [$href, $icon, $color, $title, $desc]):
        ?>
        <a href="<?= $href ?>" style="display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='transparent'">
          <span style="width:34px;height:34px;border-radius:8px;background:<?= $color ?>18;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">
            <i class="<?= $icon ?>"></i>
          </span>
          <div>
            <div style="font-size:13px;font-weight:600;color:#111;line-height:1.3;"><?= $title ?></div>
            <div style="font-size:11.5px;color:#9CA3AF;"><?= $desc ?></div>
          </div>
          <i class="fas fa-chevron-right" style="color:#D1D5DB;font-size:10px;margin-left:auto;"></i>
        </a>
        <?php endforeach; ?>

      </div>
    </div>

    <!-- Recent Leave Requests -->
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;overflow:hidden;">
      <div style="padding:14px 18px;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:13px;font-weight:700;color:#111;">Recent Leave Requests</span>
        <a href="<?= BASE_URL ?>/modules/leave/apply.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Apply</a>
      </div>
      <?php if (empty($leaveHistory)): ?>
        <div style="padding:32px;text-align:center;color:#9CA3AF;font-size:13px;">
          <i class="fas fa-calendar-times" style="font-size:28px;margin-bottom:10px;display:block;color:#E5E7EB;"></i>
          No leave requests yet.
        </div>
      <?php else: ?>
        <table class="data-table">
          <thead><tr><th>Type</th><th>Dates</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($leaveHistory as $lr): ?>
            <tr>
              <td style="font-weight:600;"><?= e($lr['leave_type_name'] ?? 'Leave') ?></td>
              <td style="font-size:12px;color:#6B7280;"><?= e(date('M d', strtotime($lr['start_date']))) ?> – <?= e(date('M d, Y', strtotime($lr['end_date']))) ?></td>
              <td><span class="badge <?= badgeClass($lr['status']) ?>"><?= e($lr['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <!-- Recent Payslips -->
      <div style="padding:14px 18px;border-top:1px solid #F3F4F6;border-bottom:1px solid #F3F4F6;">
        <span style="font-size:13px;font-weight:700;color:#111;">Recent Payslips</span>
      </div>
      <?php if (empty($recentPayroll)): ?>
        <div style="padding:24px;text-align:center;color:#9CA3AF;font-size:13px;">No payslips yet.</div>
      <?php else: ?>
        <div style="padding:10px 14px;display:flex;flex-direction:column;gap:6px;">
          <?php foreach ($recentPayroll as $pr): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#F9FAFB;border-radius:8px;">
            <span style="font-size:13px;font-weight:600;color:#374151;"><?= e($pr['period_name']) ?></span>
            <span style="font-size:13px;font-weight:700;color:#059669;">₱<?= number_format((float)$pr['net_salary'], 2) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
