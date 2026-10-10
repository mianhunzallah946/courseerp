<?php
session_start();
include("./connection.php");

// ✅ Role Check
if (!isset($_SESSION['role'])) {
    header("Location: ./login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = intval($_SESSION['user_id'] ?? 0);
?>
<div class="d-flex" id="wrapper">
    <!-- Fixed Sidebar -->
    <?php include("sidebar.php"); ?>
    
    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="w-100 bg-light">
        <?php include("header.php"); ?>
        
        <div class="container-fluid px-4 py-4">

<?php
// =========================================================================
//                             ADMIN DASHBOARD
// =========================================================================
if ($role === 'admin') {
    // 🔹 Compute live metrics from database
    $total_courses_res = mysqli_query($connec, "SELECT COUNT(*) as total FROM course");
    $total_courses = ($total_courses_res) ? mysqli_fetch_assoc($total_courses_res)['total'] : 0;

    $public_courses_res = mysqli_query($connec, "SELECT COUNT(*) as total FROM course WHERE status='public'");
    $public_courses = ($public_courses_res) ? mysqli_fetch_assoc($public_courses_res)['total'] : 0;

    $private_courses_res = mysqli_query($connec, "SELECT COUNT(*) as total FROM course WHERE status='private'");
    $private_courses = ($private_courses_res) ? mysqli_fetch_assoc($private_courses_res)['total'] : 0;

    $students_res = mysqli_query($connec, "SELECT COUNT(*) as total FROM login WHERE role='student'");
    $students_enrolled = ($students_res) ? mysqli_fetch_assoc($students_res)['total'] : 0;

    // Distinct course programs offered
    $prog_res = mysqli_query($connec, "SELECT COUNT(DISTINCT title) as total FROM course");
    $total_programs = ($prog_res) ? mysqli_fetch_assoc($prog_res)['total'] : 0;

    // Recent students / enrollments query
    $recent_students = mysqli_query($connec, "SELECT user_id, fname, lname, email, role, course_name FROM login WHERE role='student' ORDER BY user_id DESC LIMIT 8");
?>
            <!-- 🔹 Page Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1">Admin Executive Dashboard</h1>
                    <p class="text-secondary small mb-0">Overview of courses, active student enrollments, and academic curriculum</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="./add_course.php" class="btn btn-primary rounded-3 px-3 shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> Add Course
                    </a>
                    <a href="./manageusers.php" class="btn btn-outline-secondary rounded-3 px-3">
                        <i class="fas fa-users me-1"></i> Manage Users
                    </a>
                </div>
            </div>

            <!-- 🔹 4 KPI / Metrics Cards -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Total Courses -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-erp shadow-sm border-0 rounded-4 h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-secondary small fw-semibold text-uppercase">Total Video Lessons</span>
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-video fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold text-dark mb-1"><?= $total_courses; ?></h2>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-primary-subtle text-primary fw-semibold">
                                Published Modules
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Active Students -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-erp shadow-sm border-0 rounded-4 h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-secondary small fw-semibold text-uppercase">Enrolled Students</span>
                            <div class="rounded-3 bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-user-graduate fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold text-dark mb-1"><?= $students_enrolled; ?></h2>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-success-subtle text-success fw-semibold">Active Accounts</span>
                            <span class="text-secondary small">Across all programs</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Distinct Curriculums -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-erp shadow-sm border-0 rounded-4 h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-secondary small fw-semibold text-uppercase">Course Programs</span>
                            <div class="rounded-3 bg-info bg-opacity-10 text-info p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-book-bookmark fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold text-dark mb-1"><?= $total_programs; ?></h2>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-info-subtle text-info fw-semibold"><?= $private_courses; ?> Restricted</span>
                            <span class="badge bg-light text-secondary border"><?= $public_courses; ?> Open</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Learning Engagement -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-erp shadow-sm border-0 rounded-4 h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-secondary small fw-semibold text-uppercase">Curriculum Access</span>
                            <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-circle-check fa-lg"></i>
                            </div>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">100%</h2>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-success-subtle text-success fw-semibold">Open Learning</span>
                            <span class="text-secondary small">Direct Enrollment</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔹 Recent Enrollments Table -->
            <div class="card card-erp shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 rounded-top-4">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Recent Student Enrollments</h5>
                        <span class="text-secondary small">Students actively registered across curriculum courses</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="position-relative">
                            <input type="text" id="dashSearch" class="form-control form-control-sm ps-4 rounded-3" placeholder="Filter table…" style="width: 200px;">
                            <i class="fas fa-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem;"></i>
                        </div>
                        <a href="./manageusers.php" class="btn btn-outline-primary btn-sm rounded-3">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center" id="dashTable">
                            <thead class="table-light text-uppercase small text-secondary">
                                <tr>
                                    <th class="ps-4 text-start">Student Name</th>
                                    <th class="text-start">Email Address</th>
                                    <th>Enrolled Course</th>
                                    <th>Enrollment Status</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_students && mysqli_num_rows($recent_students) > 0): ?>
                                    <?php while ($st = mysqli_fetch_assoc($recent_students)): ?>
                                    <tr>
                                        <td class="ps-4 text-start">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr($st['fname'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($st['fname'] . ' ' . $st['lname']); ?></span>
                                                    <span class="text-muted small">ID: #<?= $st['user_id']; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-start text-secondary">
                                            <?= htmlspecialchars($st['email']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                                <i class="fas fa-graduation-cap me-1 text-primary"></i>
                                                <?= htmlspecialchars(!empty($st['course_name']) ? $st['course_name'] : 'General'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i> Active
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="./manageusers.php" class="btn btn-sm btn-light border rounded-3 px-3">
                                                <i class="fas fa-user-pen text-primary me-1"></i> Manage
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No student enrollments found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<?php
// =========================================================================
//                             STUDENT DASHBOARD
// =========================================================================
} elseif ($role === 'student') {
    // Current student data
    $courseQuery = mysqli_query($connec, "SELECT course_name FROM login WHERE user_id='$user_id' LIMIT 1");
    $studentCourse = ($courseQuery && mysqli_num_rows($courseQuery) > 0)
        ? trim(mysqli_fetch_assoc($courseQuery)['course_name'])
        : "";

    $vsql = "SELECT course.* FROM course 
             JOIN enrollments ON course.course_id = enrollments.course_id 
             WHERE enrollments.user_id = '$user_id' 
             ORDER BY course.course_id DESC";
    $vrun = mysqli_query($connec, $vsql);

    if (!$vrun || mysqli_num_rows($vrun) == 0) {
        if (!empty($studentCourse)) {
            $escaped_sc = mysqli_real_escape_string($connec, $studentCourse);
            $vsql = "SELECT * FROM course WHERE TRIM(LOWER(title)) = TRIM(LOWER('$escaped_sc')) ORDER BY course_id DESC";
            $vrun = mysqli_query($connec, $vsql);
        }
    }

    $total_lessons = ($vrun) ? mysqli_num_rows($vrun) : 0;
?>
            <!-- 🔹 Student Welcome Header -->
            <div class="card card-erp shadow-sm border-0 rounded-4 mb-4 p-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important; color: #ffffff;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge bg-primary px-3 py-1 rounded-pill mb-2">Student Learning Space</span>
                        <h2 class="fw-bold mb-2 text-white">Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Learner'); ?>! 👋</h2>
                        <p class="text-white-50 mb-3">
                            You are currently enrolled in <strong><?= htmlspecialchars(!empty($studentCourse) ? $studentCourse : 'General Curriculum'); ?></strong>. Keep making steady progress!
                        </p>
                        <div>
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                                <i class="fas fa-check-circle me-1"></i> Active Enrolled Student
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-center text-md-end mt-3 mt-md-0 d-none d-md-block">
                        <i class="fas fa-graduation-cap text-primary" style="font-size: 5rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>

            <!-- 🔹 Enrolled Course Modules -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-bold text-dark mb-0">🎥 Available Video Modules</h4>
                <span class="text-secondary small"><?= $total_lessons; ?> lesson(s) in curriculum</span>
            </div>

            <div class="row g-4 mb-4">
                <?php if ($vrun && mysqli_num_rows($vrun) > 0): ?>
                    <?php while ($vfet = mysqli_fetch_assoc($vrun)):
                        $course_id = intval($vfet['course_id']);
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card card-erp shadow-sm border-0 rounded-4 overflow-hidden h-100">
                            <div class="bg-dark text-center position-relative">
                                <video id="video_<?= $course_id; ?>" class="w-100" style="height: 220px; object-fit: cover;" controls>
                                    <source src="./coursevideo/<?= htmlspecialchars($vfet['videos']); ?>" type="video/mp4">
                                    Your browser does not support video playback.
                                </video>
                            </div>
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                                            Lesson #<?= $vfet['course_id']; ?>
                                        </span>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                            <i class="fas fa-circle-check me-1"></i> Enrolled
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($vfet['title']); ?></h5>
                                    <p class="text-secondary small">Comprehensive instructional lesson module.</p>
                                </div>
                                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="./watch_course.php?id=<?= $course_id; ?>" class="btn btn-sm btn-primary rounded-3 px-3">
                                        <i class="fas fa-play me-1"></i> Watch Lesson
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card card-erp shadow-sm border-0 rounded-4 text-center py-5">
                            <i class="fas fa-film fa-3x text-secondary opacity-50 mb-3"></i>
                            <h5 class="fw-bold text-dark">No lessons available yet</h5>
                            <p class="text-secondary small mb-0">Your instructor will publish course video modules shortly.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

<?php } ?>

        </div> <!-- end container-fluid -->
        <?php include("footer.php"); ?>
    </div> <!-- end page-content-wrapper -->
</div> <!-- end wrapper -->

<script>
// Filter for recent enrollments table
document.getElementById('dashSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('#dashTable tbody tr');
    rows.forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
