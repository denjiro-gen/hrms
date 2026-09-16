<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr', 'dept_head', 'school']);

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("SELECT e.*, d.name as department_name, d.code as dept_code 
                      FROM employees e 
                      LEFT JOIN departments d ON e.department_id = d.id 
                      WHERE e.id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    die("Employee not found.");
}

// Security: Dept Head can only view their own department's employees
if (isDeptHead() && $employee['department_id'] != $_SESSION['dept_id']) {
    http_response_code(403);
    die("Access denied. This employee belongs to a different department.");
}

$fullName = $employee['first_name'] . ' ' . $employee['middle_name'] . ' ' . $employee['last_name'] . ($employee['suffix'] ? ' '.$employee['suffix'] : '');

$pageTitle = 'Employee Profile - ' . $employee['employee_code'];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
      <h1 class="page-title mb-0">Employee Profile</h1>
    </div>
    <?php if (in_array($_SESSION['role_slug'], ['admin', 'hr'])): ?>
    <div>
      <a href="edit.php?id=<?= $id ?>" class="btn btn-outline text-primary"><i class="fas fa-edit me-1"></i> Edit Profile</a>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="row">
    <!-- Profile Card -->
    <div class="col-md-4 mb-4">
      <div class="card h-100 text-center">
        <div class="card-body py-5">
          <div class="avatar mx-auto mb-3" style="width:100px;height:100px;font-size:36px;background:var(--brand-primary);box-shadow:var(--shadow-card)">
            <?= e(substr($employee['first_name'],0,1).substr($employee['last_name'],0,1)) ?>
          </div>
          <h3 class="mb-1 fw-bold"><?= e($fullName) ?></h3>
          <div class="text-muted mb-2"><?= e($employee['position']) ?></div>
          <span class="badge <?= badgeClass($employee['status']) ?> mb-3 px-3 py-2"><?= e($employee['status']) ?></span>
          
          <hr class="mx-4 my-4 border-light">
          
          <div class="text-start px-4">
            <div class="mb-2"><i class="fas fa-id-badge text-muted me-2" style="width:20px"></i> <strong><?= e($employee['employee_code']) ?></strong></div>
            <div class="mb-2"><i class="fas fa-building text-muted me-2" style="width:20px"></i> <?= e($employee['department_name']) ?></div>
            <div class="mb-2"><i class="fas fa-envelope text-muted me-2" style="width:20px"></i> <a href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a></div>
            <div class="mb-2"><i class="fas fa-phone text-muted me-2" style="width:20px"></i> <?= e($employee['contact_number']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Information -->
    <div class="col-md-8 mb-4">
      <div class="card h-100">
        <div class="card-header border-bottom bg-white pb-0">
          <ul class="nav nav-tabs border-bottom-0" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal Data</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">Employment</button>
            </li>
            <?php if (in_array($_SESSION['role_slug'], ['admin', 'hr'])): ?>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab">Financial/Govt</button>
            </li>
            <?php endif; ?>
          </ul>
        </div>
        
        <div class="card-body p-4 tab-content">
          <!-- Personal Tab -->
          <div class="tab-pane fade show active" id="personal" role="tabpanel">
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Date of Birth</div>
              <div class="col-sm-8 fw-semibold"><?= e(date('F j, Y', strtotime($employee['date_of_birth']))) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Gender</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['gender']) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Civil Status</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['civil_status']) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Address</div>
              <div class="col-sm-8 fw-semibold"><?= nl2br(e($employee['address'])) ?></div>
            </div>
          </div>

          <!-- Employment Tab -->
          <div class="tab-pane fade" id="employment" role="tabpanel">
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Date Hired</div>
              <div class="col-sm-8 fw-semibold"><?= e(date('F j, Y', strtotime($employee['date_hired']))) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Employment Type</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['employment_type']) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Department</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['department_name']) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Position</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['position']) ?></div>
            </div>
          </div>

          <!-- Financial Tab (Restricted) -->
          <?php if (in_array($_SESSION['role_slug'], ['admin', 'hr'])): ?>
          <div class="tab-pane fade" id="financial" role="tabpanel">
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Basic Salary</div>
              <div class="col-sm-8 fw-semibold text-success"><?= peso($employee['basic_salary']) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Salary Grade</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['salary_grade']) ?: 'N/A' ?></div>
            </div>
            <hr>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">SSS Number</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['sss_number']) ?: 'N/A' ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">PhilHealth No.</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['philhealth_number']) ?: 'N/A' ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Pag-IBIG No.</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['pagibig_number']) ?: 'N/A' ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">TIN</div>
              <div class="col-sm-8 fw-semibold"><?= e($employee['tin_number']) ?: 'N/A' ?></div>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
