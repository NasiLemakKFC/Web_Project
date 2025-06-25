<?php
session_start();
require('../inc/connect.php');

// Get category list from database
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
$categoryOptions = [];
while ($row = $catResult->fetch_assoc()) {
    $categoryOptions[] = $row['Category'];
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
  <title>Post Product</title>
  <link rel="stylesheet" href="style.css">
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

<main>
  <div class="form-card">
    <h2>Product Information</h2>
    <form action="post-product.php" method="POST" enctype="multipart/form-data">
      <div class="form-row">
        <input type="text" name="title" placeholder="Product title" required>
        <select name="category" required>
          <option value="" disabled selected>Select a category</option>
          <?php
          $catResult = $conn->query("SELECT Category_ID, Category FROM Categories");
          while ($cat = $catResult->fetch_assoc()) {
              echo '<option value="' . $cat['Category_ID'] . '">' . htmlspecialchars($cat['Category']) . '</option>';
          }
          ?>
        </select>

      </div>

      <div class="form-row">
        <input name="quantity" type="number" placeholder="Quantity" min="1" required>
        <input name="price" type="number" placeholder="Price (RM)" step="0.01" min="0" required>
      </div>

      <textarea name="description" placeholder="Write something to describe your product." required></textarea>

      <input type="file" name="media" id="mediaInput" accept="image/*" style="display: none">

      <h3>Media (Images)</h3>
      <div class="media-grid" id="mediaGrid">
        <div class="media-box add-media" id="addMediaBtn">+</div>
      </div>

      <button type="submit" class="post-btn">Post Product</button>
    </form>
  </div>
</main>

<script>
const addMediaBtn = document.getElementById('addMediaBtn');
const mediaInput = document.getElementById('mediaInput');
const mediaGrid = document.getElementById('mediaGrid');

addMediaBtn.addEventListener('click', () => {
  mediaInput.click();
});

mediaInput.addEventListener('change', () => {
  const file = mediaInput.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const existingImages = mediaGrid.querySelectorAll('.media-box:not(.add-media)');
      existingImages.forEach(img => img.remove());

      const imgBox = document.createElement('div');
      imgBox.classList.add('media-box');

      const img = document.createElement('img');
      img.src = e.target.result;
      img.alt = "Uploaded image";

      imgBox.appendChild(img);
      mediaGrid.insertBefore(imgBox, addMediaBtn);
    };
    reader.readAsDataURL(file);
  }
});
</script>
</body>
</html>
