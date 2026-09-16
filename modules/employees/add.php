<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    // Auto-generate employee code if empty
    $empCode = trim($_POST['employee_code'] ?? '');
    if (empty($empCode)) {
        $empCode = generateCode('BCP', 'employees', 'employee_code');
    }

    try {
        $stmt = $db->prepare("INSERT INTO employees (
            employee_code, department_id, position, first_name, middle_name, last_name, suffix,
            date_of_birth, gender, civil_status, contact_number, email, address,
            employment_type, date_hired, employment_status, salary_grade, basic_salary,
            sss_number, philhealth_number, pagibig_number, tin_number, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
        
        $stmt->execute([
            $empCode,
            $_POST['department_id'] ?: null,
            $_POST['position'] ?? '',
            $_POST['first_name'] ?? '',
            $_POST['middle_name'] ?? '',
            $_POST['last_name'] ?? '',
            $_POST['suffix'] ?? null,
            $_POST['date_of_birth'] ?? null,
            $_POST['gender'] ?? 'Male',
            $_POST['civil_status'] ?? 'Single',
            $_POST['contact_number'] ?? '',
            $_POST['email'] ?? '',
            $_POST['address'] ?? '',
            $_POST['employment_type'] ?? 'Probationary',
            $_POST['date_hired'] ?? null,
            $_POST['employment_status'] ?? 'Active',
            $_POST['salary_grade'] ?? '',
            (float)($_POST['basic_salary'] ?? 0),
            $_POST['sss_number'] ?? '',
            $_POST['philhealth_number'] ?? '',
            $_POST['pagibig_number'] ?? '',
            $_POST['tin_number'] ?? ''
        ]);
        
        $newId = $db->lastInsertId();
        logAudit('Add Employee', 'Employees', (string)$newId, "Added new employee: $empCode");
        
        flash('success', 'Employee added successfully!');
        header("Location: view.php?id=$newId");
        exit;
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

$departments = $db->query("SELECT * FROM departments WHERE status='Active' ORDER BY name")->fetchAll();

$pageTitle = 'Add Employee';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
    <h1 class="page-title mb-0">Add New Employee</h1>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" class="card p-4">
    <?= csrfField() ?>
    
    <h4 class="mb-3 text-primary border-bottom pb-2">Personal Information</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">First Name *</label>
        <input type="text" name="first_name" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Middle Name</label>
        <input type="text" name="middle_name" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Last Name *</label>
        <input type="text" name="last_name" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Suffix (e.g. Jr, Sr)</label>
        <input type="text" name="suffix" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date of Birth *</label>
        <input type="date" name="date_of_birth" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control">
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Civil Status</label>
        <select name="civil_status" class="form-control">
          <option value="Single">Single</option>
          <option value="Married">Married</option>
          <option value="Widowed">Widowed</option>
          <option value="Divorced">Divorced</option>
        </select>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Contact Information</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Contact Number *</label>
        <input type="text" name="contact_number" class="form-control" required>
      </div>
      <div class="col-md-12">
        <label class="form-label">Complete Address</label>
        <textarea name="address" class="form-control" rows="2"></textarea>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Employment Details</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Employee Code</label>
        <input type="text" name="employee_code" class="form-control" placeholder="Leave empty to auto-generate">
      </div>
      <div class="col-md-3">
        <label class="form-label">Department *</label>
        <select name="department_id" class="form-control" required>
          <option value="">-- Select Dept --</option>
          <?php foreach($departments as $d): ?>
            <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Position *</label>
        <input type="text" name="position" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Date Hired *</label>
        <input type="date" name="date_hired" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Employment Type</label>
        <select name="employment_type" class="form-control">
          <option value="Regular">Regular</option>
          <option value="Probationary">Probationary</option>
          <option value="Contractual">Contractual</option>
          <option value="Part-time">Part-time</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Salary Grade</label>
        <input type="text" name="salary_grade" class="form-control" placeholder="e.g. SG-12">
      </div>
      <div class="col-md-3">
        <label class="form-label">Basic Salary (₱) *</label>
        <input type="number" step="0.01" name="basic_salary" class="form-control" required>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Government Numbers</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">SSS Number</label>
        <input type="text" name="sss_number" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">PhilHealth Number</label>
        <input type="text" name="philhealth_number" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Pag-IBIG Number</label>
        <input type="text" name="pagibig_number" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">TIN</label>
        <input type="text" name="tin_number" class="form-control">
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-2">
      <a href="index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Employee</button>
    </div>
  </form>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
