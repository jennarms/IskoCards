<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Profile Picture Update
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $file = $_FILES['profile_picture'];
    $file_name = basename($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_type = $file['type'];
    $file_size = $file['size'];

    // Define allowed file types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($file_type, $allowed_types)) {
        // Define the upload path
        $upload_dir = '../uploads/';
        $new_file_name = uniqid('profile_') . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $upload_path = $upload_dir . $new_file_name;

        // Move the uploaded file to the server
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Update the database with the new profile picture
            $sql = "UPDATE users SET profile_picture = '$new_file_name' WHERE id = '$user_id'";
            if (mysqli_query($conn, $sql)) {
                header("Location: home.php?success=Profile picture updated successfully.");
                exit();
            } else {
                $_SESSION['error'] = "Error updating profile picture.";
                header("Location: home.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Error uploading file.";
            header("Location: home.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid file type.";
        header("Location: home.php");
        exit();
    }
}

// Handle Name Update
if (isset($_POST['name']) && !empty($_POST['name'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $sql = "UPDATE users SET name = '$name' WHERE id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: home.php?success=Name updated successfully.");
        exit();
    } else {
        $_SESSION['error'] = "Error updating name.";
        header("Location: home.php");
        exit();
    }
}

// Handle Username Update
if (isset($_POST['username']) && !empty($_POST['username'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // Check if the username already exists
    $sql_check = "SELECT * FROM users WHERE username = '$username' AND id != '$user_id'";
    $result = mysqli_query($conn, $sql_check);
    if (mysqli_num_rows($result) == 0) {
        $sql = "UPDATE users SET username = '$username' WHERE id = '$user_id'";
        if (mysqli_query($conn, $sql)) {
            header("Location: home.php?success=Username updated successfully.");
            exit();
        } else {
            $_SESSION['error'] = "Error updating username.";
            header("Location: home.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Username already exists.";
        header("Location: home.php");
        exit();
    }
}

// Handle Password Update
if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate that passwords match
    if ($new_password === $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Password updated successfully.";
            header("Location: home.php");  // Redirect after success
            exit();
        } else {
            $_SESSION['error'] = "Error updating password.";
            header("Location: home.php");  // Stay on the same page if there's an error
            exit();
        }
    } else {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: home.php");  // Stay on the same page if passwords don't match
        exit();
    }
}




?>
