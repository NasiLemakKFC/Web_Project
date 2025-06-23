<?php
session_start();
require('../inc/connect.php');

// --- FILTER HANDLING ---
$search = $_GET['search'] ?? '';
$rating = $_GET['rating'] ?? '';
$categories = $_GET['category'] ?? [];
$sort = $_GET['sort'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
$isDefaultView = true; // Track if we're in default view

if ($search !== '') {
    $where .= " AND item.Name LIKE ?";
    $params[] = "%$search%";
    $isDefaultView = false;
}

if (!empty($categories)) {
    $in = implode(',', array_fill(0, count($categories), '?'));
    $where .= " AND item.Category IN ($in)";
    $params = array_merge($params, $categories);
    $isDefaultView = false;
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
    $isDefaultView = false;
} else {
    $sql .= " HAVING 1=1";
}

$sql .= " $orderBy LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products = $stmt->get_result();

$productsArray = [];
while ($row = $products->fetch_assoc()) {
    $productsArray[] = $row;
}

// Dummy items for default view
if ($isDefaultView) {
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

// Count query
$countSql = "SELECT COUNT(*) as total FROM (
                SELECT item.Item_ID 
                FROM item 
                LEFT JOIN review ON item.Item_ID = review.Item_ID
                $where
                GROUP BY item.Item_ID";

if ($rating !== '') {
    $countSql .= " HAVING COALESCE(AVG(review.Rating), 0) >= ?";
} else {
    $countSql .= " HAVING 1=1";
}

$countSql .= ") AS filtered_items";
$countStmt = $conn->prepare($countSql);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

if ($isDefaultView) {
    $totalPages = max(3, $totalPages);
}

$sqluser = "SELECT Affiliate FROM user WHERE User_ID = '$_SESSION[User_ID]'";
$result = $conn->query($sqluser);
$user = $result->fetch_assoc();
$categoryResult = $conn->query("SELECT DISTINCT Category FROM item WHERE Category IS NOT NULL AND Category != ''");
$categoryList = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categoryList[] = $row['Category'];
}
?>

<!-- HTML code remains unchanged -->


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
        <input type="checkbox" name="category[]" value="<?= $cat ?>" <?= in_array($cat, $categories) ? 'checked' : '' ?>>
        <?= htmlspecialchars($cat) ?>
      </label><br>
    <?php endforeach; ?>

    <h4>Sort By</h4>
    <select name="sort">
      <option value="">Default</option>
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

