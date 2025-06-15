<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - UTeMHub</title>
  <link rel="stylesheet" href="global.css">
  <link rel="stylesheet" href="profile.css">
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

  <section class="account">
    <h1>My Account</h1>
    <div class="tabs">
      <button class="active">My Profile</button>
      <button>Purchase History</button>
      <button>Product Dashboard</button>
      <button>Dashboard</button>
    </div>

    <div class="profile-form">
      <p>Username : <span>Enter Your Name</span></p>
      <label>Name :</label> <input type="text"  readonly>
      <label>Email :</label> <input type="email"  readonly>
      <label>Phone Number :</label> <input type="tel"  readonly>
      <label>Gender :</label>
      <label><input type="radio" name="gender" checked> Male</label>
      <label><input type="radio" name="gender"> Female</label>
      <label>Date of Birth :</label>
      <select>
        <option value="">Day</option>
        <?php for ($i = 1; $i <= 31; $i++) { echo "<option value=\"$i\">$i</option>"; } ?>
      </select>
      <select>
        <option value="">Month</option>
        <?php
          $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
          ];
          foreach ($months as $num => $name) { echo "<option value=\"$num\">$name</option>"; }
        ?>
      </select>
      <select>
        <option value="">Year</option>
        <?php
          $currentYear = date('Y');
          for ($i = $currentYear; $i >= $currentYear - 100; $i--) { echo "<option value=\"$i\">$i</option>"; }
        ?>
      </select>
      <button class="save">Save</button>
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