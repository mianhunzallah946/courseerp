<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ./login.php");
    exit();
}

include("./connection.php");

$user_id   = intval($_SESSION['user_id'] ?? 0);
$user_role = $_SESSION['role'] ?? 'student';
$course_id = intval($_GET['id'] ?? 0);

// Strict enrollment check: Verify user is enrolled or is admin
$is_authorized = false;

if ($user_role === 'admin') {
    $is_authorized = true;
} else {
    // 1. Check direct enrollment record
    $enroll_check = mysqli_query($connec, "SELECT * FROM enrollments WHERE user_id='$user_id' AND course_id='$course_id' LIMIT 1");
    if ($enroll_check && mysqli_num_rows($enroll_check) > 0) {
        $is_authorized = true;
    } else {
        // 2. Check title matching with student's assigned course
        $st_res = mysqli_query($connec, "SELECT course_name FROM login WHERE user_id='$user_id' LIMIT 1");
        if ($st_res && mysqli_num_rows($st_res) > 0) {
            $st_course = trim(mysqli_fetch_assoc($st_res)['course_name'] ?? '');
            if (!empty($st_course)) {
                $c_check = mysqli_query($connec, "SELECT course_id FROM course WHERE course_id='$course_id' AND TRIM(LOWER(title)) = TRIM(LOWER('" . mysqli_real_escape_string($connec, $st_course) . "')) LIMIT 1");
                if ($c_check && mysqli_num_rows($c_check) > 0) {
                    $is_authorized = true;
                    // Auto-sync into enrollments table
                    mysqli_query($connec, "INSERT IGNORE INTO enrollments (user_id, course_id) VALUES ('$user_id', '$course_id')");
                }
            }
        }
    }
}

// Fetch the requested lesson if authorized
$lesson = null;
if ($is_authorized && $course_id > 0) {
    $current_lesson_query = mysqli_query($connec, "SELECT * FROM course WHERE course_id='$course_id' LIMIT 1");
    $lesson = ($current_lesson_query && mysqli_num_rows($current_lesson_query) > 0)
        ? mysqli_fetch_assoc($current_lesson_query)
        : null;
}

$active_title = $lesson['title'] ?? '';

// Fetch playlist lessons for this course
$playlist_query = null;
if ($lesson && !empty($active_title)) {
    $playlist_query = mysqli_query($connec, "SELECT * FROM course WHERE title='" . mysqli_real_escape_string($connec, $active_title) . "' ORDER BY course_id ASC");
}
?>
<div class="d-flex" id="wrapper">
    <!-- Fixed Sidebar -->
    <?php include("sidebar.php"); ?>
    
    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="w-100 bg-light">
        <?php include("header.php"); ?>
        
        <div class="container-fluid px-4 py-4">

            <!-- Breadcrumb Navigation -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="./dashboard.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="./my_courses.php" class="text-decoration-none">My Courses</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($lesson['title'] ?? 'Player'); ?></li>
                    </ol>
                </nav>
                <a href="./my_courses.php" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="fas fa-arrow-left me-1"></i> Back to Courses
                </a>
            </div>

            <?php if (!$is_authorized): ?>
                <!-- 🔒 Unauthorized / Not Enrolled Alert Card -->
                <div class="card card-erp shadow-sm border-0 rounded-4 text-center py-5 my-4">
                    <div class="card-body p-5">
                        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-shield-halved fa-2x"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">You are not enrolled in this course.</h4>
                        <p class="text-secondary small mb-4 mx-auto" style="max-width: 450px;">
                            Access to this instructional video module is restricted. Please confirm your course enrollment with the portal administrator.
                        </p>
                        <a href="./my_courses.php" class="btn btn-primary rounded-3 px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Return to My Courses
                        </a>
                    </div>
                </div>
            <?php elseif ($lesson): ?>
                <!-- Video Theater Player Row -->
                <div class="row g-4">
                    <!-- Left: Big Video Player & Notes -->
                    <div class="col-12 col-lg-8">
                        <div class="card card-erp shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                            <div class="bg-black text-center position-relative">
                                <video class="w-100" style="max-height: 480px; object-fit: contain;" controls autoplay>
                                    <source src="./coursevideo/<?= htmlspecialchars($lesson['videos']); ?>" type="video/mp4">
                                    Your browser does not support HTML5 video playback.
                                </video>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold">
                                        Module #<?= $lesson['course_id']; ?>
                                    </span>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-circle-check me-1"></i> Active Curriculum
                                    </span>
                                </div>

                                <h3 class="fw-bold text-dark mb-2"><?= htmlspecialchars($lesson['title']); ?></h3>
                                <p class="text-secondary mb-4">
                                    Follow along closely and take practical notes as you progress through this module.
                                </p>

                                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark small">Complete Lesson</div>
                                            <span class="text-muted" style="font-size: 0.75rem;">Mark as completed to update your curriculum progress</span>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-success btn-sm rounded-3 px-3" onclick="showToast('success', 'Progress Saved', 'Lesson marked as completed!');">
                                        Mark Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Playlist Sidebar -->
                    <div class="col-12 col-lg-4">
                        <div class="card card-erp shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header bg-white border-bottom py-3 px-4 rounded-top-4">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-list-check me-2 text-primary"></i>Course Playlist
                                </h5>
                                <span class="text-secondary small">Click any module to stream</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush rounded-bottom-4">
                                    <?php if ($playlist_query && mysqli_num_rows($playlist_query) > 0): ?>
                                        <?php $p_idx = 1; while ($item = mysqli_fetch_assoc($playlist_query)):
                                            $is_active = ($item['course_id'] == $lesson['course_id']);
                                        ?>
                                        <a href="./watch_course.php?id=<?= $item['course_id']; ?>" class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3 <?= $is_active ? 'bg-primary bg-opacity-10 border-start border-4 border-primary' : ''; ?>">
                                            <div class="rounded-3 <?= $is_active ? 'bg-primary text-white' : 'bg-light text-secondary'; ?> d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; flex-shrink: 0;">
                                                <?= $p_idx; ?>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-semibold text-dark text-truncate <?= $is_active ? 'text-primary' : ''; ?>">
                                                    <?= htmlspecialchars($item['title']); ?>
                                                </div>
                                                <span class="text-secondary small">
                                                    <i class="fas fa-play fa-xs me-1"></i> Part <?= $p_idx; ?>
                                                </span>
                                            </div>
                                            <?php if ($is_active): ?>
                                                <span class="badge bg-primary rounded-pill px-2 py-1">Playing</span>
                                            <?php endif; ?>
                                        </a>
                                        <?php $p_idx++; endwhile; ?>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-muted">No additional lessons found.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-erp shadow-sm border-0 rounded-4 text-center py-5">
                    <i class="fas fa-film fa-3x text-secondary opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark">Lesson not found</h5>
                    <p class="text-secondary small mb-3">The requested video course module is not available.</p>
                    <a href="./my_courses.php" class="btn btn-primary rounded-3 px-4 mx-auto">Return to My Courses</a>
                </div>
            <?php endif; ?>

        </div> <!-- end container-fluid -->
        <?php include("footer.php"); ?>
    </div> <!-- end page-content-wrapper -->
</div> <!-- end wrapper -->
