<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } else {
        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();
        
        if (!password_verify($currentPassword, $hash)) {
            $error = 'Incorrect current password.';
        } else {
            // Update to new password
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE users SET password_hash = ?, first_login = 0 WHERE id = ?");
            if ($update->execute([$newHash, $userId])) {
                $_SESSION['first_login'] = 0;
                $success = 'Password successfully changed.';
            } else {
                $error = 'An error occurred while updating your password. Please try again.';
            }
        }
    }
}

$pageTitle = 'Change Password';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="hrms-main">
  <div class="main-content">
    <div class="d-flex align-items-center mb-4">
      <h1 class="page-title mb-0">Change Password</h1>
    </div>

    <div class="row">
      <div class="col-md-6 col-lg-5">
        <div class="card">
          <div class="card-body p-4">
            <?php if ($error): ?>
              <div class="alert alert-danger d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i> <?= e($error) ?>
              </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
              <div class="alert alert-success d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i> <?= e($success) ?>
              </div>
            <?php endif; ?>
            
            <form method="POST" action="">
              <?= csrfField() ?>
              
              <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
              </div>
              
              <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                <div class="form-text">Must be at least 8 characters long.</div>
              </div>
              
              <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
              </div>
              
              <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
