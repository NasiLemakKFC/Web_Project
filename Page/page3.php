<?php
require('../inc/connect.php');

$sql = "SELECT Item_ID, Name, Picture, Price FROM item LIMIT 12";
$result = $conn->query($sql);

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
  <header class="navbar">
    <div class="logo">UTeMHub</div>
    <nav>
      <a href="../Page/page3.php">Home Page</a>
      <a href="../Page/page4.php">Search Item</a>
      <a href="../product/store_register.php">Apply as Seller</a>
      <a href="../Page/contact.php">Contact Us</a>
    </nav>
      <div class="profile-cart">
        <a href="../auth/logout.php"><i class="fa-regular fa-user"></i></a>
      </div>

  </header>

  <section class="hero">
    <h1>Welcome To UtemHub !</h1>
    <p>Build Marketing & Networking Online</p>
    <a href="register.php"><button>Daftar Perniagaan</button></a>
  </section>

  <section class="categories">
    <h2>Categories</h2>
    <div class="category-list">
      <?php
        $resultCategory = $conn->query($sqlCategory);
        while ($rows = $resultCategory->fetch_assoc()) {
          echo '<div class="category-item">';
          echo '<img src="../media/' . $rows['Picture'] . '" alt="Category Image" class="image-box">';
          echo '<p>' . $rows['Category'] . '</p>';
          echo '</div>';
        }
      ?>
    </div>
  </section>


  <section class="top-products">
    <h2>Top Products</h2>
    <div class="product-grid">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="product-item">
            <a href="page5.php?id=<?php echo $row['Item_ID']; ?>">
              <img src="../product/uploads/<?php echo htmlspecialchars($row['Picture']); ?>" alt="Product Image" class="image-box">
            </a>
            <p><?php echo htmlspecialchars($row['Name']); ?></p>
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
