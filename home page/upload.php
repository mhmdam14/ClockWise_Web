<?php
session_start();
include("../login/connection.php");

if (!isset($_SESSION['user_email'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_email = $_SESSION['user_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $imageData = file_get_contents($_FILES["profile_picture"]["tmp_name"]); // Read the image file
    $imageType = $_FILES["profile_picture"]["type"];

    $allowedTypes = ["image/jpg", "image/jpeg", "image/png", "image/gif"];
    
    if (in_array($imageType, $allowedTypes)) {
        $query = "UPDATE admins SET profile_pic = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("bs", $imageData, $user_email);
        $stmt->send_long_data(0, $imageData); // Ensure the binary data is properly sent
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: home.php");
exit();
?>