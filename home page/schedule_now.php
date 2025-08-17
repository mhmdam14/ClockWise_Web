<?php
session_start();

include('../login/connection.php');

if ($conn) {
    $conn->select_db($db_name);
} else {
    die("Database connection failed.");
}

if (!isset($_SESSION['user_email'])) {
    header("Location: http://localhost/ClockWise/login/login.php");
    exit();
}
$user_email = $_SESSION['user_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_description = $_POST['event_description'];
    $event_date = $_POST['event_date'];
    $event_start_time = $_POST['event_start_time'];
    $event_finish_time = $_POST['event_finish_time'];


    $insert_event_query = "INSERT INTO events (user_email, event_name, event_description, event_date, event_start_time, event_finish_time,sync_status) 
                            VALUES (?, ?, ?, ?, ?, ?,'PENDING_ADD')";

    if ($stmt = $conn->prepare($insert_event_query)) {
    $stmt->bind_param('ssssss', $user_email, $event_name, $event_description, $event_date, $event_start_time, $event_finish_time);

                if ($stmt->execute()) {
                    header("Location: calendar.php");
                    exit();
                } else {
                    echo "Error: " . $stmt->error;
                }
        
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        }
   
?>