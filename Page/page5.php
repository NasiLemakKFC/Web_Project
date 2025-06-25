<?php
session_start();
require('../inc/connect.php'); // DB connection
$id = $_GET['id']; // Get product ID from URL
$sql = "SELECT * FROM item WHERE Item_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Get average rating for the item
$avgRating = 0;
$ratingQuery = $conn->prepare("SELECT AVG(Rating) as avg_rating FROM review WHERE Item_ID = ?");
$ratingQuery->bind_param("i", $id);
$ratingQuery->execute();
$ratingResult = $ratingQuery->get_result();
if ($ratingRow = $ratingResult->fetch_assoc()) {
    $avgRating = round(floatval($ratingRow['avg_rating']), 1);
}
$ratingQuery->close();

// Step 1: Get the user's store ID from user table
$userID = $product['User_ID'];
$sql1 = "SELECT Store_ID FROM user WHERE User_ID = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $userID);
$stmt1->execute();
$result1 = $stmt1->get_result();
$user = $result1->fetch_assoc();

if ($user && $user['Store_ID']) {
    $storeID = $user['Store_ID'];

    // Step 2: Use the store ID to get the store info
    $sql2 = "SELECT * FROM storetable WHERE Store_ID = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $storeID);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $shop = $result2->fetch_assoc();

    if ($shop) {
        $rawPhone = $shop['Phone_Number'];
        $phone = preg_replace('/^0/', '60', $rawPhone); // Replace leading 0 with 60
    }
}

if (isset($_POST['buy_now'])) {
    if (!isset($_SESSION['User_ID'])) {
        // Redirect to login if user not logged in
        header('Location: ../Auth/login.php');
        exit();
    }
    
    $buyer_id = $_SESSION['User_ID'];
    $quantity = intval($_POST['quantity']);
    $item_id = intval($_POST['item_id']);
    $total_price = $quantity * $product['Price'];
    
    // Check for existing pending order (status is in item_order)
    $order_id = null;
    $sql = "SELECT o.Order_ID 
            FROM ordertable o
            JOIN item_order io ON o.Order_ID = io.Order_ID
            WHERE o.User_ID = ? AND io.Status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Use existing pending order
        $row = $result->fetch_assoc();
        $order_id = $row['Order_ID'];
    } else {
        // Create new order
        $sql = "INSERT INTO ordertable (User_ID, Order_Date) 
                VALUES (?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $buyer_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;
    }
    
    // Insert into item_order bridge table
    $sql = "INSERT INTO item_order (Item_ID, Order_ID, Quantity, Total_Price, Status) 
            VALUES (?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiid", $item_id, $order_id, $quantity, $total_price);
    
    if ($stmt->execute()) {
        // Format phone number for WhatsApp (replace leading 0 with 60)
        $whatsapp_phone = preg_replace('/^0/', '60', $shop['Phone_Number']);
        
        // Create WhatsApp message with order details
        $product_name = $product['Name'];
        $message = urlencode("Hi seller, I want to buy this item:\n\n");
        $message .= urlencode("Product: $product_name\n");
        $message .= urlencode("Quantity: $quantity\n");
        $message .= urlencode("Total Price: RM " . number_format($total_price, 2) . "\n\n");
        $message .= urlencode("My Order ID: $order_id");
        
        // Redirect to WhatsApp
       $_SESSION['whatsapp_url'] = "https://wa.me/$whatsapp_phone?text=$message";
       $_SESSION['buy_success'] = "Order placed successfully!";
    } else {
        $_SESSION['buy_error'] = "Error adding item to order: " . $conn->error;
    }
}
$sqluserAffi = "SELECT Affiliate FROM user WHERE User_ID = '{$_SESSION['User_ID']}'";
$userAffi = $conn->query($sqluserAffi)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Product Page - UTeMHub</title>
  <link rel="stylesheet" href="page5.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
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
            if ($userAffi['Affiliate'] == "Buyer") {
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
  
  <main class="product-container">
    <section class="product-top">
      <div class="image-gallery">
        <div class="main-image">
          <img src="../product/uploads/<?php echo $product['Picture']; ?>" alt="Product Image">
        </div> 

        <div class="mini-buttons">
            <div class="share-btn">
              <button onclick="copyLink()">🔗 Share</button>
            </div>
          </div>
          
     </div>
  
        </div>
      </div>
      <div class="product-details">
        <h2><?php echo $product['Name']; ?></h2>
        <p class="price">RM <?php echo number_format($product['Price'], 2); ?></p>

        <div class="stars">
          <?php
          $fullStars = floor($avgRating);
          $halfStar = ($avgRating - $fullStars) >= 0.5 ? 1 : 0;
          $emptyStars = 5 - $fullStars - $halfStar;

          for ($i = 0; $i < $fullStars; $i++) {
              echo '<i class="fas fa-star" style="color: gold;"></i>';
          }
          if ($halfStar) {
              echo '<i class="fas fa-star-half-alt" style="color: gold;"></i>';
          }
          for ($i = 0; $i < $emptyStars; $i++) {
              echo '<i class="far fa-star" style="color: gold;"></i>';
          }

          echo " <span style='color: #444; font-weight: bold;'>($avgRating/5)</span>";
          ?>
      </div>
        <p>Quantity</p>
        
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['buy_success'])): ?>
            <div class="success-message"><?php echo $_SESSION['buy_success']; ?></div>
            <?php unset($_SESSION['buy_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['buy_error'])): ?>
            <div class="error-message"><?php echo $_SESSION['buy_error']; ?></div>
            <?php unset($_SESSION['buy_error']); ?>
        <?php endif; ?>
        
        <!-- Buy Now Form -->
        <form id="buyNowForm" method="post">
            <input type="hidden" name="item_id" value="<?php echo $id; ?>">
            <div class="quantity">
              <button type="button" onclick="decrementValue()">-</button>
              <input type="number" name="quantity" value="1" min="1" max="<?= $product['Quantity'] ?>" onchange="updateValue(this)" />
              <button type="button" onclick="incrementValue()">+</button>
            </div>

            <div class="action-btns">
              <!-- Buy Now Button -->
              <button type="submit" name="buy_now" class="buy-now">Buy Now</button>
            </div>
        </form>
      </div>
    </section>

    <section class="seller-info">
      <div class="shop-logo" style="border-radius: 50%; overflow: hidden;">
        <img src="../product/uploads/<?php echo $shop['Picture']; ?>" alt="shop logo" style="width:100%; height:100%; object-fit:cover;">
      </div>
      <div class="shop-details">
        <h2><?php echo $shop['Store_Name']; ?></h2>
        <!-- Chat Seller Button -->
        <a 
          class="chat-seller" 
          href="https://wa.me/<?= $phone ?>?text=Hi%20seller%2C%20I%20have%20a%20question%20about%20your%20shop." 
          target="_blank"
        >
          Chat Seller
        </a>
        <!-- <button>View Shop</button> -->
      </div>
    </section>

    <section class="description">
      <h3>Product Description</h3>
      <p><?php echo $product['Description']; ?></p>
    </section>

    <section class="ratings">
      <h3>Product Rating</h3>
      <?php
        $reviewSql = "SELECT review.*, user.Name AS UserName, user.Picture AS UserPic 
              FROM review 
              JOIN user ON review.User_ID = user.User_ID 
              WHERE review.Item_ID = ?
              ORDER BY review.Review_ID DESC";

        $reviewStmt = $conn->prepare($reviewSql);
        $reviewStmt->bind_param("i", $id);
        $reviewStmt->execute();
        $reviewResult = $reviewStmt->get_result();
        $reviews = [];
        while ($row = $reviewResult->fetch_assoc()) {
            $reviews[] = $row;
        }

      ?>
      <div class="rating-filter">
        <button data-filter="all">All</button>
        <button data-filter="5">5 stars</button>
        <button data-filter="4">4 stars</button>
        <button data-filter="3">3 stars</button>
        <button data-filter="2">2 stars</button>
        <button data-filter="1">1 star</button>
      </div>
      <div class="user-reviews">
      <?php if (empty($reviews)): ?>
        <p style="font-style: italic; color: #777;">This item has no reviews.</p>
      <?php else: ?>
        <?php foreach ($reviews as $review): ?>
          <div class="review" data-rating="<?= $review['Rating'] ?>">
            <div class="user-pic" style="width:40px; height:40px; border-radius:50%; overflow:hidden;">
              <img src="../profile/uploads/<?= htmlspecialchars($review['UserPic'] ?? 'accountdefault.png') ?>" alt="User Picture" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <p class="user-name"><?= htmlspecialchars($review['UserName']) ?></p>
            <p><?= str_repeat('★', $review['Rating']) . str_repeat('☆', 5 - $review['Rating']) ?></p>
            <p><?= htmlspecialchars($review['Comment']) ?></p>
          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['whatsapp_url'])): ?>
        window.open('<?php echo $_SESSION['whatsapp_url']; ?>', '_blank');
        <?php unset($_SESSION['whatsapp_url']); ?>
    <?php endif; ?>
});
function copyLink() {
  navigator.clipboard.writeText(window.location.href).then(() => {
    alert("Link copied to clipboard!");
  }).catch(err => {
    alert("Failed to copy link");
    console.error(err);
  });
}
    function incrementValue() {
    let input = document.querySelector(".quantity input");
    let max = parseInt(input.max);
    if (parseInt(input.value) < max) {
      input.value = parseInt(input.value) + 1;
    }
  }
  
  function decrementValue() {
    let input = document.querySelector(".quantity input");
    if (input.value > 1) {
      input.value = parseInt(input.value) - 1;
        }
      }
  function updateValue(input) {
    if (input.value < 1) {
      input.value = 1;
    }
    if (parseInt(input.value) > parseInt(input.max)) {
      input.value = input.max;
    }
  }
  const filterButtons = document.querySelectorAll('.rating-filter button');
  const reviews = document.querySelectorAll('.review');

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const rating = btn.getAttribute('data-filter');

      reviews.forEach(review => {
        if (rating === "all" || review.getAttribute('data-rating') === rating) {
          review.style.display = "block";
        } else {
          review.style.display = "none";
        }
      });

      // Optional: Highlight active button
      filterButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  document.getElementById("buyNowForm").addEventListener("submit", function (e) {
  });
</script>

</body>
</html>