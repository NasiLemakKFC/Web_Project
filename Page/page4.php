<?php
session_start();
require('../inc/connect.php');

// Get current user's Store_ID
$store_id = null;
if (isset($_SESSION['User_ID'])) {
    $stmt = $conn->prepare("SELECT Store_ID FROM user WHERE User_ID = ?");
    $stmt->bind_param("i", $_SESSION['User_ID']);
    $stmt->execute();
    $stmt->bind_result($store_id);
    $stmt->fetch();
    $stmt->close();
}

$search = $_GET['search'] ?? '';
$rating = $_GET['rating'] ?? '';
$categories = $_GET['category'] ?? [];
$sort = $_GET['sort'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
$types = '';
$isDefaultView = true;

if ($search !== '') {
    $where .= " AND item.Name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
    $isDefaultView = false;
}

if (!empty($categories)) {
  $in = implode(',', array_fill(0, count($categories), '?'));
  $where .= " AND item.Category_ID IN ($in)";
  $params = array_merge($params, $categories);
  $types .= str_repeat('i', count($categories));
  $isDefaultView = false;
}

if (!is_null($store_id)) {
    $where .= " AND (item.Store_ID IS NULL OR item.Store_ID != ?)";
    $params[] = $store_id;
    $types .= 'i';
}

$orderBy = "ORDER BY item.Item_ID DESC";
switch ($sort) {
    case 'latest': $orderBy = "ORDER BY item.Item_ID DESC"; break;
    case 'price_asc': $orderBy = "ORDER BY item.Price ASC"; break;
    case 'price_desc': $orderBy = "ORDER BY item.Price DESC"; break;
    case 'rating': $orderBy = "ORDER BY AvgRating DESC"; break;
}

$sql = "SELECT item.*, COALESCE(AVG(review.Rating), 0) as AvgRating 
        FROM item 
        LEFT JOIN review ON item.Item_ID = review.Item_ID
        $where
        GROUP BY item.Item_ID";

if ($rating !== '') {
    $sql .= " HAVING COALESCE(AVG(review.Rating), 0) >= ?";
    $params[] = $rating;
    $types .= 's';
    $isDefaultView = false;
} else {
    $sql .= " HAVING 1=1";
}

$sql .= " $orderBy LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$productsArray = [];
while ($row = $result->fetch_assoc()) {
    $productsArray[] = $row;
}

$stmt->close();
// Count total results
$countSql = "SELECT COUNT(*) as total FROM (
  SELECT item.Item_ID 
  FROM item 
  LEFT JOIN review ON item.Item_ID = review.Item_ID
  $where
  GROUP BY item.Item_ID
  HAVING " . ($rating !== '' ? "COALESCE(AVG(review.Rating), 0) >= ?" : "1=1") . "
) AS filtered_items";

$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
  $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
if ($isDefaultView) $totalPages = max(3, $totalPages);

// Dummy items to pad grid
if ($isDefaultView && $page == $totalPages && count($productsArray) < $limit) {
  while (count($productsArray) < $limit) {
      $productsArray[] = [
          'Item_ID' => 0,
          'Picture' => 'placeholder.jpg',
          'Name' => 'Coming Soon',
          'Price' => 0.00,
          'AvgRating' => 0,
          'is_dummy' => true
      ];
  }
}

$sqluser = "SELECT Affiliate FROM user WHERE User_ID = '{$_SESSION['User_ID']}'";
$user = $conn->query($sqluser)->fetch_assoc();

$categoryResult = $conn->query("SELECT Category_ID, Category FROM Categories ORDER BY Category ASC");
$categoryList = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categoryList[] = $row; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>UTeMHub</title>
  <link rel="stylesheet" href="page4.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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

  <div class="search-bar">
    <form method="GET" style="display:flex; align-items:center; flex-wrap:wrap; gap:10px; justify-content:center; width:100%">
      <input type="text" name="search" placeholder="Search what you need..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Search</button>
      <div class="pagination-top">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $i === $page ? 'active' : '' ?>"> <?= $i ?> </a>
        <?php endfor; ?>
      </div>
    </form>
  </div>

  <main>
    <aside>
  <h3><i class="fa-solid fa-filter"></i> Search Filter</h3>
  <form method="GET">
    <h4>Rating</h4>
    <?php for ($i = 5; $i >= 0; $i--): ?>
      <label>
        <input type="radio" name="rating" value="<?= $i ?>" <?= $rating == $i ? 'checked' : '' ?>>
        <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
      </label><br>
    <?php endfor; ?>

    <h4>Type</h4>
    <?php foreach ($categoryList as $cat): ?>
      <label>
        <input type="checkbox" name="category[]" value="<?= $cat['Category_ID'] ?>" <?= in_array($cat['Category_ID'], $categories) ? 'checked' : '' ?>>
        <?= htmlspecialchars($cat['Category']) ?>
      </label><br>
    <?php endforeach; ?>

    <h4>Sort By</h4>
    <select name="sort">
      <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
      <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price Low to High</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price High to Low</option>
      <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Rating</option>
    </select><br><br>

    <button type="submit">Apply Filter</button>
    <button type="button" onclick="window.location.href='page4.php'">Reset Filter</button>
  </form>
</aside>

    <section class="products-section">
      <?php if (count($productsArray) === 0): ?>
        <p style="text-align:center;">No items found!</p>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($productsArray as $row): ?>
            <a href="<?= ($row['is_dummy'] ?? false) ? '#' : '../page/page5.php?id=' . $row['Item_ID'] ?>" class="product-card" style="<?= ($row['is_dummy'] ?? false) ? 'pointer-events:none;opacity:0.5;' : '' ?>">
              <div class="product-img">
                <img src="../product/uploads/<?= htmlspecialchars($row['Picture']) ?>" alt="Product Image" style="width:100%; height:150px; object-fit:cover;">
              </div>
              <div class="product-info">
                <h5><?= htmlspecialchars($row['Name']) ?></h5>
                <p><?= ($row['is_dummy'] ?? false) ? '' : 'RM ' . number_format($row['Price'], 2) ?></p>
                <div class="stars">
                  <?php
                  $avg = round($row['AvgRating']);
                  echo str_repeat('★', $avg) . str_repeat('☆', 5 - $avg);
                  ?>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>

