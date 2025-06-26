<?php
session_start();
require('../inc/connect.php'); // $conn2 contains both user and staff tables

$id_number = trim($_POST['id_Number']);
$ic_number = trim($_POST['IC_Number']);

// ✅ STEP 1: Check if ID and IC match in either 'user' or 'staff' table
$checkUser = $conn2->prepare("SELECT * FROM user WHERE Matric_Number = ? AND IC_Number = ?");
$checkUser->bind_param("ss", $id_number, $ic_number);
$checkUser->execute();
$resultUser = $checkUser->get_result();

$checkStaff = $conn2->prepare("SELECT * FROM staff WHERE Staff_ID = ? AND IC_Number = ?");
$checkStaff->bind_param("ss", $id_number, $ic_number);
$checkStaff->execute();
$resultStaff = $checkStaff->get_result();

if ($resultUser->num_rows === 0 && $resultStaff->num_rows === 0) {
    echo "Invalid ID number or IC number. Please contact admin.";
    header("refresh:3; url=login.html");
    exit();
}

// ✅ STEP 2: Check if IC exists in either 'user' or 'staff' table (already registered)
$stmtUser = $conn->prepare("SELECT * FROM user WHERE IC_Number = ?");
$stmtUser->bind_param("s", $ic_number);
$stmtUser->execute();
$existsInUser = $stmtUser->get_result();

if ($existsInUser->num_rows > 0) {
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