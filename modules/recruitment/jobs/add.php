<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireRole(['admin', 'hr']);

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $jobCode = generateCode('JOB', 'job_postings', 'job_code');

    try {
        $stmt = $db->prepare("INSERT INTO job_postings (
            job_code, position_title, department_id, employment_type, job_description, 
            responsibilities, qualifications, required_skills, preferred_skills, 
            min_education, experience_required, salary_min, salary_max, slots, 
            status, posted_by, date_posted, closing_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $jobCode,
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
            $_SESSION['user_id'],
            date('Y-m-d'),
            $_POST['closing_date'] ?? null
        ]);
        
        $newId = $db->lastInsertId();
        logAudit('Add Job Posting', 'Recruitment', (string)$newId, "Added new job posting: $jobCode");
        
        flash('success', 'Job posting created successfully!');
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}

$departments = $db->query("SELECT * FROM departments WHERE status='Active' ORDER BY name")->fetchAll();

$pageTitle = 'Post New Job';
include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/navbar.php';
include __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center mb-4 gap-3">
    <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
    <h1 class="page-title mb-0">Post New Job</h1>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="" class="card p-4">
    <?= csrfField() ?>
    
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label">Position Title *</label>
        <input type="text" name="position_title" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Department *</label>
        <select name="department_id" class="form-control" required>
          <option value="">-- Select Dept --</option>
          <?php foreach($departments as $d): ?>
            <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
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
        <label class="form-label">Vacant Slots *</label>
        <input type="number" name="slots" class="form-control" value="1" min="1" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Closing Date *</label>
        <input type="date" name="closing_date" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="Draft">Draft</option>
          <option value="Open">Open</option>
        </select>
      </div>
    </div>

    <h4 class="mb-3 text-primary border-bottom pb-2">Requirements & Details</h4>
    <div class="row g-3 mb-4">
      <div class="col-md-12">
        <label class="form-label">Job Description *</label>
        <textarea name="job_description" class="form-control" rows="3" required></textarea>
      </div>
      <div class="col-md-12">
        <label class="form-label">Responsibilities</label>
        <textarea name="responsibilities" class="form-control" rows="3"></textarea>
      </div>
      <div class="col-md-12">
        <label class="form-label">General Qualifications</label>
        <textarea name="qualifications" class="form-control" rows="3"></textarea>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label">Required Skills (Comma-separated) *</label>
        <input type="text" name="required_skills" class="form-control" placeholder="e.g. PHP, MySQL, Communication" required>
        <div class="small text-muted mt-1">These skills will be heavily weighed by the AI Recruiter.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Preferred Skills (Comma-separated)</label>
        <input type="text" name="preferred_skills" class="form-control" placeholder="e.g. Laravel, HRIS">
      </div>
      <div class="col-md-6">
        <label class="form-label">Minimum Education</label>
        <input type="text" name="min_education" class="form-control" placeholder="e.g. Bachelor's Degree in IT">
      </div>
      <div class="col-md-6">
        <label class="form-label">Experience Required</label>
        <input type="text" name="experience_required" class="form-control" placeholder="e.g. 2 years">
      </div>
      <div class="col-md-6">
        <label class="form-label">Salary Range (Minimum)</label>
        <input type="number" step="0.01" name="salary_min" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Salary Range (Maximum)</label>
        <input type="number" step="0.01" name="salary_max" class="form-control">
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-2">
      <a href="index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Job Posting</button>
    </div>
  </form>
</main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
