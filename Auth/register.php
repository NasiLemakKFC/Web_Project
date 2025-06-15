<?php
session_start();
require('../inc/connect.php');

$id_number = trim($_POST['id_Number']);
$ic_number = trim($_POST['IC_Number']);

// Check if user already registered
$stmt = $conn->prepare("SELECT * FROM user WHERE IC_Number = ?");
$stmt->bind_param("s", $ic_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "User already registered. Redirecting to login...";
    header("refresh:2; url=login.html");
    exit();
}

// ✅ Check if ID and IC number exist in a master table (e.g., 'students' or 'staff')
$checkStmt = $conn2->prepare("SELECT * FROM user WHERE Matric_Number = ? AND IC_Number = ?");
$checkStmt->bind_param("ss", $id_number, $ic_number);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $_SESSION['id_Number'] = $id_number;
    $_SESSION['IC_Number'] = $ic_number;
    header("Location: set_credentials.php");
    exit();
} else {
    echo "Invalid ID number or IC number. Please contact admin.";
    header("refresh:3; url=login.html");
    exit();
}
?>


