<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Here you would typically save to database or send email
    $_SESSION['success_message'] = "Terima kasih! Pesan anda telah diterima.";
    
    // Redirect to prevent form resubmission and clear POST data
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Check for success message from session
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Remove message after displaying
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - UTeMHub</title>
    <link rel="stylesheet" href="contact.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-icon">📱</span>
                <span class="logo-text">UTeMHub</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Utama</a></li>
                <li><a href="products.php">Cari Barang</a></li>
                <li><a href="services.php">Daftar Perniagaan</a></li>
                <li><a href="contact.php" class="active">Hubungi Kami</a></li>
            </ul>
            <div class="nav-profile">
                <a href="account.php" class="profile-icon">👤</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="contact-container">
            <div class="contact-form-wrapper">
                <h1 class="contact-title">Contact Us</h1>
                
                <?php if (!empty($success_message)): ?>
                    <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <form class="contact-form" method="POST" action="">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Name" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Phone" required>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="message" placeholder="Message" rows="6" required></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Submit</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success message after 5 seconds
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>