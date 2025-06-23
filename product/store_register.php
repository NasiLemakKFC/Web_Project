<?php
session_start();
require('../inc/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['User_ID'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $storeName = $_POST["store_name"];
    $mobile = $_POST["mobile"];
    $userID = $_SESSION["User_ID"];

// Handle file upload
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Create if not exists
}

    $imageName = 'default.png';
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $tmpFile = $_FILES['picture']['tmp_name'];
        $originalName = basename($_FILES['picture']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate image
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedTypes)) {
            die("Invalid file type. Only JPG, JPEG, and PNG allowed.");
        }

        if ($_FILES['picture']['size'] > 2 * 1024 * 1024) {
            die("File too large. Max 2MB allowed.");
        }

        $imageName = uniqid('store_') . '.' . $extension;
        $targetFile = $uploadDir . $imageName;

        if (!move_uploaded_file($tmpFile, $targetFile)) {
            die("Image upload failed.");
        }
    }

    // Check if store name already exists
    $check = $conn->prepare("SELECT * FROM storetable WHERE store_name = ?");
    $check->bind_param("s", $storeName);
    $check->execute();
    $check->store_result();

    $affiliated = "Seller";

    if ($check->num_rows > 0) {
        echo "<script>alert('Store name already exists. Please choose another.');</script>";
    } else {
        // Save to DB
        $stmt = $conn->prepare("INSERT INTO storetable (store_name, Phone_Number, picture) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $storeName, $mobile, $imageName);

        if ($stmt->execute()) {
                $storeID = $conn->insert_id; // Get the last inserted store ID
                $stmt = $conn->prepare("UPDATE user SET Affiliate = ?, Store_ID = ? WHERE User_ID = ?");
                $stmt->bind_param("sii", $affiliated, $storeID, $userID);
                $stmt->execute();
                $stmt->close();
            echo "<script>
                alert('Store registered successfully!');
                window.location.href = '../Page/page3.php';
            </script>";
            exit;
        } else {
            echo "<script>alert('Database error.');</script>";
        }

        $stmt->close();
    }
    $check->close();

}
$sqluser = "SELECT Affiliate FROM user WHERE User_ID = '$_SESSION[User_ID]'";
$result = $conn->query($sqluser);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register Store - UTeMHub</title>
  <link rel="stylesheet" href="../Page/contact.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <span class="logo-icon">📱</span>
            <span class="logo-text">UTeMHub</span>
        </div>
        <div class="nav-menu">
            <a href="../Page/page3.php">Home Page</a>
            <a href="../Page/page4.php">Search Item</a>
            <?php
            if ($user['Affiliate'] == "Buyer") {
                echo '<a href="../product/store_register.php">Apply as Seller</a>';
            } else {
                echo '<a href="../product/page10.php">Add Product</a>';
            }
            ?>
            <a href="../Page/contact.php">Contact Us</a>
        </div>
        <div class="nav-profile">
            <a href="../profile/account.php" class="profile-icon active">👤</a>
        </div>
    </div>
</nav>

    <div class="main-content">
    <div class="contact-container">
        <div class="contact-form-wrapper">
        <h2 class="contact-title">Register Your Store</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="contact-form">
            <div class="form-group">
            <input type="text" name="store_name" placeholder="Store Name" required>
            </div>

            <div class="form-group">
            <input type="tel" name="mobile" placeholder="Mobile Number" required>
            </div>

            <div class="form-group">
            <input type="file" name="picture" accept="image/*" required>
            </div>

            <button type="submit" class="submit-btn">Register</button>
        </form>
        </div>
    </div>
    </div>
</body>
</html>
