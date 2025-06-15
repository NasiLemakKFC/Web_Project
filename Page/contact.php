<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - UTeMHub</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="contact.css">
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

  <section class="contact-form">
    <h2>Contact Us</h2>
    <form id="contactForm">
      <input type="text" name="name" placeholder="Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="tel" name="phone" placeholder="Phone" required>
      <textarea name="message" placeholder="Message" required></textarea>
      <button type="submit">Submit</button>
    </form>
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
