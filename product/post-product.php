<?php
session_start();
require('../inc/connect.php');

// Check if user is logged in
if (!isset($_SESSION['User_ID'])) {
    die("Unauthorized access. Please log in.");
}

$user_id = $_SESSION['User_ID'];

// Get store ID from user
$store_id = null;
$stmt = $conn->prepare("SELECT Store_ID FROM user WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($store_id);
$stmt->fetch();
$stmt->close();

if (!$store_id) {
    die("No store found for this user.");
}

// Get and validate form inputs
$title = trim($_POST['title'] ?? '');
$category_id = intval($_POST['category'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');

// Check required fields
if (empty($title) || $category_id <= 0 || $quantity <= 0 || $price <= 0 || empty($description)) {
    die("All fields are required.");
}

// Handle image upload
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$imageName = 'default.png';
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $imageTmp = $_FILES['media']['tmp_name'];
    $originalName = basename($_FILES['media']['name']);
    $imageName = uniqid('img_') . '_' . $originalName;
    $targetFilePath = $uploadDir . $imageName;

    if (!move_uploaded_file($imageTmp, $targetFilePath)) {
        die("Image upload failed.");
    }
}

// Insert into database (Category now uses Category_ID as int)
$stmt = $conn->prepare("INSERT INTO item (Name, Category_ID, Quantity, Price, Description, Store_ID, Picture, User_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siidsisi", $title, $category_id, $quantity, $price, $description, $store_id, $imageName, $user_id);

if ($stmt->execute()) {
    echo "<script>
        alert('Product posted successfully!');
        window.location.href = '../Page/page3.php';
    </script>";
} else {
    echo "<script>
        alert('Error: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}

$stmt->close();
$conn->close();
?>