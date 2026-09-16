<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);

$pageTitle = 'Payroll';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate') {
    verifyCsrf();
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $period = date('M d', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));
    
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO payroll (payroll_period, start_date, end_date, processed_by, total_amount, status) VALUES (?, ?, ?, ?, 0, 'Draft')");
        $stmt->execute([$period, $startDate, $endDate, $_SESSION['user_id']]);
        $payrollId = $db->lastInsertId();
        
        $employees = $db->query("SELECT id, basic_salary FROM employees WHERE status='Active'")->fetchAll();
        $totalAmount = 0;
        foreach ($employees as $emp) {
            $basic = $emp['basic_salary'] / 2;
            $sss = $basic * 0.045;
            $philhealth = $basic * 0.02;
            $pagibig = 100;
            $tax = ($basic - $sss - $philhealth - $pagibig) * 0.10;
            $deductions = $sss + $philhealth + $pagibig + $tax;
            $net = $basic - $deductions;
            
            $stmt = $db->prepare("INSERT INTO payslips (payroll_id, employee_id, basic_pay, overtime_pay, deductions, net_pay) VALUES (?, ?, ?, 0, ?, ?)");
            $stmt->execute([$payrollId, $emp['id'], $basic, $deductions, $net]);
            $totalAmount += $net;
        }
        
        $db->prepare("UPDATE payroll SET total_amount = ? WHERE id = ?")->execute([$totalAmount, $payrollId]);
        $db->commit();
        logAudit('Generate Payroll', 'Payroll', (string)$payrollId, "Generated payroll for $period");
        flash('success', "Payroll generated successfully.");
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

$payrolls = $db->query("SELECT p.*, e.first_name, e.last_name, pp.period_name FROM payroll p LEFT JOIN employees e ON p.employee_id = e.id LEFT JOIN payroll_periods pp ON p.period_id = pp.id ORDER BY p.created_at DESC")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="hrms-main"><div class="main-content">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
    <div><h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Payroll Management</h1><p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;">Generate and review employee payroll</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#genModal"><i class="fas fa-plus"></i> Generate Payroll</button>
  </div>
  <?php if ($msg = getFlash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
  <div class="card"><table class="data-table">
    <thead><tr><th>Period</th><th>Date Range</th><th>Processed By</th><th>Total Net</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($payrolls as $p): ?>
      <tr>
        <td style="font-weight:600;color:#111;"><?= e($p['payroll_period']) ?></td>
        <td><?= e(date('M d',strtotime($p['start_date']))) ?> - <?= e(date('M d, Y',strtotime($p['end_date']))) ?></td>
        <td><?= e($p['first_name'].' '.$p['last_name']) ?></td>
        <td style="color:#12B76A;font-weight:600;"><?= peso($p['total_amount']) ?></td>
        <td><span class="badge badge-info"><?= e($p['status']) ?></span></td>
      </tr>
      <?php endforeach; if(empty($payrolls)): ?><tr class="empty-row"><td colspan="5">No payrolls found.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div></main>
<div class="modal fade" id="genModal"><div class="modal-dialog"><div class="modal-content"><form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="generate"><div class="modal-header"><h5 class="modal-title">Generate Payroll</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div><div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Generate</button></div></form></div></div></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

