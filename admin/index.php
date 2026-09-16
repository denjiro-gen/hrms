<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/auth.php";
requireRole(["admin"]);
$pageTitle = "Administration";
$db = getDB();

$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$logCount  = $db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$deptCount = $db->query("SELECT COUNT(*) FROM departments")->fetchColumn();

$logs = $db->query(
    "SELECT a.*, u.email FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.created_at DESC LIMIT 20"
)->fetchAll();

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
include __DIR__ . "/../includes/sidebar.php";
?>
<main class="hrms-main"><div class="main-content">
  <div style="margin-bottom:22px;">
    <h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">System Administration</h1>
    <p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;">System overview and full audit trail</p>
  </div>

  <!-- KPIs -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;">
    <?php
    $kpis = [
      ["System Users",  $userCount, "fas fa-user-shield", "#2F7BEE"],
      ["Departments",   $deptCount, "fas fa-building",    "#12B76A"],
      ["Audit Entries", $logCount,  "fas fa-history",     "#8B5CF6"],
    ];
    foreach ($kpis as [$label,$val,$icon,$color]):
    ?>
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:18px 16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#9CA3AF;"><?= $label ?></span>
        <span style="color:<?= $color ?>;font-size:15px;"><i class="<?= $icon ?>"></i></span>
      </div>
      <div style="font-size:30px;font-weight:700;color:#111;letter-spacing:-.5px;"><?= $val ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick links -->
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:22px;">
    <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-outline btn-sm"><i class="fas fa-user-shield me-1"></i>Manage Users</a>
    <a href="<?= BASE_URL ?>/admin/departments/index.php" class="btn btn-outline btn-sm"><i class="fas fa-building me-1"></i>Departments</a>
    <a href="<?= BASE_URL ?>/admin/settings.php" class="btn btn-outline btn-sm"><i class="fas fa-cog me-1"></i>Settings</a>
  </div>

  <!-- Audit log table -->
  <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #F3F4F6;">
      <span style="font-size:14px;font-weight:700;color:#111;">Audit Log</span>
    </div>
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>User</th>
            <th>Action</th>
            <th>Module</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr class="empty-row"><td colspan="5">No audit records yet.</td></tr>
          <?php else: ?>
            <?php foreach ($logs as $l): ?>
              <tr>
                <td style="white-space:nowrap;"><?= e(date("M d, Y H:i", strtotime($l["created_at"]))) ?></td>
                <td><?= e($l["email"] ?? "System") ?></td>
                <td><span class="badge badge-info"><?= e($l["action"]) ?></span></td>
                <td><?= e($l["module"]) ?></td>
                <td style="max-width:320px;"><?= e($l["description"]) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></main>
<?php include __DIR__ . "/../includes/footer.php"; ?>

