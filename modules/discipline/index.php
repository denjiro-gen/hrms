<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);

$pageTitle = 'Disciplinary Actions';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    verifyCsrf();
    
    // Generate a unique record code
    $recordCode = 'DISC-' . strtoupper(substr(uniqid(), -6));
    
    $stmt = $db->prepare("INSERT INTO disciplinary_records (record_code, employee_id, incident_date, incident_type, severity, description, action_taken, status, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, 'Open', ?)");
    if ($stmt->execute([$recordCode, $_POST['employee_id'], $_POST['incident_date'], $_POST['incident_type'], $_POST['severity'], $_POST['description'], $_POST['action_taken'], $_SESSION['user_id']])) {
        logAudit('File Incident', 'Discipline', $recordCode, "Filed incident for employee ID {$_POST['employee_id']}");
        flash('success', 'Disciplinary record added successfully.');
    } else {
        flash('error', 'Failed to add disciplinary record.');
    }
    header("Location: index.php");
    exit;
}

$stmt = $db->query("SELECT d.*, e.first_name, e.last_name, e.employee_code 
                    FROM disciplinary_records d 
                    JOIN employees e ON d.employee_id = e.id 
                    ORDER BY d.incident_date DESC");
$records = $stmt->fetchAll();

$emps = $db->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE status='Active'")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="main-content hrms-main">
  <div class="d-flex justify-content-between mb-4">
    <h1 class="page-title mb-0"><i class="fas fa-gavel me-2"></i> Disciplinary Records</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> File Incident</button>
  </div>
  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div><?php endif; ?>
  <div class="card">
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Employee</th><th>Incident Date</th><th>Violation</th><th>Action Taken</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($records as $r): ?>
            <tr>
              <td><div class="fw-semibold"><?= e($r['last_name'].', '.$r['first_name']) ?></div><div class="small text-muted"><?= e($r['employee_code']) ?></div></td>
              <td><?= e(date('M d, Y', strtotime($r['incident_date']))) ?></td>
              <td class="fw-bold text-danger"><?= e($r['incident_type']) ?></td>
              <td><?= e($r['action_taken']) ?></td>
              <td><span class="badge badge-warning"><?= e($r['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if(empty($records)): ?><tr class="empty-row"><td colspan="5">No records found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<!-- Add Modal -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?><input type="hidden" name="action" value="add">
        <div class="modal-header"><h5 class="modal-title text-danger">File Disciplinary Action</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Employee *</label>
            <select name="employee_id" class="form-control" required>
              <?php foreach($emps as $emp): ?><option value="<?= $emp['id'] ?>"><?= e($emp['last_name'].', '.$emp['first_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="row mb-3">
            <div class="col"><label>Incident Date *</label><input type="date" name="incident_date" class="form-control" required></div>
            <div class="col"><label>Incident Type *</label><input type="text" name="incident_type" class="form-control" required></div>
          </div>
          <div class="mb-3">
            <label>Severity</label>
            <select name="severity" class="form-control" required>
              <option value="Minor">Minor</option>
              <option value="Moderate">Moderate</option>
              <option value="Serious">Serious</option>
              <option value="Grave">Grave</option>
            </select>
          </div>
          <div class="mb-3"><label>Description *</label><textarea name="description" class="form-control" required></textarea></div>
          <div class="mb-3">
            <label>Action Taken / Penalty *</label>
            <select name="action_taken" class="form-control" required>
              <option value="Verbal Warning">Verbal Warning</option>
              <option value="Written Warning">Written Warning</option>
              <option value="Suspension">Suspension</option>
              <option value="Termination">Termination</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-danger">Save Record</button></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
