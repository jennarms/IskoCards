<?php
session_start();
require_once 'config.php'; // Include your database connection file

// Handle the Log In Process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if username and password are correct
        $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: home.php");
            } else {
                $error = "Invalid username or password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In - IskoCards</title>
  <link rel="stylesheet" href="../css/loginsignup.css">
</head>
<body>
  <header>
    <a href="../index.php">
      <div class="logo"></div>
    </a>
  </header>

  <main>

  <div class="background-image">
    <img src="../assets/LoginSignup.png " alt="background-image" style="width: 100%; height: auto;">
  </div>
  
    <div class="auth-container">
      <h1>Log In to Your Account</h1>

      <!-- Log In Form -->
      <form action="login.php" method="POST">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" name="login" class="login-btn">Log In</button>
      </form>

      <?php
      if (isset($error)) {
          echo "<p style='color: red;'>$error</p>";
      }
      ?>

      <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
  </main>
</body>
</html>
