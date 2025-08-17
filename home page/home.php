<?php
session_start();
include ("../login/connection.php");

if (!isset($_SESSION['user_email'])) {
    header("Location: http://localhost/ClockWise/login/login.php");
    exit();
}

$user_email = $_SESSION['user_email'];
$first_name = '';
$profile_picture = $row['profile_pic'] = 'profile.png';
$greeting = '';
$current_hour = date("H"); // Get the current hour in 24-hour format

if ($current_hour < 12) {
    $greeting = 'Good Morning';
} else {
    $greeting = "Good Afternoon";
}


if (!empty($user_email)) {
    $query = "SELECT first_name, profile_pic FROM admins WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $first_name = $row['first_name'];
        if (!empty($row['profile_pic'])) {
            $profile_picture = 'data:image/jpeg;base64,' . base64_encode($row['profile_pic']);
        }
        else{
            $profile_picture = 'profile.png';
        }
        
    }
    $stmt->close();
}

$events = [];
if (!empty($user_email)) {
    $event_query = "SELECT * FROM events WHERE user_email = ? AND event_date = CURDATE() AND sync_status!='PENDING_DELETE' ORDER BY event_start_time";
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
}
// Get the current date or the selected month/year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m'); // Default to current month
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');    // Default to current year

// Handle previous and next month navigation
$prevMonth = $month - 1;
$nextMonth = $month + 1;
$prevYear = $year;
$nextYear = $year;

if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Get the first day of the month and the number of days in the month
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$startDayOfWeek = date('w', $firstDayOfMonth);

// Days of the week
$daysOfWeek = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

// Get the current day, month, and year
$currentDay = date('j');
$currentMonth = date('m');
$currentYear = date('Y');

$nb_events_of_today = "0";
$nb_events_of_week = "0";
$nb_events_of_month = "0";


if ($conn && $user_email) {
    $count_event_today_query = "SELECT COUNT(*) FROM events WHERE user_email = ? AND sync_status!='PENDING_DELETE' AND event_date = CURDATE()";

    if ($stmt = $conn->prepare($count_event_today_query)) {
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $stmt->bind_result($nb_events_of_today);
        $stmt->fetch();
        $stmt->close();
    }
}

if ($conn && $user_email) {
    $count_event_week_query = "SELECT COUNT(*) FROM events WHERE user_email = ? AND sync_status!='PENDING_DELETE' AND WEEK(event_date) = WEEK(CURDATE())";
    if ($stmt = $conn->prepare($count_event_week_query)) {
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $stmt->bind_result($nb_events_of_week );
        $stmt->fetch();
        $stmt->close();
    }
}

if ($conn && $user_email) {
    $count_event_month_query = "SELECT COUNT(*) FROM events WHERE user_email = ? AND sync_status!='PENDING_DELETE' AND MONTH(event_date) = MONTH(CURDATE())";

    if ($stmt = $conn->prepare($count_event_month_query)) {
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $stmt->bind_result($nb_events_of_month);
        $stmt->fetch();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="schedule.js" defer></script>
    <title>Navigation Menu</title>
    <link rel="stylesheet" href="home.css">
</head>


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

    <div class="content">
        <div class="top_section">
        <div class="agenda-container">
            <div class="profile-container">
                <h2 style="margin:10px 0px;"><?php echo $greeting?>, <?= htmlspecialchars($first_name) ?>!</h2>
                <div class="profile-pic-container">
                    <img src="<?= htmlspecialchars(string: $profile_picture) ?>" alt="Profile Picture" class="profile-pic">
                    <div class="edit-icon" onclick="toggleOptions()">
                        <img src="icons/edit.svg" alt="Edit">
                    </div>
                    <div class="profile-options" id="profile-options">
                        <form id="upload-form" action="upload.php" method="POST" enctype="multipart/form-data">
                            <input type="file" id="profile-upload" name="profile_picture" accept="image/*" onchange="this.form.submit()" hidden>
                            <button type="button" onclick="document.getElementById('profile-upload').click()">Upload New Picture</button>
                        </form>
                        <form action="removeProfilePic.php" method="POST">
                            <button type="button" onclick="removeProfilePicture()">Remove Picture</button>
                        </form>
                    </div>
            </div>

    <script>
        function toggleOptions() {
            let options = document.getElementById("profile-options");
            options.style.display = options.style.display === "block" ? "none" : "block";
        }

        function removeProfilePicture() {
            fetch("removeProfilePic.php", {
            method: "POST",
            })
            .then(response => response.text())
            .then(data => {
            if (data.trim() === "success") {
                document.querySelector(".profile-pic").src = "profile.png"; // Default image
            } else {
                alert("Failed to remove profile picture.");
            }
            })
            .catch(error => console.error("Error:", error));
        }


    </script>

                <h3 style="margin: 10px 0px;">Your agenda today:</h3></div>
                <div class="today-events"><ul class="agenda-list">
                    <?php if (empty($events)) : ?>
                        <li>No events scheduled for today.</li>
                    <?php else : ?>
                        <?php foreach ($events as $event) : ?>
                            <li>
                                <div class="event-info">
                                    <span class="event-name"><?= htmlspecialchars($event['event_name']) ?></span>
                                </div>
                                <span class="event-time"><?= date("H:i", strtotime($event['event_start_time'])) ?> - <?= date("H:i", strtotime($event['event_finish_time'])) ?></span>
                                <button onclick="editEvent(
                                '<?= htmlspecialchars($event['id']) ?>',
                                '<?= htmlspecialchars($event['event_name']) ?>',
                                '<?= htmlspecialchars($event['event_start_time']) ?>',
                                '<?= htmlspecialchars($event['event_finish_time']) ?>',
                                '<?= htmlspecialchars($event['event_description']) ?>',
                                '<?= htmlspecialchars($event['event_date']) ?>')" id="reschedule-btn" class="btn-reschedule">Reschedule</button>
                                <button onclick="deleteEvent('<?=htmlspecialchars($event['id']) ?>')" id="delete-btn" class="btn-reschedule" style="background:#e74c3c;">Delete</button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                </div>
            </div>
            <div class="meetings">
                <div class="start_meeting" onclick="location.href='http://localhost/ClockWise/home%20page/startStudying.php';" style="cursor: pointer;">
                    <a class="icon-text-container">
                       <img src="icons\study.svg" alt="Icon_study" class="icons">
                       <p class="meetings_p">Start Studying</p>
                    </a>
                </div>
                <div class="join_meeting" onclick="location.href='http://localhost/ClockWise/home%20page/recordings.php';" style="cursor: pointer;">
                    <a class="icon-text-container">
                        <img src="icons\recording.svg" alt="Icon_recording" class="icons">
                        <p class="meetings_p">Recordings</p>
                    </a>
                </div>
                <div class="scheduale_meeting" onclick="openModal(-1,'','','','','');" style="cursor: pointer;">
                    <a class="icon-text-container">
                       <img src="icons\calendar.svg" alt="Icon_calendar" class="icons">
                       <p class="meetings_p">Schedule Now</p>
                    </a>
                </div>
            </div>
        </div>

        <div class="bottom-section">
            <div class="calendar"  onclick="location.href='http://localhost/ClockWise/home%20page/calendar.php';" style="cursor: pointer;">
                <div class="calendar-header">
                    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">← Prev</a>
                    <h2><?= date('F Y', $firstDayOfMonth) ?></h2>
                    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">Next →</a>
                </div>
                <div class="days">
                    <?php foreach ($daysOfWeek as $day): ?>
                        <div><?= $day ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="dates">
                    <?php
                    // Print empty cells for days before the first day of the month
                    for ($i = 0; $i < $startDayOfWeek; $i++) {
                        echo '<div></div>';
                    }

                    // Print the days of the month
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $class = ($day == $currentDay && $month == $currentMonth && $year == $currentYear) ? 'today' : '';
                        echo "<div class='$class'>$day</div>";
                    }

                    // Calculate remaining cells to complete the last row
                    $filledCells = $startDayOfWeek + $daysInMonth;
                    $remainingCells = 7 - ($filledCells % 7);
                    if ($remainingCells < 7) { // Only add empty cells if not already a full row
                        for ($i = 0; $i < $remainingCells; $i++) {
                            echo '<div></div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="invitations">
    <div class="holiday-title">
        <h2>Holidays</h2>
    </div>
    <div class="holidays-container">
        <div class="holiday-row">
            <div class="holiday-date">Feb 14, 2025</div>
            <div class="holiday-info">
                <h3>Valentine’s Day</h3>
                <p>A day to celebrate love and affection between loved ones.</p>
            </div>
        </div>

        <div class="holiday-row">
            <div class="holiday-date">April 1, 2025</div>
            <div class="holiday-info">
                <h3>Eid al-Fitr</h3>
                <p>A significant Islamic holiday marking the end of Ramadan.</p>
            </div>
        </div>

        <div class="holiday-row">
            <div class="holiday-date">Nov 22, 2025</div>
            <div class="holiday-info">
                <h3>Independence Day</h3>
                <p>Celebrates Lebanon's independence with national pride, honoring the country's freedom and heritage</p>
            </div>
        </div>

    </div>
</div>

            <div class="Insight">
                 <p class="insight-title">Insights</p>
        
                <div class="insight-item" onclick="location.href='http://localhost/ClockWise/home%20page/calendar.php';" style="cursor: pointer;">
                    <p class="insight-text">Events For Today</p>
                    <span class="insight-number"><?php  echo $nb_events_of_today?></span>
                </div>

                <div class="insight-item"  onclick="location.href='http://localhost/ClockWise/home%20page/calendar.php';" style="cursor: pointer;">
                    <p class="insight-text">Events For This Week</p>
                    <span class="insight-number"><?php  echo $nb_events_of_week?></span>
                </div>

                <div class="insight-item"  onclick="location.href='http://localhost/ClockWise/home%20page/calendar.php';" style="cursor: pointer;">
                    <p class="insight-text">Events For This Month</p>
                    <span class="insight-number"><?php  echo $nb_events_of_month?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!----------------------------------------------------- for pop up -------------------------------------------------------------->
    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content"> 
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Schedule Now</h2>
            <form id="eventForm" action="schedule_now.php" method="post">
                <input type="hidden" id='event_id' name="event_id">
                <div class="input-field">
                    <input id="event_name" type="text" name="event_name" required>
                    <label>Event Name</label>
                </div>
                <div class="input-field">
                    <input id="event_description" type="text" name="event_description" required>
                    <label>Event Description</label>
                </div>
                <div class="input-field">
                    <input id="event_date" type="date" name="event_date" required>
                    <label>Event Date</label>
                </div>
                <div class="input-field">
                    <input id="event_start_time" type="time" name="event_start_time" required>
                    <label>Event Start Time</label>
                </div>
                <div class="input-field">
                    <input id="event_finish_time" type="time" name="event_finish_time" required>
                    <label>Event Finish Time</label>
                </div>
                <button name="edit_btn" id="submit_btn" class="submit" type="submit">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>