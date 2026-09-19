<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$role = $_SESSION['role_slug'] ?? '';
$isEmployee = ($role === 'employee');
if (!in_array($role, ['admin', 'hr', 'employee'])) {
    http_response_code(403); die('Access denied.');
}

$pageTitle = $isEmployee ? 'My Benefits' : 'Employee Benefits';
$db = getDB();

if ($isEmployee) {
    // Find their employee record
    $s = $db->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
    $s->execute([$_SESSION['email'] ?? '']);
    $myEmpId = $s->fetchColumn() ?: 0;

    $enrollments = $db->prepare("SELECT be.*, b.name as benefit_name, b.benefit_type FROM employee_benefits be JOIN benefits b ON be.benefit_id = b.id WHERE be.employee_id = ? ORDER BY be.start_date DESC");
    $enrollments->execute([$myEmpId]);
    $enrollments = $enrollments->fetchAll();
} else {
    $enrollments = $db->query("SELECT be.*, b.name as benefit_name, b.benefit_type, e.first_name, e.last_name, e.employee_code FROM employee_benefits be JOIN benefits b ON be.benefit_id = b.id JOIN employees e ON be.employee_id = e.id ORDER BY be.start_date DESC")->fetchAll();
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <?php if ($isEmployee): ?>
    <div class="d-flex align-items-center gap-3">
      <a href="<?= BASE_URL ?>/portal.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
      <div>
        <h1 class="page-title mb-0"><i class="fas fa-hand-holding-heart me-2"></i>My Benefits</h1>
        <p class="page-subtitle">Your enrolled benefit plans</p>
      </div>
    </div>
    <?php else: ?>
    <div>
      <h1 class="page-title mb-0"><i class="fas fa-heart me-2"></i>Employee Benefits</h1>
      <p class="page-subtitle">View all active benefit enrollments</p>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <table class="data-table">
      <thead>
        <tr>
          <?php if (!$isEmployee): ?><th>Employee</th><?php endif; ?>
          <th>Benefit Plan</th>
          <th>Type</th>
          <th>Enrolled On</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($enrollments as $b): ?>
        <tr>
          <?php if (!$isEmployee): ?>
          <td>
            <div style="font-weight:600;color:#111;"><?= e($b['last_name'].', '.$b['first_name']) ?></div>
            <div style="font-size:11.5px;color:#9CA3AF;"><?= e($b['employee_code']) ?></div>
          </td>
          <?php endif; ?>
          <td style="font-weight:600;"><?= e($b['benefit_name']) ?></td>
          <td><?= e($b['benefit_type'] ?? $b['type'] ?? 'N/A') ?></td>
          <td><?= isset($b['enrollment_date']) ? e(date('M d, Y', strtotime($b['enrollment_date']))) : (isset($b['start_date']) ? e(date('M d, Y', strtotime($b['start_date']))) : 'N/A') ?></td>
          <td><span class="badge badge-success"><?= e($b['status'] ?? 'Active') ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($enrollments)): ?>
        <tr class="empty-row"><td colspan="<?= $isEmployee ? 4 : 5 ?>">
          <?= $isEmployee ? 'You have no benefits enrolled yet. Please contact HR.' : 'No enrollments found.' ?>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
