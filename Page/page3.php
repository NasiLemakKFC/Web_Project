<?php
session_start();
require('../inc/connect.php');

// Get current user info
$sqluser = "SELECT Affiliate, Store_ID FROM user WHERE User_ID = ?";
$stmtUser = $conn->prepare($sqluser);
$stmtUser->bind_param("i", $_SESSION['User_ID']);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$user = $userResult->fetch_assoc();
$currentStoreID = $user['Store_ID'];
$stmtUser->close();

// Adjust query depending on whether user has a Store_ID
if ($currentStoreID === null) {
    // If user has no store, just fetch all items
    $sql = "SELECT Item_ID, Name, Picture, Price, Item_Sold 
            FROM item 
            ORDER BY Item_Sold DESC 
            LIMIT 12";
    $stmt = $conn->prepare($sql);
} else {
    // Exclude items from the same store
    $sql = "SELECT Item_ID, Name, Picture, Price, Item_Sold 
            FROM item 
            WHERE Store_ID IS NULL OR Store_ID != ?
            ORDER BY Item_Sold DESC 
            LIMIT 12";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentStoreID);
}

$stmt->execute();
$resultItem = $stmt->get_result();

$sqlCategory = "SELECT * FROM Categories";
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UTeMHub</title>
  <link rel="stylesheet" href="page3.css">
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

  <section class="hero">
    <h1>Welcome To UtemHub !</h1>
    <p>Build Marketing & Networking Online</p>
  </section>

  <section class="categories">
    <h2>Categories</h2>
      <div class="category-list">
      <?php
        $resultCategory = $conn->query($sqlCategory);
        while ($rows = $resultCategory->fetch_assoc()) {
          $categoryID = (int)$rows['Category_ID'];
          echo '<a href="../Page/page4.php?category[]=' . $categoryID . '" class="category-item">';
          echo '<img src="../media/' . htmlspecialchars($rows['Picture']) . '" alt="Category Image" class="image-box">';
          echo '<p>' . htmlspecialchars($rows['Category']) . '</p>';
          echo '</a>';
        }
      ?>
  </div>
  </section>

  <section class="top-products">
    <h2>Top Products</h2>
    <div class="product-grid">
      <?php if ($resultItem->num_rows > 0): ?>
        <?php while($row = $resultItem->fetch_assoc()): ?>
          <div class="product-item">
            <a href="page5.php?id=<?php echo $row['Item_ID']; ?>">
              <img src="../product/uploads/<?php echo htmlspecialchars($row['Picture']); ?>" alt="Product Image" class="image-box">
            </a>
            <p><?php echo htmlspecialchars($row['Name']); ?></p>
            <p>Sold: <?php echo intval($row['Item_Sold']); ?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No products available.</p>
      <?php endif; ?>
    </div>
  </section>

</body>
</html>

<?php $conn->close(); ?>
