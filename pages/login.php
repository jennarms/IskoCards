<?php
session_start();
require_once 'config.php'; // Include your database connection file

// Initialize an error message variable
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if username and password are correct
        $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $user = mysqli_fetch_assoc($result);
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: home.php");
                exit();
            } else {
                // Incorrect username or password
                $error = "Invalid username or password!";
            }
        } else {
            // Query failed or user not found
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
      <img src="../assets/LoginSignup.png" alt="background-image" style="width: 100%; height: auto;">
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

      <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>
  </main>

  <!-- Custom Alert Modal -->
  <div id="custom-alert" class="modal" style="display: none;">
    <div class="modal-content">
      <h2 id="alert-title"></h2>
      <p id="alert-message"></p>
    </div>
  </div>

  <script>
    function showAlert(title, message, type = 'success') {
      const alertTitle = document.getElementById('alert-title');
      const alertMessage = document.getElementById('alert-message');
      const alertBox = document.getElementById('custom-alert');

      // Set the alert title and message
      alertTitle.textContent = title;
      alertMessage.textContent = message;

      // Style the alert based on type
      if (type === 'success') {
          alertBox.querySelector('.modal-content').style.backgroundColor = '#a1e3b7'; // Light green for success
      } else if (type === 'error') {
          alertBox.querySelector('.modal-content').style.backgroundColor = '#f8b0b0'; // Light red for error
      } else {
          alertBox.querySelector('.modal-content').style.backgroundColor = '#ffebf0'; // Default pink
      }

      alertBox.style.display = 'block'; // Show the alert
      // Close the alert after 3 seconds
      setTimeout(() => {
          alertBox.style.display = 'none';
      }, 3000); // Hide after 3 seconds
    }

    // Show PHP error message if exists
    const phpError = "<?php echo isset($error) ? addslashes($error) : ''; ?>";
    if (phpError) {
      showAlert('Error', phpError, 'error');
    }
  </script>
</body>
</html>
