<?php
require_once ("../login/connection.php");
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: http://localhost/ClockWise/login/login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $uploadFile = $_FILES['fileToUpload'];
    $fileName = basename($uploadFile['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileSize = $uploadFile['size'];
    $allowedExtensions = ['mp3', 'wav', 'm4a', 'OGG','pdf'];
    $maxFileSize = 3 * pow(2, 30); // 3GB

    $email = $_SESSION['user_email'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM recording WHERE email = ? AND recording_name = ?");
    $stmt->bind_param("ss", $email, $fileName);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script>alert('This recording already exists in the database.'); window.location.href='recordings.php';</script>";
        exit();
    }

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo "<script>alert('Invalid file type. Allowed types: " . implode(', ', $allowedExtensions) . "'); window.location.href='recordings.php';</script>";
        exit();
    } elseif ($fileSize > $maxFileSize) {
        echo "<script>alert('File is too large. Maximum size is 3 GB.'); window.location.href='recordings.php';</script>";
        exit();
    } else {
        $fileData = file_get_contents($uploadFile['tmp_name']);

        $stmt = $conn->prepare("INSERT INTO recording (email, recording_name, recording_file) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $fileName, $fileData);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('File uploaded successfully.'); window.location.href='recordings.php';</script>";
        exit();
    }
}

if (isset($_GET['delete'])) {
    $recordingId = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM recording WHERE id = ?");
    $stmt->bind_param("i", $recordingId);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('File deleted successfully.'); window.location.href='recordings.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Recordings</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="recordings.css">
    <script>
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, null, window.location.href);
        };
    </script>
</head>
<body>
    <div class="navbar">
        <div class="icon1">
        <img src="icons/logoicon.png" alt="Icon">
        </div>
        <div class="logo">ClockWise</div>
    </div>

    <div class="parent">
        <div class="navbar1">
            <ul>
                <li><a href="home.php"><img src="icons/home.svg" alt="Home Icon" class="icon"><span class="text">Home</span></a></li>
                <li><a href="calendar.php"><img src="icons/calendar.svg" alt="Calendar Icon" class="icon"><span class="text">Calendar</span></a></li>
                <li class="active"><a href="recordings.php"><img src="icons/recording.svg" alt="Recording Icon" class="icon"><span class="text">Recordings</span></a></li>
                <li><a href="contact.php"><img src="icons/contact.svg" alt="Contacts Icon" class="icon"><span class="text">Contact</span></a></li>
                <li class="logout-button">
                <a href="logout.php">
                <img src="icons/logout.svg" alt="Logout Icon" class="icon">
                <span class="text">Log out</span>
                </a>
            </li>
            </ul>
        </div>

        <div class="content">
            <div class="container">
                <h3 style="color:#2196F3">Select Recording to Upload</h3>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                    <label for="fileToUpload" class="choose-file-btn">Choose File</label>
                    <input type="file" name="fileToUpload" id="fileToUpload" required>
                    <input type="submit" value="Upload Recording" name="submit">
                </form>
                <div class="uploaded-files">
                    <h4 style="color:#2196F3">Uploaded Recordings:</h4>
                    <?php
                        $stmt = $conn->prepare("SELECT id, recording_name, recording_file FROM recording WHERE email = ?");
                        $stmt->bind_param("s", $_SESSION['user_email']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $fileId = $row['id'];
                                $fileName = $row['recording_name'];
                                $fileData = base64_encode($row['recording_file']);  
                                $fileType = "audio/mpeg";

                                echo "<div class='audio-item'>
                                        <audio controls>
                                            <source src='data:$fileType;base64,$fileData' type='$fileType'>Your browser does not support the audio element.
                                        </audio>
                                        <div class='audio-name'>$fileName</div>
                                        <a href='?delete=$fileId' class='delete-btn'>Delete</a>
                                    </div>";
                            }
                        } else {
                            echo "<p style='color:#2196F3;'>No recordings uploaded yet.</p>";
                        }
                        $stmt->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>