<?php
session_start();
require('../inc/connect.php');

$username = $_POST['username'];
$password = $_POST['password'];

// Get hashed password from DB
$stmt = $conn1->prepare("SELECT * FROM user WHERE Name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['Password'])) {
        $_SESSION['username'] = $username;
        echo "Login successful. Redirecting...";
        header("refresh:2; url=/Page/index.html");
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}
?>
