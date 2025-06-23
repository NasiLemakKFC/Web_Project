<?php
session_start();
require '../inc/connect.php';

if (!isset($_SESSION['User_ID'])) {
    header('Location: ../Auth/login.php');
    exit();
}

$user_id = $_SESSION['User_ID'];

// Get item ID from URL
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
if (!$item_id) {
    header('Location: ../profile/account.php');
    exit();
}

// Get item details
$sql = "SELECT * FROM item WHERE Item_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating (1-5 stars)";
    } else {
        // Find Order_ID where user bought the item
        $sqlOrder = "
        SELECT io.Order_ID 
        FROM item_order io
        JOIN ordertable o ON io.Order_ID = o.Order_ID
        WHERE o.User_ID = ? AND io.Item_ID = ? AND io.status = 'Done'
        LIMIT 1";
        
        $stmt = $conn->prepare($sqlOrder);
        $stmt->bind_param("ii", $user_id, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();

        if (!$order) {
            $error = "You must complete a purchase before submitting a review.";
        } else {
            $order_id = $order['Order_ID'];

            // Insert review
            $sql = "INSERT INTO review (User_ID, Item_ID, Order_ID, Rating, Comment, Review_Date) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiis", $user_id, $item_id, $order_id, $rating, $comment);

            if ($stmt->execute()) {
                $success = "Review submitted successfully!";
            } else {
                $error = "Error submitting review: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$sqluser = "SELECT Affiliate FROM user WHERE User_ID = '$user_id'";
$result = $conn->query($sqluser);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Review - UTeMHub</title>
  <link rel="stylesheet" href="review.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<section class="review">
    <h2><?php echo htmlspecialchars($item['Name']); ?></h2>
    
    <div class="image-placeholder">
        <?php if ($item['Picture']): ?>
            <img src="../product/uploads/<?php echo $item['Picture']; ?>" alt="<?php echo $item['Name']; ?>">
        <?php else: ?>
            <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
        <?php endif; ?>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success-message"><?php echo $success; ?></div>
        <script>
            setTimeout(function() {
                window.location.href = '../profile/account.php';
            }, 2000);
        </script>
    <?php endif; ?>
    
    <form method="post">
        <p>Product Quality</p>
        <div class="stars" id="starRating">
            <i class="fas fa-star star" data-rating="1"></i>
            <i class="fas fa-star star" data-rating="2"></i>
            <i class="fas fa-star star" data-rating="3"></i>
            <i class="fas fa-star star" data-rating="4"></i>
            <i class="fas fa-star star" data-rating="5"></i>
        </div>
        <input type="hidden" name="rating" id="ratingValue" value="0">
        
        <p>Product Review</p>
        <textarea name="comment" placeholder="Share your experience with this product..." required></textarea>
        
        <div class="btn-group">
            <button type="button" class="btn cancel" onclick="window.history.back();">Cancel</button>
            <button type="submit" class="btn submit">Submit Review</button>
        </div>
    </form>
</section>

<script>
    // Star rating functionality
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingValue');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.getAttribute('data-rating');
            ratingInput.value = rating;
            
            // Update star display
            stars.forEach(s => {
                if (s.getAttribute('data-rating') <= rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });
    
    // Hover effect for stars
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const hoverRating = this.getAttribute('data-rating');
            
            stars.forEach(s => {
                if (s.getAttribute('data-rating') <= hoverRating) {
                    s.classList.add('hover');
                } else {
                    s.classList.remove('hover');
                }
            });
        });
        
        star.addEventListener('mouseout', function() {
            stars.forEach(s => s.classList.remove('hover'));
        });
    });
</script>
</body>
</html>