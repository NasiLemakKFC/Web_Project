<?php
session_start();
require('../inc/connect.php');

// Ensure user is admin
$sql = "SELECT Affiliate FROM user WHERE User_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['User_ID']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle reply and delete
$action_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply'], $_POST['msg_id'])) {
        $reply = trim($_POST['reply']);
        $msg_id = intval($_POST['msg_id']);

        $stmt = $conn->prepare("UPDATE contact_messages SET Reply = ?, Status = 'Replied' WHERE Message_ID = ?");
        $stmt->bind_param("si", $reply, $msg_id);
        if ($stmt->execute()) {
            $action_message = "Reply sent successfully.";
        } else {
            $action_message = "Failed to send reply.";
        }
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE Message_ID = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $action_message = "Message deleted successfully.";
        } else {
            $action_message = "Failed to delete message.";
        }
    }
    $_SESSION['action_message'] = $action_message;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Search filter
$search = $_GET['search'] ?? '';
$searchQuery = '';
if (!empty($search)) {
    $search = "%" . $conn->real_escape_string($search) . "%";
    $searchQuery = "AND (u.Name LIKE '$search' OR DATE(m.Created_At) = '$search')";
}

// Fetch only pending messages
$result = $conn->query("SELECT m.*, u.Name AS UserName FROM contact_messages m JOIN user u ON m.User_ID = u.User_ID WHERE m.Status = 'Pending' $searchQuery ORDER BY m.Created_At DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Contact Messages</title>
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="contact_replied.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <span class="logo-icon">📱</span>
            <span class="logo-text">UTeMHub</span>
        </div>
        <div class="nav-menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="management/userManage.php">User Management</a>
            <a href="addCategory.php">Add Categories</a>
            <a href="dashboard/productdash.php">Product Dashboard</a>
            <a href="management/itemManage.php">Product Management</a>
            <a href="#" class="active">Message List</a>
        </div>
        <div class="nav-profile">
            <button type="button" class="save-btn" onclick="window.location.href='../auth/logout.php'">Log Out</button>
        </div>
    </div>
</nav>

<main class="message-main">
    <h2>Pending Contact Messages</h2>

    <?php if (isset($_SESSION['action_message'])): ?>
        <div class="alert-message">
            <?= htmlspecialchars($_SESSION['action_message']) ?>
        </div>
        <?php unset($_SESSION['action_message']); ?>
    <?php endif; ?>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search by name or date (YYYY-MM-DD)" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit">Search</button>
    </form>

    <div class="message-grid">
        <?php while ($msg = $result->fetch_assoc()): ?>
            <div class="message-card message-summary" data-msg-id="<?= $msg['Message_ID'] ?>">
                <p><strong>#<?= $msg['Message_ID'] ?></strong><br><?= date('M d, Y', strtotime($msg['Created_At'])) ?></p>
            </div>

            <div class="modal" id="modal-<?= $msg['Message_ID'] ?>">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal(<?= $msg['Message_ID'] ?>)">&times;</span>
                    <p><strong>User:</strong> <?= htmlspecialchars($msg['UserName']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($msg['Email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($msg['Phone']) ?></p>
                    <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($msg['Message'])) ?></p>
                    <form method="POST" class="reply-form">
                        <textarea name="reply" placeholder="Write your reply..." required></textarea>
                        <input type="hidden" name="msg_id" value="<?= $msg['Message_ID'] ?>">
                        <button type="submit" class="reply-btn">Send Reply</button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="delete_id" value="<?= $msg['Message_ID'] ?>">
                        <button type="submit" class="delete-btn">Delete Message</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<script>
    const summaries = document.querySelectorAll('.message-summary');
    summaries.forEach(summary => {
        summary.addEventListener('click', () => {
            const id = summary.getAttribute('data-msg-id');
            document.getElementById('modal-' + id).style.display = 'flex';
        });
    });

    function closeModal(id) {
        document.getElementById('modal-' + id).style.display = 'none';
    }
</script>
</body>
</html>
 