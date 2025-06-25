<?php
require('../../inc/connect.php');

$search = $_GET['search'] ?? '';
$sql = "
    SELECT 
        i.*, 
        s.Store_Name, 
        c.Category AS Category_Name 
    FROM item i 
    LEFT JOIN storetable s ON i.Store_ID = s.Store_ID 
    LEFT JOIN Categories c ON i.Category_ID = c.Category_ID
";

if (!empty($search)) {
    $sql .= " WHERE i.Name LIKE ?";
}

$sql .= " ORDER BY i.Item_ID ASC";

$stmt = $conn->prepare($sql);

// Only bind parameter if search is not empty
if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("s", $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();
$count = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="../theme.css" />
  <link rel="stylesheet" href="user.css" />
  <title>Item Management</title>
</head>
<body>

<nav class="navbar">
  <div class="nav-container">
    <div class="logo">
      <span class="logo-icon">📱</span>
      <span class="logo-text">UTeMHub</span>
    </div>
    <div class="nav-menu">
      <a href="../Dashboard.php">Dashboard</a>
      <a href="userManage.php">User Management</a>
      <a href="../addCategory.php">Add Categories</a>
      <a href="../dashboard/productdash.php">Product Dashboard</a>
      <a href="itemManage.php">Product Management</a>
    </div>
    <div class="nav-profile">
            
        </div>
  </div>
</nav>  

<div class="container">
  <h1>Item Management</h1>

  <div class="search-bar">
    <form method="GET" style="display: flex; gap: 1rem;">
        <input type="text" class="search-input" name="search" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="add-user-btn">Search</button>
    </form>
  </div>

  <div class="table-container">
    <h2>Items</h2>
    <table>
      <thead>
        <tr>
          <th>No.</th>
          <th>Item ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price (RM)</th>
          <th>Quantity</th>
          <th>Store</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($item = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $count++ ?></td>
          <td><?= htmlspecialchars($item['Item_ID']) ?></td>
          <td><?= htmlspecialchars($item['Name']) ?></td>
          <td><?= htmlspecialchars($item['Category_Name'] ?? '-') ?></td>
          <td><?= number_format($item['Price'], 2) ?></td>
          <td><?= htmlspecialchars($item['Quantity']) ?></td>
          <td><?= htmlspecialchars($item['Store_Name'] ?? '-') ?></td>
          <td class="action-buttons">
            <a class="btn btn-delete" href="delete_item.php?id=<?= $item['Item_ID'] ?>" onclick="return confirm('Delete this item?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
