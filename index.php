<?php
require_once __DIR__ . '/config/config.php';
// If already logged in, redirect to dashboard or portal
session_start();
if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['role_slug'] === 'employee') {
        header('Location: ' . BASE_URL . '/portal.php');
    } else {
        header('Location: ' . BASE_URL . '/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bestlink College of the Philippines | HRMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { 
      font-family: 'Inter', sans-serif; 
      background: #F8FAFC; 
      color: #0F172A; 
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      -webkit-font-smoothing: antialiased;
    }

    /* Navbar */
    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 24px 48px;
      background: transparent;
      width: 100%;
      max-width: 1280px;
      margin: 0 auto;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    .brand img {
      height: 40px;
      width: auto;
    }
    .brand span {
      font-weight: 700;
      font-size: 18px;
      color: #0F172A;
      letter-spacing: -0.5px;
    }

    /* Main Hero Area */
    .hero {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 40px 20px;
      position: relative;
      overflow: hidden;
    }
    
    /* Background Blur Blobs */
    .blob {
      position: absolute;
      filter: blur(80px);
      z-index: -1;
      opacity: 0.6;
    }
    .blob-1 {
      top: -10%; left: -5%;
      width: 400px; height: 400px;
      background: #BFDBFE;
      border-radius: 50%;
    }
    .blob-2 {
      bottom: -10%; right: -5%;
      width: 500px; height: 500px;
      background: #E0E7FF;
      border-radius: 50%;
    }

    .hero h1 {
      font-size: 56px;
      font-weight: 800;
      letter-spacing: -1.5px;
      color: #0F172A;
      margin-bottom: 16px;
      line-height: 1.1;
      max-width: 800px;
    }
    .hero h1 span {
      background: linear-gradient(135deg, #1D4ED8, #3B82F6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .hero p {
      font-size: 18px;
      color: #64748B;
      max-width: 540px;
      margin-bottom: 48px;
      line-height: 1.6;
    }

    /* Cards Container */
    .options-grid {
      display: flex;
      gap: 24px;
      justify-content: center;
      max-width: 800px;
      width: 100%;
    }

    /* Option Cards */
    .option-card {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 20px;
      padding: 40px 32px;
      text-decoration: none;
      color: #0F172A;
      width: 320px;
      text-align: left;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
      position: relative;
      overflow: hidden;
    }
    .option-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(29,78,216,0) 0%, rgba(29,78,216,0.03) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    .option-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      border-color: #BFDBFE;
    }
    .option-card:hover::before {
      opacity: 1;
    }

    .icon-wrapper {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 24px;
    }
    .employee-card .icon-wrapper {
      background: #EFF6FF;
      color: #1D4ED8;
    }
    .career-card .icon-wrapper {
      background: #F0FDF4;
      color: #15803D;
    }

    .option-card h3 {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .option-card p {
      font-size: 14px;
      color: #64748B;
      margin-bottom: 0;
      line-height: 1.5;
    }
    
    .arrow-icon {
      position: absolute;
      bottom: 40px;
      right: 32px;
      color: #CBD5E1;
      font-size: 18px;
      transition: transform 0.3s ease, color 0.3s ease;
    }
    .option-card:hover .arrow-icon {
      transform: translateX(6px);
    }
    .employee-card:hover .arrow-icon { color: #1D4ED8; }
    .career-card:hover .arrow-icon { color: #15803D; }

    /* Footer */
    footer {
      text-align: center;
      padding: 24px;
      color: #94A3B8;
      font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero h1 { font-size: 40px; }
      .options-grid { flex-direction: column; align-items: center; }
      .option-card { width: 100%; max-width: 360px; }
      .navbar { padding: 20px; }
    }
  </style>
</head>
<body>

  <!-- Blur Background Effects -->
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>

  <nav class="navbar">
    <a href="#" class="brand">
      <img src="https://bcp.edu.ph/logo.png" alt="Bestlink Logo">
      <span>Bestlink College</span>
    </a>
  </nav>

  <main class="hero">
    <h1>Welcome to the <br><span>HR Management System</span></h1>
    <p>Streamlining employee resources and careers for the Bestlink College of the Philippines community.</p>

    <div class="options-grid">
      <!-- Employee Portal Card -->
      <a href="login.php" class="option-card employee-card">
        <div class="icon-wrapper">
          <i class="fas fa-user-circle"></i>
        </div>
        <h3>Employee Portal</h3>
        <p>Access your dashboard, attendance, payroll, and leave requests.</p>
        <i class="fas fa-arrow-right arrow-icon"></i>
      </a>

      <!-- Careers / Recruitment Card -->
      <a href="careers.php" class="option-card career-card">
        <div class="icon-wrapper">
          <i class="fas fa-briefcase"></i>
        </div>
        <h3>Careers & Jobs</h3>
        <p>View open positions and submit your application to join our team.</p>
        <i class="fas fa-arrow-right arrow-icon"></i>
      </a>
    </div>
  </main>

  <footer>
    &copy; <?= date('Y') ?> Bestlink College of the Philippines. All rights reserved.
  </footer>

</body>
</html>
