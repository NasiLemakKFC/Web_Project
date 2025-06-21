<?php
session_start();

// Sample user data - in real application, this would come from database/session
$user = [
    'username' => 'Username',
    'name' => '',
    'email' => 'Email',
    'phone' => 'Phone Number',
    'gender' => '',
    'birth_day' => '',
    'birth_month' => '',
    'birth_year' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user['name'] = $_POST['name'] ?? $user['name'];
    $user['gender'] = $_POST['gender'] ?? $user['gender'];
    $user['birth_day'] = $_POST['birth_day'] ?? '';
    $user['birth_month'] = $_POST['birth_month'] ?? '';
    $user['birth_year'] = $_POST['birth_year'] ?? '';
    
    // Here you would typically update the database
    $_SESSION['success_message'] = "Profil telah dikemaskini!";
    
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
    <title>My Account - UTeMHub</title>
    <link rel="stylesheet" href="account.css">
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
                <li><a href="contact.php">Hubungi Kami</a></li>
            </ul>
            <div class="nav-profile">
                <a href="account.php" class="profile-icon active">👤</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="account-container">
            <h1 class="page-title">My Account</h1>
            
            <div class="account-tabs">
                <button class="tab-btn active" data-tab="profile">My Profile</button>
                <button class="tab-btn" data-tab="history">Purchase History</button>
                <button class="tab-btn" data-tab="products">Product Dashboard</button>
                <button class="tab-btn" data-tab="dashboard">Dashboard</button>
            </div>

            <div class="tab-content active" id="profile">
                <div class="profile-section">
                    <h2 class="section-title">My Profile</h2>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <form class="profile-form" method="POST" action="">
                        <div class="form-container">
                            <div class="form-row">
                                <label class="form-label">Username :</label>
                                <div class="form-value readonly"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Name :</label>
                                <input type="text" name="name" placeholder="Enter Your Name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Email :</label>
                                <div class="form-value readonly"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Phone Number :</label>
                                <div class="form-value readonly"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Gender :</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="gender" value="male" <?php echo $user['gender'] == 'male' ? 'checked' : ''; ?>>
                                        <span class="radio-custom"></span>
                                        Male
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="gender" value="female" <?php echo $user['gender'] == 'female' ? 'checked' : ''; ?>>
                                        <span class="radio-custom"></span>
                                        Female
                                    </label>
                                </div>
                            </div>

                            <div class="form-row">
                                <label class="form-label">Date of Birth :</label>
                                <div class="date-group">
                                    <select name="birth_day" class="date-select">
                                        <option value="">Day</option>
                                        <?php for($i = 1; $i <= 31; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $user['birth_day'] == $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    
                                    <select name="birth_month" class="date-select">
                                        <option value="">Month</option>
                                        <?php 
                                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                                  'July', 'August', 'September', 'October', 'November', 'December'];
                                        foreach($months as $index => $month): 
                                        ?>
                                            <option value="<?php echo $index + 1; ?>" <?php echo $user['birth_month'] == ($index + 1) ? 'selected' : ''; ?>>
                                                <?php echo $month; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <select name="birth_year" class="date-select">
                                        <option value="">Year</option>
                                        <?php for($i = date('Y'); $i >= 1950; $i--): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $user['birth_year'] == $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="save-btn">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-content" id="history">
                <div class="coming-soon">
                    <h3>Purchase History</h3>
                    <p>This section is coming soon...</p>
                </div>
            </div>

            <div class="tab-content" id="products">
                <div class="coming-soon">
                    <h3>Product Dashboard</h3>
                    <p>This section is coming soon...</p>
                </div>
            </div>

            <div class="tab-content" id="dashboard">
                <div class="coming-soon">
                    <h3>Dashboard</h3>
                    <p>This section is coming soon...</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabBtns.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active');
                    document.getElementById(tabName).classList.add('active');
                });
            });

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