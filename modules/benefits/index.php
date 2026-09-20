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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_benefit') {
    verifyCsrf();
    $empId = $_POST['employee_id'];
    $benefitId = $_POST['benefit_id'];
    $startDate = $_POST['start_date'];
    
    // Auto-create benefit if it doesn't exist and user typed a string (just a fallback, but we'll use a dropdown)
    // Assuming dropdowns for both.
    
    $stmt = $db->prepare("INSERT INTO employee_benefits (employee_id, benefit_id, start_date, enrolled_by) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$empId, $benefitId, $startDate, $_SESSION['user_id']])) {
        logAudit('Assign Benefit', 'Benefits', (string)$db->lastInsertId(), "Assigned benefit #$benefitId to employee #$empId");
        flash('success', 'Benefit assigned successfully.');
    } else {
        flash('error', 'Failed to assign benefit.');
    }
    header("Location: index.php");
    exit;
}

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
    
    // Fetch options for the modal
    $employeesList = $db->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status='Active' ORDER BY last_name ASC")->fetchAll();
    $benefitsList = $db->query("SELECT id, name, benefit_type FROM benefits WHERE status='Active' ORDER BY name ASC")->fetchAll();
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
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal"><i class="fas fa-plus me-1"></i> Assign Benefit</button>
    <?php endif; ?>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= e($msg) ?></div>
  <?php endif; ?>

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
  </div>
</main>

<?php if (!$isEmployee): ?>
<!-- Assign Benefit Modal -->
<div class="modal fade" id="assignModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="assign_benefit">
        <div class="modal-header">
          <h5 class="modal-title">Assign Benefit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-control" required>
              <option value="">Select Employee...</option>
              <?php foreach ($employeesList as $e): ?>
                <option value="<?= $e['id'] ?>"><?= e($e['last_name'].', '.$e['first_name'].' ('.$e['employee_code'].')') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Benefit Plan</label>
            <select name="benefit_id" class="form-control" required>
              <option value="">Select Benefit...</option>
              <?php foreach ($benefitsList as $b): ?>
                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?> (<?= e($b['benefit_type']) ?>)</option>
              <?php endforeach; ?>
            </select>
            <?php if(empty($benefitsList)): ?>
              <div class="text-danger mt-1" style="font-size:12px;">No active benefits found in the database. Please insert into `benefits` table first.</div>
            <?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
