<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Review - UTeMHub</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="review.css">
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

  <section class="review">
    <h2>Product Title</h2>
    <div class="image-placeholder"></div>
    <p>Product Quality</p>
    <div class="stars">★★★★★</div>
    <p>Product Review</p>
    <textarea placeholder="Input text"></textarea>
    <div class="btn-group">
      <button class="cancel">Cancel</button>
      <button class="submit">Submit</button>
    </div>
  </section>
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
