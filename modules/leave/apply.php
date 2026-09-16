<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../includes/auth.php";
requireLogin();
$pageTitle = "Apply for Leave";
$db = getDB();

$stmt = $db->prepare("SELECT id FROM employees WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION["user_id"]]);
$emp   = $stmt->fetch();
$empId = $emp["id"] ?? null;

$leaveTypes = $db->query("SELECT * FROM leave_types ORDER BY name")->fetchAll();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrf();
    if (!$empId) {
        $error = "No employee record is linked to your account. Contact HR.";
    } else {
        $start  = $_POST["start_date"];
        $end    = $_POST["end_date"];
        $typeId = (int)$_POST["leave_type_id"];
        $reason = trim($_POST["reason"]);
        $days   = max(1, (int)((strtotime($end) - strtotime($start)) / 86400) + 1);
        $db->prepare("INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status) VALUES (?,?,?,?,?,?,'Pending')")
           ->execute([$empId, $typeId, $start, $end, $days, $reason]);
        logAudit("Apply Leave", "Leave", (string)$db->lastInsertId(), "Employee applied for leave.");
        flash("success", "Leave request submitted successfully.");
        header("Location: index.php");
        exit;
    }
}
include __DIR__ . "/../../includes/header.php";
include __DIR__ . "/../../includes/navbar.php";
include __DIR__ . "/../../includes/sidebar.php";
?>
<main class="hrms-main"><div class="main-content">
  <div style="max-width:560px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;">
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
      <h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Apply for Leave</h1>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>
    <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:24px;">
      <form method="POST">
        <?= csrfField() ?>
        <div style="margin-bottom:16px;">
          <label class="form-label">Leave Type *</label>
          <select name="leave_type_id" class="form-control" required>
            <option value="">-- Select --</option>
            <?php foreach ($leaveTypes as $t): ?>
              <option value="<?= $t["id"] ?>"><?= e($t["name"]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
          <div>
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control" required>
          </div>
          <div>
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" class="form-control" required>
          </div>
        </div>
        <div style="margin-bottom:20px;">
          <label class="form-label">Reason *</label>
          <textarea name="reason" class="form-control" rows="4" required placeholder="Briefly describe your reason..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit Leave Request</button>
      </form>
    </div>
  </div>
</div></main>
<?php include __DIR__ . "/../../includes/footer.php"; ?>
