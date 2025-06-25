<?php
session_start();
require('../inc/connect.php'); // contains both $conn (for registered users) and $conn2 (for master records)

$id_number = trim($_POST['id_Number']);
$ic_number = trim($_POST['IC_Number']);

// ✅ STEP 1: Check if ID and IC match in master table (using $conn2)
$checkStmt = $conn2->prepare("SELECT * FROM user WHERE Matric_Number = ? AND IC_Number = ?");
$checkStmt->bind_param("ss", $id_number, $ic_number);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo "Invalid ID number or IC number. Please contact admin.";
    header("refresh:3; url=login.html");
    exit();
}

// ✅ STEP 2: Check if already registered (using $conn)
$stmt = $conn->prepare("SELECT * FROM user WHERE IC_Number = ?");
$stmt->bind_param("s", $ic_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "This IC is already registered. Redirecting to login...";
    header("refresh:3; url=login.html");
    exit();
}

// ✅ STEP 3: All clear — proceed
$_SESSION['id_Number'] = $id_number;
$_SESSION['IC_Number'] = $ic_number;
header("Location: set_credentials.php");
exit();
?>
