<?php
require('../inc/connect.php');

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Delete image if exists
    $imgQuery = $conn->prepare("SELECT Picture FROM Categories WHERE Category_ID = ?");
    $imgQuery->bind_param("i", $delete_id);
    $imgQuery->execute();
    $imgResult = $imgQuery->get_result()->fetch_assoc();
    if (!empty($imgResult['Picture']) && file_exists("../media/" . $imgResult['Picture'])) {
        unlink("../media/" . $imgResult['Picture']);
    }    
    $imgQuery->close();

    $stmt = $conn->prepare("DELETE FROM Categories WHERE Category_ID = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: addCategory.php");
    exit;
}

// Handle add request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_category'])) {
    $new_category = trim($_POST['new_category']);
    $imageName = '';

    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../media/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $originalName = basename($_FILES['category_image']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($extension, $allowed)) {
            $imageName = uniqid('cat_') . '.' . $extension;
            move_uploaded_file($_FILES['category_image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    $stmt = $conn->prepare("INSERT INTO Categories (Category, Picture) VALUES (?, ?)");
    $stmt->bind_param("ss", $new_category, $imageName);
    $stmt->execute();
    $stmt->close();
    header("Location: addCategory.php");
    exit;
}

// Fetch categories
$categories = $conn->query("SELECT * FROM Categories ORDER BY Category_ID ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Categories</title>
  <link rel="stylesheet" href="theme.css" />
  <link rel="stylesheet" href="category.css" />
</head>
<body>
<nav class="navbar">
  <div class="nav-container">
    <div class="logo">
      <span class="logo-icon">📱</span>
      <span class="logo-text">UTeMHub</span>
    </div>
    <div class="nav-menu">
      <a href="../adminPage/Dashboard.php">Dashboard</a>
      <a href="../adminpage/management/userManage.php">User Management</a>
      <a href="../addCategory.php">Add Categories</a>
      <a href="dashboard/productdash.php">Product Dashboard</a>
      <a href="../adminpage/management/itemManage.php">Product Management</a>
      <a href="contact_replied.php">Message List</a>
    </div>
    <div class="nav-profile">
    <button type="button" class="save-btn" onclick="window.location.href='../auth/logout.php'">Log Out</button>
    </div>
  </div>
</nav>
  <div class="container">
    <h2>Category List</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Category Name</th>
          <th>Picture</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <tr>
            <td><?= $cat['Category_ID'] ?></td>
            <td><?= htmlspecialchars($cat['Category']) ?></td>
            <td>
              <?php if (!empty($cat['Picture'])): ?>
                <img class="thumb" src="../media/<?= $cat['Picture'] ?>" alt="Category">
              <?php else: ?>
                <span>No image</span>
              <?php endif; ?>
            </td>
            <td>
              <a class="btn-delete" href="?delete=<?= $cat['Category_ID'] ?>" onclick="return confirm('Delete this category?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <h3>Add New Category</h3>
    <form method="post" enctype="multipart/form-data">
      <div class="form-group">
        <input type="text" name="new_category" placeholder="Enter category name..." required>
        <input type="file" name="category_image" accept="image/*">
        <button type="submit">Add Category</button>
      </div>
    </form>
  </div>
</body>
</html>
