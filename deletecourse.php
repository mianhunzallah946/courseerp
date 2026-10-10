<?php
include('./connection.php');
$delid = $_GET['delid'];

$dsql = "SELECT `videos` FROM `course` WHERE `course_id`='$delid'";
$drun = mysqli_query($connec, $dsql);
$dfet = mysqli_fetch_assoc($drun);

if ($dfet && file_exists("./coursevideo/" . $dfet['video'])) {
    unlink("./coursevideo/" . $dfet['video']);
}

$sql = "DELETE FROM `course` WHERE `course_id`='$delid'";
$run = mysqli_query($connec, $sql);

if ($run) {
    header("Location: ./index.php");
    exit;
}
?>
