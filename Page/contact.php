<?php
session_start();
require('../inc/connect.php');

$user_id = $_SESSION['User_ID'] ?? null;

// Fetch user role
$sqluser = "SELECT Affiliate FROM user WHERE User_ID = ?";
$stmt = $conn->prepare($sqluser);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check existing message (not done)
$msgCheck = $conn->prepare("SELECT * FROM contact_messages WHERE User_ID = ? AND Status != 'Done'");
$msgCheck->bind_param("i", $user_id);
$msgCheck->execute();
$msgResult = $msgCheck->get_result();
$existingMsg = $msgResult->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Delete message
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE Message_ID = ? AND User_ID = ?");
        $stmt->bind_param("ii", $_POST['message_id'], $user_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Message deleted.";
        header("Location: contact.php");
        exit();
    } elseif (isset($_POST['done'])) {
        // Mark as done
        $stmt = $conn->prepare("UPDATE contact_messages SET Status = 'Done' WHERE Message_ID = ? AND User_ID = ?");
        $stmt->bind_param("ii", $_POST['message_id'], $user_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Conversation marked as done.";
        header("Location: contact.php");
        exit();
    } elseif (isset($_POST['update'])) {
        // Update message
        $stmt = $conn->prepare("UPDATE contact_messages SET Name=?, Email=?, Phone=?, Message=? WHERE Message_ID=? AND User_ID=?");
        $stmt->bind_param("ssssii", $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['message'], $_POST['message_id'], $user_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Message updated.";
        header("Location: contact.php");
        exit();
    } else {
        // New message
        $stmt = $conn->prepare("INSERT INTO contact_messages (User_ID, Name, Email, Phone, Message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['message']);
        $stmt->execute();
        $_SESSION['success_message'] = "Message sent successfully!";
        header("Location: contact.php");
        exit();
    }
}

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - UTeMHub</title>
    <link rel="stylesheet" href="contact.css">
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
            <?php if ($user['Affiliate'] === "Buyer"): ?>
                <a href="../product/store_register.php">Apply as Seller</a>
            <?php else: ?>
                <a href="../product/page10.php">Add Product</a>
            <?php endif; ?>
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
            <h1 class="contact-title">Contact Us</h1>

            <?php if ($success_message): ?>
                <div class="success-message" id="successMessage"><?= $success_message ?></div>
            <?php endif; ?>

            <?php if ($existingMsg): ?>
                <div class="message-box">
                    <p><strong>Name:</strong> <?= htmlspecialchars($existingMsg['Name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($existingMsg['Email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($existingMsg['Phone']) ?></p>
                    <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($existingMsg['Message'])) ?></p>
                    <p><strong>Status:</strong> <?= $existingMsg['Status'] ?></p>

                    <?php if ($existingMsg['Status'] === 'Pending'): ?>
                        <form method="POST">
                            <input type="hidden" name="message_id" value="<?= $existingMsg['Message_ID'] ?>">
                            <input type="hidden" name="name" value="<?= $existingMsg['Name'] ?>">
                            <input type="hidden" name="email" value="<?= $existingMsg['Email'] ?>">
                            <input type="hidden" name="phone" value="<?= $existingMsg['Phone'] ?>">
                            <textarea name="message"><?= $existingMsg['Message'] ?></textarea>
                            <button type="submit" name="update">Update</button>
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    <?php elseif ($existingMsg['Status'] === 'Replied'): ?>
                        <p><strong>Reply:</strong> <?= nl2br(htmlspecialchars($existingMsg['Reply'] ?? '')) ?></p>
                        <form method="POST">
                            <input type="hidden" name="message_id" value="<?= $existingMsg['Message_ID'] ?>">
                            <button type="submit" name="done">Mark as Done</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <form class="contact-form" method="POST">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Phone" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Message" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Submit</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    setTimeout(() => {
        const msg = document.getElementById("successMessage");
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.style.display = 'none', 300);
        }
    }, 5000);
</script>
</body>
</html>
