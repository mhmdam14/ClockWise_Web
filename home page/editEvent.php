<?php

include('../login/connection.php');

if (!$conn) {
    die(json_encode(["success" =>false, "message" => "Database connection failed."]));
}

$conn->select_db($db_name);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'], $_POST['event_name'], $_POST['event_description'], $_POST['event_date'], $_POST['event_start_time'], $_POST['event_finish_time'])) {

    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_description = $_POST['event_description'];
    $event_date = $_POST['event_date'];
    $event_start_time = $_POST['event_start_time'];
    $event_finish_time = $_POST['event_finish_time'];

    $query = "SELECT * FROM events WHERE id = '$event_id'";
    $res = $conn->query($query);

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();

        $sync_status = "PENDING_EDIT/" . 
            $row['event_name'] . "/" . $event_name . "/" . 
            $row['event_description'] . "/" . $event_description . "/" . 
            $row['event_date'] . "/" . $event_date . "/" . 
            $row['event_start_time'] . "/" . $event_start_time . "/" . 
            $row['event_finish_time'] . "/" . $event_finish_time;

        $edit_event_query = "UPDATE events 
                             SET event_name='$event_name',
                                 event_description='$event_description',
                                 event_date='$event_date', 
                                 event_start_time='$event_start_time',
                                 event_finish_time='$event_finish_time',
                                 sync_status='$sync_status' 
                             WHERE id='$event_id'";

        if ($conn->query($edit_event_query)) {
            echo json_encode(["success" => true, "message" => "Event updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update event."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Event not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing required parameters."]);
}
?>
