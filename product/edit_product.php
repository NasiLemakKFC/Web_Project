<?php
session_start();
require('../inc/connect.php');

if (!isset($_GET['id'])) {
    die("No product selected.");
}

$item_id = intval($_GET['id']);

// Fetch existing item
$stmt = $conn->prepare("SELECT * FROM item WHERE Item_ID = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = intval($_POST['quantity']);
    $price = floatval($_POST['price']);

    $update = $conn->prepare("UPDATE item SET Quantity = ?, Price = ? WHERE Item_ID = ?");
    $update->bind_param("idi", $quantity, $price, $item_id);

    if ($update->execute()) {
        echo "<script>
        alert('Product updated successfully!');
        </script>";
        header("Refresh:2; url=../profile/account.php");
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}
$sqluser = "SELECT Affiliate FROM user WHERE User_ID = '$_SESSION[User_ID]'";
$result = $conn->query($sqluser);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../Page/contact.css">
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

<main class="main-content">
    <div class="contact-container">
        <div class="contact-form-wrapper">
            <h1 class="contact-title">Edit Item: <?= htmlspecialchars($item['Name']) ?></h1>

            <?php if (!empty($success_message)): ?>
                <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form class="contact-form" method="post">
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" value="<?= $item['Quantity'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (RM)</label>
                    <input type="number" step="0.01" id="price" name="price" value="<?= $item['Price'] ?>" required>
                </div>

                <button type="submit" class="submit-btn">Update</button>
            </form>
        </div>
    </div>
</main>

</body>
</html>
