<?php
include("./connection.php");

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Pehle ka status nikal lo
    $query = "SELECT status FROM course WHERE course_id = '$id'";
    $result = mysqli_query($connec, $query);
    $row = mysqli_fetch_assoc($result);

    $current_status = $row['status'];

    // Agar public hai to private kar do, warna public
    $new_status = ($current_status == 'public') ? 'private' : 'public';

    $update = "UPDATE course SET status = '$new_status' WHERE course_id = '$user_id'";
    mysqli_query($connec, $update);
}

// Wapis manage page pe bhej do
header("Location: ./updatemembership.php");
exit;
?>
