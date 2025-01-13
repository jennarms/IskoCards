<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - IskoCards</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>
    <header>
        <div class="header-actions">
            <img 
                src="../uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'Default.jpg'; ?>"
                alt="User Profile" 
                class="profile-pic" 
                onclick="toggleDashboard()">
        </div>
    </header>

<!-- Dashboard -->
<div id="dashboard" class="dashboard" style="display: none;">
    <div class="user-info">
        <img src="../uploads/<?php echo $user['profile_picture'] ?: 'Default.jpg'; ?>" alt="Profile Picture" class="profile-pic">
        <p class="name"><?php echo $user['name']; ?></p>
        <p class="username"><?php echo $user['username']; ?></p>
        <a href="edit_profile.php">Edit Account</a>
        <a href="?logout=true">Logout</a>
    </div>
    <div class="bottom-links">
        <a href="privacy_policy.php">Privacy Policy</a>
        <a href="about.php">About Website</a>
    </div>
</div>


    <main>
        <h1>Welcome Back, <?php echo $user['name']; ?>!</h1>
        <p>Retain Knowledge Like Never Before</p>
        <!-- Add Folder and Folder Management -->
        <section>
            <button onclick="createFolder()">Create New Folder</button>
            <div id="folder-list">
                <!-- Placeholder for dynamic folder management -->
                <p>No folders yet. Start organizing your flashcards!</p>
            </div>
        </section>
    </main>

    <script>
        function toggleDashboard() {
            const dashboard = document.getElementById('dashboard');
            dashboard.style.display = dashboard.style.display === 'block' ? 'none' : 'block';
        }


        function createFolder() {
            // Placeholder for folder creation logic
            alert("Folder creation feature coming soon!");
        }
    </script>
</body>
</html>
