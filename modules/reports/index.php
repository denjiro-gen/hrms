<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../includes/auth.php";
requireRole(["admin","hr"]);
$pageTitle = "Reports";
$db = getDB();
$empCount   = $db->query("SELECT COUNT(*) FROM employees WHERE status='Active'")->fetchColumn();
$leaveCount = $db->query("SELECT COUNT(*) FROM leave_requests")->fetchColumn();
$payCount   = $db->query("SELECT COUNT(*) FROM payroll")->fetchColumn();
$perfCount  = $db->query("SELECT COUNT(*) FROM performance_evaluations")->fetchColumn();
include __DIR__ . "/../../includes/header.php";
include __DIR__ . "/../../includes/navbar.php";
include __DIR__ . "/../../includes/sidebar.php";
?>
<main class="hrms-main"><div class="main-content">
  <div style="margin-bottom:22px;">
    <h1 style="font-size:20px;font-weight:700;color:#111;margin:0;">Reports</h1>
    <p style="font-size:13px;color:#9CA3AF;margin:4px 0 0;">Download or view summaries for each HR module</p>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
    <?php
    $cards = [
      ["Employee Report",    "fas fa-users",          "#2F7BEE", "$empCount active employees",  BASE_URL."/modules/employees/index.php"],
      ["Leave Report",       "fas fa-calendar-minus", "#F59E0B", "$leaveCount total requests",  BASE_URL."/modules/leave/index.php"],
      ["Payroll Report",     "fas fa-wallet",          "#12B76A", "$payCount payroll runs",       BASE_URL."/modules/payroll/index.php"],
      ["Performance Report", "fas fa-chart-line",      "#8B5CF6", "$perfCount evaluations",       BASE_URL."/modules/performance/index.php"],
      ["Attendance Report",  "fas fa-calendar-check", "#0891B2", "View daily logs",              BASE_URL."/modules/attendance/index.php"],
      ["Recruitment Report", "fas fa-briefcase",       "#E11D48", "View applicants and jobs",     BASE_URL."/modules/recruitment/jobs/index.php"],
    ];
    foreach ($cards as [$title, $icon, $color, $sub, $href]):
    ?>
    <a href="<?= $href ?>" style="background:#fff;border:1.5px solid #E8ECF0;border-radius:12px;padding:20px;text-decoration:none;display:flex;flex-direction:column;gap:12px;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
      <span style="width:38px;height:38px;border-radius:9px;background:<?= $color ?>18;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:16px;"><i class="<?= $icon ?>"></i></span>
      <div>
        <div style="font-size:14px;font-weight:700;color:#111;"><?= $title ?></div>
        <div style="font-size:12px;color:#9CA3AF;margin-top:3px;"><?= $sub ?></div>
      </div>
      <div style="font-size:12px;color:<?= $color ?>;font-weight:600;">Open &rarr;</div>
    </a>
    <?php endforeach; ?>
  </div>
</div></main>
<?php include __DIR__ . "/../../includes/footer.php"; ?>
