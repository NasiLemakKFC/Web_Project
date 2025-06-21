<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'] ?? 0;
    $review_text = $_POST['review_text'] ?? '';
    
    // Here you would typically save to database
    $_SESSION['success_message'] = "Terima kasih atas ulasan anda!";
    
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

// Sample product data - in real application, this would come from database
$product = [
    'title' => 'Oden Kebaboom Letups',
    'image' => 'odensedap.jpg'  // Updated to match your image file
];
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Review - UTeMHub</title>
    <link rel="stylesheet" href="review.css?v=<?php echo time(); ?>">
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
                <a href="account.php" class="profile-icon">👤</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="review-container">
            <?php if (!empty($success_message)): ?>
                <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <div class="product-section">
                <div class="product-image" style="width: 250px; height: 250px;">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="product-img" style="width: 250px; height: 250px; object-fit: cover;">
                </div>
                <div class="product-info">
                    <h1 class="product-title"><?php echo $product['title']; ?></h1>
                </div>
            </div>

            <div class="review-section">
                <div class="quality-section">
                    <h2 class="section-title">Product Quality</h2>
                    <div class="star-rating" id="starRating">
                        <span class="star" data-rating="1"></span>
                        <span class="star" data-rating="2"></span>
                        <span class="star" data-rating="3"></span>
                        <span class="star" data-rating="4"></span>
                        <span class="star" data-rating="5"></span>
                    </div>
                </div>

                <form class="review-form" method="POST" action="" onsubmit="return validateForm()">
                    <input type="hidden" name="rating" id="ratingInput" value="0">
                    
                    <div class="review-text-section">
                        <h2 class="section-title">Product Review</h2>
                        <div class="review-input-container">
                            <textarea name="review_text" placeholder="Input text" rows="8" required></textarea>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="button" class="cancel-btn" onclick="history.back()">Cancel</button>
                        <button type="submit" class="submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('ratingInput');
            let currentRating = 0;

            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    currentRating = parseInt(star.getAttribute('data-rating'));
                    ratingInput.value = currentRating;
                    updateStarDisplay();
                });

                star.addEventListener('mouseenter', function() {
                    const hoverRating = parseInt(star.getAttribute('data-rating'));
                    highlightStars(hoverRating);
                });
            });

            document.getElementById('starRating').addEventListener('mouseleave', function() {
                updateStarDisplay();
            });

            function updateStarDisplay() {
                highlightStars(currentRating);
            }

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            }

            // Initialize with hollow stars - user must choose
            currentRating = 0;
            ratingInput.value = 0;
            updateStarDisplay();

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

        // Form validation function
        function validateForm() {
            const rating = document.getElementById('ratingInput').value;
            if (rating == 0) {
                alert('Sila pilih rating bintang sebelum submit!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>