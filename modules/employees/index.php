<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr', 'dept_head']);

$pageTitle = 'Employees Directory';
$db = getDB();

$search = $_GET['search'] ?? '';
$dept   = $_GET['dept'] ?? '';
$status = $_GET['status'] ?? 'Active';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($dept) {
    $where[] = "e.department_id = ?";
    $params[] = $dept;
}
if ($status) {
    $where[] = "e.status = ?";
    $params[] = $status;
}

// Department head can only see their own department's employees
if (isDeptHead()) {
    $where[] = "e.department_id = ?";
    $params[] = $_SESSION['dept_id'];
}

$whereClause = implode(' AND ', $where);

// Count total
$stmt = $db->prepare("SELECT COUNT(*) FROM employees e WHERE $whereClause");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$pag = paginate($total, $perPage, $page);

// Fetch records
$sql = "SELECT e.*, d.name as department_name, d.code as dept_code 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE $whereClause 
        ORDER BY e.last_name ASC 
        LIMIT {$pag['per_page']} OFFSET {$pag['offset']}";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Fetch departments for filter
$stmt = $db->query("SELECT id, name FROM departments WHERE status='Active' ORDER BY name");
$departments = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title mb-0"><i class="fas fa-users me-2"></i> Employee Directory</h1>
    <?php if (in_array($_SESSION['role_slug'], ['admin', 'hr'])): ?>
    <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Employee</a>
    <?php endif; ?>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body p-3">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control" placeholder="Name or Code..." value="<?= e($search) ?>">
        </div>
        <?php if (!isDeptHead()): ?>
        <div class="col-md-3">
          <label class="form-label">Department</label>
          <select name="dept" class="form-control">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $dept == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="">All Status</option>
            <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
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
            <th>Code</th>
            <th>Name</th>
            <th>Department</th>
            <th>Position</th>
            <th>Contact</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employees)): ?>
            <tr class="empty-row"><td colspan="7">No employees found.</td></tr>
          <?php else: ?>
            <?php foreach ($employees as $emp): ?>
              <tr>
                <td><strong><?= e($emp['employee_code']) ?></strong></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar shadow-sm" style="width:32px;height:32px;font-size:12px;background:var(--brand-primary)">
                      <?= e(substr($emp['first_name'],0,1).substr($emp['last_name'],0,1)) ?>
                    </div>
                    <div>
                      <div class="fw-semibold text-dark"><?= e($emp['last_name'] . ', ' . $emp['first_name'] . ' ' . $emp['middle_name']) ?></div>
                      <div class="small text-muted"><?= e($emp['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td><span class="badge bg-light text-dark border"><?= e($emp['dept_code']) ?></span></td>
                <td><?= e($emp['position']) ?></td>
                <td><?= e($emp['contact_number']) ?></td>
                <td><span class="badge <?= badgeClass($emp['status']) ?>"><?= e($emp['status']) ?></span></td>
                <td class="text-end">
                  <div class="btn-group">
                    <a href="view.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline" title="View Profile"><i class="fas fa-eye"></i></a>
                    <?php if (in_array($_SESSION['role_slug'], ['admin', 'hr'])): ?>
                    <a href="edit.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                    <?php endif; ?>
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
        <div class="small text-muted">Showing <?= count($employees) ?> of <?= $total ?> employees</div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
              <li class="page-item <?= $i === $pag['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&dept=<?= urlencode($dept) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
