<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);
$pageTitle = 'Benefits';
$db = getDB();
$enrollments = $db->query("SELECT be.*, b.name as benefit_name, b.benefit_type, e.first_name, e.last_name, e.employee_code FROM employee_benefits be JOIN benefits b ON be.benefit_id = b.id JOIN employees e ON be.employee_id = e.id ORDER BY be.start_date DESC")->fetchAll();
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="hrms-main"><div class="main-content">
  <div style="margin-bottom:22px;"><h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Employee Benefits</h1><p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;">View active benefit enrollments</p></div>
  <div class="card"><table class="data-table">
    <thead><tr><th>Employee</th><th>Benefit Plan</th><th>Type</th><th>Enrolled On</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($enrollments as $e): ?>
      <tr>
        <td><div style="font-weight:600;color:#111;"><?= e($e['last_name'].', '.$e['first_name']) ?></div><div style="font-size:11.5px;color:#9CA3AF;"><?= e($e['employee_code']) ?></div></td>
        <td style="font-weight:600;"><?= e($e['benefit_name']) ?></td>
        <td><?= e($e['type']) ?></td>
        <td><?= e(date('M d, Y', strtotime($e['enrollment_date']))) ?></td>
        <td><span class="badge badge-success"><?= e($e['status']) ?></span></td>
      </tr>
      <?php endforeach; if(empty($enrollments)): ?><tr class="empty-row"><td colspan="5">No enrollments.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div></main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


