<?php
session_start();
include("../login/connection.php");

$user_email = $_SESSION['user_email'];  // Get the logged-in user's email

if (!empty($user_email)) {
    // Set the profile_pic to NULL in the database to remove the picture
    $query = "UPDATE admins SET profile_pic = NULL WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_email);

    if ($stmt->execute()) {
        echo "success";  // Successful update
    } else {
        echo "error: " . $stmt->error;  // Error occurred
    }

    $stmt->close();
}
?>