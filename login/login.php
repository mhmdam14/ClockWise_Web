<?php
session_start();
include_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);
    $error_message = "";

    $query = "SELECT email, password FROM admins WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_email'] = $email;

            if ($remember) {
                $remember = $_POST['remember'];
                setcookie("remember_email", $email, time() + 3600*24*365);
                setcookie("remember", $remember, time() + 3600*24*365);
                setcookie("remember_password", $_POST['password'], time() + 3600*24*365);

            }

            else{
                setcookie("remember_email", "", time() - 36000);
                setcookie("remember", "", time() - 36000);
                setcookie("remember_password", "", time() - 36000);
            }

            // Redirect to home page
            header("Location: ../home%20page/home.php");
      
        } else {
            $error_message = "Incorrect Password!";
        }
    } else {
        $error_message = "Email address doesn't exist!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | ClockWise</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<script>
    window.history.forward();
</script>
<div class="container">
  <div class="left-side">
      <ul>
        <li>
          <img src="../home%20page/icons/logoicon.png" alt="logo-icon" id="logo-icon">
          <h1>ClockWise</h1>
        </li>
      </ul>
  </div>
  <div class="right-side">
    <div class="form-content">
    <form action="login.php" method="post"> 
      <h2>Log In</h2>
        <div class="input-field">
          <input name="email" type="email" 
          value="<?php if(!empty($email)){echo $email;}
          else if(isset($_COOKIE['remember_email'])){echo $_COOKIE['remember_email'];} ?>" required>
          <label>Enter your email</label>
        </div>
        <div class="input-field">
          <input name="password" type="password" value="<?php if(!empty($_POST['password'])){echo $_POST['password'];}
          else if(isset($_COOKIE['remember_password'])){echo $_COOKIE['remember_password'];} ?>" required>
          <label>Enter your password</label>
        </div>
        <div class="forget">
          <label for="remember">
            <input type="checkbox" name="remember" id="remember" 
            <?php if(!empty($remember)){ ?>checked <?php } else if(isset($_COOKIE['remember'])) { ?> checked <?php } ?>>
            <p>Remember me</p>
          </label>
          <a href="#">Forgot password?</a>
        </div>
        <button type="submit">Log in</button>
        <div class="register">
          <p>Don't have an account? <a href="signup.php">Register</a></p>
        </div>
        <?php if (!empty($error_message)){?>
          <p class="error-message"><?php echo $error_message;?></p>
        <?php } ?>
    </form>
    </div>
  </div>
</div>
</body>
</html>