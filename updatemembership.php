<?php
// ── Tell the browser this is a JSON response ──
header('Content-Type: application/json');

include("./connection.php");

if (isset($_POST['user_id']) && isset($_POST['membership'])) {

    // Sanitize inputs (beginner-friendly)
    $user_id    = mysqli_real_escape_string($connec, $_POST['user_id']);
    $membership = mysqli_real_escape_string($connec, $_POST['membership']);

    // Only allow the two valid values
    if ($membership !== 'paid' && $membership !== 'free') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid membership value.']);
        exit();
    }

    // Update the login table
    $sql    = "UPDATE `login` SET `membership` = '$membership' WHERE `user_id` = '$user_id'";
    $result = mysqli_query($connec, $sql);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connec)]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters.']);
}
?>
