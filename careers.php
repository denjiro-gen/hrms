<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/mailer.php';
$db = getDB();

$error = "";
$success = "";

// Ensure upload directory exists
$uploadDir = __DIR__ . '/uploads/resumes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle Application Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
    $jobId      = (int)$_POST['job_id'];
    $firstName  = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName   = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone'] ?? '');
    $gender     = $_POST['gender'] ?? null;
    $dob        = $_POST['date_of_birth'] ?? null;
    $address    = trim($_POST['address'] ?? '');
    $education  = trim($_POST['highest_education'] ?? '');
    $experience = (float)($_POST['years_experience'] ?? 0);
    $skills     = trim($_POST['skills'] ?? '');
    $coverLetter= trim($_POST['cover_letter'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($_FILES['resume']['name'])) {
        $error = "Please fill in all required fields and upload your CV.";
    } else {
        $resumeFile = $_FILES['resume'];
        $ext = strtolower(pathinfo($resumeFile['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Please upload a PDF or Word document (.doc, .docx).";
        } elseif ($resumeFile['size'] > 5 * 1024 * 1024) {
            $error = "File is too large. Maximum size is 5MB.";
        } else {
            $filename     = uniqid('cv_') . '_' . time() . '.' . $ext;
            $destination  = $uploadDir . $filename;
            $relativePath = $filename;

            if (move_uploaded_file($resumeFile['tmp_name'], $destination)) {
                try {
                    $db->beginTransaction();

                    // 1. Insert into applicants table
                    $stmtApp = $db->prepare("INSERT INTO applicants 
                        (first_name, middle_name, last_name, email, contact_number, gender, date_of_birth, 
                         address, highest_education, years_experience, skills, resume_path, resume_original_name) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtApp->execute([
                        $firstName, $middleName ?: null, $lastName, $email, $phone ?: null,
                        $gender ?: null, $dob ?: null, $address ?: null, $education ?: null,
                        $experience, $skills ?: null, $relativePath, $resumeFile['name']
                    ]);
                    $applicantId = $db->lastInsertId();

                    // 2. Insert into applications table
                    $stmtJob = $db->prepare("INSERT INTO applications (job_posting_id, applicant_id, application_date, status, cover_letter) VALUES (?, ?, CURRENT_DATE, 'Applied', ?)");
                    $stmtJob->execute([$jobId, $applicantId, $coverLetter ?: null]);

                    $db->commit();
                    $success = "Your application has been submitted successfully! Our recruitment team will review it shortly.";

                    // ── Send confirmation email to applicant ─────────────
                    $jobTitle = '';
                    try {
                        $jobStmt = $db->prepare("SELECT position_title FROM job_postings WHERE id = ?");
                        $jobStmt->execute([$jobId]);
                        $jobTitle = $jobStmt->fetchColumn() ?: 'the position';
                    } catch (Exception $e) {}

                    $applicantHtml = "
                    <div style='font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;'>
                      <div style='background:linear-gradient(135deg,#0A2342,#1a56a0);padding:32px;border-radius:12px 12px 0 0;text-align:center;'>
                        <h1 style='color:#fff;font-size:24px;margin:0;'>Application Received!</h1>
                      </div>
                      <div style='background:#fff;padding:32px;border:1px solid #E5E7EB;border-top:none;border-radius:0 0 12px 12px;'>
                        <p style='color:#374151;font-size:15px;'>Dear <strong>{$firstName} {$lastName}</strong>,</p>
                        <p style='color:#374151;font-size:15px;margin-top:12px;'>Thank you for applying for the <strong>{$jobTitle}</strong> position at <strong>Bestlink College of the Philippines</strong>.</p>
                        <p style='color:#374151;font-size:15px;margin-top:12px;'>We have successfully received your application and resume. Our recruitment team will carefully review your qualifications and contact you if you are shortlisted.</p>
                        <div style='background:#F0F4FA;border-radius:8px;padding:16px;margin:20px 0;'>
                          <p style='color:#6B7280;font-size:13px;margin:0;'><strong>Position Applied:</strong> {$jobTitle}</p>
                          <p style='color:#6B7280;font-size:13px;margin:8px 0 0;'><strong>Application Date:</strong> " . date('F d, Y') . "</p>
                        </div>
                        <p style='color:#9CA3AF;font-size:13px;margin-top:24px;'>Best regards,<br><strong>Bestlink College HR Department</strong></p>
                      </div>
                    </div>";

                    sendMail(
                        $email,
                        "{$firstName} {$lastName}",
                        "Application Received — {$jobTitle} | Bestlink College",
                        $applicantHtml
                    );

                    // ── Notify HR team ──────────────────────────────────
                    $hrHtml = "
                    <div style='font-family:Inter,Arial,sans-serif;max-width:600px;margin:0 auto;'>
                      <div style='background:#1D4ED8;padding:24px;border-radius:12px 12px 0 0;'>
                        <h2 style='color:#fff;margin:0;font-size:18px;'>New Job Application Received</h2>
                      </div>
                      <div style='background:#fff;padding:24px;border:1px solid #E5E7EB;border-radius:0 0 12px 12px;'>
                        <p style='color:#374151;'><strong>Applicant:</strong> {$firstName} {$middleName} {$lastName}</p>
                        <p style='color:#374151;'><strong>Email:</strong> {$email}</p>
                        <p style='color:#374151;'><strong>Phone:</strong> {$phone}</p>
                        <p style='color:#374151;'><strong>Position:</strong> {$jobTitle}</p>
                        <p style='color:#374151;'><strong>Education:</strong> {$education}</p>
                        <p style='color:#374151;'><strong>Experience:</strong> {$experience} years</p>
                        <p style='color:#374151;'><strong>Skills:</strong> {$skills}</p>
                        <p style='color:#374151;margin-top:12px;'>Please log in to the HRMS to review this application.</p>
                      </div>
                    </div>";

                    sendMail(
                        MAIL_FROM,
                        'HR Department',
                        "New Application: {$firstName} {$lastName} — {$jobTitle}",
                        $hrHtml
                    );
                } catch (PDOException $e) {
                    $db->rollBack();
                    $error = "An error occurred while saving your application. Please try again. " . $e->getMessage();
                }
            } else {
                $error = "Failed to upload your CV. Please try again.";
            }
        }
    }
}

// Fetch open jobs
$jobs = $db->query("SELECT j.*, d.name as department_name FROM job_postings j LEFT JOIN departments d ON j.department_id = d.id WHERE j.status = 'Open' ORDER BY j.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Careers — Bestlink College of the Philippines</title>
  <meta name="description" content="Join the Bestlink College team. View open positions and submit your application online.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #F0F4FA; font-family: 'Inter', sans-serif; color: #111827; -webkit-font-smoothing: antialiased; }

    .careers-header {
      background: linear-gradient(135deg, #0A2342 0%, #1a56a0 100%);
      color: #fff; padding: 70px 20px 100px; text-align: center; position: relative; overflow: hidden;
    }
    .careers-header::before {
      content: ''; position: absolute; bottom: -40px; left: 50%; transform: translateX(-50%);
      width: 120%; height: 80px; background: #F0F4FA; border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    .logo-circle {
      width: 80px; height: 80px; background: rgba(255,255,255,0.15); border-radius: 50%;
      display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
      font-size: 32px; color: #fff; font-weight: 800; border: 2px solid rgba(255,255,255,0.3);
    }
    .careers-header h1 { font-size: 38px; font-weight: 800; letter-spacing: -1px; margin-bottom: 10px; }
    .careers-header p  { font-size: 16px; color: rgba(255,255,255,0.7); max-width: 500px; margin: 0 auto; }
    .header-nav { position: absolute; top: 20px; right: 24px; display: flex; gap: 10px; }
    .header-nav a {
      font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.85); text-decoration: none;
      padding: 8px 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); transition: background 0.2s;
    }
    .header-nav a:hover { background: rgba(255,255,255,0.15); }

    .careers-container { max-width: 860px; margin: 0 auto; padding: 40px 20px 60px; }
    .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6B7280; letter-spacing: 0.1em; margin-bottom: 16px; }

    .job-card {
      background: #fff; border: 1.5px solid #E5E7EB; border-radius: 14px; padding: 24px 28px;
      margin-bottom: 14px; transition: box-shadow 0.2s, border-color 0.2s;
    }
    .job-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.08); border-color: #C7D2FE; }
    .job-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
    .job-title { font-size: 18px; font-weight: 700; color: #111; margin-bottom: 8px; }
    .job-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .job-tag { font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; background: #EEF2FF; color: #4F46E5; }
    .job-tag.green  { background: #D1FAE5; color: #065F46; }
    .job-tag.blue   { background: #DBEAFE; color: #1D4ED8; }
    .job-tag.yellow { background: #FEF3C7; color: #92400E; }
    .job-desc { font-size: 13.5px; color: #6B7280; line-height: 1.6; }
    .btn-apply {
      flex-shrink: 0; padding: 10px 22px; background: #1D4ED8; color: #fff; border: none;
      border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer;
      font-family: 'Inter', sans-serif; transition: background 0.2s; white-space: nowrap;
    }
    .btn-apply:hover { background: #1e40af; }

    .alert { padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .alert-danger  { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
    .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }

    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1000; justify-content: center; align-items: flex-start; padding: 30px 16px; backdrop-filter: blur(4px); }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); animation: slideUp 0.25s ease; }
    @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-header { padding: 24px 28px 0; display: flex; justify-content: space-between; align-items: flex-start; }
    .modal-header h2 { font-size: 20px; font-weight: 700; color: #111; }
    .modal-header p  { font-size: 13px; color: #6B7280; margin-top: 4px; }
    .modal-close { background: #F3F4F6; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #6B7280; flex-shrink: 0; }
    .modal-close:hover { background: #E5E7EB; }
    .modal-body { padding: 20px 28px 28px; }

    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 11.5px; font-weight: 600; color: #374151; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em; }
    .form-group .req { color: #EF4444; margin-left: 2px; }
    .form-control { width: 100%; padding: 9px 13px; border: 1.5px solid #D1D5DB; border-radius: 8px; font-size: 13.5px; font-family: 'Inter', sans-serif; color: #111; background: #fff; outline: none; transition: border-color 0.18s, box-shadow 0.18s; }
    .form-control:focus { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    select.form-control { cursor: pointer; }
    textarea.form-control { resize: vertical; min-height: 80px; }

    .file-upload-area { border: 2px dashed #D1D5DB; border-radius: 10px; padding: 24px; text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s; position: relative; }
    .file-upload-area:hover { border-color: #3B82F6; background: #EFF6FF; }
    .file-upload-area input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .file-upload-icon { font-size: 28px; color: #9CA3AF; margin-bottom: 8px; }
    .file-upload-text { font-size: 13px; color: #6B7280; }
    .file-upload-text strong { color: #3B82F6; }

    .btn-submit { width: 100%; padding: 13px; background: #1D4ED8; color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif; transition: background 0.2s; margin-top: 6px; }
    .btn-submit:hover { background: #1e40af; }
    .form-divider { border: none; border-top: 1px solid #F3F4F6; margin: 18px 0; }

    .empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 14px; border: 1.5px solid #E5E7EB; }
    .empty-state i { font-size: 48px; color: #D1D5DB; margin-bottom: 16px; display: block; }
    .empty-state h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
    .empty-state p  { font-size: 14px; color: #9CA3AF; }

    @media (max-width: 600px) { .form-grid-2 { grid-template-columns: 1fr; } .careers-header h1 { font-size: 26px; } .job-header { flex-direction: column; } .btn-apply { width: 100%; } }
  </style>
</head>
<body>

  <div class="careers-header">
    <div class="header-nav">
      <!-- Buttons removed as requested -->
    </div>
    <div class="logo-circle" style="background:transparent; border:none;">
      <img src="https://bcp.edu.ph/logo.png" alt="BCP Logo" style="width:100%; height:100%; object-fit:contain;">
    </div>
    <h1>Join Our Team</h1>
    <p>Shape the future of education at Bestlink College of the Philippines</p>
  </div>

  <div class="careers-container">

    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
    <?php endif; ?>

    <div class="section-label"><i class="fas fa-briefcase me-1"></i> <?= count($jobs) ?> Open Position<?= count($jobs) !== 1 ? 's' : '' ?></div>

    <?php if (empty($jobs)): ?>
      <div class="empty-state">
        <i class="fas fa-briefcase"></i>
        <h3>No Open Positions</h3>
        <p>We are not currently hiring. Please check back later.</p>
      </div>
    <?php else: ?>
      <?php foreach ($jobs as $job): ?>
        <div class="job-card">
          <div class="job-header">
            <div style="flex:1;">
              <div class="job-title"><?= e($job['position_title']) ?></div>
              <div class="job-tags">
                <span class="job-tag blue"><i class="fas fa-building me-1"></i><?= e($job['department_name'] ?? 'General') ?></span>
                <span class="job-tag green"><i class="fas fa-clock me-1"></i><?= e($job['employment_type']) ?></span>
                <?php if ($job['salary_min'] && $job['salary_max']): ?>
                  <span class="job-tag yellow">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    &#8369;<?= number_format($job['salary_min']/1000,0) ?>k – &#8369;<?= number_format($job['salary_max']/1000,0) ?>k/mo
                  </span>
                <?php endif; ?>
                <?php if ($job['experience_required']): ?>
                  <span class="job-tag"><i class="fas fa-user-clock me-1"></i><?= e($job['experience_required']) ?></span>
                <?php endif; ?>
              </div>
              <div class="job-desc"><?= nl2br(e(substr($job['qualifications'] ?? 'See full job description.', 0, 220))) ?>...</div>
            </div>
            <button class="btn-apply" onclick="openModal(<?= $job['id'] ?>, '<?= addslashes(e($job['position_title'])) ?>', '<?= addslashes(e($job['department_name'] ?? '')) ?>')">
              <i class="fas fa-paper-plane me-1"></i> Apply Now
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>

  <!-- APPLICATION MODAL -->
  <div class="modal-overlay" id="applyModal">
    <div class="modal-box">
      <div class="modal-header">
        <div>
          <h2>Apply for: <span id="modalJobTitle" style="color:#1D4ED8;"></span></h2>
          <p id="modalJobDept"></p>
        </div>
        <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="apply">
          <input type="hidden" name="job_id" id="modalJobId">

          <div class="section-label" style="margin-top:4px;">Personal Information</div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>First Name <span class="req">*</span></label>
              <input type="text" name="first_name" class="form-control" required placeholder="Juan">
            </div>
            <div class="form-group">
              <label>Middle Name</label>
              <input type="text" name="middle_name" class="form-control" placeholder="(optional)">
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>Last Name <span class="req">*</span></label>
              <input type="text" name="last_name" class="form-control" required placeholder="Dela Cruz">
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender" class="form-control">
                <option value="">Select...</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>Email Address <span class="req">*</span></label>
              <input type="email" name="email" class="form-control" required placeholder="juan@email.com">
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" class="form-control" placeholder="09XX XXX XXXX">
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label>Date of Birth</label>
              <input type="date" name="date_of_birth" class="form-control">
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="address" class="form-control" placeholder="City, Province">
            </div>
          </div>

          <hr class="form-divider">
          <div class="section-label">Qualifications</div>

          <div class="form-grid-2">
            <div class="form-group">
              <label>Highest Education <span class="req">*</span></label>
              <select name="highest_education" class="form-control" required>
                <option value="">Select...</option>
                <option>High School Graduate</option>
                <option>Vocational</option>
                <option>BS Computer Science</option>
                <option>BS Information Technology</option>
                <option>BS Computer Engineering</option>
                <option>BS Accountancy</option>
                <option>BS Human Resource Management</option>
                <option>BS Psychology</option>
                <option>College Graduate (Other)</option>
                <option>Masteral</option>
                <option>Doctoral</option>
              </select>
            </div>
            <div class="form-group">
              <label>Years of Experience <span class="req">*</span></label>
              <input type="number" name="years_experience" class="form-control" required min="0" step="0.5" placeholder="e.g. 2.5">
            </div>
          </div>
          <div class="form-group">
            <label>Skills <span class="req">*</span></label>
            <input type="text" name="skills" class="form-control" required placeholder="e.g. PHP, MySQL, JavaScript, HTML, CSS">
            <div style="font-size:11.5px;color:#9CA3AF;margin-top:4px;">Separate with commas. This is used by our AI to match your profile to the job.</div>
          </div>
          <div class="form-group">
            <label>Cover Letter / Message</label>
            <textarea name="cover_letter" class="form-control" placeholder="Briefly describe why you are interested in this role..."></textarea>
          </div>

          <hr class="form-divider">
          <div class="section-label">Resume / CV</div>
          <div class="form-group">
            <div class="file-upload-area">
              <input type="file" name="resume" accept=".pdf,.doc,.docx" required id="resumeInput" onchange="updateFileName(this)">
              <div class="file-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
              <div class="file-upload-text" id="fileUploadText">
                <strong>Click to upload</strong> or drag and drop<br>
                PDF, DOC, DOCX — Max 5MB
              </div>
            </div>
          </div>

          <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane me-2"></i> Submit Application
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openModal(id, title, dept) {
      document.getElementById('modalJobId').value = id;
      document.getElementById('modalJobTitle').innerText = title;
      document.getElementById('modalJobDept').innerText = dept ? '📍 ' + dept : '';
      document.getElementById('applyModal').classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeModal() {
      document.getElementById('applyModal').classList.remove('open');
      document.body.style.overflow = '';
    }
    document.getElementById('applyModal').addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });
    function updateFileName(input) {
      const text = document.getElementById('fileUploadText');
      if (input.files && input.files[0]) {
        text.innerHTML = '<i class="fas fa-check-circle" style="color:#10B981;margin-right:6px;"></i><strong>' + input.files[0].name + '</strong>';
      }
    }
  </script>
</body>
</html>
