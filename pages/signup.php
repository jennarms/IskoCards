<?php
session_start();
require_once 'config.php'; // Include your database connection file

// Initialize an error message variable
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
  $name = trim($_POST['name']);

  // Validate name (only letters, spaces, periods, and dashes)
  if (!preg_match('/^[a-zA-Z\s.-]+$/', $name)) {
      $_SESSION['error'] = "Name must only contain letters, spaces, periods, and dashes.";
      header("Location: home.php"); // Redirect back with an error
      exit();
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signup'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        // Check if any required fields are empty
        if (empty($name) || empty($username) || empty($password) || empty($confirm_password)) {
            $error = "All fields are required!";
        } elseif ($password !== $confirm_password) {
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
      <h1>Create Your Account</h1>

      <!-- Sign Up Form -->
      <form action="signup.php" method="POST" onsubmit="return validateAndSubmitName()">
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

      <p>Already have an account? <a href="login.php">Log In</a></p>
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
    function validateName(name) {
    const regex = /^[a-zA-Z\s.-]+$/; // Allows letters, spaces, periods, and dashes
    return regex.test(name);
    }

    function validateAndSubmitName() {
        const nameInput = document.querySelector('input[name="name"]');
        const nameValue = nameInput.value.trim();

        if (!validateName(nameValue)) {
            showAlert('Error', 'Name must only contain letters, spaces, periods, and dashes.', 'error');
            return false; // Prevent form submission
        }

        // Form submission proceeds if validation passes
        nameInput.form.submit();
    }

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
    const phpError = "<?php echo isset($error) ? $error : ''; ?>";
    if (phpError) {
      showAlert('Error', phpError, 'error');
    }
  </script>
</body>
</html>
