<?php
include('./connection.php');
$delid = $_GET['delid'];

$dsql = "SELECT `user_id` FROM `login` WHERE `user_id`='$delid'";
$drun = mysqli_query($connec, $dsql);
$dfet = mysqli_fetch_assoc($drun);


$sql = "DELETE FROM `login` WHERE `user_id`='$delid'";
$run = mysqli_query($connec, $sql);

if ($run) {
    header("Location: ./manageusers.php");
    exit;
}
?>
