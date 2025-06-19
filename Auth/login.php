<?php
session_start();
require('../inc/connect.php');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    die("Username and password are required.");
}

// Get user record
$stmt = $conn->prepare("SELECT * FROM user WHERE Name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['Password'])) {
        // Store both username and User_ID in session
        $_SESSION['username'] = $username;
        $_SESSION['User_ID'] = $row['User_ID']; 

        echo "Login successful. Redirecting...";
        header("refresh:2; url=../Page/page3.php");
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}
?>
