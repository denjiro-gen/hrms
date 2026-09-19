<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin(); // All logged-in users can access their profile

$db        = getDB();
$userId    = $_SESSION['user_id'];
$userEmail = $_SESSION['email'] ?? '';
$role      = $_SESSION['role_slug'] ?? '';

// Load employee record
$emp = false;
if ($userEmail) {
    $stmt = $db->prepare("SELECT e.*, d.name as dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.email = ? LIMIT 1");
    $stmt->execute([$userEmail]);
    $emp = $stmt->fetch();
}

if (!$emp) {
    // No employee record — show a plain message
    $pageTitle = 'My Profile';
    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/navbar.php';
    include __DIR__ . '/includes/sidebar.php';
    echo '<main class="main-content hrms-main"><div class="alert alert-warning" style="max-width:500px;margin:40px auto;"><i class="fas fa-exclamation-triangle me-2"></i>No employee profile found for your account. Please contact HR.</div></main>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$empId = $emp['id'];

// Handle Profile Photo Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['profile_photo']['tmp_name'];
        $name    = basename($_FILES['profile_photo']['name']);
        $ext     = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $uploadDir = __DIR__ . '/uploads/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $newName = 'emp_' . $empId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                $db->prepare("UPDATE employees SET profile_photo = ? WHERE id = ?")->execute([$newName, $empId]);
                flash('success', 'Profile photo updated successfully!');
                header("Location: employee_profile.php");
                exit;
            } else {
                flash('error', 'Failed to upload photo.');
            }
        } else {
            flash('error', 'Invalid file format. Please upload an image.');
        }
    }
}

// Handle Profile Update Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    verifyCsrf();
    
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['date_of_birth'] ?? '';
    $civil = $_POST['civil_status'] ?? '';
    $nat = $_POST['nationality'] ?? '';
    $rel = $_POST['religion'] ?? '';
    $contact = $_POST['contact_number'] ?? '';
    $address = $_POST['address'] ?? '';

    // Update employees table
    $upd = $db->prepare("UPDATE employees SET gender = ?, date_of_birth = ?, civil_status = ?, nationality = ?, religion = ?, contact_number = ?, address = ? WHERE id = ?");
    if ($upd->execute([$gender, $dob, $civil, $nat, $rel, $contact, $address, $empId])) {
        
        // Notify HR and Admins
        $admins = $db->query("SELECT id FROM users WHERE role_id IN (SELECT id FROM roles WHERE slug IN ('admin', 'hr'))")->fetchAll(PDO::FETCH_COLUMN);
        
        $insertNotif = $db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $notifMsg = e($emp['first_name'] . ' ' . $emp['last_name']) . " updated their personal profile details.";
        $notifLink = BASE_URL . '/modules/employees/view.php?id=' . $empId;
        
        foreach ($admins as $adminId) {
            $insertNotif->execute([$adminId, 'Profile Update', $notifMsg, 'info', $notifLink]);
        }

        flash('success', 'Profile updated successfully! HR has been notified.');
        header("Location: employee_profile.php");
        exit;
    } else {
        flash('error', 'Failed to update profile.');
    }
}

// Load education records
$education = $db->prepare("SELECT * FROM employee_education WHERE employee_id = ? ORDER BY year_to DESC");
$education->execute([$empId]);
$education = $education->fetchAll();

// Load certifications
$certs = $db->prepare("SELECT * FROM employee_certifications WHERE employee_id = ? ORDER BY issue_date DESC");
$certs->execute([$empId]);
$certs = $certs->fetchAll();

// Load emergency contacts
$contacts = $db->prepare("SELECT * FROM employee_emergency_contacts WHERE employee_id = ?");
$contacts->execute([$empId]);
$contacts = $contacts->fetchAll();

$fullName = trim($emp['first_name'] . ' ' . ($emp['middle_name'] ? $emp['middle_name'] . ' ' : '') . $emp['last_name']);
$initials = strtoupper(substr($emp['first_name'],0,1).substr($emp['last_name'],0,1));

// Years of service
$yearsOfService = 'N/A';
if (!empty($emp['date_hired'])) {
    $diff = (new DateTime())->diff(new DateTime($emp['date_hired']));
    $yearsOfService = ($diff->y > 0 ? $diff->y . ' yr' . ($diff->y > 1 ? 's' : '') . ' ' : '') . $diff->m . ' mo';
}

$pageTitle = 'My Profile';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content hrms-main">

  <!-- Header -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <?php if ($role === 'employee'): ?>
    <a href="<?= BASE_URL ?>/portal.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i></a>
    <?php endif; ?>
    <h1 class="page-title mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
  </div>

  <div class="row g-4">

    <!-- ── Left: Profile Card ─────────────────────── -->
    <div class="col-lg-4">
      <div class="card mb-4">
        <div style="background:linear-gradient(135deg,#2F7BEE,#7C3AED);padding:28px;text-align:center;border-radius:14px 14px 0 0;position:relative;">
          
          <form method="POST" enctype="multipart/form-data" id="photoForm" style="display:inline-block; position:relative; cursor:pointer;" onclick="document.getElementById('photoInput').click()">
            <?php if (!empty($emp['profile_photo']) && file_exists(__DIR__.'/uploads/profiles/'.$emp['profile_photo'])): ?>
              <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($emp['profile_photo']) ?>" alt="Profile" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.5);margin-bottom:14px;">
            <?php else: ?>
              <div style="width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.5);display:inline-flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;color:#fff;margin-bottom:14px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                <?= e($initials) ?>
              </div>
            <?php endif; ?>
            
            <?php if ($role === 'employee'): ?>
            <div style="position:absolute;bottom:14px;right:0;background:#111;color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;box-shadow:0 2px 5px rgba(0,0,0,0.3);border:2px solid #fff;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="Change Photo">
              <i class="fas fa-camera"></i>
            </div>
            <input type="file" id="photoInput" name="profile_photo" accept="image/*" style="display:none;" onchange="document.getElementById('photoForm').submit()">
            <?php endif; ?>
          </form>

          <h2 style="margin:0 0 4px;font-size:18px;font-weight:800;color:#fff;"><?= e($fullName) ?></h2>
          <div style="font-size:13px;color:rgba(255,255,255,0.8);margin-bottom:8px;"><?= e($emp['position'] ?? 'N/A') ?></div>
          <span class="badge badge-<?= $emp['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= e($emp['status'] ?? 'Active') ?></span>
        </div>
        <div class="card-body">
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
              <i class="fas fa-id-badge" style="color:#2F7BEE;width:18px;text-align:center;"></i>
              <div><span style="color:#9CA3AF;display:block;font-size:11px;">Employee ID</span><strong><?= e($emp['employee_code'] ?? 'N/A') ?></strong></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
              <i class="fas fa-building" style="color:#7C3AED;width:18px;text-align:center;"></i>
              <div><span style="color:#9CA3AF;display:block;font-size:11px;">Department</span><strong><?= e($emp['dept_name'] ?? 'N/A') ?></strong></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
              <i class="fas fa-calendar-check" style="color:#10B981;width:18px;text-align:center;"></i>
              <div><span style="color:#9CA3AF;display:block;font-size:11px;">Date Hired</span><strong><?= $emp['date_hired'] ? e(date('F d, Y', strtotime($emp['date_hired']))) : 'N/A' ?></strong></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
              <i class="fas fa-award" style="color:#F59E0B;width:18px;text-align:center;"></i>
              <div><span style="color:#9CA3AF;display:block;font-size:11px;">Service Duration</span><strong><?= $yearsOfService ?></strong></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-address-card me-2 text-primary"></i>Contact Information</span></div>
        <div class="card-body">
          <div style="display:flex;flex-direction:column;gap:12px;font-size:13px;">
            <div>
              <div style="font-size:11px;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">Email</div>
              <div><?= e($emp['email'] ?? 'N/A') ?></div>
            </div>
            <div>
              <div style="font-size:11px;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">Phone</div>
              <div><?= e($emp['contact_number'] ?? 'N/A') ?></div>
            </div>
            <div>
              <div style="font-size:11px;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">Address</div>
              <div style="line-height:1.5;"><?= nl2br(e($emp['address'] ?? 'N/A')) ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Right: Details ─────────────────────────── -->
    <div class="col-lg-8">

      <!-- Personal Info -->
      <div class="card mb-4">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
          <span class="card-title"><i class="fas fa-user me-2 text-primary"></i>Personal Information</span>
          <button type="button" class="btn btn-sm btn-outline" style="border-color:#E5E7EB; color:#374151; font-weight:600;" onclick="document.getElementById('editProfileModal').classList.add('open'); document.body.style.overflow='hidden';">
            <i class="fas fa-edit me-1"></i> Edit Profile
          </button>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <?php
            $fields = [
              ['Full Name',    $fullName],
              ['Gender',       $emp['gender'] ?? 'N/A'],
              ['Date of Birth',$emp['date_of_birth'] ? date('F d, Y', strtotime($emp['date_of_birth'])) : 'N/A'],
              ['Civil Status', $emp['civil_status'] ?? 'N/A'],
              ['Nationality',  $emp['nationality'] ?? 'N/A'],
              ['Religion',     $emp['religion'] ?? 'N/A'],
            ];
            foreach ($fields as [$label, $val]):
            ?>
            <div class="col-md-6">
              <div style="font-size:11px;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;"><?= $label ?></div>
              <div style="font-size:14px;font-weight:600;color:#111;"><?= e($val) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Education -->
      <?php if (!empty($education)): ?>
      <div class="card mb-4">
        <div class="card-header"><span class="card-title"><i class="fas fa-graduation-cap me-2" style="color:#7C3AED;"></i>Education</span></div>
        <table class="data-table">
          <thead><tr><th>Institution</th><th>Degree / Course</th><th>Graduated</th></tr></thead>
          <tbody>
            <?php foreach ($education as $ed): ?>
            <tr>
              <td style="font-weight:600;"><?= e($ed['institution'] ?? '') ?></td>
              <td><?= e($ed['degree'] ?? '') ?></td>
              <td><?= e($ed['year_to'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Certifications -->
      <?php if (!empty($certs)): ?>
      <div class="card mb-4">
        <div class="card-header"><span class="card-title"><i class="fas fa-certificate me-2" style="color:#F59E0B;"></i>Licenses & Certifications</span></div>
        <table class="data-table">
          <thead><tr><th>Certificate Name</th><th>Issuing Body</th><th>Issued</th><th>Expires</th></tr></thead>
          <tbody>
            <?php foreach ($certs as $c): ?>
            <tr>
              <td style="font-weight:600;"><?= e($c['name'] ?? '') ?></td>
              <td><?= e($c['issuer'] ?? '') ?></td>
              <td><?= $c['issue_date'] ? e(date('M Y', strtotime($c['issue_date']))) : 'N/A' ?></td>
              <td><?= $c['expiry_date'] ? e(date('M Y', strtotime($c['expiry_date']))) : 'No Expiry' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Emergency Contacts -->
      <?php if (!empty($contacts)): ?>
      <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-phone-alt me-2" style="color:#E11D48;"></i>Emergency Contacts</span></div>
        <table class="data-table">
          <thead><tr><th>Name</th><th>Relationship</th><th>Phone</th></tr></thead>
          <tbody>
            <?php foreach ($contacts as $c): ?>
            <tr>
              <td style="font-weight:600;"><?= e($c['name'] ?? '') ?></td>
              <td><?= e($c['relationship'] ?? '') ?></td>
              <td><?= e($c['phone'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    </div>
  </div>
</main>

<style>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s;
}
.modal-overlay.open {
  opacity: 1;
  visibility: visible;
}
.modal-content {
  background: #fff;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
}
.modal-header {
  padding: 16px 24px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h5 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
}
.modal-close {
  background: none;
  border: none;
  font-size: 20px;
  color: #6B7280;
  cursor: pointer;
}
.modal-body {
  padding: 24px;
  overflow-y: auto;
  max-height: 65vh; /* This fixes the overlap and makes it scrollable */
}
</style>

<!-- Edit Profile Modal -->
<div class="modal-overlay" id="editProfileModal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Personal Information</h5>
      <button type="button" class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
    </div>
    <form action="employee_profile.php" method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="update_profile">
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6 form-group">
            <label>Gender</label>
            <select name="gender" class="form-control">
              <option value="">Select...</option>
              <option value="Male" <?= ($emp['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= ($emp['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= ($emp['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label>Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control" value="<?= e($emp['date_of_birth'] ?? '') ?>">
          </div>
          <div class="col-md-6 form-group">
            <label>Civil Status</label>
            <select name="civil_status" class="form-control">
              <option value="">Select...</option>
              <?php foreach (['Single','Married','Widowed','Separated','Divorced'] as $cs): ?>
                <option value="<?= $cs ?>" <?= ($emp['civil_status'] ?? '') === $cs ? 'selected' : '' ?>><?= $cs ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label>Nationality</label>
            <input type="text" name="nationality" class="form-control" value="<?= e($emp['nationality'] ?? '') ?>">
          </div>
          <div class="col-md-6 form-group">
            <label>Religion</label>
            <input type="text" name="religion" class="form-control" value="<?= e($emp['religion'] ?? '') ?>">
          </div>
          <div class="col-md-6 form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= e($emp['contact_number'] ?? '') ?>">
          </div>
          <div class="col-12 form-group">
            <label>Address</label>
            <textarea name="address" class="form-control" rows="2"><?= e($emp['address'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #E5E7EB; text-align: right;">
        <button type="button" class="btn btn-outline me-2" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function closeEditModal() {
  document.getElementById('editProfileModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('editProfileModal').addEventListener('click', function(e) {
  if (e.target === this) closeEditModal();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
