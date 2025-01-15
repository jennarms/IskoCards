<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'No action specified.']);
    exit();
}

// Check if the folder name already exists
function checkIfFolderExists($folder_name, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM folders WHERE folder_name = ? AND user_id = ?");
    $stmt->bind_param("si", $folder_name, $user_id);
    $stmt->execute();
    
    // Fetch the result directly using fetch()
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $count = $row[0];
    
    $stmt->close();
    
    return $count > 0; // Return true if the folder exists
}

switch ($action) {
    case 'add':
        $folder_name = trim($_POST['folder_name'] ?? '');
        if ($folder_name) {
            // Check if the folder already exists
            if (checkIfFolderExists($folder_name, $user_id)) {
                // If it exists, append a number to the folder name
                $i = 1;
                $new_folder_name = $folder_name . " " . $i;
                while (checkIfFolderExists($new_folder_name, $user_id)) {
                    $i++;
                    $new_folder_name = $folder_name . " " . $i;
                }
                $folder_name = $new_folder_name; // Update folder name with number
            }

            // Add the folder to the database
            $stmt = $conn->prepare("INSERT INTO folders (user_id, folder_name) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $folder_name);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Folder added successfully.', 'folder_id' => $stmt->insert_id, 'folder_name' => $folder_name]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add folder.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Folder name is required.']);
        }
        break;

    case 'edit':
        $folder_id = intval($_POST['folder_id'] ?? 0);
        $folder_name = trim($_POST['folder_name'] ?? '');
        if ($folder_id && $folder_name) {
            $stmt = $conn->prepare("UPDATE folders SET folder_name = ? WHERE folder_id = ? AND user_id = ?");
            $stmt->bind_param("sii", $folder_name, $folder_id, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Folder updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update folder.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid folder ID or name.']);
        }
        break;

    case 'delete':
        $folder_id = intval($_POST['folder_id'] ?? 0);
        if ($folder_id) {
            $stmt = $conn->prepare("DELETE FROM folders WHERE folder_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $folder_id, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Folder deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete folder.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid folder ID.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

$conn->close();
?>
