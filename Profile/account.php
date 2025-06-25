<?php
session_start();
require '../inc/connect.php';

if (!isset($_SESSION['User_ID'])) {
    header('Location: ../Auth/login.php');
    exit();
}

$user_id = $_SESSION['User_ID'];
$sql = "SELECT * FROM user WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$is_seller = ($user['Affiliate'] === 'Seller');

// Get store ID for the current user
$store_id = null;
if ($is_seller) {
    // Get store ID from the user table for the current user
    $store_id = null;
    $sql = "SELECT Store_ID FROM user WHERE User_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userStore = $result->fetch_assoc();
    $store_id = $userStore['Store_ID'] ?? null;
}

// Get dashboard metrics if seller has a store
$metrics = [
    'total_sales' => 0,
    'total_profit' => 0,
    'clean_profit' => 0,
    'monthly_sales' => [],
    'rating_distribution' => [0, 0, 0, 0, 0]
];

if ($store_id) {
    // Get total sales
    $sql = "SELECT COUNT(*) AS total_sales 
            FROM item_order io
            JOIN item i ON io.Item_ID = i.Item_ID
            WHERE i.Store_ID = ? AND io.Status = 'Done'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $metrics['total_sales'] = $row['total_sales'] ?? 0;

    // Get total profit
   $sql = "SELECT SUM(io.Total_Price) AS total
        FROM item_order io
        JOIN item i ON io.Item_ID = i.Item_ID
        WHERE i.Store_ID = ? AND io.Status = 'Done'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_profit = $row['total'] ?? 0;
    $metrics['total_profit'] = number_format($total_profit, 2);
    $metrics['clean_profit'] = number_format($total_profit * 0.98, 2); // 2% fees

    // Get monthly sales data
    $sql = "SELECT MONTH(o.Order_Date) AS month, 
        SUM(io.Total_Price) AS total
        FROM item_order io
        JOIN item i ON io.Item_ID = i.Item_ID
        JOIN ordertable o ON io.Order_ID = o.Order_ID
        WHERE i.Store_ID = ? 
          AND io.Status = 'Done' 
          AND YEAR(o.Order_Date) = YEAR(CURDATE())
        GROUP BY MONTH(o.Order_Date)
        ORDER BY month";
        
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Initialize monthly data
    $monthly_data = array_fill(0, 12, 0);
    while ($row = $result->fetch_assoc()) {
        $month_index = $row['month'] - 1; // Convert to 0-based index
        $monthly_data[$month_index] = $row['total'];
    }
    $metrics['monthly_sales'] = $monthly_data;

    // Get rating distribution
    $sql = "SELECT r.Rating, COUNT(*) AS count
            FROM review r
            JOIN item i ON r.Item_ID = i.Item_ID
            WHERE i.Store_ID = ?
            GROUP BY r.Rating";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $rating = intval($row['Rating']);
        if ($rating >= 1 && $rating <= 5) {
            $metrics['rating_distribution'][$rating - 1] = $row['count'];
        }
    }
    //get all items
    $seller_items = [];
    $sql = "SELECT Item_ID, Name AS item_name, Picture, Quantity, Price 
            FROM item 
            WHERE User_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['User_ID']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $seller_items[] = $row;
    }
    //get item that has order
    $sql = "
    SELECT io.*, i.Name AS ItemName, i.Picture, o.Order_Date
    FROM item_order io
    JOIN item i ON io.Item_ID = i.Item_ID
    JOIN ordertable o ON o.Order_ID = io.Order_ID
    WHERE i.User_ID = ?
    ORDER BY io.Order_ID DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultItemOrder = $stmt->get_result();

}

// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    // Build dynamic SQL parts
    $updates = [];
    $params = [];
    $types = '';

    if (!empty($_POST['name'])) {
        $user['name'] = $_POST['name'];
        $updates[] = "Name = ?";
        $params[] = $user['name'];
        $types .= 's';
    }

    if (!empty($_POST['password'])) {
        if (!empty($_POST['old_password']) && password_verify($_POST['old_password'], $user['Password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $updates[] = "Password = ?";
            $params[] = $hashedPassword;
            $types .= 's';
        } else {
            $_SESSION['error_message'] = "Incorrect old password!";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Handle image upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $filename = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
        $targetPath = "uploads/" . $filename;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
            $user['Picture'] = $filename;
            $updates[] = "Picture = ?";
            $params[] = $user['Picture'];
            $types .= 's';
        }
    }

    // Only run update if there's something to update
    if (!empty($updates)) {
        $sql = "UPDATE user SET " . implode(', ', $updates) . " WHERE User_ID = ?";
        $params[] = $_SESSION['User_ID'];
        $types .= 's';

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    $_SESSION['success_message'] = "Profil Updated!";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['mark_sent'])) {
    $stmt = $conn->prepare("UPDATE item_order SET Status = 'Sent', Sent_Date = NOW() WHERE Order_ID = ? AND Item_ID = ?");
    $stmt->bind_param("ii", $_POST['order_id'], $_POST['item_id']);
    $stmt->execute();
}

if (isset($_POST['approve_cancel'])) {
    $stmt = $conn->prepare("DELETE FROM item_order WHERE Order_ID = ? AND Item_ID = ?");
    $stmt->bind_param("ii", $_POST['order_id'], $_POST['item_id']);
    $stmt->execute();
}


// Handle order actions
if (isset($_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    $item_id = intval($_POST['item_id']);

    if ($_POST['action'] === 'confirm') {
        // 1. Update item_order to Done
        $sql = "UPDATE item_order SET Status = 'Done' WHERE Order_ID = ? AND Item_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $order_id, $item_id);
        $stmt->execute();

        // 2. Get the quantity from the item_order
        $stmt = $conn->prepare("SELECT Quantity FROM item_order WHERE Order_ID = ? AND Item_ID = ?");
        $stmt->bind_param("ii", $order_id, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $purchasedQty = intval($row['Quantity']);

        // 3. Decrease item Quantity and increase Total_Sold
        $stmt = $conn->prepare("UPDATE item SET Quantity = Quantity - ?, Item_Sold = Item_Sold + ? WHERE Item_ID = ?");
        $stmt->bind_param("iii", $purchasedQty, $purchasedQty, $item_id);
        $stmt->execute();

        $_SESSION['success_message'] = "Order confirmed and item quantity updated!";
    }
    elseif ($_POST['action'] === 'cancel') {
        $sql = "UPDATE item_order SET Cancel_Request = 'Pending' WHERE Order_ID = ? AND Item_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $order_id, $item_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Cancel request sent!";
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch purchase history
$purchase_history = [];
$sql = "SELECT o.Order_ID, o.Order_Date, i.Item_ID, i.Name AS item_name, i.Picture, 
       io.Quantity, io.Total_Price, io.Status,
       CASE WHEN r.Review_ID IS NOT NULL THEN 1 ELSE 0 END AS HasReview
        FROM ordertable o
        JOIN item_order io ON o.Order_ID = io.Order_ID
        JOIN item i ON io.Item_ID = i.Item_ID
        LEFT JOIN review r ON r.Item_ID = i.Item_ID 
                        AND r.User_ID = o.User_ID 
                        AND r.Order_ID = o.Order_ID 
        WHERE o.User_ID = ?
        ORDER BY o.Order_Date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchase_history[] = $row;
    }
}
// Auto mark 'Sent' orders as 'Done' after 7 days
$sql = "
    UPDATE item_order
    SET Status = 'Done'
    WHERE Status = 'Sent'
      AND Sent_Date IS NOT NULL
      AND Sent_Date <= DATE_SUB(NOW(), INTERVAL 7 DAY)
";

if ($conn->query($sql)) {
    if ($conn->affected_rows > 0) {
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>My Account - UTeMHub</title>
    <link rel="stylesheet" href="account.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            <a href="account.php" class="profile-icon active">👤</a>
        </div>
    </div>
</nav>

<main class="main-content">
    <div class="account-container">
        <h1 class="page-title">My Account</h1>

        <div class="account-tabs">
            <button class="tab-btn active" data-tab="profile">My Profile</button>
            <button class="tab-btn" data-tab="history">Purchase History</button>
            <?php if ($is_seller): ?>
                <button class="tab-btn" data-tab="products">Product Dashboard</button>
                <button class="tab-btn" data-tab="productUser">My Products</button>
                <button class="tab-btn" data-tab="order">Order Request</button>
            <?php endif; ?>
        </div>

        <div class="tab-content active" id="profile">
            <div class="profile-section">
                <h2 class="section-title">My Profile</h2>

                <?php if (!empty($success_message)): ?>
                    <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="error-message" id="errorMessage"><?php echo $_SESSION['error_message']; ?></div>
                <?php unset($_SESSION['error_message']); endif; ?>

                <form class="profile-form" method="POST" action="" enctype="multipart/form-data">
                    <div class="form-container">
                        <!-- NAME -->
                        <div class="form-row">
                            <label class="form-label">Name :</label>
                            <div class="form-value readonly"><?php echo htmlspecialchars($user['Name']); ?></div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Change Name :</label>
                            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
                        </div>

                        <!-- PASSWORD -->
                        <div class="form-row">
                            <label class="form-label">New Password :</label>
                            <input type="password" name="password" class="form-input" placeholder="Leave empty to keep current">
                        </div>
                        <div class="form-row">
                            <label class="form-label">Old Password :</label>
                            <input type="password" name="old_password" class="form-input" placeholder="Enter old password to change">
                        </div>

                        <!-- PROFILE PICTURE -->
                        <div class="form-row">
                            <label class="form-label">Current Profile Picture:</label>
                            <div class="form-value readonly">
                                <?php 
                                $profilePath = 'uploads/' . $user['Picture'];
                                if (!empty($user['Picture']) && file_exists($profilePath)): ?>
                                    <img src="<?php echo $profilePath; ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 0.5rem; object-fit: cover;">
                                <?php else: ?>
                                    <span>No profile picture</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <label class="form-label">Change Profile Picture:</label>
                            <input type="file" name="profile_pic" accept="image/*" class="form-input">
                        </div>

                        <?php if ($is_seller): ?>
                            <!-- SHOP NAME -->
                            <div class="form-row">
                                <label class="form-label">Shop Name :</label>
                                <input type="text" name="shop_name" class="form-input" value="<?php echo htmlspecialchars($user['Shop_Name'] ?? ''); ?>">
                            </div>

                            <!-- SHOP PHONE -->
                            <div class="form-row">
                                <label class="form-label">Shop Phone :</label>
                                <input type="text" name="shop_phone" class="form-input" value="<?php echo htmlspecialchars($user['Shop_Phone'] ?? ''); ?>">
                            </div>

                            <!-- SHOP PICTURE -->
                            <div class="form-row">
                                <label class="form-label">Shop Picture :</label>
                                <input type="file" name="shop_pic" accept="image/*" class="form-input">
                            </div>
                        <?php endif; ?>

                        <div class="form-actions">
                            <!-- Save Button (Form submission) -->
                            <button type="submit" class="save-btn">Save</button>

                            <!-- Logout Button (Redirect to logout page) -->
                            <button type="button" class="save-btn" onclick="window.location.href='../auth/logout.php'">Log Out</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-content" id="history">
            <h3>Purchase History</h3>
            <?php if (!empty($success_message)): ?>
                <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <div class="history-container">
                <?php if (empty($purchase_history)): ?>
                    <div class="no-orders">
                        <p>You haven't made any purchases yet. Start shopping to see your orders here!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($purchase_history as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <span>Order #<?php echo $order['Order_ID']; ?></span>
                                <span><?php echo date('M d, Y', strtotime($order['Order_Date'])); ?></span>
                                <span class="status <?php echo strtolower($order['Status']); ?>"><?php echo $order['Status']; ?></span>
                            </div>
                            <div class="order-content">
                                <img class="order-image" src="../product/uploads/<?php echo $order['Picture']; ?>" alt="<?php echo $order['item_name']; ?>">
                                <div class="order-details">
                                    <p><strong><?php echo $order['item_name']; ?></strong></p>
                                    <p>Quantity: <?php echo $order['Quantity']; ?></p>
                                    <p>Price: RM <?php echo number_format($order['Total_Price'], 2); ?></p>
                                    <div class="order-actions">
                                    <?php if (in_array($order['Status'], ['Pending', 'Sent'])): ?>
                                        <form method="post">
                                            <input type="hidden" name="order_id" value="<?= $order['Order_ID'] ?>">
                                            <input type="hidden" name="item_id" value="<?= $order['Item_ID'] ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn confirm">Confirm</button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="order_id" value="<?= $order['Order_ID'] ?>">
                                            <input type="hidden" name="item_id" value="<?= $order['Item_ID'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn cancel">Cancel</button>
                                        </form>
                                    <?php elseif ($order['Status'] === 'Done'): ?>
                                        <?php if ($order['HasReview'] == 0): ?>
                                            <a class="action-btn review-btn" href="../page/review.php?item_id=<?= $order['Item_ID'] ?>">
                                                Write Review
                                            </a>
                                        <?php else: ?>
                                            <span class="reviewed-status">Reviewed</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($is_seller): ?>
            <!-- Product Dashboard Tab -->
            <div class="tab-content" id="products">
                <h2 class="section-title">Product Dashboard</h2>
                
                <div class="dashboard-grid">
                    <div class="metric-card">
                        <div class="metric-value"><?php echo $metrics['total_sales']; ?></div>
                        <div class="metric-label">Number of Items Sales</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-value">RM<?php echo $metrics['total_profit']; ?></div>
                        <div class="metric-label">Total Sales</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-value">RM<?php echo $metrics['clean_profit']; ?></div>
                        <div class="metric-label">Net Profit</div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-header">
                            <h3 class="chart-title">Monthly Sales</h3>
                            <div class="chart-filters">
                            </div>
                        </div>

                        <div class="chart-flex">
                            <div class="sales-chart">
                                <canvas id="salesBarChart" width="600" height="300"></canvas>
                            </div>
                            <div class="ratings-chart">
                                <h4>Rating Distribution</h4>
                                <canvas id="ratingPieChart" width="300" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-content" id="productUser">
                <h3>Your Listed Products</h3>
                <div class="history-container">
                <?php if (empty($seller_items)): ?>
                    <div class="no-orders">
                    <p>You haven't listed any items yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($seller_items as $item): ?>
                    <div class="order-card">
                        <div class="order-header">
                        <span>Item #<?php echo $item['Item_ID']; ?></span>
                        <span class="status done">Listed</span>
                        </div>
                        <div class="order-content">
                        <img class="order-image" src="../product/uploads/<?php echo htmlspecialchars($item['Picture']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                        <div class="order-details">
                            <p><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></p>
                            <p>Quantity: <?php echo $item['Quantity']; ?></p>
                            <p>Price: RM <?php echo number_format($item['Price'], 2); ?></p>
                            <div class="order-actions">
                            <a class="action-btn review-btn" href="../product/edit_product.php?id=<?= $item['Item_ID'] ?>">Edit</a>
                            <a class="action-btn cancel" href="../product/delete_product.php?id=<?= $item['Item_ID'] ?>" onclick="return confirm('Delete this item?')">Delete</a>
                            </div>
                        </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>

            <div class="tab-content" id="order">
                <h3>Order List:</h3>
                <div class="history-container">
                    <?php if ($resultItemOrder->num_rows > 0): ?>
                        <?php while ($row = $resultItemOrder->fetch_assoc()): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <span>Order #<?= $row['Order_ID'] ?></span>
                                    <span><?= date('M d, Y', strtotime($row['Order_Date'])) ?></span>
                                    <span class="status <?= strtolower($row['Status']) ?>"><?= $row['Status'] ?></span>
                                </div>
                                <div class="order-content">
                                    <img class="order-image" src="../product/uploads/<?= htmlspecialchars($row['Picture']) ?>" alt="Item">
                                    <div class="order-details">
                                        <p><strong><?= htmlspecialchars($row['ItemName']) ?></strong></p>
                                        <p>Quantity: <?= $row['Quantity'] ?></p>
                                        <p>Total: RM <?= number_format($row['Total_Price'], 2) ?></p>
                                        <div class="order-actions">
                                            <?php if ($row['Status'] === 'Pending'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="order_id" value="<?= $row['Order_ID'] ?>">
                                                    <input type="hidden" name="item_id" value="<?= $row['Item_ID'] ?>">
                                                    <button type="submit" name="mark_sent" class="btn confirm">Sent</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($row['Status'] === 'Pending' && $row['Cancel_Request'] === 'Pending'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="order_id" value="<?= $row['Order_ID'] ?>">
                                                    <input type="hidden" name="item_id" value="<?= $row['Item_ID'] ?>">
                                                    <button type="submit" name="approve_cancel" class="btn cancel">Approve</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <p>Your items have no orders yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    let chartsInitialized = false;

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const tabName = this.getAttribute('data-tab');
            tabBtns.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(tabName).classList.add('active');

            if (tabName === 'products' && !chartsInitialized) {
                initializeCharts(); // ← Only draw charts when tab is active
                chartsInitialized = true;
            }
        });
    });

    function initializeCharts() {
        const ctx = document.getElementById('ratingPieChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['1★', '2★', '3★', '4★', '5★'],
                datasets: [{
                    label: 'Ratings',
                    data: <?php echo json_encode($metrics['rating_distribution']); ?>,
                    backgroundColor: ['#FF6384', '#FFCE56', '#36A2EB', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutCirc'
                },
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Product Review Ratings' }
                }
            }
        });

        const ctxBar = document.getElementById('salesBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                         'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Total Sales (RM)',
                    data: <?php echo json_encode(array_map('floatval', $metrics['monthly_sales'])); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutCubic',
                    delay: (context) => context.dataIndex * 100
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales (RM)'
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Optional: Trigger default tab manually if needed
    document.querySelector('.tab-btn.active')?.click();
});
</script>

</body>
</html>