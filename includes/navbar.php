<header class="hrms-topbar" id="topbar">
  <div class="topbar-page-title"><?= e($pageTitle ?? 'Dashboard') ?></div>

  <!-- Search bar -->
  <div class="topbar-search">
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search employees, jobs..." aria-label="Search">
  </div>

  <div class="topbar-actions">
    <!-- Clock -->
    <span id="topbarClock"></span>

    <div class="topbar-sep"></div>

    <!-- Notifications -->
    <button class="topbar-icon-btn" title="Notifications">
      <i class="fas fa-bell"></i>
      <span class="topbar-badge"></span>
    </button>

    <!-- Fullscreen -->
    <button class="topbar-icon-btn" id="fullscreenBtn" title="Fullscreen">
      <i class="fas fa-expand"></i>
    </button>

    <div class="topbar-sep"></div>

    <!-- User -->
    <div class="dropdown">
      <div class="topbar-user" data-bs-toggle="dropdown">
        <div class="topbar-user-avatar">
          <?= e(strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1) . substr($_SESSION['last_name'] ?? '', 0, 1))) ?>
        </div>
        <div class="topbar-user-info">
          <div class="tu-name"><?= e(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?></div>
        </div>
        <i class="fas fa-chevron-down" style="font-size:9px; color:var(--c-txt3); margin-left:2px;"></i>
      </div>
      <ul class="dropdown-menu dropdown-menu-end" style="min-width:190px;">
        <li>
          <div style="padding: 10px 12px; border-bottom: 1px solid var(--c-border); margin-bottom:4px;">
            <div style="font-weight:700; font-size:13px;"><?= e(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?></div>
            <div style="font-size:11.5px; color:var(--c-txt2);"><?= e($_SESSION['role_name'] ?? '') ?></div>
          </div>
        </li>
        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sign Out</a></li>
      </ul>
    </div>
  </div>
</header>

<script>
// Live clock
(function() {
  function tick() {
    const el = document.getElementById('topbarClock');
    if (el) {
      const n = new Date();
      el.textContent = n.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    }
  }
  tick();
  setInterval(tick, 1000);
})();

// Fullscreen
document.getElementById('fullscreenBtn')?.addEventListener('click', function() {
  const icon = this.querySelector('i');
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    icon.className = 'fas fa-compress';
  } else {
    document.exitFullscreen();
    icon.className = 'fas fa-expand';
  }
});
</script>
