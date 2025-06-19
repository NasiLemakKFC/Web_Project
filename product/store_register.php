<?php
session_start();
require('../inc/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $storeName = $_POST["store_name"];
    $mobile = $_POST["mobile"];
    $userID = $_SESSION["UserID"];

    // Handle file upload
    $targetDir = "../media/";
    $fileName = basename($_FILES["picture"]["name"]);
    $targetFile = $targetDir . $fileName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is actual image
    $check = getimagesize($_FILES["picture"]["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File is not an image.');</script>";
        $uploadOk = 0;
    }

    // Check file size (max 2MB)
    if ($_FILES["picture"]["size"] > 2 * 1024 * 1024) {
        echo "<script>alert('Sorry, your file is too large.');</script>";
        $uploadOk = 0;
    }

    // Allow only jpg, jpeg, png
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        echo "<script>alert('Only JPG, JPEG, PNG files are allowed.');</script>";
        $uploadOk = 0;
    }

    if ($uploadOk && move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
        // Save to DB
        $stmt = $conn->prepare("INSERT INTO storetable (store_name, mobile, user_id, picture) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $storeName, $mobile, $userID, $fileName);

        if ($stmt->execute()) {
            echo "<script>alert('Store registered successfully!');</script>";
        } else {
            echo "<script>alert('Database error.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('File upload failed.');</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register Store - UTeMHub</title>
  <link rel="stylesheet" href="contact.css" />
</head>
<body>
  <header class="navbar">
    <div class="logo">UTeMHub</div>
    <nav>
      <a href="../Page/page3.php">Utama</a>
      <a href="../Page/page4.php">Cari Barang</a>
      <a href="../product/page10.html">Daftar Perniagaan</a>
      <a href="../Page/contact.php">Hubungi Kami</a>
    </nav>
    <div class="profile-cart">
      <a href="../auth/logout.php"><i class="fa-regular fa-user"></i></a>
    </div>
  </header>

  <div class="contact-wrapper">
    <section class="contact-form">
      <h2>Register Your Store</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="text" name="store_name" placeholder="Store Name" required>
        <input type="tel" name="mobile" placeholder="Mobile Number" required>
        <input type="file" name="picture" accept="image/*" required>
        <button type="submit">Register</button>
      </form>
    </section>
  </div>
</body>
</html>
