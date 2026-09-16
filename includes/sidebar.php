<?php
$currentPage = $_SERVER['PHP_SELF'] ?? '';
function activeIf(string $path): string {
    global $currentPage;
    return str_contains($currentPage, $path) ? 'active' : '';
}
$role = $_SESSION['role_slug'] ?? '';
$initials = strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1) . substr($_SESSION['last_name'] ?? '', 0, 1));
?>
<aside class="hrms-sidebar" id="sidebar">

  <!-- Logo -->
  <div class="sidebar-logo">
    <a href="<?= BASE_URL ?>/dashboard.php" title="Bestlink HRMS">
      <img src="https://bcp.edu.ph/logo.png" alt="BCP" 
           style="width:46px;height:46px;object-fit:contain;border-radius:8px;background:#fff;padding:4px;display:block;"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
      <div style="display:none;width:46px;height:46px;border-radius:8px;background:#2F7BEE;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;">BCP</div>
    </a>
  </div>

  <!-- Nav icons -->
  <nav class="sidebar-nav">

    <a href="<?= BASE_URL ?>/dashboard.php" class="nav-link <?= activeIf('dashboard') ?>" data-tip="Dashboard">
      <i class="fas fa-th-large"></i>
    </a>

    <?php if (in_array($role, ['admin','hr','dept_head','school'])): ?>

    <div class="nav-divider"></div>

    <?php if (in_array($role, ['admin','hr','dept_head'])): ?>
    <a href="<?= BASE_URL ?>/modules/employees/index.php" class="nav-link <?= activeIf('/employees/') ?>" data-tip="Employees">
      <i class="fas fa-users"></i>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','hr'])): ?>
    <a href="<?= BASE_URL ?>/modules/recruitment/jobs/index.php" class="nav-link <?= activeIf('/recruitment/') ?>" data-tip="Recruitment">
      <i class="fas fa-briefcase"></i>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','hr','dept_head'])): ?>
    <a href="<?= BASE_URL ?>/modules/attendance/index.php" class="nav-link <?= activeIf('/attendance/') ?>" data-tip="Attendance">
      <i class="fas fa-calendar-check"></i>
    </a>
    <a href="<?= BASE_URL ?>/modules/leave/index.php" class="nav-link <?= (str_contains($currentPage,'/leave/') || str_contains($currentPage,'/overtime/')) ? 'active' : '' ?>" data-tip="Leave &amp; Overtime">
      <i class="fas fa-calendar-minus"></i>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','hr'])): ?>
    <a href="<?= BASE_URL ?>/modules/payroll/index.php" class="nav-link <?= activeIf('/payroll/') ?>" data-tip="Payroll">
      <i class="fas fa-money-bill-wave"></i>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','hr','dept_head'])): ?>
    <a href="<?= BASE_URL ?>/modules/performance/index.php" class="nav-link <?= activeIf('/performance/') ?>" data-tip="Performance">
      <i class="fas fa-chart-line"></i>
    </a>
    <a href="<?= BASE_URL ?>/modules/training/index.php" class="nav-link <?= activeIf('/training/') ?>" data-tip="Training">
      <i class="fas fa-graduation-cap"></i>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin','hr'])): ?>
    <a href="<?= BASE_URL ?>/modules/benefits/index.php" class="nav-link <?= activeIf('/benefits/') ?>" data-tip="Benefits">
      <i class="fas fa-heart"></i>
    </a>
    <a href="<?= BASE_URL ?>/modules/discipline/index.php" class="nav-link <?= (str_contains($currentPage,'/discipline/') || str_contains($currentPage,'/grievances/')) ? 'active' : '' ?>" data-tip="Discipline">
      <i class="fas fa-gavel"></i>
    </a>
    <?php endif; ?>

    <div class="nav-divider"></div>

    <a href="<?= BASE_URL ?>/modules/reports/index.php" class="nav-link <?= activeIf('/reports/') ?>" data-tip="Reports">
      <i class="fas fa-file-alt"></i>
    </a>

    <?php if ($role === 'admin'): ?>
    <div class="nav-divider"></div>
    <a href="<?= BASE_URL ?>/admin/users/index.php" class="nav-link <?= activeIf('/admin/users/') ?>" data-tip="User Accounts">
      <i class="fas fa-user-shield"></i>
    </a>
    <a href="<?= BASE_URL ?>/admin/departments/index.php" class="nav-link <?= activeIf('/admin/departments/') ?>" data-tip="Departments">
      <i class="fas fa-building"></i>
    </a>
    <a href="<?= BASE_URL ?>/admin/index.php" class="nav-link <?= activeIf('/admin/index') ?>" data-tip="Audit Logs">
      <i class="fas fa-history"></i>
    </a>
    <a href="<?= BASE_URL ?>/admin/settings.php" class="nav-link <?= activeIf('settings') ?>" data-tip="Settings">
      <i class="fas fa-cog"></i>
    </a>
    <?php endif; ?>

    <?php endif; ?>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <div class="sf-avatar" title="<?= e(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?>">
      <?= e($initials) ?>
    </div>
    <a href="<?= BASE_URL ?>/logout.php" class="sf-logout" data-tip="Sign Out" title="Sign Out">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</aside>
