<?php
session_start();
require('../inc/connect.php');

$metrics = [
    'total_users' => 0,
    'total_category' => 0,
    'total_product' => 0,
    'total_orders' => 0,
];

// Total users
$result = $conn->query("SELECT COUNT(*) AS total FROM user");
$row = $result->fetch_assoc();
$metrics['total_users'] = $row['total'] ?? 0;

// Total categories
$result = $conn->query("SELECT COUNT(*) AS total FROM categories");
$row = $result->fetch_assoc();
$metrics['total_category'] = $row['total'] ?? 0;

// Total products
$result = $conn->query("SELECT COUNT(*) AS total FROM item");
$row = $result->fetch_assoc();
$metrics['total_product'] = $row['total'] ?? 0;

// Total orders
$result = $conn->query("SELECT COUNT(DISTINCT Order_ID) AS total FROM ordertable");
$row = $result->fetch_assoc();
$metrics['total_orders'] = $row['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../profile/dashboard.css">
  <link rel="stylesheet" href="theme.css">
  <title>Dashboard</title>
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <span class="logo-icon">📱</span>
            <span class="logo-text">UTeMHub</span>
        </div>
        <div class="nav-menu">
            <a href="#">Dashboard</a>
            <a href="management/userManage.php">User Management</a> 
            <a href="addCategory.php">Add Categories</a>
            <a href="dashboard/productdash.php">Product Dashboard</a>
            <a href="management/itemManage.php">Product Management</a>
        </div>
        <div class="nav-profile">
          <button type="button" class="save-btn" onclick="window.location.href='../auth/logout.php'">Log Out</button>
        </div>
    </div>
</nav>

  <div class="account-container">
    <div class="main-content">
      <h1 class="page-title">Dashboard</h1>

      <div class="dashboard-grid">
        <div class="metric-card">
          <div class="metric-value">
            <?php echo $metrics['total_users']; ?>
          </div>
          <div class="metric-label">Total Users</div>
        </div>

        <div class="metric-card">
          <div class="metric-value">
            <?php echo $metrics['total_category']; ?>
          </div>
          <div class="metric-label">Total Categories</div>
        </div>

        <div class="metric-card">
          <div class="metric-value">
            <?php echo $metrics['total_product']; ?>
          </div>
          <div class="metric-label">Total Items</div>
        </div>

    </div>
  </div>
</body>
</html>
