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

$stmt = $db->prepare("SELECT a.*, app.*, j.position_title, j.job_code, j.department_id, 
                      j.required_skills as job_req_skills, j.preferred_skills as job_pref_skills,
                      d.name as department_name, r.match_score, r.matched_skills, r.missing_skills, r.recommendation
                      FROM applications a 
                      JOIN applicants app ON a.applicant_id = app.id 
                      JOIN job_postings j ON a.job_posting_id = j.id
                      LEFT JOIN departments d ON j.department_id = d.id
                      LEFT JOIN recruitment_ai_results r ON a.id = r.application_id
                      WHERE a.id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch();

if (!$application) {
    die("Application not found.");
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    verifyCsrf();
    $newStatus = $_POST['status'] ?? '';
    
    if (in_array($newStatus, ['Applied','Screening','Interview','Shortlisted','Hired','Rejected'])) {
        $db->prepare("UPDATE applications SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        logAudit('Update Applicant Status', 'Recruitment', (string)$id, "Status changed to $newStatus for application $id");
        flash('success', 'Applicant status updated to ' . $newStatus);
        header("Location: view.php?id=$id");
        exit;
    }
}

$fullName = $application['first_name'] . ' ' . $application['middle_name'] . ' ' . $application['last_name'];

$pageTitle = 'Applicant Profile - ' . $fullName;
include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/navbar.php';
include __DIR__ . '/../../../includes/sidebar.php';
?>

<main class="main-content hrms-main">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
      <h1 class="page-title mb-0">Applicant Review</h1>
    </div>
    
    <!-- Status Update Form -->
    <form method="POST" action="" class="d-flex align-items-center gap-2">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="update_status">
      <select name="status" class="form-control" style="width:160px">
        <?php foreach(['Applied','Screening','Interview','Shortlisted','Hired','Rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $application['status']===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
  </div>

  <?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= e($msg) ?></div>
  <?php endif; ?>

  <div class="row">
    <!-- Left Column: Applicant Details -->
    <div class="col-md-7 mb-4">
      <div class="card h-100">
        <div class="card-header border-bottom">
          <h3 class="mb-0">Applicant Profile</h3>
        </div>
        <div class="card-body p-4">
          <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
            <div class="avatar shadow-sm" style="width:80px;height:80px;font-size:30px;background:var(--brand-primary)">
              <?= e(substr($application['first_name'],0,1).substr($application['last_name'],0,1)) ?>
            </div>
            <div>
              <h2 class="mb-1 fw-bold"><?= e($fullName) ?></h2>
              <div class="text-muted"><i class="fas fa-envelope me-2"></i> <a href="mailto:<?= e($application['email']) ?>"><?= e($application['email']) ?></a></div>
              <div class="text-muted"><i class="fas fa-phone me-2"></i> <?= e($application['contact_number']) ?></div>
            </div>
          </div>
          
          <h5 class="text-primary mb-3">Background Information</h5>
          <div class="row mb-3">
            <div class="col-sm-4 text-muted">Gender</div>
            <div class="col-sm-8 fw-semibold"><?= e($application['gender']) ?></div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4 text-muted">Date of Birth</div>
            <div class="col-sm-8 fw-semibold"><?= e(date('F j, Y', strtotime($application['date_of_birth']))) ?></div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4 text-muted">Address</div>
            <div class="col-sm-8 fw-semibold"><?= nl2br(e($application['address'])) ?></div>
          </div>
          
          <hr>
          <h5 class="text-primary mb-3">Qualifications</h5>
          <div class="row mb-3">
            <div class="col-sm-4 text-muted">Highest Education</div>
            <div class="col-sm-8 fw-semibold"><?= e($application['highest_education']) ?></div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4 text-muted">Years of Experience</div>
            <div class="col-sm-8 fw-semibold"><?= e($application['years_experience']) ?> Years</div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-4 text-muted">Applicant Skills</div>
            <div class="col-sm-8">
              <?php foreach(explode(',', $application['skills']) as $skill): ?>
                <span class="badge bg-light text-dark border me-1 mb-1"><?= e(trim($skill)) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          
          <?php if (!empty($application['resume_path'])): ?>
          <div class="mt-4">
            <a href="<?= BASE_URL ?>/uploads/resumes/<?= e($application['resume_path']) ?>" target="_blank" class="btn btn-outline w-100">
              <i class="fas fa-file-pdf text-danger me-2"></i> View Resume / CV
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right Column: Job & AI Review -->
    <div class="col-md-5 mb-4">
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h3 class="mb-0">Application Info</h3>
        </div>
        <div class="card-body p-4">
          <div class="mb-3">
            <div class="text-muted small">Applied For</div>
            <div class="fw-bold text-primary fs-5"><?= e($application['position_title']) ?></div>
            <div class="text-muted small"><?= e($application['job_code']) ?> &bull; <?= e($application['department_name']) ?></div>
          </div>
          <div class="mb-3">
            <div class="text-muted small">Date Applied</div>
            <div class="fw-semibold"><?= e(date('F j, Y', strtotime($application['application_date']))) ?></div>
          </div>
          <div>
            <div class="text-muted small mb-1">Current Status</div>
            <span class="badge fs-6 px-3 py-2 <?= badgeClass($application['status']) ?>"><?= e($application['status']) ?></span>
          </div>
        </div>
      </div>

      <!-- AI Recruiter Panel -->
      <div class="card" style="border-top: 4px solid var(--brand-accent)">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="avatar" style="width:28px;height:28px;background:var(--brand-accent);color:#fff"><i class="fas fa-robot"></i></div>
          <h3 class="mb-0">BERT AI Evaluation</h3>
        </div>
        <div class="card-body p-4">
          <?php if (isset($application['match_score'])): ?>
            <div class="text-center mb-4">
              <div class="text-muted small text-uppercase mb-2">Overall Match Score</div>
              <?php $color = $application['match_score'] >= 85 ? 'success' : ($application['match_score'] >= 70 ? 'warning' : 'danger'); ?>
              <h1 class="display-4 fw-bold text-<?= $color ?> mb-0"><?= number_format($application['match_score'], 1) ?>%</h1>
              <div class="badge bg-<?= $color ?> mt-2 px-3 py-1"><?= e($application['recommendation']) ?></div>
            </div>
            
            <div class="mb-3">
              <div class="text-success small fw-bold mb-2"><i class="fas fa-check-circle me-1"></i> Matched Skills</div>
              <div class="d-flex flex-wrap gap-1">
                <?php 
                $matched = json_decode($application['matched_skills'], true) ?? [];
                foreach($matched as $skill): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle"><?= e($skill) ?></span>
                <?php endforeach; ?>
                <?php if(empty($matched)) echo '<span class="text-muted small">None identified</span>'; ?>
              </div>
            </div>
            
            <div>
              <div class="text-danger small fw-bold mb-2"><i class="fas fa-times-circle me-1"></i> Missing Required Skills</div>
              <div class="d-flex flex-wrap gap-1">
                <?php 
                $missing = json_decode($application['missing_skills'], true) ?? [];
                foreach($missing as $skill): ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= e($skill) ?></span>
                <?php endforeach; ?>
                <?php if(empty($missing)) echo '<span class="text-muted small">Applicant possesses all required skills.</span>'; ?>
              </div>
            </div>
            
            <div class="mt-4 pt-3 border-top text-center">
              <form action="../ai_match.php" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="application_id" value="<?= $id ?>">
                <button type="submit" class="btn btn-sm btn-outline text-muted w-100">
                  <i class="fas fa-sync-alt me-1"></i> Re-run AI Evaluation
                </button>
              </form>
            </div>
            
          <?php else: ?>
            <div class="text-center py-4">
              <div class="mb-3 text-muted"><i class="fas fa-brain fa-3x"></i></div>
              <h6>Not Evaluated Yet</h6>
              <p class="text-muted small mb-4">Run the AI recruiter to analyze the applicant's qualifications against the job requirements.</p>
              <form action="../ai_match.php" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="application_id" value="<?= $id ?>">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-magic me-2"></i> Run AI Match Analysis
                </button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
