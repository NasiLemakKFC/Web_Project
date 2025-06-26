<?php
session_start();
require('../inc/connect.php');

if (!isset($_SESSION['id_Number'], $_SESSION['IC_Number'])) {
    echo "<script>alert('Session expired.'); window.location.href='login.html';</script>";
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
    echo "<script>alert('Username already exists. Please choose another.'); window.location.href='set_credentials.php';</script>";
    exit();
}

// Handle profile picture upload
$targetDir = "../profile/uploads/";
$defaultImage = "accountdefault.png";
$pictureName = $defaultImage;
$userStatus = "Active";

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['profile_picture']['tmp_name'];
    $originalName = basename($_FILES['profile_picture']['name']);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $newName = uniqid("profile_", true) . "." . $extension;

    // Only allow jpg, jpeg, png
    $allowed = ['jpg', 'jpeg', 'png'];
    if (in_array(strtolower($extension), $allowed)) {
        if (move_uploaded_file($tmpName, $targetDir . $newName)) {
            $pictureName = $newName;
        }
    }
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user with profile picture
$stmt = $conn->prepare("INSERT INTO user (Name, Password, Role, IC_Number, Affiliate, Picture, Status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $username, $hashedPassword, $UserRole, $IC_Number, $Affiliated, $pictureName, $userStatus);

if ($stmt->execute()) {
    session_destroy();
    echo "<script>alert('Account created. Redirecting to login...'); window.location.href='login.html';</script>";
} else {
    echo "<script>alert('Error saving credentials. Please try again.'); window.location.href='set_credentials.php';</script>";
}

