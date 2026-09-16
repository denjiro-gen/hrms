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

$emp = $db->prepare("SELECT * FROM employees WHERE id = ?");
$emp->execute([$id]);
$employee = $emp->fetch();

if (!$employee) {
    die("Employee not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        $stmt = $db->prepare("UPDATE employees SET 
            department_id = ?, position = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?,
            date_of_birth = ?, gender = ?, civil_status = ?, contact_number = ?, email = ?, address = ?,
            employment_type = ?, date_hired = ?, employment_status = ?, salary_grade = ?, basic_salary = ?,
            sss_number = ?, philhealth_number = ?, pagibig_number = ?, tin_number = ?, status = ?
            WHERE id = ?");
        
        $stmt->execute([
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
            $_POST['employment_type'] ?? 'Regular',
            $_POST['date_hired'] ?? null,
            $_POST['employment_status'] ?? 'Active',
            $_POST['salary_grade'] ?? '',
            (float)($_POST['basic_salary'] ?? 0),
            $_POST['sss_number'] ?? '',
            $_POST['philhealth_number'] ?? '',
            $_POST['pagibig_number'] ?? '',
            $_POST['tin_number'] ?? '',
            $_POST['status'] ?? 'Active',
            $id
        ]);
        
        logAudit('Update Employee', 'Employees', (string)$id, "Updated details for: {$employee['employee_code']}");
        flash('success', 'Employee updated successfully!');
        header("Location: view.php?id=$id");
        exit;
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

$departments = $db->query("SELECT * FROM departments WHERE status='Active' ORDER BY name")->fetchAll();

$pageTitle = 'Edit Employee';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
include __DIR__ . '/../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="view.php?id=<?= $id ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
    <h1 class="page-title mb-0">Edit Employee: <?= e($employee['employee_code']) ?></h1>
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
        <input type="text" name="first_name" class="form-control" value="<?= e($employee['first_name']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Middle Name</label>
        <input type="text" name="middle_name" class="form-control" value="<?= e($employee['middle_name']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Last Name *</label>
        <input type="text" name="last_name" class="form-control" value="<?= e($employee['last_name']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Suffix</label>
        <input type="text" name="suffix" class="form-control" value="<?= e($employee['suffix']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date of Birth *</label>
        <input type="date" name="date_of_birth" class="form-control" value="<?= e($employee['date_of_birth']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control">
          <option value="Male" <?= $employee['gender']==='Male'?'selected':'' ?>>Male</option>
          <option value="Female" <?= $employee['gender']==='Female'?'selected':'' ?>>Female</option>
          <option value="Other" <?= $employee['gender']==='Other'?'selected':'' ?>>Other</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Civil Status</label>
        <select name="civil_status" class="form-control">
          <option value="Single" <?= $employee['civil_status']==='Single'?'selected':'' ?>>Single</option>
          <option value="Married" <?= $employee['civil_status']==='Married'?'selected':'' ?>>Married</option>
          <option value="Widowed" <?= $employee['civil_status']==='Widowed'?'selected':'' ?>>Widowed</option>
          <option value="Divorced" <?= $employee['civil_status']==='Divorced'?'selected':'' ?>>Divorced</option>
        </select>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Contact Information</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control" value="<?= e($employee['email']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Contact Number *</label>
        <input type="text" name="contact_number" class="form-control" value="<?= e($employee['contact_number']) ?>" required>
      </div>
      <div class="col-md-12">
        <label class="form-label">Complete Address</label>
        <textarea name="address" class="form-control" rows="2"><?= e($employee['address']) ?></textarea>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Employment Details</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Department *</label>
        <select name="department_id" class="form-control" required>
          <?php foreach($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $employee['department_id']==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Position *</label>
        <input type="text" name="position" class="form-control" value="<?= e($employee['position']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Date Hired *</label>
        <input type="date" name="date_hired" class="form-control" value="<?= e($employee['date_hired']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Employment Type</label>
        <select name="employment_type" class="form-control">
          <option value="Regular" <?= $employee['employment_type']==='Regular'?'selected':'' ?>>Regular</option>
          <option value="Probationary" <?= $employee['employment_type']==='Probationary'?'selected':'' ?>>Probationary</option>
          <option value="Contractual" <?= $employee['employment_type']==='Contractual'?'selected':'' ?>>Contractual</option>
          <option value="Part-time" <?= $employee['employment_type']==='Part-time'?'selected':'' ?>>Part-time</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Salary Grade</label>
        <input type="text" name="salary_grade" class="form-control" value="<?= e($employee['salary_grade']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Basic Salary (₱) *</label>
        <input type="number" step="0.01" name="basic_salary" class="form-control" value="<?= e($employee['basic_salary']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">System Status</label>
        <select name="status" class="form-control fw-bold">
          <option value="Active" <?= $employee['status']==='Active'?'selected':'' ?>>Active</option>
          <option value="Inactive" <?= $employee['status']==='Inactive'?'selected':'' ?>>Inactive</option>
          <option value="Terminated" <?= $employee['status']==='Terminated'?'selected':'' ?>>Terminated</option>
          <option value="Resigned" <?= $employee['status']==='Resigned'?'selected':'' ?>>Resigned</option>
        </select>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Government Numbers</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">SSS Number</label>
        <input type="text" name="sss_number" class="form-control" value="<?= e($employee['sss_number']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">PhilHealth</label>
        <input type="text" name="philhealth_number" class="form-control" value="<?= e($employee['philhealth_number']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Pag-IBIG</label>
        <input type="text" name="pagibig_number" class="form-control" value="<?= e($employee['pagibig_number']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">TIN</label>
        <input type="text" name="tin_number" class="form-control" value="<?= e($employee['tin_number']) ?>">
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-2">
      <a href="view.php?id=<?= $id ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update Employee</button>
    </div>
  </form>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
