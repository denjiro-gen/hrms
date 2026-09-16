<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
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
    $jobId = (int)$_POST['job_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
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
            $filename = uniqid('cv_') . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;
            $relativePath = 'uploads/resumes/' . $filename;
            
            if (move_uploaded_file($resumeFile['tmp_name'], $destination)) {
                try {
                    $stmt = $db->prepare("INSERT INTO applicants (job_posting_id, first_name, last_name, email, contact_number, resume_path, resume_original_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'New')");
                    $stmt->execute([$jobId, $firstName, $lastName, $email, $phone, $relativePath, $resumeFile['name']]);
                    $success = "Your application and CV have been submitted successfully! Our recruitment team will review it shortly.";
                } catch (PDOException $e) {
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
  <title>Careers - Bestlink College of the Philippines</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/hrms.css">
  <style>
    body { background-color: #F5F6FA; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
    .careers-header { background: #0A192F; color: #fff; padding: 60px 20px; text-align: center; }
    .careers-header img { height: 80px; margin-bottom: 20px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); }
    .careers-header h1 { font-size: 36px; font-weight: 800; margin: 0 0 10px; letter-spacing: -1px; }
    .careers-header p { font-size: 16px; color: #9CA3AF; margin: 0; }
    .careers-container { max-width: 900px; margin: -30px auto 40px; padding: 0 20px; }
    
    .job-card { background: #fff; border: 1.5px solid #E8ECF0; border-radius: 12px; padding: 24px; margin-bottom: 16px; transition: box-shadow 0.2s; display: flex; flex-direction: column; gap: 16px; }
    .job-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .job-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .job-title { font-size: 18px; font-weight: 700; color: #111; margin: 0 0 4px; }
    .job-dept { font-size: 13px; font-weight: 600; color: #2F7BEE; background: #2F7BEE18; padding: 4px 10px; border-radius: 6px; display: inline-block; }
    .job-desc { font-size: 14px; color: #6B7280; line-height: 1.5; margin: 0; white-space: pre-wrap; }
    
    /* Simple Modal */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 20px; }
    .modal-content { background: #fff; border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    .modal-close { float: right; background: none; border: none; font-size: 20px; cursor: pointer; color: #9CA3AF; }
  </style>
</head>
<body>

  <div class="careers-header">
    <img src="https://bcp.edu.ph/logo.png" alt="Bestlink College Logo" onerror="this.style.display='none'">
    <h1>Join Our Team</h1>
    <p>Discover opportunities to shape the future of education at Bestlink College.</p>
  </div>

  <div class="careers-container">
    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom:20px;"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success" style="margin-bottom:20px;"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (empty($jobs)): ?>
      <div class="job-card" style="text-align: center; padding: 40px;">
        <i class="fas fa-briefcase" style="font-size: 40px; color: #D1D5DB; margin-bottom: 16px;"></i>
        <h3 style="margin:0 0 8px; color:#111;">No open positions</h3>
        <p style="margin:0; color:#6B7280;">We are not currently hiring. Please check back later.</p>
      </div>
    <?php else: ?>
      <?php foreach ($jobs as $job): ?>
        <div class="job-card">
          <div class="job-header">
            <div>
              <h2 class="job-title"><?= e($job['position_title']) ?></h2>
              <div class="job-dept"><?= e($job['department_name'] ?? 'General') ?></div>
            </div>
            <button class="btn btn-primary" onclick="openApplyModal(<?= $job['id'] ?>, '<?= htmlspecialchars(e($job['position_title']), ENT_QUOTES) ?>')">Apply Now</button>
          </div>
          <div class="job-desc"><strong>Qualifications:</strong><br><?= e($job['qualifications']) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    
    <div style="text-align:center; margin-top:30px;">
      <a href="login.php" style="color:#6B7280; font-size:13px; text-decoration:none;">&larr; Back to Employee Login</a>
    </div>
  </div>

  <!-- Apply Modal -->
  <div class="modal-overlay" id="applyModal">
    <div class="modal-content">
      <button class="modal-close" onclick="closeApplyModal()"><i class="fas fa-times"></i></button>
      <h2 style="margin:0 0 20px; font-size:22px; color:#111;">Apply for: <span id="modalJobTitle" style="color:#2F7BEE;"></span></h2>
      
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="apply">
        <input type="hidden" name="job_id" id="modalJobId" value="">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
          <div><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" required></div>
          <div><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
          <div><label class="form-label">Email Address *</label><input type="email" name="email" class="form-control" required></div>
          <div><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control"></div>
        </div>
        
        <div style="margin-bottom:20px;">
          <label class="form-label">Upload CV / Resume *</label>
          <p style="font-size:11.5px; color:#9CA3AF; margin:0 0 8px;">Please upload your CV in PDF or Word format (Max 5MB).</p>
          <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100" style="padding:12px;">Submit Application</button>
      </form>
    </div>
  </div>

  <script>
    function openApplyModal(id, title) {
      document.getElementById('modalJobId').value = id;
      document.getElementById('modalJobTitle').innerText = title;
      document.getElementById('applyModal').style.display = 'flex';
    }
    function closeApplyModal() {
      document.getElementById('applyModal').style.display = 'none';
    }
  </script>
</body>
</html>
