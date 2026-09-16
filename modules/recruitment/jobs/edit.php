<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireRole(['admin', 'hr']);

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM job_postings WHERE id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job posting not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        $stmt = $db->prepare("UPDATE job_postings SET 
            position_title = ?, department_id = ?, employment_type = ?, job_description = ?, 
            responsibilities = ?, qualifications = ?, required_skills = ?, preferred_skills = ?, 
            min_education = ?, experience_required = ?, salary_min = ?, salary_max = ?, slots = ?, 
            status = ?, closing_date = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['position_title'] ?? '',
            $_POST['department_id'] ?: null,
            $_POST['employment_type'] ?? 'Regular',
            $_POST['job_description'] ?? '',
            $_POST['responsibilities'] ?? '',
            $_POST['qualifications'] ?? '',
            $_POST['required_skills'] ?? '',
            $_POST['preferred_skills'] ?? '',
            $_POST['min_education'] ?? '',
            $_POST['experience_required'] ?? '',
            $_POST['salary_min'] ?: null,
            $_POST['salary_max'] ?: null,
            $_POST['slots'] ?: 1,
            $_POST['status'] ?? 'Draft',
            $_POST['closing_date'] ?? null,
            $id
        ]);
        
        logAudit('Update Job Posting', 'Recruitment', (string)$id, "Updated job posting: {$job['job_code']}");
        flash('success', 'Job posting updated successfully!');
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

$departments = $db->query("SELECT * FROM departments WHERE status='Active' ORDER BY name")->fetchAll();

$pageTitle = 'Edit Job Posting';
include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/navbar.php';
include __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
    <h1 class="page-title mb-0">Edit Job: <?= e($job['job_code']) ?></h1>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" class="card p-4">
    <?= csrfField() ?>
    
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label">Position Title *</label>
        <input type="text" name="position_title" class="form-control" value="<?= e($job['position_title']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Department *</label>
        <select name="department_id" class="form-control" required>
          <option value="">-- Select Dept --</option>
          <?php foreach($departments as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $job['department_id']==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Employment Type</label>
        <select name="employment_type" class="form-control">
          <option value="Regular" <?= $job['employment_type']==='Regular'?'selected':'' ?>>Regular</option>
          <option value="Probationary" <?= $job['employment_type']==='Probationary'?'selected':'' ?>>Probationary</option>
          <option value="Contractual" <?= $job['employment_type']==='Contractual'?'selected':'' ?>>Contractual</option>
          <option value="Part-time" <?= $job['employment_type']==='Part-time'?'selected':'' ?>>Part-time</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Vacant Slots *</label>
        <input type="number" name="slots" class="form-control" value="<?= e($job['slots']) ?>" min="1" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Closing Date *</label>
        <input type="date" name="closing_date" class="form-control" value="<?= e($job['closing_date']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control fw-bold">
          <option value="Draft" <?= $job['status']==='Draft'?'selected':'' ?>>Draft</option>
          <option value="Open" <?= $job['status']==='Open'?'selected':'' ?>>Open</option>
          <option value="Closed" <?= $job['status']==='Closed'?'selected':'' ?>>Closed</option>
          <option value="Cancelled" <?= $job['status']==='Cancelled'?'selected':'' ?>>Cancelled</option>
        </select>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Requirements & Details</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-12">
        <label class="form-label">Job Description *</label>
        <textarea name="job_description" class="form-control" rows="3" required><?= e($job['job_description']) ?></textarea>
      </div>
      <div class="col-md-12">
        <label class="form-label">Responsibilities</label>
        <textarea name="responsibilities" class="form-control" rows="3"><?= e($job['responsibilities']) ?></textarea>
      </div>
      <div class="col-md-12">
        <label class="form-label">General Qualifications</label>
        <textarea name="qualifications" class="form-control" rows="3"><?= e($job['qualifications']) ?></textarea>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label">Required Skills (Comma-separated) *</label>
        <input type="text" name="required_skills" class="form-control" value="<?= e($job['required_skills']) ?>" required>
        <div class="small text-muted mt-1">These skills will be heavily weighed by the AI Recruiter.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Preferred Skills (Comma-separated)</label>
        <input type="text" name="preferred_skills" class="form-control" value="<?= e($job['preferred_skills']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Minimum Education</label>
        <input type="text" name="min_education" class="form-control" value="<?= e($job['min_education']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Experience Required</label>
        <input type="text" name="experience_required" class="form-control" value="<?= e($job['experience_required']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Salary Range (Minimum)</label>
        <input type="number" step="0.01" name="salary_min" class="form-control" value="<?= e($job['salary_min']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Salary Range (Maximum)</label>
        <input type="number" step="0.01" name="salary_max" class="form-control" value="<?= e($job['salary_max']) ?>">
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-2">
      <a href="index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Update Job Posting</button>
    </div>
  </form>
</main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
