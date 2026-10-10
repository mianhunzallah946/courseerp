<?php 
include("./connection.php");
session_start();

$msg = "";
$msg_type = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($connec, trim($_POST['email']));
    $password = mysqli_real_escape_string($connec, trim($_POST['password']));

    if (empty($email) || empty($password)) {
        $msg = "Please fill in all fields.";
        $msg_type = "warning";
    } else {
        $sql = "SELECT * FROM `login` WHERE `email`='$email' AND `password`='$password' LIMIT 1";
        $run = mysqli_query($connec, $sql);

        if ($run && mysqli_num_rows($run) > 0) {
            $rfet = mysqli_fetch_assoc($run);

            $_SESSION['user_id']    = $rfet['user_id'];
            $_SESSION['email']      = $rfet['email'];
            $_SESSION['role']       = $rfet['role'];
            $_SESSION['name']       = trim($rfet['fname'] . ' ' . $rfet['lname']);
            $_SESSION['membership'] = $rfet['membership'] ?? 'free';

            header("Location: ./dashboard.php");
            exit();
        } else {
            $msg = "Invalid email address or password.";
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login — CourseERP Portal</title>
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
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                
                <!-- Branding Header -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 shadow-sm mb-3" style="width: 56px; height: 56px; background-color: #0f172a; color: #ffffff;">
                        <i class="fas fa-graduation-cap fa-xl"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Course<span class="text-primary">ERP</span></h3>
                    <p class="text-secondary small mb-0">Sign in to your learning and management portal</p>
                </div>

                <!-- Elevated Clean Card Container -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 bg-white">
                    <div class="card-body p-2">

                        <!-- Feedback Alert -->
                        <?php if (!empty($msg)): ?>
                            <div class="alert alert-<?= $msg_type; ?> alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
                                <i class="fas <?= ($msg_type === 'danger') ? 'fa-circle-exclamation' : 'fa-triangle-exclamation'; ?>"></i>
                                <span class="fw-medium"><?= htmlspecialchars($msg); ?></span>
                                <button type="button" class="btn-close ms-auto p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="./login.php">
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold small" for="email">Email Address</label>
                                <div class="position-relative">
                                    <input type="email" name="email" id="email" class="form-control rounded-3 ps-4" style="border-color: #cbd5e1;" placeholder="name@example.com" required autocomplete="email">
                                    <i class="fas fa-envelope position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
                                </div>
                            </div>

                            <!-- Password Input -->
                            <div class="mb-4">
                                <label class="form-label text-dark fw-semibold small" for="password">Password</label>
                                <div class="position-relative">
                                    <input type="password" name="password" id="password" class="form-control rounded-3 ps-4" style="border-color: #cbd5e1;" placeholder="Enter your password" required autocomplete="current-password">
                                    <i class="fas fa-lock position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button type="submit" name="login" class="btn btn-primary rounded-3 w-100 py-2 fw-semibold shadow-sm mb-3" style="background-color: #3b82f6; border-color: #3b82f6;">
                                <i class="fas fa-right-to-bracket me-2"></i>Sign In
                            </button>

                            <!-- Sign Up Navigation Link -->
                            <div class="text-center pt-2 border-top">
                                <span class="text-secondary small">Don't have an account? </span>
                                <a href="./signup.php" class="text-primary text-decoration-none fw-semibold small">Create Account</a>
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
