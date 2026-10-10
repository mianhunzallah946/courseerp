<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Automatically output HTML head if not already printed
if (!defined('ERP_HEAD_RENDERED')) {
    define('ERP_HEAD_RENDERED', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>CourseERP — Portal</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="./img/ibe.png" type="image/x-icon">

  <!-- Inter Font -->
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

// Detect active page for sidebar highlight
$current_page = basename($_SERVER['PHP_SELF']);
$user_role    = $_SESSION['role'] ?? 'guest';
?>

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay"></div>

<!-- ════════ SIDEBAR ════════ -->
<nav class="sidebar" id="sidebarNav" style="background-color: #0f172a !important;">

  <!-- Brand / Logo -->
  <a class="sidebar-brand text-decoration-none" href="./dashboard.php">
    <div class="sidebar-brand-icon">
      <i class="fas fa-graduation-cap fa-sm"></i>
    </div>
    <div class="sidebar-brand-text text-white">
      Course<sup class="text-primary fw-bold">ERP</sup>
    </div>
  </a>

  <!-- Nav items list -->
  <ul class="nav flex-column py-3 flex-grow-1" style="padding:0;list-style:none;">

    <!-- Dashboard Link -->
    <li class="nav-item <?= in_array($current_page, ['dashboard.php', 'index.php']) ? 'active' : ''; ?>">
      <a class="nav-link <?= in_array($current_page, ['dashboard.php', 'index.php']) ? 'active' : ''; ?>" href="./dashboard.php">
        <i class="fas fa-gauge-high fa-fw me-2"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <hr class="sidebar-divider">

    <?php if ($user_role === 'admin') { ?>
      <!-- ── Admin Section ── -->
      <li class="px-3 pt-2 pb-1">
        <div class="sidebar-heading text-uppercase small text-white-50 fw-bold">Admin Controls</div>
      </li>

      <!-- Add Course -->
      <li class="nav-item <?= in_array($current_page, ['add_course.php', 'addcourse.php']) ? 'active' : ''; ?>">
        <a class="nav-link <?= in_array($current_page, ['add_course.php', 'addcourse.php']) ? 'active' : ''; ?>" href="./add_course.php">
          <i class="fas fa-plus-circle fa-fw me-2"></i>
          <span>Add Course</span>
        </a>
      </li>

      <!-- Manage Courses -->
      <li class="nav-item <?= in_array($current_page, ['manage_courses.php', 'viewcourse.php']) ? 'active' : ''; ?>">
        <a class="nav-link <?= in_array($current_page, ['manage_courses.php', 'viewcourse.php']) ? 'active' : ''; ?>" href="./manage_courses.php">
          <i class="fas fa-book-open fa-fw me-2"></i>
          <span>Manage Courses</span>
        </a>
      </li>

      <!-- Manage Users / Students -->
      <li class="nav-item <?= ($current_page === 'manageusers.php') ? 'active' : ''; ?>">
        <a class="nav-link <?= ($current_page === 'manageusers.php') ? 'active' : ''; ?>" href="./manageusers.php">
          <i class="fas fa-users-gear fa-fw me-2"></i>
          <span>Manage Students</span>
        </a>
      </li>
    <?php } ?>

    <?php if ($user_role === 'student') { ?>
      <!-- ── Student Section ── -->
      <li class="px-3 pt-2 pb-1">
        <div class="sidebar-heading text-uppercase small text-white-50 fw-bold">Student Portal</div>
      </li>

      <!-- My Courses -->
      <li class="nav-item <?= in_array($current_page, ['my_courses.php', 'usercourse.php', 'watch_course.php']) ? 'active' : ''; ?>">
        <a class="nav-link <?= in_array($current_page, ['my_courses.php', 'usercourse.php', 'watch_course.php']) ? 'active' : ''; ?>" href="./my_courses.php">
          <i class="fas fa-circle-play fa-fw me-2"></i>
          <span>My Enrolled Courses</span>
        </a>
      </li>

      <!-- Profile -->
      <li class="nav-item <?= ($current_page === 'profile.php') ? 'active' : ''; ?>">
        <a class="nav-link <?= ($current_page === 'profile.php') ? 'active' : ''; ?>" href="./profile.php">
          <i class="fas fa-id-card fa-fw me-2"></i>
          <span>My Profile</span>
        </a>
      </li>
    <?php } ?>

    <hr class="sidebar-divider mt-auto">

    <!-- Profile & Settings -->
    <li class="nav-item <?= ($current_page === 'profile.php') ? 'active' : ''; ?>">
      <a class="nav-link" href="./profile.php">
        <i class="fas fa-gear fa-fw me-2"></i>
        <span>Settings</span>
      </a>
    </li>

    <!-- Logout -->
    <li class="nav-item pb-2">
      <a class="nav-link logout-link text-danger" href="#" id="sidebarLogoutBtn">
        <i class="fas fa-right-from-bracket fa-fw me-2"></i>
        <span>Logout</span>
      </a>
    </li>

  </ul>
</nav>
<!-- ════════ END SIDEBAR ════════ -->