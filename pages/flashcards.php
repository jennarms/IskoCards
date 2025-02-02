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
$user_query = $conn->prepare("SELECT storage_used, storage_limit FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
$user_query->close();

$storage_used = $user['storage_used'];
$storage_limit = $user['storage_limit'];

// Get the folder_id from the URL or set a default value
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

// Fetch the total number of flashcards in this folder
$flashcards_query = $conn->prepare("SELECT COUNT(*) AS total_flashcards FROM flashcards WHERE user_id = ? AND folder_id = ?");
$flashcards_query->bind_param("ii", $user_id, $folder_id);
$flashcards_query->execute();
$flashcards_result = $flashcards_query->get_result()->fetch_assoc();
$total_flashcards = $flashcards_result['total_flashcards'];
$flashcards_query->close();

// Prepare the summary text based on the flashcard count
if ($total_flashcards == 1) {
    $flashcard_text = "You have 1 flashcard in this folder";
} elseif ($total_flashcards > 1) {
    $flashcard_text = "You have $total_flashcards flashcards in this folder";
} else {
    $flashcard_text = "You have 0 flashcards in this folder";
}

// Handle POST requests for adding, editing, and deleting flashcards
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Prevent action if the user has exceeded their storage limit
    if ($storage_used >= $storage_limit) {
        echo json_encode(['status' => 'error', 'message' => 'You have reached your storage limit. Cannot add, edit, or delete flashcards.']);
        exit();
    }

    // Add Flashcard
    if ($action === 'add') {
        $folder_id = intval($_POST['folder_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        $question = trim($_POST['front'] ?? '');
        $answer = trim($_POST['back'] ?? '');

        if ($folder_id > 0 && !empty($question) && !empty($answer)) {
            // Calculate the size of the flashcard in KB
            $flashcard_content = $question . $answer;
            $size_bytes = strlen($flashcard_content);  // Get size in bytes
            $size_kb = $size_bytes / 1024;  // Convert bytes to KB

            // Check if adding this flashcard will exceed the storage limit
            if ($storage_used + $size_kb > $storage_limit) {
                echo json_encode(['status' => 'error', 'message' => 'Adding this flashcard will exceed your storage limit.']);
                exit();
            }

            // Insert flashcard into the database
            $query = $conn->prepare("INSERT INTO flashcards (folder_id, user_id, question, answer, size_kb) VALUES (?, ?, ?, ?, ?)");
            $query->bind_param("iissd", $folder_id, $user_id, $question, $answer, $size_kb);

            if ($query->execute()) {
                // Recalculate the total storage used by the user
                $total_storage_used_query = $conn->prepare("SELECT SUM(size_kb) AS total_storage_used FROM flashcards WHERE user_id = ?");
                $total_storage_used_query->bind_param("i", $user_id);
                $total_storage_used_query->execute();
                $total_storage_used_result = $total_storage_used_query->get_result();
                $total_storage_used = $total_storage_used_result->fetch_assoc()['total_storage_used'];
                $total_storage_used_query->close();

                // Update the user's storage_used field
                $update_storage_query = $conn->prepare("UPDATE users SET storage_used = ? WHERE id = ?");
                $update_storage_query->bind_param("di", $total_storage_used, $user_id);
                $update_storage_query->execute();
                $update_storage_query->close();

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            }

            $query->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        }
        exit();
    }

    // Edit Flashcard
    if ($action === 'edit') {
        $flashcard_id = intval($_POST['flashcard_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
    
        if ($flashcard_id > 0 && !empty($question) && !empty($answer)) {
            // Fetch the current size of the flashcard
            $current_flashcard_query = $conn->prepare("SELECT size_kb FROM flashcards WHERE flashcard_id = ? AND user_id = ?");
            $current_flashcard_query->bind_param("ii", $flashcard_id, $user_id);
            $current_flashcard_query->execute();
            $current_flashcard = $current_flashcard_query->get_result()->fetch_assoc();
            $current_size_kb = $current_flashcard['size_kb'] ?? 0;
            $current_flashcard_query->close();
    
            // Calculate new size
            $flashcard_content = $question . $answer;
            $size_bytes = strlen($flashcard_content);
            $size_kb = $size_bytes / 1024;
    
            // Check storage limit
            if ($storage_used - $current_size_kb + $size_kb > $storage_limit) {
                echo json_encode(['status' => 'error', 'message' => 'Updating this flashcard will exceed your storage limit.']);
                exit();
            }
    
            // Update flashcard
            $query = $conn->prepare("UPDATE flashcards SET question = ?, answer = ?, size_kb = ? WHERE flashcard_id = ? AND user_id = ?");
            $query->bind_param("ssdii", $question, $answer, $size_kb, $flashcard_id, $user_id);
    
            if ($query->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            }
            $query->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        }
        exit();
    }
    

    // Delete Flashcard
    if ($action === 'delete') {
        $flashcard_id = intval($_POST['flashcard_id'] ?? 0);
        $user_id = $_SESSION['user_id']; // Ensure user_id is set

        if ($flashcard_id > 0) {
            // Delete flashcard from the database
            $query = $conn->prepare("DELETE FROM flashcards WHERE flashcard_id = ? AND user_id = ?");
            $query->bind_param("ii", $flashcard_id, $user_id);

            if ($query->execute()) {
                // Recalculate the total storage used by the user
                $total_storage_used_query = $conn->prepare("SELECT SUM(size_kb) AS total_storage_used FROM flashcards WHERE user_id = ?");
                $total_storage_used_query->bind_param("i", $user_id);
                $total_storage_used_query->execute();
                $total_storage_used_result = $total_storage_used_query->get_result();
                $total_storage_used = $total_storage_used_result->fetch_assoc()['total_storage_used'];
                $total_storage_used_query->close();

                // Update the user's storage_used field
                $update_storage_query = $conn->prepare("UPDATE users SET storage_used = ? WHERE id = ?");
                $update_storage_query->bind_param("di", $total_storage_used, $user_id);
                $update_storage_query->execute();
                $update_storage_query->close();

                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            }

            $query->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid flashcard ID.']);
        }
        exit();
    }

    // Fetch Flashcard details for editing
    if ($action === 'get_flashcard') {
        $flashcard_id = intval($_POST['flashcard_id'] ?? 0);
        $user_id = $_SESSION['user_id']; // Ensure user_id is set

        if ($flashcard_id > 0) {
            // Fetch the flashcard from the database
            $query = $conn->prepare("SELECT question, answer FROM flashcards WHERE flashcard_id = ? AND user_id = ?");
            $query->bind_param("ii", $flashcard_id, $user_id);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows > 0) {
                $flashcard = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'flashcard' => $flashcard]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Flashcard not found or you do not have permission to edit it.']);
            }

            $query->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid flashcard ID.']);
        }
        exit();
    }
}

// Get the folder ID from the URL
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

// Fetch folder details
$folder_query = $conn->prepare("SELECT folder_name FROM folders WHERE folder_id = ? AND user_id = ?");
$folder_query->bind_param("ii", $folder_id, $user_id);
$folder_query->execute();
$folder = $folder_query->get_result()->fetch_assoc();
$folder_name = $folder['folder_name'] ?? "Unknown Folder";
$folder_query->close();

// Fetch flashcards
$flashcards_query = $conn->prepare("SELECT * FROM flashcards WHERE folder_id = ? AND user_id = ?");
$flashcards_query->bind_param("ii", $folder_id, $user_id);
$flashcards_query->execute();
$flashcards = $flashcards_query->get_result()->fetch_all(MYSQLI_ASSOC);
$flashcards_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcards - IskoCards</title>
    <link rel="stylesheet" href="../css/flashcardpage.css">
</head>
<body>
    <!-- Header -->
    <header>
    <div class="header-actions">
        <img 
            src="../assets/home.png" 
            alt="Home Icon" 
            class="profile-pic" 
            onclick="location.href='home.php'">
    </div>
    </header>

    <!-- Top Section -->
    <section class="top-section">
        <div class="folder-name">
            <h1><?php echo htmlspecialchars($folder_name); ?></h1>
        </div>
        <div class="actions">
            <button onclick="reviewFlashcards()">Review Flashcards</button>
            <button onclick="openAddFlashcardModal()">Add Flashcard</button>
        </div>
    </section>

    <!-- Edit Flashcard Modal -->
    <div id="edit-flashcard-modal" class="modal">
        <div class="modal-content">
            <h2>Edit Flashcard</h2>
            <p>Question</p>
            <input type="text" id="edit-question" placeholder="Enter new question">
            <p>Answer</p>
            <input type="text" id="edit-answer" placeholder="Enter new answer">
            <div class="modal-footer">
                <button class="cancel" onclick="closeEditFlashcardModal()">Cancel</button>
                <button class="confirm" onclick="saveEditedFlashcard()">Save</button>
            </div>
        </div>
    </div>

    <!-- Delete Flashcard Modal -->
    <div id="delete-flashcard-modal" class="modal">
        <div class="modal-content">
            <h2>Are you sure you want to delete this flashcard?</h2>
            <div class="modal-footer">
                <button class="cancel" onclick="closeDeleteFlashcardModal()">Cancel</button>
                <button class="confirm" onclick="confirmDeleteFlashcard()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <section class="search-bar">
        <input type="text" id="search-input" placeholder="Search flashcard questions..." onkeyup="filterFlashcards()">
        <span class="flashcard-count"><?php echo $flashcard_text; ?></span>
    </section>

    <!-- Main Section -->
    <section class="main-section">
        <div class="flashcard-container" id="flashcard-container">
            <?php foreach ($flashcards as $index => $flashcard): ?>
                <div class="flashcard-item">
                    <span>Question <?php echo $index + 1; ?></span>
                    <p class="question"><?php echo htmlspecialchars($flashcard['question']); ?></p>
                    <div class="actions">
                        <button onclick="editFlashcard(<?php echo $flashcard['flashcard_id']; ?>)">
                            <img src="../assets/edit.png" alt="Edit" style="width: 20px; height: 20px;">
                        </button>
                        <button onclick="deleteFlashcard(<?php echo $flashcard['flashcard_id']; ?>)">
                            <img src="../assets/delete.png" alt="Delete" style="width: 20px; height: 20px;">
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>


    <!-- Add Flashcard Modal -->
    <div id="add-flashcard-modal" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Add Flashcard</h2>
            <div class="modal-body">
                <input type="text" id="question" placeholder="Enter question">
                <input type="text" id="answer" placeholder="Enter answer">
            </div>
            <div class="modal-footer">
                <button class="confirm" onclick="addFlashcard()">Add</button>
                <button class="cancel" onclick="closeAddFlashcardModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="custom-alert" class="custom-alert">
        <div class="alert-content">
            <h3 id="alert-title"></h3>
            <p id="alert-message"></p>
        </div>
    </div>


    <script>
        function showAlert(title, message, type = 'success') {
        const alertBox = document.getElementById('custom-alert');
        const alertTitle = document.getElementById('alert-title');
        const alertMessage = document.getElementById('alert-message');

        // Set the title and message
        alertTitle.textContent = title;
        alertMessage.textContent = message;

        // Set the background color based on the type
        if (type === 'success') {
            alertBox.style.backgroundColor = '#a1e3b7'; // Green for success
        } else if (type === 'error') {
            alertBox.style.backgroundColor = '#f8b0b0'; // Red for error
        } else {
            alertBox.style.backgroundColor = '#ffebf0'; // Default pink
        }

        // Show the alert
        alertBox.style.display = 'block';

        // Hide the alert after 5 seconds
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 5000);
    }

        function openAddFlashcardModal() {
            document.getElementById('add-flashcard-modal').classList.add('active');
        }

        function closeAddFlashcardModal() {
            document.getElementById('add-flashcard-modal').classList.remove('active');
        }

        function addFlashcard() {
            const question = document.getElementById('question').value.trim();
            const answer = document.getElementById('answer').value.trim();
            const folderId = <?php echo $folder_id; ?>;

            if (question && answer) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=add&folder_id=${folderId}&front=${encodeURIComponent(question)}&back=${encodeURIComponent(answer)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        showAlert('Error adding flashcard: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            } else {
                showAlert('Please fill in both fields.');
            }
        }

        function reviewFlashcards() {
            // Get the total number of flashcards available in the current folder
            const flashcards = <?php echo json_encode($flashcards); ?>;
            
            if (flashcards.length === 0) {
                // Show alert if no flashcards exist
                showAlert('Error', 'No flashcards available for review.');
                return; // Stop the function execution
            }

            // Redirect to the review page if there are flashcards
            location.href = `review.php?folder_id=<?php echo $folder_id; ?>`;
        }


        function editFlashcard(flashcardId) {
            document.getElementById('edit-flashcard-modal').style.display = 'flex';
            window.flashcardToEdit = flashcardId; // Store the flashcard ID for editing
            // Fetch the current question and answer
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_flashcard&flashcard_id=${flashcardId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('edit-question').value = data.flashcard.question;
                    document.getElementById('edit-answer').value = data.flashcard.answer;
                } else {
                    showAlert('Error fetching flashcard details: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function closeEditFlashcardModal() {
            document.getElementById('edit-flashcard-modal').style.display = 'none';
        }

        function saveEditedFlashcard() {
        const question = document.getElementById('edit-question').value.trim();
        const answer = document.getElementById('edit-answer').value.trim();
        const flashcardId = window.flashcardToEdit;

        console.log("Saving flashcard: ", question, answer); // Debugging line

        if (question && answer) {
            const requestData = `action=edit&flashcard_id=${flashcardId}&question=${encodeURIComponent(question)}&answer=${encodeURIComponent(answer)}`;
            console.log("Sending request data: ", requestData); // Log request data
            
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: requestData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Log the response to check if it's successful
                if (data.status === 'success') {
                    showAlert('Flashcard updated successfully!'); // Show success alert
                    setTimeout(() => location.reload(), 1500); // Reload after 1.5 seconds to allow user to see the message
                } else {
                    showAlert('Error saving flashcard: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An unexpected error occurred. Please try again.', 'error');
            });
        } else {
            showAlert('Please fill in both fields.', 'warning');
        }
    }


        function deleteFlashcard(flashcardId) {
            document.getElementById('delete-flashcard-modal').style.display = 'flex';
            window.flashcardToDelete = flashcardId; // Store the flashcard ID for deletion
        }

        function closeDeleteFlashcardModal() {
            document.getElementById('delete-flashcard-modal').style.display = 'none';
        }

        function confirmDeleteFlashcard() {
            const flashcardId = window.flashcardToDelete;

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&flashcard_id=${flashcardId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    showAlert('Error deleting flashcard: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function filterFlashcards() {
    const searchInput = document.getElementById('search-input').value.toLowerCase();
    const flashcards = document.querySelectorAll('.flashcard-item');

    flashcards.forEach(flashcard => {
        const question = flashcard.querySelector('.question').textContent.toLowerCase();
        if (question.includes(searchInput)) {
            flashcard.style.display = 'block';
        } else {
            flashcard.style.display = 'none';
        }
    });
}
function toggleFlashcardContainer() {
    const flashcardContainer = document.getElementById('flashcard-container');
    const flashcards = flashcardContainer.querySelectorAll('.flashcard-item');
    
    if (flashcards.length === 0) {
        flashcardContainer.style.display = 'none'; // Hide container if no flashcards
    } else {
        flashcardContainer.style.display = 'flex'; // Show container if flashcards exist
    }
}

// Call the function on page load or when flashcards are updated
document.addEventListener('DOMContentLoaded', toggleFlashcardContainer);

    </script>
</body>
</html>
