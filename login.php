<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (!loginUser($email, $password)) {
        $error = 'Incorrect email or password.';
    } else {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}
$timeout = !empty($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Bestlink HRMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --blue:   #0093DD;
    --blue-d: #007bbf;
    --gray-50:  #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-400: #94A3B8;
    --gray-600: #475569;
    --gray-900: #0F172A;
    --red:   #EF4444;
    --amber: #F59E0B;
  }

  html, body {
    height: 100%;
    font-family: 'Inter', sans-serif;
    background: var(--gray-50);
    color: var(--gray-900);
    -webkit-font-smoothing: antialiased;
  }

  /* ── LAYOUT ── */
  .page {
    display: flex;
    min-height: 100vh;
  }

  /* LEFT STRIP */
  .left {
    width: 420px;
    flex-shrink: 0;
    background: var(--blue);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 40px;
    position: relative;
    overflow: hidden;
  }

  /* wave at the bottom-right of the strip */
  .left::after {
    content: '';
    position: absolute;
    right: -80px;
    bottom: -80px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
  }
  .left::before {
    content: '';
    position: absolute;
    left: -60px;
    top: -60px;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
  }

  .left-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0;
    position: relative;
    z-index: 1;
  }

  /* logo */
  .logo-wrap {
    width: 80px;
    height: 80px;
    background: #fff;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 22px;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
  }
  .logo-wrap img {
    width: 60px;
    height: 60px;
    object-fit: contain;
  }

  .school-name {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
    margin-bottom: 6px;
  }
  .school-sub {
    font-size: 12.5px;
    color: rgba(255,255,255,.65);
    margin-bottom: 40px;
  }

  /* divider line */
  .left-divider {
    width: 40px;
    height: 2px;
    background: rgba(255,255,255,.3);
    border-radius: 2px;
    margin-bottom: 40px;
  }

  .system-label {
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,.5);
    text-transform: uppercase;
    letter-spacing: .12em;
    margin-bottom: 16px;
  }

  .system-name {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,.9);
    line-height: 1.5;
  }

  /* ── RIGHT (form) ── */
  .right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 32px;
  }

  .form-box {
    width: 100%;
    max-width: 360px;
  }

  .form-heading {
    font-size: 26px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 6px;
    letter-spacing: -.4px;
  }
  .form-sub {
    font-size: 13.5px;
    color: var(--gray-400);
    margin-bottom: 32px;
  }

  /* alert */
  .alert {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 11px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 20px;
    font-weight: 500;
  }
  .alert-danger  { background: #FEF2F2; color: var(--red);   border: 1px solid #FECACA; }
  .alert-warning { background: #FFFBEB; color: var(--amber); border: 1px solid #FDE68A; }
  .alert i { flex-shrink: 0; }

  /* field */
  .field { margin-bottom: 18px; }
  .field label {
    display: block;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--gray-600);
    margin-bottom: 7px;
    letter-spacing: .01em;
  }
  .input-wrap { position: relative; }
  .field input {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1.5px solid var(--gray-200);
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: var(--gray-900);
    background: #fff;
    outline: none;
    transition: border-color .18s, box-shadow .18s;
  }
  .field input::placeholder { color: #BCC8D6; }
  .field input:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(0,147,221,.1);
  }
  .eye-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #BCC8D6;
    font-size: 14px;
    line-height: 1;
    padding: 4px;
    transition: color .18s;
  }
  .eye-btn:hover { color: var(--blue); }

  /* forgot */
  .forgot {
    text-align: right;
    margin-top: -10px;
    margin-bottom: 24px;
  }
  .forgot a {
    font-size: 12.5px;
    color: var(--blue);
    text-decoration: none;
    font-weight: 500;
  }
  .forgot a:hover { text-decoration: underline; }

  /* submit */
  .btn-submit {
    width: 100%;
    height: 44px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14.5px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    letter-spacing: .01em;
    transition: background .18s, box-shadow .18s, transform .1s;
    box-shadow: 0 2px 10px rgba(0,147,221,.28);
  }
  .btn-submit:hover {
    background: var(--blue-d);
    box-shadow: 0 4px 16px rgba(0,147,221,.36);
  }
  .btn-submit:active { transform: translateY(1px); }

  /* footer */
  .form-footer {
    margin-top: 32px;
    text-align: center;
    font-size: 11.5px;
    color: var(--gray-400);
  }

  /* ── RESPONSIVE ── */
  @media (max-width: 680px) {
    .page    { flex-direction: column; }
    .left    { width: 100%; padding: 36px 24px; }
    .left::after, .left::before { display: none; }
    .left-inner { flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 16px; }
    .logo-wrap   { margin-bottom: 0; width: 56px; height: 56px; border-radius: 14px; }
    .logo-wrap img { width: 42px; height: 42px; }
    .school-sub  { margin-bottom: 0; }
    .left-divider, .system-label, .system-name { display: none; }
    .right   { padding: 36px 20px; }
  }
</style>
</head>
<body>

<div class="page">

  <!-- ── LEFT STRIP ── -->
  <div class="left">
    <div class="left-inner">
      <div class="logo-wrap">
        <img src="https://bcp.edu.ph/logo.png" alt="BCP" onerror="this.style.display='none'">
      </div>
      <div class="school-name">Bestlink College<br>of the Philippines</div>
      <div class="school-sub">Quezon City, Philippines</div>
      <div class="left-divider"></div>
      <div class="system-label">System</div>
      <div class="system-name">Human Resource<br>Management System</div>
    </div>
  </div>

  <!-- ── RIGHT FORM ── -->
  <div class="right">
    <div class="form-box">

      <div class="form-heading">Welcome back</div>
      <div class="form-sub">Sign in to your HRMS account</div>

      <?php if ($timeout): ?>
      <div class="alert alert-warning">
        <i class="fas fa-clock"></i>
        <span>Your session expired. Please sign in again.</span>
      </div>
      <?php endif; ?>

      <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= e($error) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <?= csrfField() ?>

        <div class="field">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email"
            placeholder="you@bestlink.edu.ph"
            autocomplete="email" required
            value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password"
              placeholder="••••••••"
              autocomplete="current-password" required>
            <button type="button" class="eye-btn" id="togglePw">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <div class="forgot">
          <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="btn-submit">Sign in</button>
      </form>

      <div class="form-footer">
        &copy; <?= date('Y') ?> Bestlink College of the Philippines
      </div>

    </div>
  </div>

</div>

<script>
document.getElementById('togglePw').addEventListener('click', function () {
  const pw   = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  const show = pw.type === 'password';
  pw.type          = show ? 'text' : 'password';
  icon.className   = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
</script>
</body>
</html>
