<?php 
require('../../inc/connect.php'); 

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM user WHERE Role = 'user'";
if (!empty($search)) {
    $sql .= " AND Name LIKE ?";
}
$sql .= " ORDER BY User_ID ASC";

$stmt = $conn->prepare($sql);
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
  <title>User Management</title>
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
      <a href="../contact_replied.php">Message List</a>
    </div>
    <div class="nav-profile">
    <button type="button" class="save-btn" onclick="window.location.href='../../auth/logout.php'">Log Out</button>
    </div>
  </div>
</nav>

<div class="container">
  <h1>User Management</h1>
  
  <div class="search-bar">
  <form method="GET" style="display: flex; gap: 1rem;">
      <input type="text" class="search-input" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="add-user-btn">Search</button>
  </form>
</div>

  <div class="table-container">
    <h2>Users</h2>
    <table>
      <thead>
        <tr>
          <th>No.</th>
          <th>User ID</th>
          <th>Username</th>
          <th>Status</th>
          <th>Affiliate</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
        while ($user = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $count++ . "</td>";
            echo "<td>" . htmlspecialchars($user['User_ID']) . "</td>";
            echo "<td>" . htmlspecialchars($user['Name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['Status']) . "</td>";
            echo "<td class='" . ($user['Affiliate'] === 'Seller' ? 'status-active' : 'status-inactive') . "'>" . htmlspecialchars($user['Affiliate']) . "</td>";
            echo "<td class='action-buttons'>";
            echo "<a class='btn btn-edit' href='edit_user.php?id=" . $user['User_ID'] . "'>Edit</a> ";
            $toggleText = $user['Status'] === 'Active' ? 'Deactivate' : 'Activate';
            $toggleClass = $user['Status'] === 'Active' ? 'btn-delete' : 'btn-activate';
            echo "<a class='btn $toggleClass' href='user_status.php?id=" . $user['User_ID'] . "' onclick=\"return confirm('Are you sure you want to $toggleText this user?')\">$toggleText</a>";

            echo "</td>";
            echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
