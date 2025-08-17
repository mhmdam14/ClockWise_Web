<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: http://localhost/ClockWise/login/login.php");
    exit();
}

// Initialize session variables if not set
if (!isset($_SESSION['minutes'])) {
    $_SESSION['minutes'] = 25;
    $_SESSION['seconds'] = 0;
}

if (!isset($_SESSION['isStudying'])) {
    $_SESSION['isStudying'] = false;
}

if (!isset($_SESSION['isPaused'])) {
    $_SESSION['isPaused'] = false;
}

// Update the session variables with the new timer values
if (isset($_POST['minutes']) && isset($_POST['seconds'])) {
    $_SESSION['minutes'] = (int)$_POST['minutes'];
    $_SESSION['seconds'] = (int)$_POST['seconds'];
}

// Reset timer if required
if (isset($_POST['resetTimer']) && $_POST['resetTimer'] == 'true') {
    $_SESSION['minutes'] = 25;
    $_SESSION['seconds'] = 0;
    $_SESSION['isStudying'] = false;
    $_SESSION['isPaused'] = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="startStudying.css">
    <title>Study Timer</title>
    <script>
        let timer;
        let minutes = <?php echo $_SESSION['minutes']; ?>;
        let seconds = <?php echo $_SESSION['seconds']; ?>;
        let isStudying = <?php echo $_SESSION['isStudying'] ? 'true' : 'false'; ?>;
        let isPaused = <?php echo $_SESSION['isPaused'] ? 'true' : 'false'; ?>;

        // Add the audio for the timer alert
        const audio = new Audio('audio.mp3');  // Ensure the correct path to the sound file

        // Function to update the timer on the page
        function updateTimer() {
            if (seconds === 0) {
                if (minutes === 0) {
                    clearInterval(timer);
                    audio.play();  // Play audio when timer ends
                    isStudying = false;
                    document.getElementById('startStopButton').textContent = 'Start Studying';
                    return;
                } else {
                    minutes--;
                    seconds = 59;
                }
            } else {
                seconds--;
            }

            // Format the timer to show two digits
            let formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
            let formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

            // Display the updated time
            document.getElementById('timer').textContent = formattedMinutes + ':' + formattedSeconds;

            // Update session values
            updateSessionTimer(minutes, seconds);
        }

        // Send the updated timer to the server
        function updateSessionTimer(min, sec) {
            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", "", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("minutes=" + min + "&seconds=" + sec);
        }

        function toggleTimer() {
    const startStopButton = document.getElementById('startStopButton');
    const pauseButton = document.getElementById('pauseButton');

    if (!isStudying && !isPaused) {
        // Start studying
        isStudying = true;
        startStopButton.textContent = 'Stop Studying';
        pauseButton.style.display = 'inline-block'; // Show the Pause button
        timer = setInterval(updateTimer, 1000);
    } else if (isStudying) {
        // Stop studying (reset to initial state)
        isStudying = false;
        startStopButton.textContent = 'Start Studying';
        pauseButton.style.display = 'none'; // Hide the Pause button
        clearInterval(timer);
        document.getElementById('resetForm').submit();
    }
}

// Ensure Pause button is hidden on page load
window.onload = function () {
    if (!isStudying) {
        document.getElementById('pauseButton').style.display = 'none';
    }
};


        // Pause the timer
        function pauseTimer() {
            const pauseButton = document.getElementById('pauseButton');
            clearInterval(timer); // Pause the timer
            isPaused = true;
            pauseButton.textContent = 'Resume'; // Change text to "Resume"
            // Update session paused state
            updateSessionPausedState(true);
        }

        // Resume the timer from the paused state
        function resumeTimer() {
            const pauseButton = document.getElementById('pauseButton');
            pauseButton.textContent = 'Pause'; // Change back to "Pause"
            isPaused = false;
            timer = setInterval(updateTimer, 1000); // Resume the timer
            // Update session paused state
            updateSessionPausedState(false);
        }

        // Toggle Pause/Resume
        function togglePauseResume() {
            const pauseButton = document.getElementById('pauseButton');
            if (isPaused) {
                resumeTimer();
            } else {
                pauseTimer();
            }
        }

        // Update session paused state
        function updateSessionPausedState(paused) {
            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", "", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("isPaused=" + paused);
        }
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
            <li class="active">
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
            <li>
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

    <div class="study-section">
    <div class="study-container">
    <h1>Let’s Begin Your Study Session!</h1>

    <!-- Start/Stop Studying Button -->
    <div class="button-container">
        <button id="startStopButton" onclick="toggleTimer()">Start Studying</button>
        <p id="timer" class="Timer">
            <?php echo str_pad($_SESSION['minutes'], 2, '0', STR_PAD_LEFT); ?>:<?php echo str_pad($_SESSION['seconds'], 2, '0', STR_PAD_LEFT); ?>
        </p>
    </div>

    
    <!-- Pause/Resume Button (Initially Hidden) -->
    <div class="button-container">
        <button id="pauseButton" onclick="togglePauseResume()" style="display: none;">Pause</button>
    </div>

    <!-- Motivational Quote -->
    <div class="motivation">
        <p>"Don’t watch the clock, do what it does—keep going."</p>
    </div>
</div>
    </div>
</div>

<form id="resetForm" method="POST" style="display: none;">
    <input type="hidden" name="resetTimer" value="true">
</form>

</body>
</html>