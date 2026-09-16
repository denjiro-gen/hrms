<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../includes/auth.php";
requireRole(["admin"]);
$pageTitle = "User Accounts";
$db = getDB();

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "toggle") {
    verifyCsrf();
    $uid   = (int)$_POST["uid"];
    $newSt = $_POST["current_status"] === "active" ? "inactive" : "active";
    $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newSt, $uid]);
    flash("success", "User status updated.");
    header("Location: index.php"); exit;
}

$users = $db->query(
    "SELECT u.*, r.name as role_name FROM users u
     LEFT JOIN roles r ON u.role_id = r.id
     ORDER BY u.created_at DESC"
)->fetchAll();

if ($msg = getFlash("success")) { echo '<div class="alert alert-success" style="margin:16px 24px 0;">' . e($msg) . '</div>'; }
include __DIR__ . "/../../includes/header.php";
include __DIR__ . "/../../includes/navbar.php";
include __DIR__ . "/../../includes/sidebar.php";
?>
<main class="hrms-main"><div class="main-content">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div>
      <h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">User Accounts</h1>
      <p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;"><?= count($users) ?> system users</p>
    </div>
  </div>
  <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;overflow:hidden;">
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <span style="width:30px;height:30px;border-radius:50%;background:#2F7BEE18;color:#2F7BEE;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                    <?= strtoupper(substr($u["first_name"]??'U',0,1).substr($u["last_name"]??'',0,1)) ?>
                  </span>
                  <div>
                    <div style="font-weight:600;font-size:13.5px;color:#111;"><?= e(($u["first_name"]??"")." ".($u["last_name"]??"")) ?></div>
                  </div>
                </div>
              </td>
              <td style="color:#6B7280;"><?= e($u["email"]) ?></td>
              <td><span class="badge badge-info"><?= e($u["role_name"]??"—") ?></span></td>
              <td><span class="badge <?= $u["status"]==="active" ? "badge-success" : "badge-secondary" ?>"><?= e(ucfirst($u["status"])) ?></span></td>
              <td style="color:#9CA3AF;"><?= e(date("M d, Y", strtotime($u["created_at"]))) ?></td>
              <td class="text-end">
                <?php if ($u["id"] != $_SESSION["user_id"]): ?>
                <form method="POST" style="display:inline;">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="uid" value="<?= $u["id"] ?>">
                  <input type="hidden" name="current_status" value="<?= e($u["status"]) ?>">
                  <button class="btn btn-outline btn-sm" onclick="return confirm('Toggle user status?')">
                    <i class="fas fa-<?= $u["status"]==="active" ? "ban" : "check" ?>"></i>
                    <?= $u["status"]==="active" ? "Disable" : "Enable" ?>
                  </button>
                </form>
                <?php else: ?>
                  <span style="font-size:12px;color:#9CA3AF;">You</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></main>
<?php include __DIR__ . "/../../includes/footer.php"; ?>

