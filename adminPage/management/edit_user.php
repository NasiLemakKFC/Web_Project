<?php
require('../../inc/connect.php');
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user SET Name = ?, Password = ? WHERE User_ID = ?");
    $stmt->bind_param("ssi", $username, $hashed, $id);

    if ($stmt->execute()) {
        header("Location: userManage.php");
        exit;
    } else {
        echo "Update failed.";
    }
}

$user = $conn->query("SELECT * FROM user WHERE User_ID = $id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<main class="main-content">
    <div class="contact-container">
        <div class="contact-form-wrapper">
            <h1 class="contact-title">Edit User: <?= htmlspecialchars($username) ?></h1>

            <form class="contact-form" method="post">
                <label>Username: <input type="text" name="username" value="<?= $user['Name'] ?>" required></label><br>
                <label>New Password: <input type="password" name="password" required></label><br>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
