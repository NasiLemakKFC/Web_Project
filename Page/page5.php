<?php
require('../inc/connect.php'); // DB connection
  $id = $_GET['id']; // Get product ID from URL
  $sql = "SELECT Name, Price, Picture, User_ID FROM item WHERE Item_ID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $product = $result->fetch_assoc();

  $userID = $product['User_ID'];
  $sql2 = "SELECT Name FROM user WHERE User_ID = ?";
  $stmt2 = $conn->prepare($sql2);
  $stmt2->bind_param("i", $userID);
  $stmt2->execute();
  $result2 = $stmt2->get_result();
  $user = $result2->fetch_assoc();

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
  <header class="navbar">
    <div class="logo">UTeMHub</div>
    <nav>
      <a href="../Page/page3.php">Home Page</a>
      <a href="../Page/page4.php">Search Item</a>
      <a href="../product/store_register.php">Apply as Seller</a>
      <a href="../Page/contact.php">Contact Us</a>
    </nav>
      <div class="profile-cart">
        <a href="../auth/logout.php"><i class="fa-regular fa-user"></i></a>
      </div>

  </header>
  
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

        <div class="stars">★★★★☆</div>
        <p>Quantity</p>
        <div class="quantity">
          <button onclick="decrementValue()">-</button>
          <input type="number" value="1" min="1" onchange="updateValue(this)" />
          <button onclick="incrementValue()">+</button>
        </div>

        <div class="action-btns">
          <button class="add-cart">Add To Cart</button>
          <button class="buy-now">Buy Now</button>
        </div>
      </div>
    </section>

    <section class="seller-info">
      <div class="shop-logo"></div>
      <div class="shop-details">
        <h2><?php echo $user['Name']; ?></h2>
        <button>Chat Seller</button>
        <button>View Shop</button>
        <p>Rating: 90%</p>
        <p>Response Rate: 88%</p>
        <p>Products: 12</p>
        <p>Response Time: 30 minutes</p>
        <p>Joined: 9 weeks ago</p>
        <p>Followers: 6.8k</p>
      </div>
    </section>

    <section class="description">
      <h3>Product Description</h3>
      <ul>
        <li>-</li>
        <li>-</li>
        <li>-</li>
      </ul>
    </section>

    <section class="ratings">
      <h3>Product Rating</h3>
      <p>4.9 out of 5</p>
      <div class="rating-filter">
        <div class="stars">★★★★☆</div>
        <button>All</button>
        <button>5 stars (6)</button>
        <button>4 stars (2)</button>
        <button>3 stars (0)</button>
        <button>2 stars (0)</button>
        <button>1 star (0)</button>
        <button>With Comment</button>
      </div>
      <div class="user-reviews">
        <div class="review">
          <div class="user-pic"></div>
          <p class="user-name">Username</p>
          <p>★★★★★</p>
          <p>comment</p>
        </div>
        <div class="review">
          <div class="user-pic"></div>
          <p class="user-name">Username</p>
          <p>★★★★★</p>
          <p>comment</p>
        </div>
      </div>
    </section>
  </main>

  <script>
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
    input.value = parseInt(input.value) + 1;
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
      }

</script>


</body>
</html>