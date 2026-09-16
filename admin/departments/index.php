<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../includes/auth.php";
requireRole(["admin"]);
$pageTitle = "Departments";
$db = getDB();

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add") {
    verifyCsrf();
    $db->prepare("INSERT INTO departments (name, code, status) VALUES (?,?,'Active')")
       ->execute([trim($_POST["name"]), strtoupper(trim($_POST["code"]))]);
    flash("success", "Department added.");
    header("Location: index.php"); exit;
}

$depts = $db->query(
    "SELECT d.*, COUNT(e.id) as emp_count FROM departments d
     LEFT JOIN employees e ON e.department_id = d.id AND e.status='Active'
     GROUP BY d.id ORDER BY d.name"
)->fetchAll();

include __DIR__ . "/../../includes/header.php";
include __DIR__ . "/../../includes/navbar.php";
include __DIR__ . "/../../includes/sidebar.php";
?>
<main class="hrms-main"><div class="main-content">
  <?php if ($msg = getFlash("success")): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div>
      <h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Departments</h1>
      <p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;"><?= count($depts) ?> departments</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="fas fa-plus"></i> Add Department
    </button>
  </div>
  <div style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;overflow:hidden;">
    <table class="data-table">
      <thead><tr><th>Code</th><th>Department Name</th><th>Employees</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($depts as $d): ?>
          <tr>
            <td><span class="badge badge-info"><?= e($d["code"]) ?></span></td>
            <td style="font-weight:600;color:#111;"><?= e($d["name"]) ?></td>
            <td><?= $d["emp_count"] ?> active</td>
            <td><span class="badge <?= $d["status"]==="Active" ? "badge-success" : "badge-secondary" ?>"><?= e($d["status"]) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($depts)): ?><tr class="empty-row"><td colspan="4">No departments found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></main>

<!-- Modal -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?><input type="hidden" name="action" value="add">
        <div class="modal-header"><h5 class="modal-title">New Department</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div style="margin-bottom:14px;"><label class="form-label">Department Name *</label><input type="text" name="name" class="form-control" required></div>
          <div><label class="form-label">Code (e.g. HR, IT, FIN) *</label><input type="text" name="code" class="form-control" maxlength="10" required></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Department</button></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . "/../../includes/footer.php"; ?>

