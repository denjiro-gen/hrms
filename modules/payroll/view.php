<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {
    verifyCsrf();
    $db->prepare("UPDATE payroll SET status='Approved' WHERE id=?")->execute([$id]);
    logAudit('Approve Payroll', 'Payroll', (string)$id, "Approved payroll.");
    flash('success', 'Payroll approved successfully!');
    header("Location: view.php?id=$id");
    exit;
}

$stmt = $db->prepare("SELECT p.*, u.first_name, u.last_name FROM payroll p LEFT JOIN users u ON p.processed_by = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$payroll = $stmt->fetch();

if (!$payroll) {
    die("Payroll not found.");
}

$stmt = $db->prepare("SELECT ps.*, e.employee_code, e.first_name, e.last_name, d.code as dept_code
                      FROM payslips ps 
                      JOIN employees e ON ps.employee_id = e.id 
                      LEFT JOIN departments d ON e.department_id = d.id
                      WHERE ps.payroll_id = ?
                      ORDER BY e.last_name ASC");
$stmt->execute([$id]);
$payslips = $stmt->fetchAll();

$pageTitle = 'Payroll Details - ' . $payroll['payroll_period'];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
      <h1 class="page-title mb-0">Payroll Details</h1>
    </div>
    <?php if ($payroll['status'] === 'Draft'): ?>
    <form method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="approve">
      <button type="submit" class="btn btn-success" onclick="return confirm('Approve this payroll? This action cannot be undone.')"><i class="fas fa-check me-1"></i> Approve Payroll</button>
    </form>
    <?php endif; ?>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="text-muted small">Payroll Period</div>
          <div class="fw-bold fs-5"><?= e($payroll['payroll_period']) ?></div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Processed By</div>
          <div class="fw-semibold"><?= e($payroll['first_name'] . ' ' . $payroll['last_name']) ?></div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Status</div>
          <span class="badge <?= badgeClass($payroll['status']) ?>"><?= e($payroll['status']) ?></span>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Total Net Pay</div>
          <div class="fw-bold fs-4 text-success"><?= peso($payroll['total_amount']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header border-bottom">
      <h3 class="mb-0">Employee Payslips (<?= count($payslips) ?>)</h3>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Dept</th>
            <th class="text-end">Basic Pay</th>
            <th class="text-end">Overtime</th>
            <th class="text-end text-danger">Deductions</th>
            <th class="text-end text-success">Net Pay</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payslips as $ps): ?>
            <tr>
              <td>
                <div class="fw-semibold text-dark"><?= e($ps['last_name'] . ', ' . $ps['first_name']) ?></div>
                <div class="small text-muted"><?= e($ps['employee_code']) ?></div>
              </td>
              <td><?= e($ps['dept_code']) ?></td>
              <td class="text-end"><?= peso($ps['basic_pay']) ?></td>
              <td class="text-end"><?= peso($ps['overtime_pay']) ?></td>
              <td class="text-end text-danger">-<?= peso($ps['deductions']) ?></td>
              <td class="text-end text-success fw-bold"><?= peso($ps['net_pay']) ?></td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline text-primary" onclick="alert('Payslip generation logic to be implemented for: <?= e($ps['first_name']) ?>')"><i class="fas fa-file-invoice"></i> PDF</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
