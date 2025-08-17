<?php
include '../login/connection.php'; // Ensure database connection

if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Securely cast to an integer

    $query = "SELECT * FROM events WHERE id='$id'";
    $res = $conn->query($query);

    if ($res->num_rows > 0) {
        $updateQuery = "UPDATE events 
                        SET sync_status='PENDING_DELETE'
                        WHERE id='$id'";
        
        if ($conn->query($updateQuery)) {
            echo json_encode(["success" => true, "message" => "Event marked for deletion"]);
        } else {
            echo json_encode(["success" => false, "message" => "Database update failed"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Event not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
