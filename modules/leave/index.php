<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr', 'dept_head']);

$pageTitle = 'Leave Requests';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    verifyCsrf();
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    
    // Authorization check
    $stmt = $db->prepare("SELECT l.*, e.department_id FROM leave_requests l JOIN employees e ON l.employee_id = e.id WHERE l.id = ?");
    $stmt->execute([$id]);
    $leave = $stmt->fetch();
    
    if ($leave) {
        if (isDeptHead() && $leave['department_id'] == $_SESSION['dept_id']) {
            if ($action === 'approve') {
                $db->prepare("UPDATE leave_requests SET status='Dept Approved', dept_head_action='Approved', dept_head_id=?, dept_head_remarks=?, dept_head_at=NOW() WHERE id=?")->execute([$_SESSION['user_id'], $remarks, $id]);
            } elseif ($action === 'reject') {
                $db->prepare("UPDATE leave_requests SET status='Rejected', dept_head_action='Rejected', dept_head_id=?, dept_head_remarks=?, dept_head_at=NOW() WHERE id=?")->execute([$_SESSION['user_id'], $remarks, $id]);
            }
            logAudit('Review Leave', 'Leave', (string)$id, "Dept Head $action leave request.");
            flash('success', 'Leave request updated successfully.');
        } elseif (isHR() || isAdmin()) {
            if ($action === 'approve') {
                $db->prepare("UPDATE leave_requests SET status='HR Approved', hr_action='Approved', hr_id=?, hr_remarks=?, hr_at=NOW() WHERE id=?")->execute([$_SESSION['user_id'], $remarks, $id]);
                // Here we would also deduct from leave balance in a full implementation
            } elseif ($action === 'reject') {
                $db->prepare("UPDATE leave_requests SET status='Rejected', hr_action='Rejected', hr_id=?, hr_remarks=?, hr_at=NOW() WHERE id=?")->execute([$_SESSION['user_id'], $remarks, $id]);
            }
            logAudit('Review Leave', 'Leave', (string)$id, "HR $action leave request.");
            flash('success', 'Leave request updated successfully.');
        }
    }
    header("Location: index.php");
    exit;
}

$tab = $_GET['tab'] ?? 'pending';
$where = ["1=1"];
$params = [];

if ($tab === 'pending') {
    if (isDeptHead()) {
        $where[] = "l.status = 'Pending'";
    } else {
        $where[] = "(l.status = 'Pending' OR l.status = 'Dept Approved')";
    }
} else {
    $where[] = "(l.status = 'HR Approved' OR l.status = 'Rejected')";
}

if (isDeptHead()) {
    $where[] = "e.department_id = ?";
    $params[] = $_SESSION['dept_id'];
}

$whereClause = implode(' AND ', $where);

$sql = "SELECT l.*, e.first_name, e.last_name, d.code as dept_code, t.name as leave_type
        FROM leave_requests l
        JOIN employees e ON l.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        JOIN leave_types t ON l.leave_type_id = t.id
        WHERE $whereClause
        ORDER BY l.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title mb-0"><i class="fas fa-calendar-times me-2"></i> Leave Management</h1>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-header border-bottom bg-white pb-0">
      <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
          <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" href="?tab=pending">Pending / For Approval</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $tab === 'history' ? 'active' : '' ?>" href="?tab=history">History</a>
        </li>
      </ul>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Duration</th>
            <th>Reason</th>
            <th>Status</th>
            <?php if ($tab === 'pending'): ?>
            <th class="text-end">Action</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($requests)): ?>
            <tr class="empty-row"><td colspan="6">No leave requests found.</td></tr>
          <?php else: ?>
            <?php foreach ($requests as $r): ?>
              <tr>
                <td>
                  <div class="fw-semibold text-dark"><?= e($r['last_name'] . ', ' . $r['first_name']) ?></div>
                  <div class="small text-muted"><?= e($r['dept_code']) ?></div>
                </td>
                <td>
                  <div class="fw-semibold"><?= e($r['leave_type']) ?></div>
                  <div class="small text-muted"><?= $r['total_days'] ?> Day(s)</div>
                </td>
                <td>
                  <?= e(date('M d', strtotime($r['start_date']))) ?> - <?= e(date('M d, Y', strtotime($r['end_date']))) ?>
                </td>
                <td><div class="text-truncate" style="max-width: 250px;" title="<?= e($r['reason']) ?>"><?= e($r['reason']) ?></div></td>
                <td><span class="badge <?= badgeClass($r['status']) ?>"><?= e($r['status']) ?></span></td>
                <?php if ($tab === 'pending'): ?>
                <td class="text-end">
                  <form method="POST" action="" class="d-inline-block">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Approve this leave request?')"><i class="fas fa-check"></i></button>
                  </form>
                  <form method="POST" action="" class="d-inline-block ms-1">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Reject this leave request?')"><i class="fas fa-times"></i></button>
                  </form>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
