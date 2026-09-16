<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/auth.php";
requireRole(["admin"]);
$pageTitle = "System Settings";
$db = getDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    foreach ($_POST as $key => $val) {
        if ($key === "csrf_token") continue;
        $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key, $val, $val]);
    }
    flash("success", "Settings saved successfully.");
    header("Location: settings.php"); exit;
}

$rows = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll();
$settings = [];
foreach ($rows as $r) { $settings[$r["setting_key"]] = $r["setting_value"]; }
$get = fn($k,$d="") => $settings[$k] ?? $d;

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
include __DIR__ . "/../includes/sidebar.php";
?>
<main class="hrms-main"><div class="main-content">
  <div style="max-width:600px;">
    <h1 style="font-size:20px;font-weight:700;color:#111;margin:0 0 4px;">System Settings</h1>
    <p style="font-size:13px;color:#9CA3AF;margin-bottom:22px;">Configure general system behavior</p>
    <?php if ($msg = getFlash("success")): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($msg) ?></div><?php endif; ?>
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:24px;">
      <form method="POST">
        <?= csrfField() ?>
        <div style="margin-bottom:16px;"><label class="form-label">Institution Name</label><input type="text" name="institution_name" class="form-control" value="<?= e($get("institution_name","Bestlink College of the Philippines")) ?>"></div>
        <div style="margin-bottom:16px;"><label class="form-label">HR Email Address</label><input type="email" name="hr_email" class="form-control" value="<?= e($get("hr_email","hr@bestlink.edu.ph")) ?>"></div>
        <div style="margin-bottom:16px;"><label class="form-label">Work Start Time</label><input type="time" name="work_start" class="form-control" value="<?= e($get("work_start","08:00")) ?>"></div>
        <div style="margin-bottom:16px;"><label class="form-label">Work End Time</label><input type="time" name="work_end" class="form-control" value="<?= e($get("work_end","17:00")) ?>"></div>
        <div style="margin-bottom:20px;"><label class="form-label">Late Threshold (minutes after start)</label><input type="number" name="late_threshold" class="form-control" min="0" max="60" value="<?= e($get("late_threshold","15")) ?>"></div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
      </form>
    </div>
  </div>
</div></main>
<?php include __DIR__ . "/../includes/footer.php"; ?>

