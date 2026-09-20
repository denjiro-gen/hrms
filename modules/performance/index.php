<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr', 'dept_head']);

$pageTitle = 'Performance Evaluations';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'evaluate') {
    verifyCsrf();
    $empId = $_POST['employee_id'];
    $period = $_POST['evaluation_period'];
    $score = (float)$_POST['rating'];
    $comments = $_POST['comments'];
    
    // Rating logic 1-5 (Matching ENUM)
    if ($score >= 4.5) $rating = 'Excellent';
    elseif ($score >= 3.5) $rating = 'Very Good';
    elseif ($score >= 2.5) $rating = 'Satisfactory';
    elseif ($score >= 1.5) $rating = 'Needs Improvement';
    else $rating = 'Unsatisfactory';

    $evalCode = 'EVAL-' . strtoupper(substr(uniqid(), -6));
    $pStart = date('Y-01-01');
    $pEnd = date('Y-12-31');

    $stmt = $db->prepare("INSERT INTO performance_evaluations (eval_code, employee_id, evaluator_id, evaluation_period, period_start, period_end, performance_rating, overall_score, comments, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted')");
    $stmt->execute([$evalCode, $empId, $_SESSION['user_id'], $period, $pStart, $pEnd, $rating, $score, $comments]);
    
    logAudit('Add Evaluation', 'Performance', (string)$db->lastInsertId(), "Added evaluation for employee $empId");
    flash('success', 'Performance evaluation saved successfully.');
    header("Location: index.php");
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = ["1=1"];
$params = [];
if (isDeptHead()) {
    $where[] = "e.department_id = ?";
    $params[] = $_SESSION['dept_id'];
}

$whereClause = implode(' AND ', $where);

$stmt = $db->prepare("SELECT COUNT(*) FROM performance_evaluations p JOIN employees e ON p.employee_id = e.id WHERE $whereClause");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pag = paginate($total, $perPage, $page);

$sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code, e.position, d.code as dept_code,
        u.first_name as eval_first, u.last_name as eval_last
        FROM performance_evaluations p 
        JOIN employees e ON p.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN users u ON p.evaluator_id = u.id
        WHERE $whereClause
        ORDER BY p.created_at DESC 
        LIMIT {$pag['per_page']} OFFSET {$pag['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$evaluations = $stmt->fetchAll();

// Employees for dropdown
if (isDeptHead()) {
    $stmt = $db->prepare("SELECT id, first_name, last_name, employee_code FROM employees WHERE department_id = ? AND status='Active' ORDER BY last_name");
    $stmt->execute([$_SESSION['dept_id']]);
} else {
    $stmt = $db->prepare("SELECT id, first_name, last_name, employee_code FROM employees WHERE status='Active' ORDER BY last_name");
    $stmt->execute();
}
$employees = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title mb-0"><i class="fas fa-star-half-alt me-2"></i> Performance Management</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#evaluateModal"><i class="fas fa-plus"></i> New Evaluation</button>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Dept & Position</th>
            <th>Period</th>
            <th>Rating</th>
            <th>Evaluator</th>
            <th>Date Evaluated</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($evaluations)): ?>
            <tr class="empty-row"><td colspan="6">No performance records found.</td></tr>
          <?php else: ?>
            <?php foreach ($evaluations as $e): ?>
              <tr>
                <td>
                  <div class="fw-semibold text-dark"><?= e($e['last_name'] . ', ' . $e['first_name']) ?></div>
                  <div class="small text-muted"><?= e($e['employee_code']) ?></div>
                </td>
                <td>
                  <div><?= e($e['dept_code']) ?></div>
                  <div class="small text-muted"><?= e($e['position']) ?></div>
                </td>
                <td><?= e($e['evaluation_period']) ?></td>
                <td>
                  <div class="fw-bold fs-5 text-primary"><?= number_format((float)($e['overall_score'] ?? 0), 1) ?> <span class="fs-6 text-muted">/ 5.0</span></div>
                  <div class="small badge bg-light text-dark border"><?= e($e['performance_rating'] ?? 'N/A') ?></div>
                </td>
                <td><?= e($e['eval_first'] . ' ' . $e['eval_last']) ?></td>
                <td><?= e(date('M d, Y', strtotime($e['created_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Evaluate Modal -->
<div class="modal fade" id="evaluateModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="evaluate">
        <div class="modal-header">
          <h5 class="modal-title">New Performance Evaluation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Employee *</label>
              <select name="employee_id" class="form-control" required>
                <option value="">-- Select Employee --</option>
                <?php foreach($employees as $emp): ?>
                  <option value="<?= $emp['id'] ?>"><?= e($emp['last_name'].', '.$emp['first_name'].' ('.$emp['employee_code'].')') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Evaluation Period *</label>
              <input type="text" name="evaluation_period" class="form-control" placeholder="e.g. Q1 2026 or 2025-2026" required>
            </div>
            <div class="col-md-12">
              <label class="form-label">Overall Score (1.0 to 5.0) *</label>
              <input type="number" step="0.1" name="rating" class="form-control" min="1" max="5" required>
              <div class="form-text">5=Outstanding, 4=Very Satisfactory, 3=Satisfactory, 2=Fair, 1=Poor</div>
            </div>
            <div class="col-md-12">
              <label class="form-label">Comments & Feedback *</label>
              <textarea name="comments" class="form-control" rows="4" required></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Evaluation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
