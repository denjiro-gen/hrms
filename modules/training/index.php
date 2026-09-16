<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr', 'dept_head']);
$pageTitle = 'Training';
$db = getDB();
$programs = $db->query("SELECT * FROM trainings ORDER BY training_date DESC")->fetchAll();
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="hrms-main"><div class="main-content">
  <div style="margin-bottom:22px;"><h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Training Programs</h1><p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;">Manage employee training and development</p></div>
  <div class="card"><table class="data-table">
    <thead><tr><th>Program Name</th><th>Provider</th><th>Dates</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($programs as $p): ?>
      <tr>
        <td style="font-weight:600;color:#111;"><?= e($p['title']) ?></td>
        <td><?= e($p['provider']) ?></td>
        <td><?= e(date('M d', strtotime($p['training_date']))) ?> - <?= e(date('M d, Y', strtotime($p['end_date']))) ?></td>
        <td><span class="badge badge-info"><?= e($p['status']) ?></span></td>
      </tr>
      <?php endforeach; if(empty($programs)): ?><tr class="empty-row"><td colspan="4">No training programs scheduled.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div></main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


