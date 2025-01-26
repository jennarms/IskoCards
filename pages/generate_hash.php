<?php
// This script will generate a hashed password for admin

// The password you want to hash
$password = 'admin123';

// Generate the hashed password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Output the hashed password so you can copy it
echo "Hashed password: " . $hashedPassword;
?>
