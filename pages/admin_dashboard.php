<?php
session_start();
include('config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not admin
    exit();
}

// Assuming the logged-in user's ID is stored in the session
$user_id = $_SESSION['user_id'] ?? null; // Replace 'user_id' with your session variable


if (isset($_POST['check_username']) && $_POST['check_username'] === 'true') {
    // Get the username from the POST request
    $username = $_POST['username'];
    
    // Prepare a query to check if the username already exists
    $query = "SELECT COUNT(*) AS username_count FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // If the username exists, return a response
    if ($data['username_count'] > 0) {
        echo json_encode(['exists' => true]); // Username exists
    } else {
        echo json_encode(['exists' => false]); // Username does not exist
    }
    exit(); // End the script here after sending the response
}

if ($user_id) {
    // Query to fetch admin details by role
    $query = "SELECT name, username, profile_picture FROM users WHERE id = ? AND role = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_name = htmlspecialchars($admin['name']);
        $admin_username = htmlspecialchars($admin['username']);
        $profile_picture = htmlspecialchars($admin['profile_picture']);
    } else {
        $admin_name = "Unknown Admin";
        $admin_username = "Unknown Username";
        $profile_picture = "../uploads/default.jpg"; // Default profile picture
    }
} else {
    $admin_name = "Not Logged In";
    $admin_username = "N/A";
    $profile_picture = "../uploads/default.jpg"; // Default profile picture
}

// Fetch user count
$query = "SELECT COUNT(*) AS user_count FROM users";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
$user_count = $data['user_count'];

// Fetch flashcard count
$query = "SELECT COUNT(*) AS flashcard_count FROM flashcards";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
$flashcard_count = $data['flashcard_count'];

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userId = $_POST['delete_user_id'];

    // Delete the user from the database
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "success|User deleted successfully."; // Return success message
    } else {
        echo "error|Failed to delete user."; // Return error message
    }

    $stmt->close();
    exit(); // Ensure no further output
}

// Handle flashcard deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_flashcard_id'])) {
    $flashcardId = $_POST['delete_flashcard_id'];

    // Delete the flashcard from the database
    $query = "DELETE FROM flashcards WHERE flashcard_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $flashcardId);

    if ($stmt->execute()) {
        echo "success|Flashcard deleted successfully."; // Return success message
    } else {
        echo "error|Failed to delete flashcard."; // Return error message
    }

    $stmt->close();
    exit(); // Ensure no further output
}

// Fetch users data
$query = "SELECT id, name, username, storage_used FROM users";
$result = mysqli_query($conn, $query);

// Get the search term from the query string (if any)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
// Modify the query to include the search condition
$query_flashcards = "SELECT * FROM flashcards WHERE flashcard_id LIKE '%$searchTerm%'";
$result_flashcards = mysqli_query($conn, $query_flashcards);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile">
                <img src="../uploads/<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-picture">
                <div class="profile-info">
                    <p class="admin-name"><?php echo $admin_name; ?></p>
                    <p class="admin-username"><?php echo $admin_username; ?></p>
                    <button class="edit-btn" id="editBtn">Edit Account</button>
                    <button class="logout-button" onclick="logout()">Logout</button>
                </div>
            </div>
            <div class="sidebar-logo">
                <div class="clock"><p id="time"></p></div>
                <img src="../assets/IskoCards.png" alt="Logo">
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
            </div>

            <!-- Dashboard Stats -->
            <section id="stats-section">
            <div class="stats-container">
                <div class="stat-card">
                <h3>Number of Users</h3>
                <p id="user-count"><?php echo $user_count; ?></p>
                </div>
                <div class="stat-card">
                <h3>Number of Flashcards</h3>
                <p id="flashcard-count"><?php echo $flashcard_count; ?></p>
                </div>
            </div>
            </section>

            <!-- User Management Section -->
            <div class="user-management">
                <h2>User Management</h2>
                <!-- Search Bar -->
                <div class="search-bar">
                    <input type="text" id="search" placeholder="Search user ID..." onkeyup="searchUsers()">
                </div>
                <div class="user-container">
                    <table id="userTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Storage Used (KB)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['name']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['storage_used']; ?></td>
                                    <td>
                                    <button class="delete-button" data-user-id="<?php echo $user['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Delete User Modal -->
            <div id="delete-user-modal" class="modal">
            <div class="modal-content">
                <h3>Are you sure you want to delete this user?</h3>
                <p id="user-info"></p>
                <div class="modal-actions">
                <button id="confirm-delete" class="confirm-button">Yes, Delete</button>
                <button id="cancel-delete" class="cancel-button">Cancel</button>
                </div>
            </div>
            </div>

            <!-- Flashcard Management Section -->
            <div class="flashcard-management">
            <h2>Flashcard Management</h2>
            <div class="search-bar">
                <input type="text" id="flashcardSearch" placeholder="Search flashcard ID..." onkeyup="searchFlashcards()">
            </div>
            <div class="flashcard-container">
                <table>
                    <thead>
                        <tr>
                            <th>Flashcard ID</th>
                            <th>Folder ID</th>
                            <th>User ID</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch flashcards from the database
                        $query = "SELECT flashcards.flashcard_id, flashcards.folder_id, flashcards.user_id, flashcards.question, flashcards.answer, users.username 
                                FROM flashcards 
                                JOIN users ON flashcards.user_id = users.id";
                        $result = mysqli_query($conn, $query);

                        // Loop through and display each flashcard
                        while ($flashcard = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $flashcard['flashcard_id'] . "</td>";
                            echo "<td>" . $flashcard['folder_id'] . "</td>";
                            echo "<td>" . $flashcard['user_id'] . "</td>";
                            echo "<td>" . $flashcard['question'] . "</td>";
                            echo "<td>" . $flashcard['answer'] . "</td>";
                            echo "<td>
                                    <button class='delete-flashcard-button' data-flashcard-id='" . $flashcard['flashcard_id'] . "'>Delete</button>
                                </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Flashcard Modal -->
        <div id="delete-flashcard-modal" class="modal">
            <div class="modal-content">
                <h3>Are you sure you want to delete this flashcard?</h3>
                <p id="flashcard-info"></p>
                <div class="modal-actions">
                    <button id="confirm-delete-flashcard" class="confirm-button">Yes, Delete</button>
                    <button id="cancel-delete-flashcard" class="cancel-button">Cancel</button>
                </div>
            </div>
        </div>

        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="confirmation-modal">
        <div class="confirmation-content">
            <p class="confirmation-header">Are you sure you want to log out?</p>
            <div class="confirmation-buttons">
                <button class="confirm" onclick="confirmLogout()">Log Out</button>
                <button class="cancel" onclick="closeLogoutModal()">Cancel</button>
            </div>
        </div>
    </div>

       <!-- Edit Account Form for Admin (Initially Hidden) -->
    <div class="modal-overlay" id="modal-overlay"></div>
<div id="edit-account-form" class="edit-account-form" style="display: none;">
    <h3>Edit Admin Profile</h3>

    <!-- Profile Picture -->
    <form id="profile-picture-form" action="updateadmin.php" method="POST" enctype="multipart/form-data">
        <label for="profile_picture">Change Profile Picture:</label>
        <input type="file" name="profile_picture" id="profile_picture">
        <span id="profile-picture-error" class="error-message"></span> <!-- Error message -->
        <button type="submit">Update Profile Picture</button>
    </form>

    <!-- Name Update -->
    <form id="name-form" action="updateadmin.php" method="POST">
        <label for="name">Update Name:</label>
        <input type="text" name="name" id="name" value="<?php echo $admin_name; ?>">
        <span id="name-error" class="error-message"></span> <!-- Error message -->
        <button type="submit">Update Name</button>
    </form>

    <!-- Username Update -->
    <form id="username-form" action="updateadmin.php" method="POST">
        <label for="username">Update Username:</label>
        <input type="text" name="username" id="username" value="<?php echo $admin_username; ?>">
        <span id="username-error" class="error-message"></span> <!-- Error message -->
        <button type="submit">Update Username</button>
    </form>

    <!-- Password Update -->
    <form id="password-form" action="updateadmin.php" method="POST">
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password">
        <span id="password-error" class="error-message"></span> <!-- Error message -->
        <button type="submit">Update Password</button>
    </form>

    <!-- Close Button -->
    <button type="button" id="close-edit-profile-btn">Close</button>
</div>

    <script>
    // Real-time clock
    function updateClock() {
        const timeElement = document.getElementById('time');
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        timeElement.textContent = `${hours}:${minutes}:${seconds}`;
    }

    setInterval(updateClock, 1000);
    updateClock(); // Initial call to set the clock immediately

    // Modal handling and delete user
    const modal = document.getElementById("delete-user-modal");
    const confirmDeleteBtn = document.getElementById("confirm-delete");
    const cancelDeleteBtn = document.getElementById("cancel-delete");
    let userIdToDelete = null;

    // Open modal when delete button is clicked
    document.querySelectorAll(".delete-button").forEach(button => {
        button.addEventListener("click", function() {
            userIdToDelete = this.getAttribute("data-user-id");
            modal.classList.add("show");
            document.getElementById("user-info").textContent = `User ID: ${userIdToDelete}`;
        });
    });

    // Close modal on cancel
    cancelDeleteBtn.onclick = () => {
        modal.classList.remove("show");
    };

    // Confirm deletion and update table dynamically
    confirmDeleteBtn.onclick = () => {
        if (userIdToDelete) {
            const formData = new FormData();
            formData.append("delete_user_id", userIdToDelete);

            // Send delete request to the server using fetch
            fetch(window.location.href, {
                method: "POST",
                body: formData,
            })
            .then(response => response.text())  // Parse the response as plain text
            .then(data => {
                const [status, message] = data.split('|');  // Split by the delimiter

                // Show alert based on the response status
                if (status === "success") {
                    alert("Success: " + message);  // Show success message
                    
                    // Remove the deleted user from the table
                    const row = document.querySelector(`tr[data-user-id="${userIdToDelete}"]`);
                    if (row) {
                        row.remove(); // Remove the row from the table
                    }
                } else {
                    alert("Error: " + message);  // Show error message
                }
            })
            .catch(error => {
                alert("Error", "An unexpected error occurred: " + error, "error");
            });

            // Close modal
            modal.style.display = "none";
        }
    };


    // Search users function
    function searchUsers() {
        const input = document.getElementById('search');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('userTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }

            rows[i].style.display = match ? '' : 'none';
        }
    }

    // Search function for flashcards
    function searchFlashcards() {
        var input, filter, table, rows, td, i, txtValue;
        input = document.getElementById("flashcardSearch");
        filter = input.value.toUpperCase();
        table = document.querySelector(".flashcard-container table");
        rows = table.getElementsByTagName("tr");

        for (i = 1; i < rows.length; i++) {
            td = rows[i].getElementsByTagName("td")[0]; // Flashcard ID column
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }

    // Modal handling and delete flashcard
    const flashcardModal = document.getElementById("delete-flashcard-modal");
    const confirmDeleteFlashcardBtn = document.getElementById("confirm-delete-flashcard");
    const cancelDeleteFlashcardBtn = document.getElementById("cancel-delete-flashcard");
    let flashcardIdToDelete = null;

    // Open modal when delete button is clicked
    document.querySelectorAll(".delete-flashcard-button").forEach(button => {
        button.addEventListener("click", function() {
            flashcardIdToDelete = this.getAttribute("data-flashcard-id");
            flashcardModal.classList.add("show");
            document.getElementById("flashcard-info").textContent = `Flashcard ID: ${flashcardIdToDelete}`;
        });
    });

    // Close modal on cancel
    cancelDeleteFlashcardBtn.onclick = () => {
        flashcardModal.classList.remove("show");
    };

    // Confirm deletion and update table dynamically
    confirmDeleteFlashcardBtn.onclick = () => {
        if (flashcardIdToDelete) {
            const formData = new FormData();
            formData.append("delete_flashcard_id", flashcardIdToDelete);

            // Send delete request to the server using fetch
            fetch(window.location.href, {
                method: "POST",
                body: formData,
            })
            .then(response => response.text())  // Parse the response as plain text
            .then(data => {
                const [status, message] = data.split('|');  // Split by the delimiter

                // Show alert based on the response status
                if (status === "success") {
                    alert("Success: " + message);  // Show success message
                    
                    // Remove the deleted flashcard from the table
                    const row = document.querySelector(`tr[data-flashcard-id="${flashcardIdToDelete}"]`);
                    if (row) {
                        row.remove(); // Remove the row from the table
                    }
                } else {
                    alert("Error: " + message);  // Show error message
                }
            })
            .catch(error => {
                alert("Error", "An unexpected error occurred: " + error, "error");
            });

            // Close modal
            flashcardModal.style.display = "none";
        }
    };

    //edit account function
    document.addEventListener('DOMContentLoaded', function () {
    const formProfilePicture = document.querySelector('#profile-picture-form');
    const formName = document.querySelector('#name-form');
    const formUsername = document.querySelector('#username-form');
    const formPassword = document.querySelector('#password-form');

    // Show the modal and overlay
    document.getElementById("editBtn").addEventListener("click", function () {
    document.getElementById("modal-overlay").style.display = "block";
    document.getElementById("edit-account-form").style.display = "block";
    });

    // Hide the modal and overlay
    document.getElementById("close-edit-profile-btn").addEventListener("click", function () {
    document.getElementById("modal-overlay").style.display = "none";
    document.getElementById("edit-account-form").style.display = "none";
    clearErrorMessages();
    });

    // Validation for Profile Picture
    formProfilePicture.addEventListener('submit', function (e) {
        const fileInput = document.getElementById('profile_picture');
        const errorMessage = document.getElementById('profile-picture-error');
        errorMessage.textContent = ""; // Reset error message

        // Check if file is selected
        if (!fileInput.files.length) {
            e.preventDefault();
            errorMessage.textContent = "Please select a profile picture.";
        }
    });

    // Validation for Name
    formName.addEventListener('submit', function (e) {
        const nameInput = document.getElementById('name');
        const errorMessage = document.getElementById('name-error');
        errorMessage.textContent = ""; // Reset error message

        // Check if name contains special characters
        const nameRegex = /^[A-Za-z\s]+$/; // Only letters and spaces allowed
        if (nameInput.value.trim() === "") {
            e.preventDefault();
            errorMessage.textContent = "Name cannot be empty.";
        } else if (!nameRegex.test(nameInput.value)) {
            e.preventDefault();
            errorMessage.textContent = "Name must not contain special characters.";
        }
    });

    // Validation for Username
    formUsername.addEventListener('submit', function (e) {
    const usernameInput = document.getElementById('username');
    const errorMessage = document.getElementById('username-error');
    errorMessage.textContent = ""; // Reset error message

    // Prevent form submission at the start
    e.preventDefault();

    // Check if username is empty
    if (usernameInput.value.trim() === "") {
        errorMessage.textContent = "Username cannot be empty.";
        return; // Stop further checks if empty
    } else if (usernameInput.value.length < 5) {
        errorMessage.textContent = "Username must be at least 5 characters.";
        return; // Stop further checks if too short
    } else {
        // Now, check if the username is unique via PHP
        const username = usernameInput.value.trim();

        // Use the current page URL to check in the same PHP file
        fetch(window.location.href, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'check_username=true&username=' + encodeURIComponent(username)
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                errorMessage.textContent = "Username is already taken. Please choose a different one.";
            } else {
                // Proceed with form submission if username is unique
                formUsername.submit();  // Only submit the form if validation is successful
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.textContent = "An error occurred. Please try again.";
        });
    }
});


    // Validation for Password
    formPassword.addEventListener('submit', function (e) {
        const passwordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const errorMessage = document.getElementById('password-error');
        errorMessage.textContent = ""; // Reset error message

        // Check if passwords match
        if (passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            errorMessage.textContent = "Passwords do not match.";
        } else if (passwordInput.value.length < 6) {
            e.preventDefault();
            errorMessage.textContent = "Password must be at least 6 characters.";
        }
    });

    // Function to clear error messages
    function clearErrorMessages() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(function (errorMessage) {
            errorMessage.textContent = ""; // Clear text content
        });
    }
});


    // Function to display the logout modal
    function logout() {
        document.getElementById('logout-modal').style.display = 'flex';
    }

    // Function to close the logout modal
    function closeLogoutModal() {
        document.getElementById('logout-modal').style.display = 'none';
    }

    // Function to handle logout confirmation
    function confirmLogout() {
        window.location.href = '../index.php';
    }

    </script>
</body>
</html>
