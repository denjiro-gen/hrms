<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);

$pageTitle = 'Payroll';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate') {
    verifyCsrf();
    $startDate = $_POST['start_date'];
    $endDate   = $_POST['end_date'];
    $period    = date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));

    try {
        $db->beginTransaction();
        $employees = $db->query("SELECT id, basic_salary FROM employees WHERE employment_status='Active' AND status='Active'")->fetchAll();
        $totalNet  = 0;

        // Check/create payroll period
        $pCheck = $db->prepare("SELECT id FROM payroll_periods WHERE start_date = ? AND end_date = ?");
        $pCheck->execute([$startDate, $endDate]);
        $periodId = $pCheck->fetchColumn();
        if (!$periodId) {
            $db->prepare("INSERT INTO payroll_periods (period_name, start_date, end_date, status) VALUES (?, ?, ?, 'Closed')")
               ->execute([$period, $startDate, $endDate]);
            $periodId = $db->lastInsertId();
        }

        foreach ($employees as $emp) {
            $basic      = round($emp['basic_salary'] / 2, 2);
            $sss        = round($basic * 0.045, 2);
            $philhealth = round($basic * 0.02, 2);
            $pagibig    = 100.00;
            $tax        = round(($basic - $sss - $philhealth - $pagibig) * 0.10, 2);
            $totalDed   = $sss + $philhealth + $pagibig + $tax;
            $net        = $basic - $totalDed;
            $code       = 'PAY-' . strtoupper(substr(uniqid(), -6));

            $db->prepare("INSERT INTO payroll (payroll_code, employee_id, period_id, basic_salary, sss_deduction, philhealth_deduction, pagibig_deduction, tax_deduction, total_deductions, gross_salary, net_salary, payment_status, prepared_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)")
               ->execute([$code, $emp['id'], $periodId, $basic, $sss, $philhealth, $pagibig, $tax, $totalDed, $basic, $net, $_SESSION['user_id']]);
            $totalNet += $net;
        }

        $db->commit();
        logAudit('Generate Payroll', 'Payroll', $period, "Generated for $period — " . count($employees) . " employees");
        flash('success', "Payroll generated for <strong>$period</strong>. " . count($employees) . " employees processed.");
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        flash('error', "Error: " . $e->getMessage());
        header("Location: index.php");
        exit;
    }
}

// Summarize by period for table display
$payrolls = $db->query("
    SELECT pp.period_name, pp.start_date, pp.end_date,
           SUM(p.net_salary) as total_net,
           COUNT(p.id) as emp_count,
           MAX(p.payment_status) as status,
           u.first_name, u.last_name,
           MAX(p.created_at) as created_at
    FROM payroll p
    LEFT JOIN payroll_periods pp ON p.period_id = pp.id
    LEFT JOIN users u ON p.prepared_by = u.id
    GROUP BY p.period_id
    ORDER BY MAX(p.created_at) DESC
    LIMIT 50
")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="hrms-main">
  <div class="main-content">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
      <div>
        <h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Payroll Management</h1>
        <p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;">Generate and review employee payroll runs</p>
      </div>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#genModal">
        <i class="fas fa-plus me-1"></i> Generate Payroll
      </button>
    </div>

    <?php if ($msg = getFlash('success')): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $msg ?></div>
    <?php endif; ?>
    <?php if ($msg = getFlash('error')): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= e($msg) ?></div>
    <?php endif; ?>

    <div class="card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Period</th>
            <th>Date Range</th>
            <th>Employees</th>
            <th>Total Net Pay</th>
            <th>Prepared By</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payrolls)): ?>
            <tr class="empty-row"><td colspan="6">No payroll records found. Click "Generate Payroll" to start.</td></tr>
          <?php else: ?>
            <?php foreach ($payrolls as $r): ?>
            <tr>
              <td style="font-weight:600;color:#111;"><?= e($r['period_name']) ?></td>
              <td><?= $r['start_date'] ? e(date('M d', strtotime($r['start_date'])) . ' – ' . date('M d, Y', strtotime($r['end_date']))) : 'N/A' ?></td>
              <td><span class="badge badge-info"><?= (int)$r['emp_count'] ?> employees</span></td>
              <td style="color:#12B76A;font-weight:600;"><?= peso((float)$r['total_net']) ?></td>
              <td><?= e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></td>
              <td><span class="badge badge-<?= $r['status'] === 'Paid' ? 'success' : 'warning' ?>"><?= e($r['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</main>

<!-- Generate Payroll Modal — placed outside <main> so Bootstrap can display it correctly -->
<div class="modal fade" id="genModal" tabindex="-1" aria-labelledby="genModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="generate">
        <div class="modal-header">
          <h5 class="modal-title" id="genModalLabel"><i class="fas fa-file-invoice-dollar me-2"></i>Generate Payroll</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p style="font-size:13px;color:#6B7280;margin-bottom:16px;">This will create payslips for ALL active employees based on their current base salary.</p>
          <div class="mb-3">
            <label class="form-label" style="font-weight:600;">Pay Period Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-weight:600;">Pay Period End Date</label>
            <input type="date" name="end_date" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-cog me-2"></i>Generate Now</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>




