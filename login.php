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
        $role = $_SESSION['role_slug'] ?? '';
        if ($role === 'employee') {
            header('Location: ' . BASE_URL . '/portal.php');
        } else {
            header('Location: ' . BASE_URL . '/dashboard.php');
        }
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

  .page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    position: relative;
    padding: 24px;
    gap: 20px;
  }

  .page::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('assets/images/bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    filter: blur(8px) brightness(0.65);
    transform: scale(1.05);
    z-index: 0;
  }

  .page::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 60, 120, 0.45);
    z-index: 0;
    pointer-events: none;
  }

  .page-logo {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .page-logo img {
    width: 90px;
    height: 90px;
    object-fit: contain;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.4));
  }

  .form-box {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    background: #fff;
    border-radius: 16px;
    padding: 44px 44px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.5);
  }

  .form-heading {
    font-size: 24px;
    font-weight: 700;
    color: var(--gray-900);
    text-align: center;
    margin-bottom: 6px;
    letter-spacing: -.3px;
  }
  .form-sub {
    font-size: 13.5px;
    color: var(--gray-400);
    text-align: center;
    margin-bottom: 28px;
  }

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
    height: 46px;
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

  .btn-submit {
    width: 100%;
    height: 46px;
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

  .form-footer {
    margin-top: 28px;
    text-align: center;
    font-size: 11.5px;
    color: var(--gray-400);
  }

  .info-box {
    margin-top: 22px;
    padding: 14px 16px;
    background: #F0F9FF;
    border: 1.5px solid #BAE6FD;
    border-radius: 10px;
    text-align: left;
  }
  .info-box-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #0369A1;
    letter-spacing: 0.06em;
    margin-bottom: 6px;
  }
  .info-box-text {
    font-size: 13px;
    color: #0C4A6E;
    line-height: 1.5;
  }

  @media (max-width: 540px) {
    .form-box { padding: 32px 24px; max-width: 100%; }
  }
</style>
</head>
<body>

<div class="page">

  <div class="page-logo">
    <img src="https://bcp.edu.ph/logo.png" alt="BCP" onerror="this.style.display='none'">
  </div>

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

    <div class="info-box">
      <div class="info-box-title">👤 New Employee?</div>
      <div class="info-box-text">Use the <strong>email and temporary password</strong> sent to your inbox when you were hired. You will be prompted to change your password on first login.</div>
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
