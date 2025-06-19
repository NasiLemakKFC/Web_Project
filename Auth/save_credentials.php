<?php
session_start();
require('../inc/connect.php');

if (!isset($_SESSION['id_Number'], $_SESSION['IC_Number'])) {
    echo "Session expired.";
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);
$UserRole = "User";
$Affiliated = "Buyer";
$IC_Number = $_SESSION['IC_Number'];

// Check if username already exists
$checkStmt = $conn->prepare("SELECT 1 FROM user WHERE Name = ?");
$checkStmt->bind_param("s", $username);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "Username already exists. Please choose another.";
    header("refresh:3; url=set_credentials.php"); // Adjust this link to your form
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO user (Name, Password, Role, IC_Number, Affiliate) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $hashedPassword, $UserRole, $IC_Number, $Affiliated);

if ($stmt->execute()) {
    session_destroy();
    echo "Account created. Redirecting to login...";
    header("refresh:2; url=login.html");
} else {
    echo "Error saving credentials. Please try again.";
}
?>
