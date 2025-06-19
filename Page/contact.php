<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - UTeMHub</title>
  <link rel="stylesheet" href="contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
    <h2>Contact Us</h2>
    <form id="contactForm">
      <input type="text" name="name" placeholder="Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="tel" name="phone" placeholder="Phone" required>
      <textarea name="message" placeholder="Message" required></textarea>
      <button type="submit">Submit</button>
    </form>
  </section>
  </div>

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
