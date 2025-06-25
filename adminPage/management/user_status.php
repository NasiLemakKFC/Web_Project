<?php
require('../../inc/connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$userId = intval($_GET['id']);

// Fetch current status
$sql = "SELECT Status FROM user WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$newStatus = $user['Status'] === 'Active' ? 'Deactivated' : 'Active';

$update = $conn->prepare("UPDATE user SET Status = ? WHERE User_ID = ?");
$update->bind_param("si", $newStatus, $userId);

if ($update->execute()) {
    header("Location: userManage.php");
    exit();
} else {
    echo "Error updating status: " . $conn->error;
}
?>
