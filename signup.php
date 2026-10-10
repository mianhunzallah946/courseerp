<?php
include("./connection.php");
session_start();

// Fetch all uploaded courses from course table
$courses = [];
$courseQuery = "SELECT DISTINCT title FROM course ORDER BY title ASC";
$courseResult = mysqli_query($connec, $courseQuery);
if ($courseResult) {
    while ($row = mysqli_fetch_assoc($courseResult)) {
        $courses[] = $row['title'];
    }
}

$msg = "";
$msg_type = "";

if (isset($_POST['signup'])) {
    $fname = mysqli_real_escape_string($connec, trim($_POST['fname'] ?? ''));
    $lname = mysqli_real_escape_string($connec, trim($_POST['lname'] ?? ''));
    $email = mysqli_real_escape_string($connec, trim($_POST['email'] ?? ''));
    $password = mysqli_real_escape_string($connec, trim($_POST['password'] ?? ''));
    $confirmpassword = mysqli_real_escape_string($connec, trim($_POST['confirmpassword'] ?? ''));
    $role = mysqli_real_escape_string($connec, trim($_POST['role'] ?? 'student'));
    $course_name = mysqli_real_escape_string($connec, trim($_POST['course_name'] ?? ''));

    if (empty($fname) || empty($email) || empty($password)) {
        $msg = "Please fill in all required fields.";
        $msg_type = "warning";
    } elseif ($password !== $confirmpassword) {
        $msg = "Password and confirm password do not match.";
        $msg_type = "danger";
    } elseif ($role === 'student' && empty($course_name)) {
        $msg = "Please select a course to enroll in.";
        $msg_type = "warning";
    } else {
        $check = "SELECT user_id FROM `login` WHERE email='$email' LIMIT 1";
        $result = mysqli_query($connec, $check);

        if ($result && mysqli_num_rows($result) > 0) {
            $msg = "This email address is already registered.";
            $msg_type = "danger";
        } else {
            $sql = "INSERT INTO `login`(`fname`, `lname`, `email`, `password`, `confirmpassword`, `role`, `course_name`, `membership`) 
                    VALUES ('$fname','$lname','$email','$password','$confirmpassword','$role','$course_name', 'paid')";
            $run = mysqli_query($connec, $sql);

            if ($run) {
                $new_uid = mysqli_insert_id($connec);
                
                // Enroll student into the course modules automatically
                if ($role === 'student' && !empty($course_name)) {
                    mysqli_query($connec, "CREATE TABLE IF NOT EXISTS `enrollments` (
                        `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
                        `user_id` int(11) NOT NULL,
                        `course_id` int(11) NOT NULL,
                        `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        PRIMARY KEY (`enrollment_id`),
                        KEY `user_id` (`user_id`),
                        KEY `course_id` (`course_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                    $find_c = mysqli_query($connec, "SELECT course_id FROM course WHERE TRIM(LOWER(title)) = TRIM(LOWER('$course_name'))");
                    if ($find_c) {
                        while ($fc = mysqli_fetch_assoc($find_c)) {
                            $cid = intval($fc['course_id']);
                            mysqli_query($connec, "INSERT INTO enrollments (user_id, course_id) VALUES ('$new_uid', '$cid')");
                        }
                    }
                }

                header("Location: ./login.php?registered=1");
                exit();
            } else {
                $msg = "Something went wrong during registration. Please try again.";
                $msg_type = "danger";
            }   
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign Up — CourseERP Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="./img/ibe.png" type="image/x-icon">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <!-- Master Theme -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5" style="background-color: #f8fafc !important;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">
                
                <!-- Branding Header -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 shadow-sm mb-3" style="width: 56px; height: 56px; background-color: #0f172a; color: #ffffff;">
                        <i class="fas fa-graduation-cap fa-xl"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Course<span class="text-primary">ERP</span></h3>
                    <p class="text-secondary small mb-0">Create your new portal student or administrator account</p>
                </div>

                <!-- Elevated Clean Card Container -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="card-body p-0">

                        <!-- Feedback Alert -->
                        <?php if (!empty($msg)): ?>
                            <div class="alert alert-<?= $msg_type; ?> alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-4" role="alert">
                                <i class="fas <?= ($msg_type === 'danger') ? 'fa-circle-exclamation' : 'fa-triangle-exclamation'; ?>"></i>
                                <span class="fw-medium"><?= htmlspecialchars($msg); ?></span>
                                <button type="button" class="btn-close ms-auto p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="./signup.php">
                            <!-- Name Inputs -->
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label text-dark fw-semibold small" for="fname">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="fname" id="fname" class="form-control rounded-3" style="border-color: #cbd5e1;" placeholder="John" required>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label text-dark fw-semibold small" for="lname">Last Name</label>
                                    <input type="text" name="lname" id="lname" class="form-control rounded-3" style="border-color: #cbd5e1;" placeholder="Doe">
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold small" for="email">Email Address <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="email" name="email" id="email" class="form-control rounded-3 ps-4" style="border-color: #cbd5e1;" placeholder="name@example.com" required autocomplete="email">
                                    <i class="fas fa-envelope position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
                                </div>
                            </div>

                            <!-- Password Inputs -->
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label text-dark fw-semibold small" for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control rounded-3" style="border-color: #cbd5e1;" placeholder="Create password" required autocomplete="new-password">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label text-dark fw-semibold small" for="confirmpassword">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirmpassword" id="confirmpassword" class="form-control rounded-3" style="border-color: #cbd5e1;" placeholder="Repeat password" required autocomplete="new-password">
                                </div>
                            </div>

                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold small" for="role">Account Role <span class="text-danger">*</span></label>
                                <select id="role" name="role" class="form-select rounded-3" style="border-color: #cbd5e1;" required onchange="toggleCourseDropdown()">
                                    <option value="student" selected>🎓 Student Learner</option>
                                    <option value="admin">👑 Portal Administrator</option>
                                </select>
                            </div>

                            <!-- Course Dropdown for Students -->
                            <div class="mb-4" id="courseDropdown">
                                <label class="form-label text-dark fw-semibold small" for="courseSelect">Choose Course Curriculum <span class="text-danger">*</span></label>
                                <select id="courseSelect" name="course_name" class="form-select rounded-3" style="border-color: #cbd5e1;" required>
                                    <option value="">-- Select Your Course --</option>
                                    <?php foreach($courses as $course): ?>
                                        <option value="<?= htmlspecialchars($course); ?>">
                                            <?= htmlspecialchars($course); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small text-muted">You will be automatically enrolled in all lessons for this course.</div>
                            </div>

                            <!-- Action Button -->
                            <button type="submit" name="signup" class="btn btn-primary rounded-3 w-100 py-2 fw-semibold shadow-sm mb-3" style="background-color: #3b82f6; border-color: #3b82f6;">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>

                            <!-- Login Navigation Link -->
                            <div class="text-center pt-2 border-top">
                                <span class="text-secondary small">Already have an account? </span>
                                <a href="./login.php" class="text-primary text-decoration-none fw-semibold small">Sign In</a>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Footer Copyright -->
                <div class="text-center mt-4 text-secondary small">
                    &copy; <?= date('Y'); ?> CourseERP. All rights reserved.
                </div>

            </div>
        </div>
    </div>

    <script>
    function toggleCourseDropdown() {
        const role = document.getElementById('role').value;
        const courseDiv = document.getElementById('courseDropdown');
        const courseSelect = document.getElementById('courseSelect');

        if (role === 'admin') {
            courseDiv.style.display = 'none';
            courseSelect.removeAttribute('required');
        } else {
            courseDiv.style.display = 'block';
            courseSelect.setAttribute('required', 'required');
        }
    }
    </script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
