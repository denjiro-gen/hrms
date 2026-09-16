<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireRole(['admin', 'hr']);

$pageTitle = 'Job Postings';
$db = getDB();

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(j.position_title LIKE ? OR j.job_code LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
}
if ($status) {
    $where[] = "j.status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Count total
$stmt = $db->prepare("SELECT COUNT(*) FROM job_postings j WHERE $whereClause");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$pag = paginate($total, $perPage, $page);

// Fetch records
$sql = "SELECT j.*, d.name as department_name, 
        (SELECT COUNT(*) FROM applications WHERE job_posting_id = j.id) as applicant_count
        FROM job_postings j 
        LEFT JOIN departments d ON j.department_id = d.id 
        WHERE $whereClause 
        ORDER BY j.date_posted DESC 
        LIMIT {$pag['per_page']} OFFSET {$pag['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/navbar.php';
include __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title mb-0"><i class="fas fa-briefcase me-2"></i> Job Postings</h1>
    <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Post New Job</a>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body p-3">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control" placeholder="Position or Code..." value="<?= e($search) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="Open" <?= $status === 'Open' ? 'selected' : '' ?>>Open</option>
            <option value="Closed" <?= $status === 'Closed' ? 'selected' : '' ?>>Closed</option>
            <option value="Draft" <?= $status === 'Draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>
        <div class="col-md-3">
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
            <th>Job Code</th>
            <th>Position Title</th>
            <th>Department</th>
            <th>Applicants</th>
            <th>Closing Date</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($jobs)): ?>
            <tr class="empty-row"><td colspan="7">No job postings found.</td></tr>
          <?php else: ?>
            <?php foreach ($jobs as $job): ?>
              <tr>
                <td><strong><?= e($job['job_code']) ?></strong></td>
                <td>
                  <div class="fw-semibold text-dark"><?= e($job['position_title']) ?></div>
                  <div class="small text-muted"><?= e($job['employment_type']) ?></div>
                </td>
                <td><?= e($job['department_name']) ?></td>
                <td>
                  <a href="../applicants/index.php?job_id=<?= $job['id'] ?>" class="badge bg-secondary text-decoration-none">
                    <?= $job['applicant_count'] ?> Applicants
                  </a>
                </td>
                <td><?= e(date('M d, Y', strtotime($job['closing_date']))) ?></td>
                <td><span class="badge <?= badgeClass($job['status']) ?>"><?= e($job['status']) ?></span></td>
                <td class="text-end">
                  <div class="btn-group">
                    <a href="edit.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                  </div>
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
        <div class="small text-muted">Showing <?= count($jobs) ?> of <?= $total ?> postings</div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
              <li class="page-item <?= $i === $pag['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
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
