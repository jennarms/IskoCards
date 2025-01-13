<?php
session_start();
require_once 'config.php'; // Include your database connection file

// Handle the Sign Up Process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signup'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        // Check if passwords match
        if ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Check if username already exists
            $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $error = "Username already taken!";
            } else {
                // Insert the new user into the database
                $query = "INSERT INTO users (name, username, password) VALUES ('$name', '$username', '$hashed_password')";
                if (mysqli_query($conn, $query)) {
                    // Redirect to login page after successful signup
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Error occurred while signing up. Please try again.";
                }
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
  <title>Sign Up - IskoCards</title>
  <link rel="stylesheet" href="../css/loginsignup.css"> <!-- Link to your new CSS file -->
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
      <h1>Create Your Account</h1>

      <!-- Sign Up Form -->
      <form action="signup.php" method="POST">
        <div class="form-group">
          <label for="name">Full Name:</label>
          <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="confirm-password">Confirm Password:</label>
          <input type="password" id="confirm-password" name="confirm-password" required>
        </div>
        <button type="submit" name="signup" class="signup-btn">Sign Up</button>
      </form>

      <?php
      if (isset($error)) {
          echo "<p style='color: red;'>$error</p>";
      }
      ?>

      <p>Already have an account? <a href="login.php">Log In</a></p>
    </div>
  </main>
</body>
</html>
