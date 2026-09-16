const sidebar      = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    document.querySelector('.hrms-main')?.classList.toggle('expanded');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
  });
  if (localStorage.getItem('sidebarCollapsed') === '1') {
    sidebar.classList.add('collapsed');
    document.querySelector('.hrms-main')?.classList.add('expanded');
  }
}

document.addEventListener('click', (e) => {
  if (window.innerWidth < 992 && sidebar && sidebarToggle) {
    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  }
});
if (sidebarToggle) {
  sidebarToggle.addEventListener('click', () => {
    if (window.innerWidth < 992) sidebar.classList.toggle('open');
  });
}

const clockEl = document.getElementById('clock');
function updateClock() {
  if (!clockEl) return;
  clockEl.textContent = new Date().toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true });
}
if (clockEl) { updateClock(); setInterval(updateClock, 1000); }

document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.pwToggle);
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
  });
});

setTimeout(() => {
  document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
    const alert = bootstrap.Alert.getOrCreateInstance(el);
    alert?.close();
  });
}, 4000);

document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', (e) => {
    if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });
});
