<?php
include_once 'connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the database
$conn->select_db($db_name);

$error_message = "";
$response = "";
$response_class = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the password meets requirements
    if (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if the email already exists in the database
        $query = "SELECT email FROM admins WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response = 'Email already exists. Please choose a different email.';
            $response_class = 'warning';
        } else {
            // Hash the password and insert it into the database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO admins (email, password, first_name, last_name) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $email, $hashed_password, $first_name, $last_name);

            if ($stmt->execute()) {
                $response = 'Registration successful! You can now <a href="index.php">login</a>.';
                $response_class = 'success';
                header("Location: login.php");
                exit;
            } else {
                $response = 'An error occurred. Please try again later.';
                $response_class = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="left-side">
            <ul>
                <li>
                <img src="../home%20page/icons/logoicon.png" alt="Logo Icon" id="logo-icon"/>
                <h1>ClockWise</h1>
                </li>
            </ul>
        </div>
        <div class="right-side">
        <div class="form-content">
        <form action="signup.php" method="post">
            <h2>Sign Up</h2>
            <div class="input-field">
                <input type="text" name="first_name" required>
                <label>Enter your first name</label>
            </div>
            <div class="input-field">
                <input type="text" name="last_name" required>
                <label>Enter your last name</label>
            </div>
            <div class="input-field">
                <input type="email" name="email" required>
                <label>Enter your email</label>
            </div>
            <div class="input-field">
                <input type="password" name="password" required>
                <label>Enter your password</label>
            </div>
            <div class="input-field">
                <input type="password" name="confirm_password" required>
                <label>Confirm your password</label>
            </div>
            <button type="submit">Sign Up</button>

            <?php if (!empty($response)): ?>
                <p class="error-message <?php echo $response_class; ?>"><?php echo $response; ?></p>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <div class="register">
                <p>Already signed up? <a href="login.php">Log in</a></p>
            </div>
        </form>
        </div>
        </div>
    </div>
</body>
</html>