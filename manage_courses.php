<?php
session_start();
include("./connection.php");

// Admin authentication check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./login.php");
    exit();
}

// Handle fast status toggle
if (isset($_POST['toggle_course_status'])) {
    $cid = intval($_POST['course_id']);
    $new_status = ($_POST['current_status'] === 'public') ? 'private' : 'public';
    mysqli_query($connec, "UPDATE course SET status='$new_status' WHERE course_id='$cid'");
    header("Location: ./manage_courses.php?toggled=1");
    exit();
}

// Handle quick edit
if (isset($_POST['update_course_title'])) {
    $cid = intval($_POST['course_id']);
    $new_title = mysqli_real_escape_string($connec, trim($_POST['title']));
    $new_status = mysqli_real_escape_string($connec, $_POST['status']);
    if (!empty($new_title)) {
        mysqli_query($connec, "UPDATE course SET title='$new_title', status='$new_status' WHERE course_id='$cid'");
    }
    header("Location: ./manage_courses.php?updated=1");
    exit();
}

// Fetch all courses
$courses_query = mysqli_query($connec, "SELECT * FROM course ORDER BY course_id DESC");
?>
<div class="d-flex" id="wrapper">
    <!-- Fixed Sidebar -->
    <?php include("sidebar.php"); ?>
    
    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="w-100 bg-light">
        <?php include("header.php"); ?>
        
        <div class="container-fluid px-4 py-4">

            <!-- Breadcrumb & Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1">Course Catalog Management</h1>
                    <p class="text-secondary small mb-0">Manage published course video lessons, curriculum access, and modules</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="./add_course.php" class="btn btn-primary rounded-3 px-3 shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> Upload New Course
                    </a>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="card card-erp shadow-sm border-0 rounded-4 mb-4 p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="position-relative">
                            <input type="text" id="courseCardSearch" class="form-control rounded-3 ps-4" placeholder="Search courses by title…">
                            <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="statusFilter" class="form-select rounded-3">
                            <option value="all">All Access Levels</option>
                            <option value="public">Public</option>
                            <option value="private">Curriculum Restricted</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-5 text-end text-secondary small">
                        Showing <?= mysqli_num_rows($courses_query); ?> total instructional video modules
                    </div>
                </div>
            </div>

            <!-- Course Grid -->
            <div class="row g-4" id="coursesGrid">
                <?php if ($courses_query && mysqli_num_rows($courses_query) > 0): ?>
                    <?php while ($c = mysqli_fetch_assoc($courses_query)): ?>
                    <div class="col-12 col-md-6 col-xl-4 course-item" data-title="<?= strtolower(htmlspecialchars($c['title'])); ?>" data-status="<?= $c['status']; ?>">
                        <div class="card card-erp shadow-sm border-0 rounded-4 overflow-hidden h-100">
                            <div class="bg-dark position-relative">
                                <video class="w-100" style="height: 200px; object-fit: cover;" controls>
                                    <source src="./coursevideo/<?= htmlspecialchars($c['videos']); ?>" type="video/mp4">
                                </video>
                                <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white">
                                    ID: #<?= $c['course_id']; ?>
                                </span>
                            </div>

                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <?php if ($c['status'] === 'public'): ?>
                                            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold">
                                                <i class="fas fa-eye me-1"></i> Public
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill fw-semibold">
                                                <i class="fas fa-lock me-1"></i> Curriculum Restricted
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($c['title']); ?></h5>
                                    <p class="text-secondary small mb-3">
                                        <i class="fas fa-file-video text-muted me-1"></i> <?= htmlspecialchars($c['videos']); ?>
                                    </p>
                                </div>

                                <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="course_id" value="<?= $c['course_id']; ?>">
                                        <input type="hidden" name="current_status" value="<?= $c['status']; ?>">
                                        <button type="submit" name="toggle_course_status" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <?= ($c['status'] === 'public') ? '🔒 Set Restricted' : '🌍 Set Public'; ?>
                                        </button>
                                    </form>

                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-light border rounded-3 text-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?= $c['course_id']; ?>" title="Edit Title">
                                            <i class="fas fa-pen-to-square text-primary"></i>
                                        </button>
                                        <a href="./deletecourse.php?delid=<?= $c['course_id']; ?>" class="btn btn-sm btn-light border rounded-3 text-danger" onclick="return confirm('Permanently remove this course video?');" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Course Modal -->
                        <div class="modal fade" id="editModal<?= $c['course_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <form method="post">
                                        <div class="modal-header border-bottom py-3 px-4">
                                            <h5 class="modal-title fw-bold text-dark">
                                                <i class="fas fa-pen-to-square text-primary me-2"></i>Edit Course Module
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="course_id" value="<?= $c['course_id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Course Title</label>
                                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($c['title']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Access Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="public" <?= ($c['status'] === 'public') ? 'selected' : ''; ?>>Public</option>
                                                    <option value="private" <?= ($c['status'] === 'private') ? 'selected' : ''; ?>>Curriculum Restricted</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-3 px-4">
                                            <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update_course_title" class="btn btn-primary rounded-3 px-4">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card card-erp shadow-sm border-0 rounded-4 text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-secondary opacity-50 mb-3"></i>
                            <h5 class="fw-bold text-dark">No courses uploaded yet</h5>
                            <p class="text-secondary small mb-3">Get started by creating your first course video module.</p>
                            <a href="./add_course.php" class="btn btn-primary rounded-3 px-4 mx-auto">
                                <i class="fas fa-plus me-1"></i> Add Course
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div> <!-- end container-fluid -->
        <?php include("footer.php"); ?>
    </div> <!-- end page-content-wrapper -->
</div> <!-- end wrapper -->

<script>
// Filter courses by title & access status
const searchInput  = document.getElementById('courseCardSearch');
const statusFilter = document.getElementById('statusFilter');

function filterCourses() {
    const q = searchInput.value.toLowerCase().trim();
    const st = statusFilter.value;
    const items = document.querySelectorAll('.course-item');

    items.forEach(item => {
        const title = item.getAttribute('data-title') || '';
        const itemStatus = item.getAttribute('data-status') || '';
        const matchQuery = title.includes(q);
        const matchStatus = (st === 'all') || (itemStatus === st);

        item.style.display = (matchQuery && matchStatus) ? '' : 'none';
    });
}

searchInput?.addEventListener('input', filterCourses);
statusFilter?.addEventListener('change', filterCourses);
</script>
