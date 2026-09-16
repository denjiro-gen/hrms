<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireRole(['admin', 'hr']);

$pageTitle = 'Applicants Tracking';
$db = getDB();

$jobId  = $_GET['job_id'] ?? '';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = ["1=1"];
$params = [];

if ($jobId) {
    $where[] = "a.job_posting_id = ?";
    $params[] = $jobId;
}
if ($search) {
    $where[] = "(app.first_name LIKE ? OR app.last_name LIKE ? OR app.email LIKE ? OR j.position_title LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($status) {
    $where[] = "a.status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Count total
$stmt = $db->prepare("SELECT COUNT(*) FROM applications a 
                      JOIN applicants app ON a.applicant_id = app.id 
                      JOIN job_postings j ON a.job_posting_id = j.id
                      WHERE $whereClause");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$pag = paginate($total, $perPage, $page);

// Fetch records
$sql = "SELECT a.*, app.first_name, app.last_name, app.email, app.contact_number, 
        j.position_title, j.job_code, r.match_score, r.recommendation 
        FROM applications a 
        JOIN applicants app ON a.applicant_id = app.id 
        JOIN job_postings j ON a.job_posting_id = j.id 
        LEFT JOIN recruitment_ai_results r ON a.id = r.application_id
        WHERE $whereClause 
        ORDER BY a.application_date DESC 
        LIMIT {$pag['per_page']} OFFSET {$pag['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Jobs for filter
$jobs = $db->query("SELECT id, position_title, job_code FROM job_postings ORDER BY date_posted DESC")->fetchAll();

include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/navbar.php';
include __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title mb-0"><i class="fas fa-user-tie me-2"></i> Applicant Tracking</h1>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body p-3">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control" placeholder="Name or Email..." value="<?= e($search) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Filter by Job Posting</label>
          <select name="job_id" class="form-control">
            <option value="">All Job Postings</option>
            <?php foreach ($jobs as $j): ?>
              <option value="<?= $j['id'] ?>" <?= $jobId == $j['id'] ? 'selected' : '' ?>>
                <?= e($j['job_code'] . ' - ' . $j['position_title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Application Status</label>
          <select name="status" class="form-control">
            <option value="">All Status</option>
            <?php 
            $statuses = ['Applied','Screening','Interview','Shortlisted','Hired','Rejected'];
            foreach($statuses as $s): ?>
              <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-outline w-100"><i class="fas fa-filter"></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Applicant Name</th>
            <th>Applied For</th>
            <th>Date Applied</th>
            <th>AI Match Score</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($applications)): ?>
            <tr class="empty-row"><td colspan="6">No applications found.</td></tr>
          <?php else: ?>
            <?php foreach ($applications as $app): ?>
              <tr>
                <td>
                  <div class="fw-semibold text-dark"><?= e($app['last_name'] . ', ' . $app['first_name']) ?></div>
                  <div class="small text-muted"><?= e($app['email']) ?></div>
                </td>
                <td>
                  <div class="fw-semibold"><?= e($app['position_title']) ?></div>
                  <div class="small text-muted"><?= e($app['job_code']) ?></div>
                </td>
                <td><?= e(date('M d, Y', strtotime($app['application_date']))) ?></td>
                <td>
                  <?php if (isset($app['match_score'])): ?>
                    <div class="d-flex align-items-center gap-2">
                      <?php 
                        $color = $app['match_score'] >= 85 ? 'success' : ($app['match_score'] >= 70 ? 'warning' : 'danger');
                      ?>
                      <div class="progress" style="width: 60px; height: 6px;">
                        <div class="progress-bar bg-<?= $color ?>" style="width: <?= $app['match_score'] ?>%"></div>
                      </div>
                      <span class="small fw-bold text-<?= $color ?>"><?= number_format($app['match_score'], 1) ?>%</span>
                    </div>
                  <?php else: ?>
                    <span class="text-muted small fst-italic">Pending AI Review</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?= badgeClass($app['status']) ?>"><?= e($app['status']) ?></span></td>
                <td class="text-end">
                  <a href="view.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-outline text-primary">View Profile <i class="fas fa-chevron-right ms-1"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pag['total_pages'] > 1): ?>
    <div class="card-body border-top">
      <div class="d-flex justify-content-between align-items-center">
        <div class="small text-muted">Showing <?= count($applications) ?> of <?= $total ?> applicants</div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
              <li class="page-item <?= $i === $pag['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&job_id=<?= urlencode($jobId) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
