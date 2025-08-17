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

// Get week offset from URL parameter
$weekOffset = isset($_GET['week']) ? intval($_GET['week']) : 0;

// Calculate the start date based on the week offset
$currentDate = new DateTime();
$currentDate->modify($weekOffset . ' weeks');

$fetch_events_query = "SELECT * FROM events WHERE user_email = ? AND sync_status!='PENDING_DELETE'";
if ($stmt = $conn->prepare($fetch_events_query)) {
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    $stmt->close();
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <script src="schedule.js" defer></script>
    <link rel="stylesheet" href="calendar.css">
    <style>
        .calendar-container {
            flex-grow: 1;
            margin-right: 20px;
            margin-left: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            max-height: 680px;
        }

        .calendar-container::-webkit-scrollbar{
            display: none;
        }

        .calendar-nav {
            display: flex;
            justify-content:space-evenly;
            align-items: center;
            margin-bottom: 20px;
        }

        .nav-button {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: #1976D2;
        }

        .nav-button svg {
            width: 16px;
            height: 16px;
        }

        .calendar-header {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .today-button {
            background-color:rgb(0, 0, 0);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            margin-top:10px ;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .today-button:hover {
            background-color:#2196F3;
        }

        .calendar-header h2 {
            margin: 0;
            font-size: 1.5em;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .calendar-table thead th {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
        }

        .calendar-table tbody td {
            position: relative;
            height: 60px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #e0e0e0;
            overflow: visible;
        }

        .event {
            position: absolute;
            color: #fff;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            line-height: 1.2;
            border-radius: 4px;
            width: 100%;
            padding: 5px;
            z-index: 10;
            cursor: pointer;
        }

        .event:hover {
            opacity: 0.8;
        }

        .time-column {
            text-align: center;
            vertical-align: middle;
            width: 60px;
            font-weight: bold;
        }

        /* Popup Style */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            z-index: 20;
        }

        .popup .close-btn {
           
            border-radius: 4px;
            position: absolute;
            font-size: 28px;
            font-weight: bold;
            top: 1px;
            right: 1px;
            color: white;
            cursor: pointer;
        }
        .popup .close-btn:hover{
            background: #e74c3c;
        }

        .popup .edit-delete-btns {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .popup #delete-btn {
            width: 60px;
            height: 20px;
            flex: 1;
            background: #e74c3c;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .popup #edit-btn {
            flex: 1;
            width: 60px;
            height: 20px;
            background: #2196F3;
            margin-right: 5px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }
    </style>
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
            <li class="active"><a href="calendar.php"><img src="icons/calendar.svg" alt="Calendar Icon" class="icon"><span class="text">Calendar</span></a></li>
            <li><a href="recordings.php"><img src="icons/recording.svg" alt="Recording Icon" class="icon"><span class="text">Recordings</span></a></li>
            <li><a href="contact.php"><img src="icons/contact.svg" alt="Contacts Icon" class="icon"><span class="text">Contact</span></a></li>
            <li class="logout-button">
                <a href="logout.php">
                <img src="icons/logout.svg" alt="Logout Icon" class="icon">
                <span class="text">Log out</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="calendar-container">
        <div class="calendar-nav">
            <button class="nav-button" onclick="navigateWeek(-1)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Previous Week
            </button>

            <div class="calendar-header">
                <?php
                $Dates = [];
                for ($i = 0; $i < 7; $i++) {
                    $Dates[] = $currentDate->format('Y-m-d');
                    $currentDate->modify('+1 day');
                }
                echo '<h2>' . date('M d', strtotime($Dates[0])) . ' - ' . date('M d, Y', strtotime(end($Dates))) . '</h2>';
                ?>
                <button class="today-button" onclick="goToToday()">Today</button>
            </div>
            <button class="nav-button" onclick="navigateWeek(1)">
                Next Week
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
        </div>

        <table class="calendar-table">
            <thead>
                <tr>
                    <th class="time-column">Time</th>
                    <?php foreach ($Dates as $date) {
                        echo '<th>' . date('M d', strtotime($date)) . '</th>';
                    } 
                    function generateColor($i) {
                        return $i % 2 == 0 ? '#2196F3' : '#000000';
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php
            $startTime = 0;
            $endTime = 23;
            $i = 1;
           
            $current_row = 0;
            for ($hour = $startTime; $hour <= $endTime; $hour++): ?>
                <tr>
                    <td class="time-column"><?= $hour ?>:00</td>
                    <?php 
                   foreach ($Dates as $date) {
                    echo "<td style='position: relative;'>";
                
                    if (!empty($events)) {
                        $eventsAtThisTime = [];
                        $continuedEvents = [];
                        
                        // Check for events that started the previous day
                        if ($hour === 0) {
                            foreach ($events as $event) {
                                $eventDate = new DateTime($event['event_date']);
                                $prevDate = (new DateTime($date))->modify('-1 day')->format('Y-m-d');
                                
                                if ($event['event_date'] == $prevDate) {
                                    $eventStartTime = strtotime($event['event_start_time']);
                                    $eventEndTime = strtotime($event['event_finish_time']);
                                    
                                    if ($eventStartTime > $eventEndTime) {
                                        $continuedEvents[] = $event;
                                    }
                                }
                            }
                        }
                
                        // Get events starting in current hour
                        foreach ($events as $event) {
                            if ($event['event_date'] == $date) {
                                $eventStartTime = strtotime($event['event_start_time']);
                                $eventEndTime = strtotime($event['event_finish_time']);
                                $eventStartHour = (int) date('H', $eventStartTime);
                
                                if ($eventStartHour == $hour) {
                                    $eventsAtThisTime[] = $event;
                                }
                            }
                        }
                
                        // Handle continued events from previous day
                        if (!empty($continuedEvents)) {
                            $eventWidth = 100 / (count($continuedEvents) + count($eventsAtThisTime));
                            $index = 0;
                            
                            foreach ($continuedEvents as $event) {
                                $eventEndTime = strtotime($event['event_finish_time']);
                                $minutesFromMidnight = (strtotime($event['event_finish_time']) - strtotime("00:00:00")) / 60;
                                $height = ($minutesFromMidnight / 60) * 100;
                                $color = generateColor($i);
                                $i++;
                
                                if ($hour === 0) {  // Only render continuation at midnight
                                    echo "<div class='event' style='
                                        position: absolute;
                                        top: 0;
                                        height: {$height}%;
                                        left: " . ($eventWidth * $index) . "%;
                                        width: {$eventWidth}%;
                                        background-color: {$color}'
                                        onclick='openPopup(\"{$event['id']}\",\"{$event['event_name']}\", \"{$event['event_start_time']}\", \"{$event['event_finish_time']}\", \"{$event['event_description']}\",\"{$event['event_date']}\")'>";
                                    echo "<strong>{$event['event_name']} (cont.)</strong><br>";
                                    echo date('g:i A', strtotime("00:00:00")) . " - " . date('g:i A', $eventEndTime);
                                    echo "</div>";
                                }
                                $index++;
                            }
                        }
                
                        // Handle events starting in current hour
                        if (!empty($eventsAtThisTime)) {
                            $eventWidth = 100 / (count($eventsAtThisTime) + count($continuedEvents));
                            $index = count($continuedEvents);  // Start after any continued events
                            
                            foreach ($eventsAtThisTime as $event) {
                                $eventStartTime = strtotime($event['event_start_time']);
                                $eventEndTime = strtotime($event['event_finish_time']);
                                $eventStartMinute = (int) date('i', $eventStartTime);
                                $topOffset = ($eventStartMinute / 60) * 100;
                                $color = generateColor($i);
                                $i++;
                
                                if ($eventStartTime > $eventEndTime) {
                                    // Event goes past midnight
                                    $minutesUntilMidnight = (strtotime("23:59:59") - $eventStartTime) / 60;
                                    $height = ($minutesUntilMidnight / 60) * 100;
                                    
                                    echo "<div class='event' style='
                                        position: absolute;
                                        top: {$topOffset}%;
                                        height: {$height}%;
                                        left: " . ($eventWidth * $index) . "%;
                                        width: {$eventWidth}%;
                                        background-color: {$color}'
                                        onclick='openPopup(\"{$event['id']}\",\"{$event['event_name']}\", \"{$event['event_start_time']}\", \"{$event['event_finish_time']}\", \"{$event['event_description']}\",\"{$event['event_date']}\")'>";
                                    echo "<strong>{$event['event_name']}</strong><br>";
                                    echo date('g:i A', $eventStartTime) . " - " . date('g:i A', $eventEndTime);
                                    echo "</div>";
                                } else {
                                    // Regular event (same day)
                                    $durationMinutes = ($eventEndTime - $eventStartTime) / 60;
                                    $height = ($durationMinutes / 60) * 100;
                                    
                                    echo "<div class='event' style='
                                        position: absolute;
                                        top: {$topOffset}%;
                                        height: {$height}%;
                                        left: " . ($eventWidth * $index) . "%;
                                        width: {$eventWidth}%;
                                        background-color: {$color}'
                                        onclick='openPopup(\"{$event['id']}\",\"{$event['event_name']}\", \"{$event['event_start_time']}\", \"{$event['event_finish_time']}\", \"{$event['event_description']}\",\"{$event['event_date']}\")'>";
                                    echo "<strong>{$event['event_name']}</strong><br>";
                                    echo date('g:i A', $eventStartTime) . " - " . date('g:i A', $eventEndTime);
                                    echo "</div>";
                                }
                                $index++;
                            }
                        }
                    }
                    echo "</td>";
                } ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Popup -->
<div id="popup" class="popup">
    <span class="close-btn" onclick="closePopup()">
        &times;
    </span>
    <h2 id="popup-event-name"></h2>
    <input id="popup-event-id" type="hidden">
    <p><strong>Start Time:</strong> <span id="popup-start-time"></span></p>
    <p><strong>End Time:</strong> <span id="popup-end-time"></span></p>
    <p><strong>Description:</strong> <span id="popup-description"></span></p>
    <div class="edit-delete-btns">
    <button id="edit-btn">Edit</button>
    <button id="delete-btn">Delete</button>
    </div>
</div>

<script>


</script>
<!----------------------------------------------------- for pop up -------------------------------------------------------------->
    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content"> 
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Schedule Now</h2>
            <form id="eventForm"  >
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
