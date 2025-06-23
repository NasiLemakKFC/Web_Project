<?php
session_start();
require('../inc/connect.php');

// Check if user is logged in
if (!isset($_SESSION['User_ID'])) {
    die("Unauthorized access. Please log in.");
}

$user_id = $_SESSION['User_ID'];

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
$category = trim($_POST['category'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$price = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = $_POST['category'];

// Check required fields
if (empty($title) || empty($category) || empty($quantity) || empty($price) || empty($description)) {
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

// Insert into database
$stmt = $conn->prepare("INSERT INTO item (Name, Category, Quantity, Price, Description, Store_ID, Picture, User_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssidsisi", $title, $category, $quantity, $price, $description, $store_id, $imageName, $user_id);

if ($stmt->execute()) {
    echo "Product posted successfully!";
    header("refresh:2; url=../Page/page3.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
