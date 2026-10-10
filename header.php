<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 1. Output HTML <head> if not already rendered ──
if (!defined('ERP_HEAD_RENDERED')) {
    define('ERP_HEAD_RENDERED', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>CourseSELL — ERP Portal</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="./img/ibe.png" type="image/x-icon">

  <!-- Inter Font (Modern, clean) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome 6 Free -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <!-- Master ERP Theme -->
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php
}

// ── 2. Top Header Navigation (Dark Slate #0f172a) ──
if (isset($connec)) {
    $aemail = $_SESSION['email'] ?? '';
    $fsql = "SELECT fname, lname, email, role FROM `login` WHERE `email` = '$aemail' LIMIT 1";
    $frun = mysqli_query($connec, $fsql);
    $ffet = ($frun && mysqli_num_rows($frun) > 0) ? mysqli_fetch_assoc($frun) : null;
    $fname = $ffet['fname'] ?? ($_SESSION['name'] ?? 'User');
    $lname = $ffet['lname'] ?? '';
    $user_role = ucfirst($ffet['role'] ?? ($_SESSION['role'] ?? 'Student'));
} else {
    $fname = $_SESSION['name'] ?? 'User';
    $lname = '';
    $user_role = ucfirst($_SESSION['role'] ?? 'Student');
}
$fullname = trim($fname . ' ' . $lname);
$initials = strtoupper(substr($fname, 0, 1) . (!empty($lname) ? substr($lname, 0, 1) : ''));
?>

<!-- ════════ TOP HEADER / NAVBAR (Dark Slate #0f172a) ════════ -->
<header class="topbar d-flex align-items-center justify-content-between px-3 px-md-4" style="background-color: #0f172a !important; color: #f8fafc !important; height: 64px; border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;">

  <!-- Left: Mobile Sidebar Toggle & Page Title / Breadcrumb -->
  <div class="d-flex align-items-center gap-3">
    <!-- Mobile sidebar toggle -->
    <button id="sidebarToggleTop" class="btn btn-link text-white p-1 d-md-none border-0 shadow-none" aria-label="Toggle sidebar">
      <i class="fas fa-bars fa-lg"></i>
    </button>

    <!-- Search bar -->
    <div class="topbar-search d-none d-md-block position-relative">
      <span class="position-absolute text-secondary" style="left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
        <i class="fas fa-magnifying-glass fa-sm text-white-50"></i>
      </span>
      <input type="text" placeholder="Search courses, students, modules…" id="topbarSearch" autocomplete="off" class="form-control form-control-sm text-white rounded-3 ps-4" style="background-color: #1e293b !important; border: 1px solid #334155 !important; width: 300px;">
    </div>
  </div>

  <!-- Right: Actions & User Dropdown -->
  <div class="topbar-actions d-flex align-items-center gap-3">

    <!-- Notification bell -->
    <button class="topbar-icon-btn rounded-3 text-white border-0 position-relative" title="Notifications" aria-label="Notifications" style="background-color: #1e293b; width: 38px; height: 38px;">
      <i class="fas fa-bell"></i>
      <span class="position-absolute bg-danger rounded-circle" style="width: 8px; height: 8px; top: 6px; right: 6px; border: 2px solid #0f172a;"></span>
    </button>

    <!-- Divider -->
    <div style="width: 1px; height: 28px; background: rgba(255, 255, 255, 0.12);"></div>

    <!-- User dropdown -->
    <div class="dropdown">
      <button class="btn btn-link text-decoration-none text-white p-0 d-flex align-items-center gap-2 border-0 shadow-none" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white bg-primary" style="width: 38px; height: 38px; font-size: 0.9rem; border: 2px solid rgba(255, 255, 255, 0.15);">
          <?= !empty($initials) ? htmlspecialchars($initials) : 'U'; ?>
        </div>
        <div class="text-start d-none d-lg-block line-height-sm">
          <div class="fw-semibold text-white small"><?= htmlspecialchars($fullname); ?></div>
          <div class="text-white-50" style="font-size: 0.72rem;"><?= htmlspecialchars($user_role); ?></div>
        </div>
        <i class="fas fa-chevron-down fa-xs text-white-50 ms-1 d-none d-lg-block"></i>
      </button>

      <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2" aria-labelledby="userDropdown" style="min-width: 200px;">
        <li class="px-3 py-2 border-bottom">
          <div class="fw-bold text-dark"><?= htmlspecialchars($fullname); ?></div>
          <div class="small text-muted"><?= htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
        </li>
        <li>
          <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="./profile.php">
            <i class="fas fa-user-circle text-secondary"></i> My Profile
          </a>
        </li>
        <li>
          <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="./dashboard.php">
            <i class="fas fa-gauge-high text-secondary"></i> Dashboard
          </a>
        </li>
        <li><hr class="dropdown-divider my-1"></li>
        <li>
          <a class="dropdown-item text-danger py-2 d-flex align-items-center gap-2" href="#" id="navLogoutBtn">
            <i class="fas fa-right-from-bracket"></i> Logout
          </a>
        </li>
      </ul>
    </div>

  </div>
</header>
<!-- ════════ END TOP HEADER ════════ -->
