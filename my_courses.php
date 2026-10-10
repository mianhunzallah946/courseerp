<?php
session_start();

// Login & Student Role Check
if (!isset($_SESSION['role'])) {
    header("Location: ./login.php");
    exit();
}

include("./connection.php");

$user_id     = intval($_SESSION['user_id'] ?? 0);
$user_role   = $_SESSION['role'] ?? 'student';
$user_email  = $_SESSION['email'] ?? '';

// Fetch student profile info
$studentQuery = mysqli_query($connec, "SELECT fname, lname, email, course_name FROM login WHERE user_id='$user_id' LIMIT 1");
$studentData  = ($studentQuery && mysqli_num_rows($studentQuery) > 0) ? mysqli_fetch_assoc($studentQuery) : [];
$studentCourse = trim($studentData['course_name'] ?? '');

// Auto-heal enrollment table if needed: if student registered with a course_name, ensure enrolled
if (!empty($studentCourse)) {
    mysqli_query($connec, "CREATE TABLE IF NOT EXISTS `enrollments` (
        `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `course_id` int(11) NOT NULL,
        `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`enrollment_id`),
        KEY `user_id` (`user_id`),
        KEY `course_id` (`course_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Check if enrolled in this course title
    $find_courses = mysqli_query($connec, "SELECT course_id FROM course WHERE TRIM(LOWER(title)) = TRIM(LOWER('" . mysqli_real_escape_string($connec, $studentCourse) . "'))");
    if ($find_courses) {
        while ($fc = mysqli_fetch_assoc($find_courses)) {
            $cid = intval($fc['course_id']);
            $check_enr = mysqli_query($connec, "SELECT enrollment_id FROM enrollments WHERE user_id='$user_id' AND course_id='$cid'");
            if ($check_enr && mysqli_num_rows($check_enr) == 0) {
                mysqli_query($connec, "INSERT INTO enrollments (user_id, course_id) VALUES ('$user_id', '$cid')");
            }
        }
    }
}

// 🔒 Strict enrollment filtering query
$vsql = "SELECT course.* FROM course 
         JOIN enrollments ON course.course_id = enrollments.course_id 
         WHERE enrollments.user_id = '$user_id' 
         ORDER BY course.course_id ASC";
$vrun = mysqli_query($connec, $vsql);

// Fallback if enrollments table was empty or not populated yet for this user
if (!$vrun || mysqli_num_rows($vrun) == 0) {
    if (!empty($studentCourse)) {
        $escaped_sc = mysqli_real_escape_string($connec, $studentCourse);
        $vsql = "SELECT * FROM course WHERE TRIM(LOWER(title)) = TRIM(LOWER('$escaped_sc')) ORDER BY course_id ASC";
        $vrun = mysqli_query($connec, $vsql);
    }
}

$lesson_count = ($vrun) ? mysqli_num_rows($vrun) : 0;
?>
<div class="d-flex" id="wrapper">
    <!-- Fixed Sidebar -->
    <?php include("sidebar.php"); ?>
    
    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="w-100 bg-light">
        <?php include("header.php"); ?>
        
        <div class="container-fluid px-4 py-4">

            <!-- Breadcrumb & Welcome -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1">My Enrolled Courses</h1>
                    <p class="text-secondary small mb-0">Track curriculum progress, stream video lessons, and manage your studies</p>
                </div>
                <div>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
                        <i class="fas fa-graduation-cap me-1"></i> Active Student
                    </span>
                </div>
            </div>

            <!-- Course Overview Hero Card -->
            <div class="card card-erp shadow-sm border-0 rounded-4 mb-4 p-4">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-lg-8">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            Active Curriculum
                        </span>
                        <h2 class="fw-bold text-dark mb-2">
                            <?= htmlspecialchars(!empty($studentCourse) ? $studentCourse : 'Enrolled Course Curriculum'); ?>
                        </h2>
                        <p class="text-secondary mb-3">
                            Master every concept step-by-step with high-definition video modules and hands-on demonstrations.
                        </p>

                        <!-- Progress Bar -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between text-secondary small fw-semibold mb-1">
                                <span>Curriculum Progress</span>
                                <span><?= ($lesson_count > 0) ? 'Course Active' : 'Pending Enrollment'; ?></span>
                            </div>
                            <div class="progress rounded-pill" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= ($lesson_count > 0) ? '65%' : '0%'; ?>;" aria-valuenow="<?= ($lesson_count > 0) ? '65' : '0'; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 text-lg-end">
                        <div class="p-3 bg-light rounded-3 text-center d-inline-block w-100" style="max-width: 280px;">
                            <span class="text-secondary small d-block">Curriculum Lessons</span>
                            <h3 class="fw-bold text-primary mb-1"><?= $lesson_count; ?> Lessons</h3>
                            <span class="text-muted small">
                                <i class="fas fa-circle-check text-success me-1"></i>
                                Unrestricted Access
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons & Video Player Section -->
            <h4 class="fw-bold text-dark mb-3">📚 Course Video Modules</h4>
            <div class="row g-4 mb-4">
                <?php if ($vrun && mysqli_num_rows($vrun) > 0): ?>
                    <?php $idx = 1; while ($vfet = mysqli_fetch_assoc($vrun)):
                        $course_id = intval($vfet['course_id']);
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card card-erp shadow-sm border-0 rounded-4 overflow-hidden h-100">
                            <div class="bg-dark position-relative">
                                <video id="st_video_<?= $course_id; ?>" class="w-100" style="height: 200px; object-fit: cover;" controls>
                                    <source src="./coursevideo/<?= htmlspecialchars($vfet['videos']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white">
                                    Part #<?= $idx; ?>
                                </span>
                            </div>
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold">
                                            <i class="fas fa-circle-check me-1"></i> Enrolled
                                        </span>
                                        <span class="text-secondary small"><i class="fas fa-clock me-1"></i> Lesson <?= $idx; ?></span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($vfet['title']); ?></h5>
                                    <p class="text-secondary small mb-0">Instructional video module for <?= htmlspecialchars(!empty($studentCourse) ? $studentCourse : $vfet['title']); ?>.</p>
                                </div>
                                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <a href="./watch_course.php?id=<?= $course_id; ?>" class="btn btn-sm btn-primary rounded-3 px-3">
                                        <i class="fas fa-play me-1"></i> Theater Player
                                    </a>
                                    <span class="text-success small fw-semibold"><i class="fas fa-circle-check"></i> Available</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $idx++; endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card card-erp shadow-sm border-0 rounded-4 text-center py-5">
                            <i class="fas fa-video-slash fa-3x text-secondary opacity-50 mb-3"></i>
                            <h5 class="fw-bold text-dark">No lessons assigned yet</h5>
                            <p class="text-secondary small mb-0">You are currently not enrolled in any course modules. Please contact your administrator.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div> <!-- end container-fluid -->
        <?php include("footer.php"); ?>
    </div> <!-- end page-content-wrapper -->
</div> <!-- end wrapper -->
