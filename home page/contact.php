<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: http://localhost/ClockWise/login/login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Prepare the mailto link
    $to = "mhmdam12.almhmd@gmail.com";
    $subjectEncoded = urlencode($subject);
    $messageEncoded = urlencode("Message:\n$message");

    // Generate the mailto link
    $mailtoLink = "mailto:$to?subject=$subjectEncoded&body=$messageEncoded";

    // Redirect the user to the email client
    echo "<script>window.open('$mailtoLink', '_blank');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contact.css">
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
            <li>
                <a href="home.php">
                    <img src="icons/home.svg" alt="Home Icon" class="icon">
                    <span class="text">Home</span>
                </a>
            </li>
            <li>
                <a href="calendar.php">
                    <img src="icons/calendar.svg" alt="Calendar Icon" class="icon">
                    <span class="text">Calendar</span>
                </a>
            </li>
            <li>
                <a href="recordings.php">
                    <img src="icons/recording.svg" alt="Recording Icon" class="icon">
                    <span class="text">Recordings</span>
                </a>
            </li>
            <li class="active">
                <a href="contact.php">
                    <img src="icons/contact.svg" alt="Contacts Icon" class="icon">
                    <span class="text">Contact</span>
                </a>
            </li>
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
        <h2>Contact Us</h2>
        <p>Fill out the form below and we’ll get back to you soon.</p>
        <form method="POST" action="">
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" placeholder="Subject" required>
            </div>
            <div class="form-group">
                <label for="message">Description:</label>
                <textarea id="message" name="message" rows="4" placeholder="Your message" required></textarea>
            </div>
            <button type="submit">Send Message</button>
        </form>
    </div>
</div>

</body>
</html>