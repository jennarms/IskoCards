<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Fetch folders for the user
$folder_sql = "SELECT * FROM folders WHERE user_id = '$user_id'";
$folder_result = mysqli_query($conn, $folder_sql);
$folders = mysqli_fetch_all($folder_result, MYSQLI_ASSOC);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check for success or error messages
$success_message = isset($_GET['success']) ? $_GET['success'] : (isset($_SESSION['success']) ? $_SESSION['success'] : '');
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
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
                <!-- Add Folder -->
            <img>
            <div id="add-folder-container">
            <button id="add-folder-button" onclick="openAddFolderModal()">
            <span class="icon">+</span>
        </button>
        </div>
        </div>
    </header>

    <!-- Add Folder Modal -->
    <div class="add-folder-modal">
        <div class="modal-content">
            <div class="modal-header">Add New Folder</div>
            <div class="modal-body">
                <input type="text" placeholder="Enter folder name" id="folder-name">
            </div>
            <div class="modal-footer">
                <button class="cancel" onclick="closeAddFolderModal()">Cancel</button>
                <button class="confirm" onclick="addFolder()">Add Folder</button>
            </div>
        </div>
    </div>

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
            <a href="about_website.php">About Website</a>
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
            <div id="alertMessage" 
                data-success-message="<?php echo htmlspecialchars($success_message); ?>" 
                data-error-message="<?php echo htmlspecialchars($error_message); ?>"></div>

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


    <section class="main-content">
    <div id="greeting-container">
        <h1>Welcome, <?php echo $user['name']; ?>!</h1>
        <p>Retain Knowledge Like Never Before</p>
    </div>
    <div class="search-bar">
    <input type="text" id="folder-search" placeholder="Search folders..." />
    </div>
    <div id="folder-list" class="folders-container">
    <?php foreach ($folders as $folder): ?>
        
        <div class="folder-card" data-folder-id="<?php echo $folder['folder_id']; ?>" onclick="openFlashcardsPage(<?php echo $folder['folder_id']; ?>)">
        <div class="folder-name"><?php echo htmlspecialchars($folder['folder_name']); ?></div>
        <img src="../assets/folder.png" alt="<?php echo htmlspecialchars($folder['folder_name']); ?>" class="folder-image">
        <div class="folder-actions">
            <img src="../assets/edit.png" alt="Edit Folder" class="edit-folder" onclick="openEditFolderModal(event, <?php echo $folder['folder_id']; ?>, '<?php echo htmlspecialchars($folder['folder_name']); ?>')">
            <img src="../assets/delete.png" alt="Delete Folder" class="delete-folder" onclick="openDeleteFolderModal(event, <?php echo $folder['folder_id']; ?>)">
        </div>
    </div>

    <?php endforeach; ?>
    </div>

    <!-- Edit Folder Modal -->
    <div id="edit-folder-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Folder</h2>
            </div>
            <div class="modal-body">
                <input type="text" id="edit-folder-name" placeholder="Enter new folder name">
            </div>
            <div class="modal-footer">
                <button class="cancel" onclick="closeEditFolderModal()">Cancel</button>
                <button class="confirm" onclick="applyFolderEdit()">Apply</button>
            </div>
        </div>
    </div>

    <!-- Delete Folder Modal -->
    <div id="delete-folder-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Delete Folder</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this folder?</p>
            </div>
            <div class="modal-footer">
                <button class="cancel" onclick="closeDeleteFolderModal()">Cancel</button>
                <button class="confirm" onclick="confirmFolderDeletion()">Confirm</button>
            </div>
        </div>
    </div>

    </section>

    <!-- Custom Alert Modal -->
    <div id="custom-alert" class="custom-alert">
        <div class="alert-content">
            <h3 id="alert-title"></h3>
            <p id="alert-message"></p>
        </div>
    </div>


    <script>
    function toggleDashboard() {
        const dashboard = document.getElementById('dashboard');
        dashboard.style.display = dashboard.style.display === 'block' ? 'none' : 'block';
    }

    // Modal open and close functions
    function openEditModal() {
        closeAllModals(); // Close all modals before opening the edit modal
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    }

    function closeEditFolderModal() {
    setTimeout(() => {
        document.getElementById('edit-folder-modal').style.display = 'none';
    }, 500); // Close after 0.5 seconds
    }

    // Function to check and show the alert based on the message
    document.addEventListener('DOMContentLoaded', function () {
        const alertMessage = document.getElementById('alertMessage');
        
        const successMessage = alertMessage.getAttribute('data-success-message');
        const errorMessage = alertMessage.getAttribute('data-error-message');

        if (successMessage) {
            showAlert('Success', successMessage, 'success');
        } else if (errorMessage) {
            showAlert('Error', errorMessage, 'error');
        }
    });
    

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

    document.addEventListener('DOMContentLoaded', () => {
        const profilePic = document.querySelector('.profile-pic');

        profilePic.addEventListener('click', () => {
            profilePic.classList.toggle('active');
        });
    });

    // Folder actions
    function openFlashcardsPage(folderId) {
    window.location.href = `flashcards.php?folder_id=${folderId}`;
}

function addFolder() {
    const folderName = document.getElementById('folder-name').value.trim();
    if (folderName) {
        fetch('folder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&folder_name=${encodeURIComponent(folderName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const folderList = document.getElementById('folder-list');
                const folderCard = document.createElement('div');
                folderCard.className = 'folder-card';
                folderCard.setAttribute('data-folder-id', data.folder_id); // Set the folder ID

                folderCard.innerHTML = `
                    <div class="folder-name">${data.folder_name}</div>
                    <img src="../assets/folder.png" alt="${data.folder_name}" class="folder-image">
                    <div class="folder-actions">
                        <img src="../assets/edit.png" alt="Edit Folder" class="edit-folder" onclick="openEditFolderModal(event, ${data.folder_id}, '${data.folder_name}')">
                        <img src="../assets/delete.png" alt="Delete Folder" class="delete-folder" onclick="openDeleteFolderModal(event, ${data.folder_id})">
                    </div>
                `;

                // Add click listener for navigating to the flashcards page
                folderCard.addEventListener('click', () => openFlashcardsPage(data.folder_id));

                folderList.appendChild(folderCard);
                closeAddFolderModal();
                document.getElementById('folder-name').value = '';
            } else {
                showAlert('Error', data.message, 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        showAlert('Error', 'Please enter a folder name.', 'error');
    }
}


// Variables to track the folder being edited or deleted
let folderToEditId = null;
let folderToDeleteId = null;

// Open the modal for editing a folder
function openEditFolderModal(event, folderId, currentName) {
    event.stopPropagation(); // Prevent navigation to flashcards page
    folderToEditId = folderId;
    document.getElementById('edit-folder-name').value = currentName;
    document.getElementById('edit-folder-modal').style.display = 'flex';
}

// Close the edit modal
function closeEditFolderModal() {
    document.getElementById('edit-folder-modal').style.display = 'none';
}

// Apply folder edit
function applyFolderEdit() {
    const newFolderName = document.getElementById('edit-folder-name').value.trim();
    if (newFolderName) {
        fetch('folder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=edit&folder_id=${folderToEditId}&folder_name=${encodeURIComponent(newFolderName)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAlert('Folder updated successfully.');
                updateFolderName(folderToEditId, newFolderName); // Dynamically update folder name
            } else {
                showAlert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        showAlert("Please enter a folder name.");
    }
    closeEditFolderModal();
}

function updateFolderName(folderId, newFolderName) {
    const folderCard = document.querySelector(`.folder-card[data-folder-id="${folderId}"]`);
    const folderNameElement = folderCard.querySelector('.folder-name');
    folderNameElement.textContent = newFolderName;
}

// Open the modal for deleting a folder
function openDeleteFolderModal(event, folderId) {
    event.stopPropagation(); // Prevent navigation to flashcards page
    folderToDeleteId = folderId;
    document.getElementById('delete-folder-modal').style.display = 'flex';
}

// Close the delete modal
function closeDeleteFolderModal() {
    setTimeout(() => {
        document.getElementById('delete-folder-modal').style.display = 'none';
    }, 500); // Close after 0.5 seconds
}

// Confirm folder deletion
function confirmFolderDeletion() {
    fetch('folder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&folder_id=${folderToDeleteId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert('Folder deleted successfully.');
            removeFolderFromList(folderToDeleteId); // Dynamically remove the folder from the list
        } else {
            showAlert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
    closeDeleteFolderModal();
}

function removeFolderFromList(folderId) {
    const folderCard = document.querySelector(`.folder-card[data-folder-id="${folderId}"]`);
    folderCard.remove(); // Remove the folder card from the DOM
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
        alertBox.style.backgroundColor = '#a1e3b7'; // Light pastel green for success
    } else if (type === 'error') {
        alertBox.style.backgroundColor = '#f8b0b0'; // Light pastel red for error
    } else {
        alertBox.style.backgroundColor = '#ffebf0'; // Default pastel pink
    }

    alertBox.style.display = 'block'; // Show the alert

    // Close the alert after 5 seconds
    setTimeout(() => {
        alertBox.style.display = 'none';
    }, 5000); // Hide after 5 seconds
}

    //search
    document.getElementById('folder-search').addEventListener('input', function () {
    const searchQuery = this.value.toLowerCase();
    const folderCards = document.querySelectorAll('.folder-card');
    
    folderCards.forEach(card => {
        const folderName = card.querySelector('.folder-name').textContent.toLowerCase();
        const folderActions = card.querySelector('.folder-actions');

        if (folderName.includes(searchQuery)) {
            card.style.display = ''; // Show the card
        } else {
            card.style.display = 'none'; // Hide the card
        }
    });
});

    // Show the modal
    function openAddFolderModal() {
        document.querySelector('.add-folder-modal').classList.add('active');
    }

    // Close the modal
    function closeAddFolderModal() {
        document.querySelector('.add-folder-modal').classList.remove('active');
    }

    // Close the modal when the user clicks the close button
    document.querySelector('.close-btn').addEventListener('click', closeAddFolderModal);

    // Close the modal when the user clicks the cancel button
    document.querySelector('.cancel').addEventListener('click', closeAddFolderModal);

    // Check if there's an error or success message in session and open modal accordingly
    window.onload = function() {
        <?php if ($success_message || $error_message): ?>
            openEditModal();
        <?php endif; ?>
    };

    function refreshFolderList() {
    fetch('fetch_folders.php') // Create a separate PHP script to fetch all folders
        .then(response => response.json())
        .then(data => {
            const folderList = document.getElementById('folder-list');
            folderList.innerHTML = ''; // Clear the current folder list
            data.folders.forEach(folder => {
                const folderCard = document.createElement('div');
                folderCard.className = 'folder-card';
                folderCard.setAttribute('data-folder-id', folder.folder_id);

                folderCard.innerHTML = `
                    <div class="folder-name">${folder.folder_name}</div>
                    <img src="../assets/folder.png" alt="${folder.folder_name}" class="folder-image">
                    <div class="folder-actions">
                        <img src="../assets/edit.png" alt="Edit Folder" class="edit-folder" onclick="openEditFolderModal(event, ${folder.folder_id}, '${folder.folder_name}')">
                        <img src="../assets/delete.png" alt="Delete Folder" class="delete-folder" onclick="openDeleteFolderModal(event, ${folder.folder_id})">
                    </div>
                `;

                folderCard.addEventListener('click', () => openFlashcardsPage(folder.folder_id));
                folderList.appendChild(folderCard);
            });
        })
        .catch(error => console.error('Error:', error));
}

</script>

</body>
</html>
