<!-- ════════ FOOTER ════════ -->
<footer class="app-footer py-3 px-4 mt-auto border-top bg-white d-flex flex-wrap justify-content-between align-items-center">
  <span class="text-secondary small">
    &copy; <?php echo date("Y"); ?>
    <a href="./dashboard.php" class="text-primary fw-semibold text-decoration-none">CourseSELL</a> — All rights reserved.
  </span>
  <!-- <span class="text-secondary small">
    Modern Student & Admin ERP Portal &bull; Bootstrap 5
  </span> -->
</footer>

<!-- ════════ Toast Container ════════ -->
<div id="toast-container" aria-live="polite"></div>

<!-- ════════ Scroll to Top ════════ -->
<button id="scrollTopBtn" title="Scroll to top" aria-label="Scroll to top">
  <i class="fas fa-arrow-up"></i>
</button>

<!-- ════════ Logout Confirmation Modal (BS5) ════════ -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom py-3 px-4">
        <h5 class="modal-title fw-bold" id="logoutModalLabel">
          <i class="fas fa-right-from-bracket me-2 text-danger"></i>Ready to leave?
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-4 px-4 text-secondary">
        Select <strong>Logout</strong> below if you are ready to end your current session.
      </div>
      <div class="modal-footer border-top py-3 px-4">
        <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
        <a href="./logout.php" class="btn btn-danger rounded-3 px-4">
          <i class="fas fa-right-from-bracket me-1"></i> Logout
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ════════ Scripts ════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Logout confirmation — both sidebar btn and navbar btn
function confirmLogout(e) {
  if (e) e.preventDefault();
  Swal.fire({
    title: 'Ready to leave?',
    text: 'You will need to log in again to access your account.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Yes, logout',
    cancelButtonText: 'Cancel',
    customClass: {
      popup: 'rounded-4 shadow-lg'
    }
  }).then(result => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Logging out...',
        text: 'See you again soon!',
        icon: 'success',
        timer: 1200,
        showConfirmButton: false
      }).then(() => window.location.href = 'logout.php');
    }
  });
}

document.getElementById('navLogoutBtn')?.addEventListener('click', confirmLogout);
document.getElementById('sidebarLogoutBtn')?.addEventListener('click', confirmLogout);

// Mobile sidebar toggle
const sidebarNav     = document.getElementById('sidebarNav');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const toggleBtn      = document.getElementById('sidebarToggleTop');

function openSidebar() {
  sidebarNav?.classList.add('sidebar-open');
  sidebarOverlay?.classList.add('show');
}
function closeSidebar() {
  sidebarNav?.classList.remove('sidebar-open');
  sidebarOverlay?.classList.remove('show');
}

toggleBtn?.addEventListener('click', () => {
  sidebarNav?.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
});
sidebarOverlay?.addEventListener('click', closeSidebar);

/* ── Scroll to Top ── */
const scrollBtn = document.getElementById('scrollTopBtn');
window.addEventListener('scroll', () => {
  scrollBtn?.classList.toggle('show', window.scrollY > 250);
}, { passive: true });
scrollBtn?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

/* ── Global Toast Utility ──
   Usage: showToast('success', 'Saved!', 'Record updated successfully.');
   Types: 'success' | 'error' | 'warning' | 'info'
*/
const TOAST_ICONS = {
  success: 'fas fa-circle-check',
  error:   'fas fa-circle-xmark',
  warning: 'fas fa-triangle-exclamation',
  info:    'fas fa-circle-info'
};
const TOAST_TITLES = {
  success: 'Success',
  error:   'Error',
  warning: 'Warning',
  info:    'Info'
};

function showToast(type = 'info', title = '', message = '', duration = 4000) {
  const container = document.getElementById('toast-container');
  if (!container) return;

  title   = title   || TOAST_TITLES[type] || 'Notification';
  const iconCls = TOAST_ICONS[type] || TOAST_ICONS.info;

  const toast = document.createElement('div');
  toast.className = `app-toast ${type}`;
  toast.innerHTML = `
    <i class="app-toast-icon ${iconCls}"></i>
    <div class="flex-grow-1">
      <div class="app-toast-title">${title}</div>
      ${message ? `<div class="app-toast-msg">${message}</div>` : ''}
    </div>
    <button class="app-toast-close" aria-label="Close">×</button>
    <div class="app-toast-progress"></div>
  `;

  container.appendChild(toast);

  // Close button
  toast.querySelector('.app-toast-close').addEventListener('click', () => dismissToast(toast));

  // Auto-dismiss
  const timer = setTimeout(() => dismissToast(toast), duration);

  function dismissToast(el) {
    clearTimeout(timer);
    el.classList.add('hiding');
    el.addEventListener('animationend', () => el.remove(), { once: true });
  }
}
</script>

</body>
</html>
