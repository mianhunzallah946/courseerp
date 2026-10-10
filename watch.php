<?php
session_start();
include("./connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? 0;

// Check if already viewed
$check = mysqli_query($connec, "SELECT * FROM user_course_views WHERE user_id='$user_id' AND course_id='$course_id'");
if (mysqli_num_rows($check) == 0) {
    mysqli_query($connec, "INSERT INTO user_course_views (user_id, course_id) VALUES ('$user_id', '$course_id')");
}

// Show video page
// $course = mysqli_fetch_assoc(mysqli_query($connec, "SELECT * FROM course WHERE course_id='$course_id'"));
?>

