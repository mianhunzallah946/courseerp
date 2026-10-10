<?php
session_start();
include("./connection.php");

// Admin authentication check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./login.php");
    exit();
}

$msg = "";
$msg_type = "";

// ✅ Fetch existing course titles
$courses = mysqli_query($connec, "SELECT DISTINCT title FROM course ORDER BY title ASC");

// ✅ Handle Form Submission
if (isset($_POST['submit'])) {
    $status = mysqli_real_escape_string($connec, $_POST['status'] ?? 'public');
    $videos = $_FILES['video']['name'] ?? [];
    $title = '';

    if (isset($_POST['title_source']) && $_POST['title_source'] === 'new') {
        $title = trim(mysqli_real_escape_string($connec, $_POST['new_title'] ?? ''));
        if (empty($title)) {
            $msg = "Course title is required.";
            $msg_type = "danger";
        } else {
            $check = mysqli_query($connec, "SELECT title FROM course WHERE TRIM(LOWER(title)) = TRIM(LOWER('$title'))");
            if ($check && mysqli_num_rows($check) > 0) {
                $msg = "A course with this title already exists. Please choose another title or select it from existing courses.";
                $msg_type = "warning";
                $title = '';
            }
        }
    } else {
        $title = trim(mysqli_real_escape_string($connec, $_POST['existing_title'] ?? ''));
        if (empty($title)) {
            $msg = "Please select an existing course or choose to add a new course title.";
            $msg_type = "danger";
        }
    }

    if (!empty($title) && !empty($videos[0])) {
        $allowed = ['mp4', 'mov', 'mkv', 'webm', 'avi', 'wmv'];
        $uploaded = [];

        if (!is_dir("./coursevideo/")) {
            mkdir("./coursevideo/", 0777, true);
        }

        foreach ($videos as $key => $video) {
            if (empty($video)) continue;
            $ext = strtolower(pathinfo($video, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $videoname = rand(1000, 999999) . "_" . time() . "." . $ext;
                $sql = "INSERT INTO `course` (`title`, `videos`, `status`) VALUES ('$title', '$videoname', '$status')";
                $run = mysqli_query($connec, $sql);
                if ($run) {
                    move_uploaded_file($_FILES['video']['tmp_name'][$key], "./coursevideo/" . $videoname);
                    $uploaded[] = $videoname;
                }
            }
        }

        if (!empty($uploaded)) {
            $msg = "✅ Course videos uploaded successfully! (" . count($uploaded) . " video files processed).";
            $msg_type = "success";
            // Refresh courses dropdown
            $courses = mysqli_query($connec, "SELECT DISTINCT title FROM course ORDER BY title ASC");
        } else {
            $msg = "❌ No valid video files were uploaded. Allowed formats: MP4, MOV, MKV, WEBM, AVI, WMV.";
            $msg_type = "danger";
        }
    }
}
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
                    <h1 class="h3 fw-bold text-dark mb-1">Add New Course</h1>
                    <p class="text-secondary small mb-0">Upload instructional video content and configure course visibility</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="./manage_courses.php" class="btn btn-outline-secondary rounded-3 px-3">
                        <i class="fas fa-book-open me-2"></i>View All Courses
                    </a>
                </div>
            </div>

            <!-- Feedback Alert -->
            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $msg_type; ?> alert-dismissible fade show rounded-3 shadow-sm mb-4 d-flex align-items-center justify-content-between" role="alert">
                    <div>
                        <?= $msg; ?>
                    </div>
                    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Global Portal Card Form -->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="card card-erp shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-bottom py-3 px-4 rounded-top-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-file-video fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Course Upload & Details</h5>
                                    <span class="text-secondary small">Fill in the fields below to publish a course module</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <form method="post" enctype="multipart/form-data" id="addCourseForm">
                                
                                <div class="row g-4">
                                    <!-- Course Title Selection / Creation -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="title_source">
                                            Course Title <span class="text-danger">*</span>
                                        </label>
                                        <select name="title_source" id="title_source" class="form-select" required>
                                            <option value="">-- Choose Option --</option>
                                            <option value="new" selected>➕ Create New Course Title</option>
                                            <?php if ($courses && mysqli_num_rows($courses) > 0): ?>
                                                <optgroup label="Or Append to Existing Course:">
                                                    <?php mysqli_data_seek($courses, 0); while($crow = mysqli_fetch_assoc($courses)): ?>
                                                        <option value="existing" data-title="<?= htmlspecialchars($crow['title']); ?>">
                                                            <?= htmlspecialchars($crow['title']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </optgroup>
                                            <?php endif; ?>
                                        </select>
                                        <input type="hidden" name="existing_title" id="existing_title_val" value="">
                                        <div class="form-text">Choose whether to create a new course title or append videos to an existing one.</div>
                                    </div>

                                    <!-- Visibility / Status -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="status">
                                            Visibility / Access <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" id="status" class="form-select" required>
                                            <option value="public">🌍 Public (Open Access)</option>
                                            <option value="private" selected>🔒 Curriculum Restricted (Enrolled Students Only)</option>
                                        </select>
                                        <div class="form-text">Controls accessibility for uploaded course video modules.</div>
                                    </div>

                                    <!-- New Course Title Input -->
                                    <div class="col-12" id="newTitleWrapper">
                                        <label class="form-label" for="new_title">
                                            New Course Title <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted">
                                                <i class="fas fa-heading"></i>
                                            </span>
                                            <input type="text" name="new_title" id="new_title" class="form-control border-start-0 ps-0" placeholder="e.g. Master Full-Stack Web Development" required>
                                        </div>
                                    </div>

                                    <!-- Video File Upload Input -->
                                    <div class="col-12">
                                        <label class="form-label" for="course_videos">
                                            Course Video Files <span class="text-danger">*</span>
                                        </label>
                                        <div class="p-3 border rounded-3 bg-light">
                                            <input class="form-control bg-white" type="file" name="video[]" id="course_videos" multiple required accept="video/mp4,video/mkv,video/webm,video/avi,video/quicktime">
                                            <div class="d-flex align-items-center justify-content-between mt-2 text-secondary small">
                                                <span><i class="fas fa-info-circle me-1 text-primary"></i> Hold <strong>Ctrl / Shift</strong> to select multiple lesson videos at once.</span>
                                                <span class="badge bg-secondary-subtle text-secondary">MP4, MKV, WEBM, MOV, AVI</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-12 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                                        <a href="./dashboard.php" class="btn btn-outline-secondary rounded-3 px-4">
                                            Cancel
                                        </a>
                                        <button type="submit" name="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                            <i class="fas fa-cloud-arrow-up me-2"></i>Upload Course
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end container-fluid -->
        <?php include("footer.php"); ?>
    </div> <!-- end page-content-wrapper -->
</div> <!-- end wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleSourceSelect = document.getElementById('title_source');
    const newTitleWrapper   = document.getElementById('newTitleWrapper');
    const newTitleInput     = document.getElementById('new_title');
    const existingTitleVal  = document.getElementById('existing_title_val');

    function handleTitleSourceChange() {
        const selectedOption = titleSourceSelect.options[titleSourceSelect.selectedIndex];
        if (titleSourceSelect.value === 'new') {
            newTitleWrapper.style.display = 'block';
            newTitleInput.required = true;
            existingTitleVal.value = '';
        } else if (titleSourceSelect.value === 'existing') {
            newTitleWrapper.style.display = 'none';
            newTitleInput.required = false;
            existingTitleVal.value = selectedOption.getAttribute('data-title') || '';
        } else {
            newTitleWrapper.style.display = 'none';
            newTitleInput.required = false;
            existingTitleVal.value = '';
        }
    }

    titleSourceSelect.addEventListener('change', handleTitleSourceChange);
    handleTitleSourceChange();
});
</script>