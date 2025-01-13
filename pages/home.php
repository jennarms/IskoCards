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

// Check for success or error messages in the URL or session
$success_message = isset($_GET['success']) ? $_GET['success'] : (isset($_SESSION['success']) ? $_SESSION['success'] : '');
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear session messages after displaying
unset($_SESSION['success']);
unset($_SESSION['error']);
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
            <a href="#" onclick="openEditModal()">Edit Account</a>
            <a href="#" onclick="openLogoutModal()">Logout</a>
        </div>
        <div class="bottom-links">
            <a href="privacy_policy.php">Privacy Policy</a>
            <a href="javascript:void(0);" onclick="notifyComingSoon()">About Website</a>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Account</h2>
            </div>

            <!-- Profile Picture Section -->
            <div>
                <h3>Change Profile Picture</h3>
                <form action="updateaccount.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profile_picture" accept="image/*">
                    <button type="submit">Change Picture</button>
                </form>
            </div>

            <!-- Name Section -->
            <div>
                <h3>Change Name</h3>
                <form action="updateaccount.php" method="POST">
                    <input type="text" name="name" placeholder="Full Name" value="<?php echo $user['name']; ?>" required>
                    <button type="submit">Change Name</button>
                </form>
            </div>

            <!-- Username Section -->
            <div>
                <h3>Change Username</h3>
                <form action="updateaccount.php" method="POST">
                    <input type="text" name="username" placeholder="Username" value="<?php echo $user['username']; ?>" required>
                    <button type="submit">Change Username</button>
                </form>
            </div>

            <!-- Password Section -->
            <div>
                <h3>Change Password</h3>
                <form action="updateaccount.php" method="POST">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">Change Password</button>
                </form>
            </div>

            <!-- Display success or error message -->
            <?php if ($success_message): ?>
                <p style="color: green;"><?php echo $success_message; ?></p>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <div class="modal-footer">
                <button onclick="closeEditModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="confirmation-modal" style="display: none;">
    <div class="confirmation-content">
        <div class="confirmation-header">
            <h2>Are you sure you want to log out?</h2>
        </div>
        <div class="confirmation-buttons">
            <a href="?logout=true" class="confirm">Yes, Logout</a>
            <button onclick="closeLogoutModal()" class="cancel">Cancel</button>
        </div>
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

        // Modal open and close functions
        function openEditModal() {
            closeAllModals(); // Close all modals before opening the edit modal
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openLogoutModal() {
            closeAllModals(); // Close all modals before opening the logout modal
            document.getElementById('logoutModal').style.display = 'flex';
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        function closeAllModals() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('logoutModal').style.display = 'none';
        }

        function notifyComingSoon() {
        alert("Coming Soon!");
        }

        // Check if there's an error or success message in session and open modal accordingly
        window.onload = function() {
            <?php if ($success_message || $error_message): ?>
                openEditModal();
            <?php endif; ?>
        };
    </script>
</body>
</html>
