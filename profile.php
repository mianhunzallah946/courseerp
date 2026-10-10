<?php
session_start();

// Authentication Check
if (!isset($_SESSION['email'])) {
    header("Location: ./login.php");
    exit();
}

include("./connection.php");

$aemail = $_SESSION['email'];
$usql   = "SELECT * FROM `login` WHERE `email`='$aemail' LIMIT 1";
$urun   = mysqli_query($connec, $usql);
$ufet   = ($urun && mysqli_num_rows($urun) > 0) ? mysqli_fetch_assoc($urun) : null;

if (!$ufet) {
    header("Location: ./logout.php");
    exit();
}

$msg = "";
$msg_type = "";

// Handle Profile & Password Update
if (isset($_POST['update_profile'])) {
    $fname = trim(mysqli_real_escape_string($connec, $_POST['fname'] ?? ''));
    $lname = trim(mysqli_real_escape_string($connec, $_POST['lname'] ?? ''));
    $email = trim(mysqli_real_escape_string($connec, $_POST['email'] ?? ''));
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($fname) || empty($email)) {
        $msg = "First Name and Email are required fields.";
        $msg_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Please enter a valid email address.";
        $msg_type = "danger";
    } else {
        // Check if email already taken by someone else
        $check_email = mysqli_query($connec, "SELECT user_id FROM `login` WHERE `email`='$email' AND `user_id` != '{$ufet['user_id']}'");
        if ($check_email && mysqli_num_rows($check_email) > 0) {
            $msg = "The email address '$email' is already registered with another account.";
            $msg_type = "danger";
        } else {
            $updates = [];
            
            if ($fname !== $ufet['fname']) {
                $updates[] = "`fname`='$fname'";
            }
            if ($lname !== $ufet['lname']) {
                $updates[] = "`lname`='$lname'";
            }
            if ($email !== $ufet['email']) {
                $updates[] = "`email`='$email'";
            }

            // Password update check
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $msg = "New password and confirmation password do not match.";
                    $msg_type = "danger";
                } elseif (strlen($new_password) < 4) {
                    $msg = "Password must be at least 4 characters long.";
                    $msg_type = "danger";
                } else {
                    $escaped_pwd = mysqli_real_escape_string($connec, $new_password);
                    $updates[] = "`password`='$escaped_pwd'";
                    $updates[] = "`confirmpassword`='$escaped_pwd'";
                }
            }

            if (empty($msg)) {
                if (!empty($updates)) {
                    $update_sql = "UPDATE `login` SET " . implode(", ", $updates) . " WHERE `user_id`='{$ufet['user_id']}'";
                    $run_update = mysqli_query($connec, $update_sql);

                    if ($run_update) {
                        $msg = "Profile updated successfully!";
                        $msg_type = "success";
                        
                        // Update session variables
                        $_SESSION['email'] = $email;
                        $_SESSION['name']  = trim($fname . ' ' . $lname);
                        
                        // Refresh user data
                        $urun = mysqli_query($connec, "SELECT * FROM `login` WHERE `user_id`='{$ufet['user_id']}'");
                        $ufet = mysqli_fetch_assoc($urun);
                    } else {
                        $msg = "An error occurred while updating your profile. Please try again.";
                        $msg_type = "danger";
                    }
                } else {
                    $msg = "No changes were made.";
                    $msg_type = "info";
                }
            }
        }
    }
}

$fullname = trim(($ufet['fname'] ?? '') . ' ' . ($ufet['lname'] ?? ''));
$initials = strtoupper(substr($ufet['fname'] ?? 'U', 0, 1) . substr($ufet['lname'] ?? '', 0, 1));
$user_role = strtolower($ufet['role'] ?? 'student');
$is_admin  = ($user_role === 'admin');

// Fetch additional contextual stats
if ($is_admin) {
    $course_count_res = mysqli_query($connec, "SELECT COUNT(*) AS total FROM `course`");
    $total_courses = ($course_count_res) ? mysqli_fetch_assoc($course_count_res)['total'] : 0;
    
    $student_count_res = mysqli_query($connec, "SELECT COUNT(*) AS total FROM `login` WHERE `role`='student'");
    $total_students = ($student_count_res) ? mysqli_fetch_assoc($student_count_res)['total'] : 0;
} else {
    $student_course = trim($ufet['course_name'] ?? '');
    $lessons_res = mysqli_query($connec, "SELECT COUNT(*) AS total FROM `course` WHERE TRIM(LOWER(title)) = TRIM(LOWER('$student_course'))");
    $total_lessons = ($lessons_res) ? mysqli_fetch_assoc($lessons_res)['total'] : 0;
}
?>
<div class="d-flex" id="wrapper">
    <!-- Fixed Sidebar -->
    <?php include("sidebar.php"); ?>
    
    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="w-100 bg-light">
        <?php include("header.php"); ?>
        
        <div class="container-fluid py-4 px-3 px-md-4">

            <!-- Breadcrumb & Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1">Account & Profile Settings</h1>
                    <p class="text-secondary small mb-0">Manage your personal credentials, contact details, and account security</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="./dashboard.php" class="btn btn-outline-secondary rounded-3 px-3">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Feedback Alert -->
            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $msg_type; ?> alert-dismissible fade show rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($msg_type === 'success'): ?>
                            <i class="fas fa-circle-check fs-5 text-success"></i>
                        <?php elseif ($msg_type === 'danger'): ?>
                            <i class="fas fa-circle-exclamation fs-5 text-danger"></i>
                        <?php else: ?>
                            <i class="fas fa-circle-info fs-5 text-info"></i>
                        <?php endif; ?>
                        <span class="fw-medium"><?= htmlspecialchars($msg); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Responsive 2-Column Grid -->
            <div class="row g-4">
                
                <!-- Left Column: User Profile Summary Card -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center p-4 bg-white h-100">
                        <!-- Avatar with Initials -->
                        <div class="position-relative d-inline-block mx-auto mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm bg-primary" style="width: 88px; height: 88px; font-size: 2rem;">
                                <?= htmlspecialchars(!empty($initials) ? $initials : 'U'); ?>
                            </div>
                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-2 border-white rounded-circle" style="width: 16px; height: 16px;" title="Online Status"></span>
                        </div>

                        <!-- Name & Email -->
                        <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars(!empty($fullname) ? $fullname : 'User'); ?></h4>
                        <p class="text-secondary small mb-3"><?= htmlspecialchars($ufet['email']); ?></p>

                        <!-- Role Badge -->
                        <div class="mb-4">
                            <?php if ($is_admin): ?>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fas fa-shield-halved me-1"></i> System Administrator
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-semibold">
                                    <i class="fas fa-user-graduate me-1"></i> Student Learner
                                </span>
                            <?php endif; ?>
                        </div>

                        <hr class="my-3 text-secondary opacity-25">

                        <!-- Contextual Stats -->
                        <div class="text-start">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-secondary small fw-medium">Account ID</span>
                                <span class="fw-semibold text-dark small">#<?= $ufet['user_id']; ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-secondary small fw-medium">Account Status</span>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 fw-semibold small">
                                    <i class="fas fa-circle-check me-1"></i> Active
                                </span>
                            </div>

                            <?php if ($is_admin): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-secondary small fw-medium">Total Courses</span>
                                    <span class="fw-semibold text-dark small"><?= $total_courses; ?> Modules</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-secondary small fw-medium">Students Managed</span>
                                    <span class="fw-semibold text-primary small"><?= $total_students; ?> Enrolled</span>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-secondary small fw-medium">Enrolled Course</span>
                                    <span class="fw-semibold text-dark small text-truncate" style="max-width: 160px;" title="<?= htmlspecialchars($ufet['course_name'] ?? 'General'); ?>">
                                        <?= htmlspecialchars(!empty($ufet['course_name']) ? $ufet['course_name'] : 'General'); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-secondary small fw-medium">Available Lessons</span>
                                    <span class="fw-semibold text-primary small"><?= $total_lessons; ?> Lessons</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Link -->
                        <div class="mt-4 pt-2">
                            <?php if ($is_admin): ?>
                                <a href="./manageusers.php" class="btn btn-outline-primary btn-sm rounded-3 w-100 py-2">
                                    <i class="fas fa-users-gear me-1"></i> Manage All Users
                                </a>
                            <?php else: ?>
                                <a href="./my_courses.php" class="btn btn-outline-primary btn-sm rounded-3 w-100 py-2">
                                    <i class="fas fa-circle-play me-1"></i> Go to My Courses
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings Card (Edit Profile & Security) -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        
                        <!-- Card Header -->
                        <div class="card-header bg-white border-bottom py-3 px-4 rounded-top-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="fas fa-user-pen"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Edit Profile & Security</h5>
                                    <span class="text-secondary small">Update your personal details and change your account password</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body with Form -->
                        <div class="card-body p-4 p-md-4">
                            <form method="post" action="">
                                
                                <!-- Section 1: Personal Details -->
                                <h6 class="text-uppercase fw-bold text-secondary small mb-3 tracking-wide">
                                    <i class="fas fa-id-card me-1 text-primary"></i> Personal Information
                                </h6>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-sm-6">
                                        <label for="fname" class="form-label fw-semibold" style="color: #475569;">
                                            First Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control rounded-3" id="fname" name="fname" 
                                               value="<?= htmlspecialchars($ufet['fname'] ?? ''); ?>" required
                                               style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="lname" class="form-label fw-semibold" style="color: #475569;">
                                            Last Name
                                        </label>
                                        <input type="text" class="form-control rounded-3" id="lname" name="lname" 
                                               value="<?= htmlspecialchars($ufet['lname'] ?? ''); ?>"
                                               style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="email" class="form-label fw-semibold" style="color: #475569;">
                                            Email Address <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-color: #cbd5e1;">
                                                <i class="fas fa-envelope fa-sm"></i>
                                            </span>
                                            <input type="email" class="form-control rounded-end-3 border-start-0 ps-0" id="email" name="email" 
                                                   value="<?= htmlspecialchars($ufet['email'] ?? ''); ?>" required
                                                   style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-semibold" style="color: #475569;">
                                            Assigned Role
                                        </label>
                                        <input type="text" class="form-control bg-light rounded-3 text-secondary" 
                                               value="<?= ucfirst($ufet['role'] ?? 'Student'); ?>" disabled
                                               style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                    </div>

                                    <?php if (!$is_admin): ?>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold" style="color: #475569;">
                                            Enrolled Course Curriculum
                                        </label>
                                        <input type="text" class="form-control bg-light rounded-3 text-secondary" 
                                               value="<?= htmlspecialchars(!empty($ufet['course_name']) ? $ufet['course_name'] : 'General Curriculum'); ?>" disabled
                                               style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                        <div class="form-text text-muted small">Course allocations are managed by your institution administrator.</div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <hr class="my-4 text-secondary opacity-25">

                                <!-- Section 2: Security & Password -->
                                <h6 class="text-uppercase fw-bold text-secondary small mb-3 tracking-wide">
                                    <i class="fas fa-lock me-1 text-primary"></i> Change Password
                                </h6>
                                <p class="text-secondary small mb-3">Leave blank if you do not wish to change your current password.</p>

                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-sm-6">
                                        <label for="new_password" class="form-label fw-semibold" style="color: #475569;">
                                            New Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control rounded-start-3" id="new_password" name="new_password" 
                                                   placeholder="Enter new password" autocomplete="new-password"
                                                   style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                            <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleNewPwd" style="border-color: #cbd5e1;">
                                                <i class="fas fa-eye fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="confirm_password" class="form-label fw-semibold" style="color: #475569;">
                                            Confirm New Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control rounded-start-3" id="confirm_password" name="confirm_password" 
                                                   placeholder="Confirm new password" autocomplete="new-password"
                                                   style="border-color: #cbd5e1; padding: 0.6rem 0.85rem;">
                                            <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleConfPwd" style="border-color: #cbd5e1;">
                                                <i class="fas fa-eye fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                                    <a href="./dashboard.php" class="btn btn-light rounded-3 px-4 border">
                                        Cancel
                                    </a>
                                    <button type="submit" name="update_profile" class="btn btn-primary rounded-3 px-4 shadow-sm" style="background-color: #3b82f6; border-color: #3b82f6;">
                                        <i class="fas fa-floppy-disk me-1"></i> Save Changes
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

            </div> <!-- end row -->

        </div> <!-- end container-fluid -->
        
        <?php include("footer.php"); ?>
    </div> <!-- end page-content-wrapper -->
</div> <!-- end wrapper -->

<script>
// Toggle Password Visibility
function setupPasswordToggle(buttonId, inputId) {
    const btn = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    if (!btn || !input) return;

    btn.addEventListener('click', function() {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        }
    });
}

setupPasswordToggle('toggleNewPwd', 'new_password');
setupPasswordToggle('toggleConfPwd', 'confirm_password');
</script>
