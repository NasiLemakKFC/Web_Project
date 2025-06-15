<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UTeMHub - Home</title>
  <link rel="stylesheet" href="global.css">
</head>
<body>
 <header>
  <div class="logo">UTeMHub</div>
  <div class="menu-toggle">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
  </div>
  <nav>
    <ul>
      <li><a href="index.php">Utama</a></li>
      <li><a href="search.php">Cari Barang</a></li>
      <li><a href="register.php">Daftar Perniagaan</a></li>
      <li><a href="contact.php">Hubungi Kami</a></li>
    </ul>
  </nav>
  <div class="profile-icon">&#128100;</div>
</header>
  <main>
  <h1>Welcome to UTeMHub</h1>

  <section class="menu-container">
    <h2>Menu / Items</h2>
    <div class="menu-grid">
      <div class="menu-item">
        <img src="placeholder.png" alt="Item Image">
        <p>Nama Produk</p>
      </div>
      <div class="menu-item">
        <img src="placeholder.png" alt="Item Image">
        <p>Nama Produk</p>
      </div>
      <div class="menu-item">
        <img src="placeholder.png" alt="Item Image">
        <p>Nama Produk</p>
      </div>
      <!-- Add more items as needed -->
    </div>
  </section>
</main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('header nav');

        if (menuToggle && nav) {
            menuToggle.addEventListener('click', function() {
                nav.classList.toggle('active');
            });
        }
    });
</script>
</body>
</html>


/////////////////////////////////////////////
<?php
require('../inc/connect.php');

$sql = "SELECT Name, Picture, Price FROM item LIMIT 12";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Menu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Link the external CSS file -->
  <link rel="stylesheet" href="global.css">
</head>
<body>

<section class="menu-container">
  <h2>Menu / Items</h2>
  <div class="menu-grid">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="menu-item">
          <img src="../<?php echo htmlspecialchars($row['Picture']); ?>" alt="Product Image">
          <p><?php echo htmlspecialchars($row['Name']); ?></p>
          <p><?php echo htmlspecialchars($row['Price']); ?></p>
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
