<?php
session_start();
require('../inc/connect.php');

// Check if user is logged in
if (!isset($_SESSION['User_ID'])) {
    die("Unauthorized access. Please log in.");
}

$user_id = $_SESSION['User_ID'];

// Get and validate form inputs
$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$price = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');

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
$stmt = $conn->prepare("INSERT INTO item (Name, Category, Quantity, Price, Description, Picture, User_ID) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $title, $category, $quantity, $price, $description, $imageName, $user_id);

if ($stmt->execute()) {
    echo "Product posted successfully!";
    header("refresh:2; url=../Page/index.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
