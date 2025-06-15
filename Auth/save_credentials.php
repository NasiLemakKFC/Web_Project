<?php
session_start();
require('../inc/connect.php');

if (!isset($_SESSION['id_Number'], $_SESSION['IC_Number'])) {
    echo "Session expired.";
    exit();
}

$username = trim($_POST['username']);
$password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // secure hash
$UserRole = "User";
$IC_Number = $_SESSION['IC_Number'];

// Insert user with hashed password
$stmt = $conn->prepare("INSERT INTO user (Name, Password, Role, IC_Number) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $password, $UserRole, $IC_Number);

if ($stmt->execute()) {
    session_destroy(); // clear session
    echo "Account created. Redirecting to login...";
    header("refresh:2; url=login.html");
} else {
    echo "Error saving credentials.";
}
?>
