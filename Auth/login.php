<?php
session_start();
require('../inc/connect.php');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo "<script>alert('Username and password are required.'); window.location.href='login.html';</script>";
    exit;
}

// Get user record
$stmt = $conn->prepare("SELECT * FROM user WHERE Name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if ($row['Status'] !== 'Active') {
        echo "<script>alert('Your account is deactivated.'); window.location.href='login.html';</script>";
        exit;
    }

    if (password_verify($password, $row['Password']) && $row['Role'] === 'User') {
        $_SESSION['username'] = $username;
        $_SESSION['User_ID'] = $row['User_ID'];

        echo "<script>alert('Login successful.'); window.location.href='../Page/page3.php';</script>";
        exit;
    } else if (password_verify($password, $row['Password']) && $row['Role'] === 'Admin') {
        echo "<script>alert('Login successful.'); window.location.href='../adminPage/dashboard.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('User not found.'); window.location.href='login.html';</script>";
    exit;
}
?>

