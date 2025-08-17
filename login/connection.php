<?php
$server_name = "localhost";
$username = "root";
$password = "";
$db_name = "mydb";

$conn = new mysqli($server_name, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SHOW DATABASES LIKE '$db_name'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    $create_db_query = "CREATE DATABASE $db_name";
    if ($conn->query($create_db_query) === TRUE) {
        $conn->select_db($db_name);

        $table_creation_query = "CREATE TABLE IF NOT EXISTS admins (
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL PRIMARY KEY,
            password VARCHAR(255) NOT NULL,
            profile_pic LONGBLOB NULL
        )";
        $conn->query($table_creation_query);
    } 
}

$conn->select_db($db_name);

$create_events_table = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(100) NOT NULL,
        event_name VARCHAR(255) NOT NULL,
        event_description TEXT NOT NULL,
        event_date DATE NOT NULL,
        event_start_time TIME NOT NULL,
        event_finish_time TIME NOT NULL,
        sync_status VARCHAR(200) NOT NULL,
        FOREIGN KEY (user_email) REFERENCES admins(email) ON DELETE CASCADE
    )";
$conn->query($create_events_table);

$create_recording_table = "CREATE TABLE IF NOT EXISTS recording (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    recording_name VARCHAR(255) NOT NULL,
    recording_file LONGBLOB NOT NULL,
    FOREIGN KEY (email) REFERENCES admins(email) ON DELETE CASCADE
)";
$conn->query($create_recording_table);


?>