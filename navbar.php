<?php
$aemail = $_SESSION['email'] ?? '';

$fsql = "SELECT * FROM `login` WHERE `email` = '$aemail'";
$frun = mysqli_query($connec, $fsql);
$ffet = mysqli_fetch_assoc($frun);

$fname = $ffet['fname'] ?? 'User';
$lname = $ffet['lname'] ?? '';
$fullname = trim($fname . ' ' . $lname);

// Build initials for avatar fallback
$initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
?>

<!-- ════════ TOP NAVBAR ════════ -->
<header class="topbar">

  <!-- Mobile sidebar toggle -->
  <button id="sidebarToggleTop" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Search bar -->
  <div class="topbar-search d-none d-sm-block">
    <span class="topbar-search-icon"><i class="fas fa-magnifying-glass"></i></span>
    <input type="text" placeholder="Search anything…" id="topbarSearch" autocomplete="off">
  </div>

  <!-- Right actions -->
  <div class="topbar-actions">

    <!-- Notification bell -->
    <button class="topbar-icon-btn" title="Notifications" aria-label="Notifications">
      <i class="fas fa-bell"></i>
      <span class="topbar-badge"></span>
    </button>

    <div class="topbar-divider"></div>

    <!-- User dropdown -->
    <div class="dropdown">
      <button class="user-dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
        <!-- Avatar initials -->
        <div class="avatar-initials" title="<?= htmlspecialchars($fullname); ?>">
          <?= htmlspecialchars($initials); ?>
        </div>
        <span class="user-name d-none d-lg-block"><?= htmlspecialchars($fullname); ?></span>
        <i class="fas fa-chevron-down fa-xs text-muted ms-1 d-none d-lg-block"></i>
      </button>

      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li>
          <a class="dropdown-item" href="./profile.php">
            <i class="fas fa-user-circle text-muted"></i> My Profile
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item text-danger" href="#" id="navLogoutBtn">
            <i class="fas fa-right-from-bracket"></i> Logout
          </a>
        </li>
      </ul>
    </div>

  </div>
</header>
<!-- ════════ END TOP NAVBAR ════════ -->

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Logout confirmation — both sidebar btn and navbar btn
  function confirmLogout(e) {
    e.preventDefault();
    Swal.fire({
      title: 'Ready to leave?',
      text: 'You will need to log in again to access your account.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#ef4444',
      confirmButtonText: 'Yes, logout',
      cancelButtonText: 'Stay',
      borderRadius: '12px'
    }).then(result => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Logged out!',
          text: 'You have been successfully logged out.',
          icon: 'success',
          timer: 1400,
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
    sidebarNav.classList.add('sidebar-open');
    sidebarOverlay.classList.add('show');
  }
  function closeSidebar() {
    sidebarNav.classList.remove('sidebar-open');
    sidebarOverlay.classList.remove('show');
  }

  toggleBtn?.addEventListener('click', () => {
    sidebarNav?.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
  });
  sidebarOverlay?.addEventListener('click', closeSidebar);
</script>
