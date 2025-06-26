<?php
require('../../inc/connect.php');

// Get all categories and join with item stats (LEFT JOIN ensures all categories show)
$sql = "
    SELECT 
        c.Category_ID,
        c.Category AS category_name,
        COALESCE(SUM(io.Quantity), 0) AS total_sales,
        COALESCE(SUM(io.Total_Price), 0) AS total_revenue
    FROM Categories c
    LEFT JOIN item i ON c.Category_ID = i.Category_ID
    LEFT JOIN item_order io ON i.Item_ID = io.Item_ID AND io.Status = 'Done'
    GROUP BY c.Category_ID, c.Category
    ORDER BY total_sales DESC
";

$result = $conn->query($sql);

$categories = [];
$totalSales = [];
$totalAmounts = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category_name']; // Display name
    $totalSales[] = (int)$row['total_sales'];
    $totalAmounts[] = round((float)$row['total_revenue'], 2);
}
// Get top store
$sqlTopStore = "
    SELECT 
        s.Store_Name,
        u.Name AS owner_name,
        ROUND(AVG(r.Rating), 2) AS avg_rating,
        ROUND(COALESCE(SUM(io.Total_Price), 0) * 0.98, 2) AS net_profit,
        s.Picture
    FROM storetable s
    JOIN user u ON u.Store_ID = s.Store_ID
    JOIN item i ON s.Store_ID = i.Store_ID
    LEFT JOIN review r ON i.Item_ID = r.Item_ID
    LEFT JOIN item_order io ON i.Item_ID = io.Item_ID AND io.Status = 'Done'
    GROUP BY s.Store_ID
    ORDER BY avg_rating DESC
    LIMIT 1
";
$topStoreResult = $conn->query($sqlTopStore);
$topStore = $topStoreResult->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../theme.css">
    <link rel="stylesheet" href="productDash.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Document</title>
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
            <a href="../management/userManage.php">User Management</a> 
            <a href="../addCategory.php">Add Categories</a>
            <a href="#">Product Dashboard</a>
            <a href="../management/itemManage.php">Product Management</a>
            <a href="../contact_replied.php">Message List</a>
        </div>
        <div class="nav-profile">
        <button type="button" class="save-btn" onclick="window.location.href='../../auth/logout.php'">Log Out</button>
        </div>
    </nav>

    <div class="charts-wrapper">
        <!-- Revenue & Sales Chart -->
        <div class="chart-box">
            <h2>Sales & Revenue by Category</h2>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Quantity Sold Chart -->
        <div class="chart-box">
            <h2>Quantity Sold by Category</h2>
            <canvas id="quantityChart"></canvas>
        </div>

        <div class="metric-card top-store-card">
            <h3>🏆 Top Store</h3>
            <?php if ($topStore): ?>
                <img src="../../product/uploads/<?= htmlspecialchars($topStore['Picture']) ?>" alt="Store Image" style="width:100px; height:100px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
                <p><strong>Store:</strong> <?= htmlspecialchars($topStore['Store_Name']) ?></p>
                <p><strong>Owner:</strong> <?= htmlspecialchars($topStore['owner_name']) ?></p>
                <p><strong>Avg Rating:</strong> <?= $topStore['avg_rating'] ?> ⭐</p>
                <p><strong>Net Profit:</strong> RM <?= number_format($topStore['net_profit'], 2) ?></p>
            <?php else: ?>
                <p>No data available</p>
            <?php endif; ?>
        </div>

    </div>

        <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
            labels: <?= json_encode($categories) ?>,
            datasets: [
                {
                label: 'Total Revenue (RM)',
                data: <?= json_encode($totalAmounts) ?>,
                backgroundColor: 'rgba(118, 75, 162, 0.7)'
                }
            ]
            },
            options: {
            responsive: true,
            scales: {
                y: {
                beginAtZero: true,
                ticks: {
                    color: '#000'
                }
                },
                x: {
                ticks: {
                    color: '#000'
                }
                }
            },
            plugins: {
                legend: {
                labels: {
                    color: '#000'
                }
                }
            }
            }
        });
        const qtyCtx = document.getElementById('quantityChart').getContext('2d');
        const quantityChart = new Chart(qtyCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    label: 'Quantity Sold',
                    data: <?= json_encode($totalSales) ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#000' }
                    },
                    x: {
                        ticks: { color: '#000' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#000' }
                    }
                }
            }
        });
        </script>

    </div>
</body>
</html>