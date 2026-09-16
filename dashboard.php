<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Overview';
$role      = $_SESSION['role_slug'] ?? '';
$db        = getDB();
$firstName = $_SESSION['first_name'] ?? 'User';

// --- Stats ---
$stats = [
    'employees'      => (int)$db->query("SELECT COUNT(*) FROM employees WHERE status='Active'")->fetchColumn(),
    'departments'    => (int)$db->query("SELECT COUNT(*) FROM departments WHERE status='Active'")->fetchColumn(),
    'pending_leaves' => (int)$db->query("SELECT COUNT(*) FROM leave_requests WHERE status='Pending'")->fetchColumn(),
    'open_jobs'      => (int)$db->query("SELECT COUNT(*) FROM job_postings WHERE status='Open'")->fetchColumn(),
];

// --- Recent audit logs ---
$recentLogs = $db->query(
    "SELECT a.action, a.module, a.created_at, u.email
     FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.created_at DESC LIMIT 8"
)->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="hrms-main">
<div class="main-content">

<!-- ========== HEADER ========== -->
<div style="margin-bottom:28px;">
  <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <div>
      <h1 style="font-size:20px; font-weight:700; color:#111; letter-spacing:-.3px; margin:0;">
        <?php
          $h = (int)date('G');
          echo $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
        ?>, <?= e($firstName) ?>
      </h1>
      <p style="font-size:13px; color:#9CA3AF; margin:4px 0 0;">
        <?= date('l, F j, Y') ?> &mdash; Bestlink College of the Philippines
      </p>
    </div>
    <?php if (in_array($role, ['admin','hr'])): ?>
    <div style="display:flex; gap:8px;">
      <a href="<?= BASE_URL ?>/modules/employees/add.php" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Employee
      </a>
      <a href="<?= BASE_URL ?>/modules/recruitment/jobs/add.php" class="btn btn-outline btn-sm">
        <i class="fas fa-briefcase"></i> Post Job
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ========== KPI ROW ========== -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:24px;">

  <?php
  $kpis = [];
  $kpis[] = ['Employees', $stats['employees'], 'fas fa-users', '#2F7BEE', $stats['departments'].' departments'];
  if (in_array($role,['admin','hr','dept_head']))
    $kpis[] = ['Pending Leaves', $stats['pending_leaves'], 'fas fa-calendar-minus',
               $stats['pending_leaves'] > 0 ? '#F59E0B' : '#12B76A',
               $stats['pending_leaves'] > 0 ? 'Needs review' : 'All resolved'];
  if (in_array($role,['admin','hr']))
    $kpis[] = ['Open Jobs', $stats['open_jobs'], 'fas fa-briefcase', '#8B5CF6', 'AI-matched'];
  $kpis[] = ['Departments', $stats['departments'], 'fas fa-building', '#0891B2', 'Active units'];

  foreach ($kpis as [$label, $value, $icon, $color, $sub]):
  ?>
  <div style="background:#fff; border:1.5px solid #E8ECF0; border-radius:12px; padding:18px 16px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
      <span style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#9CA3AF;"><?= $label ?></span>
      <span style="color:<?= $color ?>; font-size:15px;"><i class="<?= $icon ?>"></i></span>
    </div>
    <div style="font-size:32px; font-weight:700; color:#111; letter-spacing:-.5px; line-height:1;"><?= $value ?></div>
    <div style="font-size:12px; color:#9CA3AF; margin-top:8px;"><?= $sub ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ========== TWO-COL ========== -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; align-items:start;">

  <!-- LEFT: Quick Links -->
  <div style="background:#fff; border:1.5px solid #E8ECF0; border-radius:12px; overflow:hidden;">
    <div style="padding:14px 18px; border-bottom:1px solid #F3F4F6; display:flex; align-items:center; justify-content:space-between;">
      <span style="font-size:13px; font-weight:700; color:#111;">Quick Access</span>
    </div>
    <div style="padding:14px 16px; display:flex; flex-direction:column; gap:6px;">
      <?php
      $links = [];
      if (in_array($role,['admin','hr','dept_head'])) {
        $links[] = [BASE_URL.'/modules/employees/index.php',    'fas fa-users',         '#2F7BEE', 'Employees',   'Manage employee records'];
        $links[] = [BASE_URL.'/modules/attendance/index.php',  'fas fa-calendar-check','#0891B2', 'Attendance',  'View daily attendance logs'];
        $links[] = [BASE_URL.'/modules/leave/index.php',       'fas fa-calendar-minus','#F59E0B', 'Leave',       'Approve leave requests'];
        $links[] = [BASE_URL.'/modules/performance/index.php', 'fas fa-chart-line',    '#8B5CF6', 'Performance', 'Submit evaluations'];
      }
      if (in_array($role,['admin','hr'])) {
        $links[] = [BASE_URL.'/modules/recruitment/jobs/index.php','fas fa-briefcase',  '#12B76A', 'Recruitment', 'Manage job postings'];
        $links[] = [BASE_URL.'/modules/payroll/index.php',         'fas fa-wallet',     '#E11D48', 'Payroll',     'Generate & review payroll'];
        $links[] = [BASE_URL.'/modules/training/index.php',        'fas fa-graduation-cap','#6366F1','Training',  'Schedule programs'];
      }
      foreach ($links as [$href, $icon, $color, $title, $desc]):
      ?>
      <a href="<?= $href ?>" style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:8px; text-decoration:none; transition:background .15s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='transparent'">
        <span style="width:32px; height:32px; border-radius:8px; background:<?= $color ?>18; color:<?= $color ?>; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0;">
          <i class="<?= $icon ?>"></i>
        </span>
        <div>
          <div style="font-size:13px; font-weight:600; color:#111; line-height:1.3;"><?= $title ?></div>
          <div style="font-size:11.5px; color:#9CA3AF;"><?= $desc ?></div>
        </div>
        <i class="fas fa-chevron-right" style="color:#D1D5DB; font-size:10px; margin-left:auto;"></i>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- RIGHT: Activity -->
  <div style="background:#fff; border:1.5px solid #E8ECF0; border-radius:12px; overflow:hidden;">
    <div style="padding:14px 18px; border-bottom:1px solid #F3F4F6; display:flex; align-items:center; justify-content:space-between;">
      <span style="font-size:13px; font-weight:700; color:#111;">Recent Activity</span>
      <?php if ($role === 'admin'): ?>
      <a href="<?= BASE_URL ?>/admin/index.php" style="font-size:12px; color:#2F7BEE; text-decoration:none; font-weight:600;">View all</a>
      <?php endif; ?>
    </div>
    <div style="padding:6px 18px 14px;">
      <?php if (empty($recentLogs)): ?>
        <p style="text-align:center; padding:24px 0; color:#9CA3AF; font-size:13px;">No activity recorded yet.</p>
      <?php else: ?>
        <?php
        $verbColors = [
          'Login'=>'#2F7BEE','Add'=>'#12B76A','Edit'=>'#8B5CF6',
          'Delete'=>'#EF4444','Approve'=>'#12B76A','Reject'=>'#EF4444','Generate'=>'#F59E0B',
        ];
        foreach ($recentLogs as $log):
          $verb  = explode(' ', $log['action'])[0];
          $color = $verbColors[$verb] ?? '#6B7280';
        ?>
        <div style="display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid #F9FAFB;">
          <span style="width:28px; height:28px; border-radius:6px; background:<?= $color ?>16; color:<?= $color ?>; display:flex; align-items:center; justify-content:center; font-size:11px; flex-shrink:0; margin-top:1px;">
            <i class="fas fa-circle-dot"></i>
          </span>
          <div style="flex:1; min-width:0;">
            <div style="font-size:13px; color:#111; line-height:1.4;">
              <strong><?= e($log['action']) ?></strong>
              <span style="color:#9CA3AF;"> in <?= e($log['module']) ?></span>
            </div>
            <div style="font-size:11.5px; color:#9CA3AF; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              <?= e($log['email'] ?? 'System') ?> &bull; <?= date('M j, g:i A', strtotime($log['created_at'])) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /two-col -->
</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
